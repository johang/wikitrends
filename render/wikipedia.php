<?php
define("WIKIPEDIA_USERAGENT", "Wikitrends/4.0 (johang@toolserver.org)");

include_once "cache.php";

function wikipedia_fetch_main_page($L) {
	$ch = curl_init("http://$L.wikipedia.org");

	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_ENCODING, "");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_USERAGENT, WIKIPEDIA_USERAGENT);
	curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, TRUE);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

	$data = curl_exec($ch);

	for($i = 0; $i < 5 && $data === FALSE; $i++) {
		$data = curl_exec($ch);
	}

	if($data === FALSE) {
		die("CURL error 1 (" . curl_errno($ch) . "): " . curl_error($ch) . "\n");
	}

	curl_close($ch);

	$headers = array();

	foreach(explode("\n", $data) as $n) {
		@list($name, $value) = explode(": ", trim($n), 2);

		if(!is_null($name) && !is_null($value))
			$headers[$name] = $value;
	}

	$url = parse_url($headers["Location"]);

	return substr($url["path"], strlen("/wiki/"));
}

function wikipedia_get_main_page($language) {
	$name = cache_get(
		sprintf("main:%s", $language),
		60 * 60 * 24 * 14);

	if(!$name) {
		$name = wikipedia_fetch_main_page($language);

		if($name)
			cache_put(
				sprintf("main:%s", $language),
				$name);
	}

	return $name;
}

function wikipedia_fetch_information($L, $P) {
	$ch = curl_init(sprintf(
		"http://%s.wikipedia.org/w/api.php?" .
			"action=query&" .
			"titles=%s&" .
			"redirects&" .
			"prop=info&" .
			"inprop=url&" .
			"format=php",
		$L, $P));

	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
	curl_setopt($ch, CURLOPT_ENCODING, "");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_USERAGENT, WIKIPEDIA_USERAGENT);
	curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

	$data = curl_exec($ch);

	for($i = 0; $i < 5 && $data === FALSE; $i++) {
		$data = curl_exec($ch);
	}

	if($data === FALSE) {
		die("CURL error 2 (" . curl_errno($ch) . "): " . curl_error($ch) . "\n");
	}

	curl_close($ch);

	$query = unserialize($data);

	$map = array();

	if(isset($query["query"]["normalized"])) {
		foreach($query["query"]["normalized"] as $n) {
			if($n["from"] != $n["to"])
				$map[$n["from"]] = $n["to"];
		}
	}

	if(isset($query["query"]["redirects"])) {
		foreach($query["query"]["redirects"] as $n) {
			if($n["from"] != $n["to"])
				$map[$n["from"]] = $n["to"];
		}
	}

	for($name = urldecode($P); isset($map[$name]); ) {
		$name = $map[$n = $name];
	}

	if(isset($query["query"]["pages"])) {
		foreach($query["query"]["pages"] as $id => $n) {
			if($id <= 0)
				continue;

			if(isset($n["missing"]))
				continue;

			if($n["title"] == $name) {
				return array(
					"title" => $n["title"],
					"ns" => (int) $n["ns"],
					"url" => $n["fullurl"]);
			}
		}
	}

	return array(
		"title" => NULL,
		"ns" => -1,
		"url" => NULL);
}

function wikipedia_get_information($page) {
	$data = cache_get(
		sprintf("info:%s:%s", $page["language"], $page["name"]),
		60 * 60 * 24 * 7);

	if(!$data) {
		$data = wikipedia_fetch_information(
			$page["language"],
			$page["name"]);

		if($data)
			cache_put(
				sprintf("info:%s:%s", $page["language"], $page["name"]),
				$data);
	}

	return ($data["title"] && $data["url"]) ? $data : NULL;
}

function wikipedia_fetch_abstract($L, $P) {
	// Fetch page

	$url = sprintf("http://%s.wikipedia.org/wiki/%s", $L, $P);

	$ch = curl_init($url);

	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
	curl_setopt($ch, CURLOPT_ENCODING, "");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_USERAGENT, WIKIPEDIA_USERAGENT);
	curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

	$data = curl_exec($ch);

	for($i = 0; $i < 5 && $data === FALSE; $i++) {
		$data = curl_exec($ch);
	}

	curl_close($ch);

	if($data === FALSE)
		return "";

	// Parse page

	$document = new DOMDocument("1.0", "utf-8");

	// http://www.php.net/manual/en/domdocument.loadhtml.php#74777
	$data = mb_convert_encoding($data, "HTML-ENTITIES", "utf-8"); 

	if(@!$document->loadHTML($data)) {
		echo "failed to parse html: $url\n";
		return NULL;
	}

	// Rank paragraphs

	$paragraphs = array();

	$i = 0;

	foreach($document->getElementsByTagName("p") as $p) {
		$candidate = trim(preg_replace(
			"/\[\d+\]/",
			"",
			$p->textContent));

		$b = ($p->getElementsByTagName("b")->length > 0) ? 1 : 0;

		$paragraphs[$candidate] =
			($p->getElementsByTagName("b")->length > 0) *
			pow(0.95, $i++) *
			(strlen($candidate) > 50 ? 1 : 0);
	}

	arsort($paragraphs);

	// Return the best candidate

	$candidate = @array_shift(array_keys(array_filter($paragraphs)));

	return strlen($candidate) > 255 ?
		(mb_substr($candidate, 0, 255, "utf-8") . "...") :
		$candidate;
}

function wikipedia_get_abstract($page) {
	$data = cache_get(
		sprintf("abstract:%s:%s", $page["language"], $page["name"]),
		60 * 60 * 24 * 7);

	if(!$data) {
		$data = wikipedia_fetch_abstract(
			$page["language"],
			$page["name"]);

		if($data)
			cache_put(
				sprintf("abstract:%s:%s", $page["language"], $page["name"]),
				$data);
	}

	return $data;
}

function wikipedia_fetch_backlinks($L, $P) {
	$links = array();

	$continue = NULL;

	do {
		$ch = curl_init(sprintf(
			"http://%s.wikipedia.org/w/api.php?" .
				"action=query&" .
				"list=backlinks&" .
				"bltitle=%s&" .
				"blfilterredir=nonredirects&" .
				"blnamespace=0&" .
				"bllimit=500&" .
				($continue ? "blcontinue=$continue&" : "") .
				"format=php",
			$L, $P));
	
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
		curl_setopt($ch, CURLOPT_ENCODING, "");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_USERAGENT, WIKIPEDIA_USERAGENT);
		curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
	
		$data = curl_exec($ch);

		for($i = 0; $i < 5 && $data === FALSE; $i++) {
			$data = curl_exec($ch);
		}

		if($data === FALSE) {
			die("CURL error 3 (" . curl_errno($ch) . "): " . curl_error($ch) . "\n");
		}
	
		curl_close($ch);
	
		$query = unserialize($data);

		if(isset($query["query"]["backlinks"])) {
			foreach($query["query"]["backlinks"] as $n) {
				$links[] = urlencode(
					str_replace(" ", "_", $n["title"]));
			}
		}

		if(isset($query["query-continue"]["backlinks"]["blcontinue"])) {
			$continue = $query["query-continue"]["backlinks"]["blcontinue"];
		} else {
			$continue = NULL;
		}
	} while($continue && count($links) < 2000);

	return $links;
}

function wikipedia_get_backlinks($page) {
	$data = cache_get(
		sprintf("backlinks:%s:%s", $page["language"], $page["name"]),
		60 * 60 * 24 * 7);

	if(!$data) {
		$data = wikipedia_fetch_backlinks(
			$page["language"],
			$page["name"]);

		if($data)
			cache_put(
				sprintf("backlinks:%s:%s", $page["language"], $page["name"]),
				$data);
	}

	return $data;
}

//var_dump(wikipedia_get_main_page("sv"));
//var_dump(wikipedia_fetch_information("sv", urlencode("Helsingborg")));
//var_dump(wikipedia_fetch_abstract("de", "David_Alaba"));
//var_dump(wikipedia_get_abstract(array(
//	"language" => "he",
//	"name" => "%D7%9E%D7%95%D7%91%D7%99_%D7%93%D7%99%D7%A7")));
//var_dump(wikipedia_fetch_abstract("en", "HTTP_404"));
//var_dump(wikipedia_fetch_abstract("en", "Undefined"));
//var_dump(count(wikipedia_fetch_backlinks("en", "Undefined")));
//var_dump(count(wikipedia_fetch_backlinks("de", "Albert_Einstein")));
//var_dump(wikipedia_fetch_abstract("en", "Rush_(video_games)"));
?>

#!/usr/bin/php -f
<?php
error_reporting(E_ALL);
/**
 * Stuff for the templates.
 */

// Languages with >1000 hourly views (exceptions: NONE)

$languages = array(
	"af" => "Afrikaans",
	"sq" => "Albanian",
	"ar" => "Arabic",
	"hy" => "Armenian",
	"az" => "Azeri",
	"eu" => "Basque",
	"be" => "Belarusian",
	"bn" => "Bengali",
	"bs" => "Bosnian",
	"bg" => "Bulgarian",
	"zh-yue" => "Cantonese",
	"ca" => "Catalan",
	"ceb" => "Cebuano",
	"zh" => "Chinese",
	"hr" => "Croatian",
	"cs" => "Czech",
	"da" => "Danish",
	"nl" => "Dutch",
	"arz" => "Egyptian Arabic",
	"en" => "English",
	"eo" => "Esperanto",
	"et" => "Estonian",
	"fi" => "Finnish",
	"fr" => "French",
	"gl" => "Galician",
	"ka" => "Georgian",
	"de" => "German",
	"el" => "Greek",
	"he" => "Hebrew",
	"hi" => "Hindi",
	"hu" => "Hungarian",
	"is" => "Icelandic",
	"id" => "Indonesian",
	"it" => "Italian",
	"ja" => "Japanese",
	"kk" => "Kazakh",
	"ko" => "Korean",
	"lv" => "Latvian",
	"lt" => "Lithuanian",
	"mk" => "Macedonian",
	"ms" => "Malay",
	"ml" => "Malayalam",
	"mr" => "Marathi",
	"mn" => "Mongolian",
	"no" => "Norwegian",
	"nn" => "Nynorsk",
	"fa" => "Persian",
	"pl" => "Polish",
	"pt" => "Portuguese",
	"ro" => "Romanian",
	"ru" => "Russian",
	"sr" => "Serbian",
	"sh" => "Serbo-Croatian",
	"simple" => "Simple English",
	"sk" => "Slovak",
	"sl" => "Slovene",
	"es" => "Spanish",
	"sv" => "Swedish",
	"tl" => "Tagalog",
	"ta" => "Tamil",
	"te" => "Telugu",
	"th" => "Thai",
	"tr" => "Turkish",
	"uk" => "Ukrainian",
	"ur" => "Urdu",
	"vi" => "Vietnamese"
);

$spans = array(
	24 => "Today",
	168 => "This week",
	720 => "This month"
);

$types = array(
	"up" => "Uptrends",
	"down" => "Downtrends",
	"top" => "Most visited"
);

/**
 * Get parameters.
 */

@list($span, $type, $language) = array_slice(
	explode("/", $_SERVER["argv"][1]), -3, 3);

if(!isset($languages[$language])) {
	//die("l\n");
	die();
}

if(!isset($spans[$span])) {
	//die("s\n");
	die();
}

if(!isset($types[$type])) {
	//die("t\n");
	die();
}

$destination = getenv("WIKITRENDS_HTML");

if(!$destination) {
	die("Missing WIKITRENDS_HTML variable.\n");
}

/**
 * Various "libs".
 */

include_once "cache.php";
include_once "wikipedia.php";

function get_page($page) {
	static $main = NULL;

	if(!$main) {
		if(!($main = wikipedia_get_main_page($page["language"]))) {
			die(sprintf(
				"Unable to find main page for %s.\n",
				$page["language"]));
		}
	}

	// Ignore the page "Undefined" (bad bots/browsers)
	if($page["name"] == "Undefined")
		return NULL;

	// Ignore pages with trailing slash
	if(strrchr($page["name"], "%2F") == "%2F")
		return NULL;

	// Ignore pages with hash
	if(stripos($page["name"], "%23") !== FALSE)
		return NULL;

	// Get real title and URL
	if(!($info = wikipedia_get_information($page)))
		return NULL;

	// Ignore pages not in the article namespace
	if($info["ns"] != 0)
		return NULL;

	$page["title"] = $info["title"];
	$page["url"] = $info["url"];

	// Parse real name
	$page["name"] = substr(
		parse_url($info["url"], PHP_URL_PATH),
		strlen("/wiki/"));

	// Ignore the main page
	if($main == $page["name"])
		return NULL;

	return $page;
}

/**
 * Read all candidates.
 */

$candidates = array();

foreach(file($_SERVER["argv"][1]) as $page) {
	list($language, $name, $hits1, $hits0, $score) = explode(
		" ",
		trim($page));

	$candidates[$name] = array(
		"language" => $language,
		"name" => $name,
		"hits1" => (int) $hits1,
		"hits0" => (int) $hits0,
		"score" => (float) $score,
		"increase" => 100 * ($hits1 / ($hits0 + 1) - 1)
	);
}

/**
 * Produce top 10.
 */

$pages = array();

while(count($candidates) > 0 && count($pages) < 10) {
	// Update page information and possibly filter this page
	if(!($page = get_page(array_shift($candidates))))
		continue;

	// Remove if duplicate
	if(isset($pages[$page["name"]]))
		continue;

	$page["abstract"] = wikipedia_get_abstract($page);

	$pages[$page["name"]] = $page;
}

if(count($pages) < 10) {
	//die("Less than 10 pages found in $language-$span-$type.\n");
	die();
}

/**
 * Find related pages.
 */

foreach($pages as &$page) {
	$backlinks = wikipedia_get_backlinks($page);

	$r = array_merge(
		array_intersect_key(
			$candidates,
			array_flip($backlinks)),
		array_intersect_key(
			$pages,
			array_flip($backlinks)));

	$page["related"] = array();

	while(count($r) > 0 && count($page["related"]) < 5) {
		$p = array_shift($r);

		if($type == "up" && $p["score"] < 0)
			break;
		else if($type == "down" && $p["score"] > 0)
			break;

		if(!($p = get_page($p)))
			continue;

		$page["related"][$p["name"]] = $p;
	}
}

/**
 * Cache statistics.
 */

/*
printf("%s/%d/%s: %d hits, %d misses (expiry), %d misses (not found)\n",
	$language,
	$span,
	$type,
	$cache_hits,
	$cache_misses_expiry,
	$cache_misses_notfound);
*/

/**
 * Render page.
 */

ob_start();
ob_clean();

include "template-render.php";

file_put_contents(
	sprintf(
		"%s/%s-%s-%s.html",
		$destination,
		strtolower($languages[$language]),
		strtolower(str_replace(" ", "-", $types[$type])),
		strtolower(str_replace(" ", "-", $spans[$span]))),
	ob_get_contents());

ob_end_clean();
?>

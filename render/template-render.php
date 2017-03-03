<?php echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>"; ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en"<?php if(in_array($language, array("ur", "ar", "he", "fa"))) echo " dir=\"rtl\""; ?>>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Wikitrends - <?php echo $types[$type]; ?> on <?php echo $languages[$language]; ?> Wikipedia <?php echo strtolower($spans[$span]); ?></title>
		<style type="text/css">
			body {
				max-width: 900px;
				margin: 30px auto;
				font-family: sans-serif;
			}

			a {
				color: black;
			}

			h1 {
				margin: 15px 0;
				font-size: 18pt;
				font-weight: bold;
			}

			h2 {
				font-size: 13pt;
			}

			h3 {
				display: inline;
				font-size: 12pt;
			}

			h3 a {
				text-decoration: none;
			}

			div#body {
				margin: 15px 0;
			}

			div#options {
				float: left;
				font-size: 11pt;
			}

			div#topics {
				margin-left: 175px;
				font-size: 11pt;
			}

			div#topics li {
				margin: 15px 0;
			}

			div.subtopics, div.summary {
				margin: 5px 0;
				font-size: small;
				line-height: 150%;
			}

			.green {
				color: green;
				font-weight: bold;
			}

			.red {
				color: red;
				font-weight: bold;
			}
		</style>
	</head>
	<body>
		<h1>
			Wikitrends<br />
			<small>Trends on Wikipedia</small>
		</h1>
		<hr />
		<div id="body">
			<div id="options">
				<strong>Type</strong>
				<ul>
<?php
foreach($types as $Tk => $T) {
	if($Tk == $type) {
?>
					<li><strong><?php echo $T; ?></strong></li>
<?php
	} else {
?>
					<li><a href="<?php echo sprintf(
						"%s-%s-%s.html",
						strtolower($languages[$language]),
						strtolower(str_replace(" ", "-", $T)),
						strtolower(str_replace(" ", "-", $spans[$span]))); ?>"><?php echo $T; ?></a></li>
<?php
	}
}
?>
				</ul>
				<strong>Time span</strong>
				<ul>
<?php
foreach($spans as $Sk => $S) {
	if($Sk == $span) {
?>
					<li><strong><?php echo $S; ?></strong></li>
<?php
	} else {
?>
					<li><a href="<?php echo sprintf(
						"%s-%s-%s.html",
						strtolower($languages[$language]),
						strtolower(str_replace(" ", "-", $types[$type])),
						strtolower(str_replace(" ", "-", $S))); ?>"><?php echo $S; ?></a></li>
<?php
	}
}
?>
				</ul>
				<strong>Language</strong>
				<ul>
<?php
foreach($languages as $Lk => $L) {
	if($Lk == $language) {
?>
					<li><strong><?php echo $L; ?></strong></li>
<?php
	} else {
?>
					<li><a href="<?php echo sprintf(
						"%s-%s-%s.html",
						strtolower($L),
						strtolower(str_replace(" ", "-", $types[$type])),
						strtolower(str_replace(" ", "-", $spans[$span]))); ?>"><?php echo $L; ?></a></li>
<?php
	}
}
?>
				</ul>
			</div>
			<div id="topics">
				<!--
				<p style="border: 1px solid; padding: 5px;"><a href="http://tools.wmflabs.org/wikitrends/2013.html"><strong>NEW!</strong> Check out the most visited pages in 2013!</a></p>
				-->
				<h2><?php echo $types[$type]; ?> on <?php echo $languages[$language]; ?> Wikipedia <?php echo strtolower($spans[$span]); ?></h2>
				<ol>
<?php
foreach($pages as $P)
{
?>
					<li>
						<h3>
							<a href="http://<?php echo $language; ?>.wikipedia.org/wiki/<?php echo $P["name"]; ?>"><?php echo str_replace("_", " ", htmlspecialchars(urldecode($P["name"]))); ?></a>
<?php
	if($type == "up" || $type == "down") {
?>
							<span dir="ltr" class="<?php echo ($P["score"] >= 0) ? "green" : "red"; ?>">(<?php echo ($P["increase"] >= 0 ? "+" : "") . str_replace(' ', '&nbsp;', number_format($P["increase"], $P["score"] >= 0 ? 0 : 2, ".", " ")); ?>%)</span>
<?php
	} else if($type == "top") {
?>
							<span dir="ltr" class="green">(<?php echo str_replace(' ', '&nbsp;', number_format($P["hits1"], 0, ".", " ")); ?> views)</span>
<?php
	}
?>
						</h3>
						<div class="summary"><?php echo $P["abstract"]; ?></div>
						<div class="subtopics">
<?php
	if(count($P["related"]) > 0)
	{
?>
							<strong>Related pages:</strong>
<?php
		$n0 = 0;
		$n1 = count($P["related"]);

		foreach($P["related"] as $K => $R)
		{
			$n0++;

			if($type == "top") {
?>
							<a href="http://<?php echo $language; ?>.wikipedia.org/wiki/<?php echo $R["name"]; ?>"><?php echo str_replace("_", " ", htmlspecialchars(urldecode($R["name"]))); ?></a>&nbsp;<span dir="ltr" class="green">(<?php echo str_replace(' ', '&nbsp;', number_format($R["hits1"], 0, ".", " ")); ?> views)</span><?php if($n0 != $n1) echo ","; ?>

<?php
			} else {
?>
							<a href="http://<?php echo $language; ?>.wikipedia.org/wiki/<?php echo $R["name"]; ?>"><?php echo str_replace("_", " ", htmlspecialchars(urldecode($R["name"]))); ?></a>&nbsp;<span dir="ltr" class="<?php echo ($R["score"] >= 0) ? "green" : "red"; ?>">(<?php echo ($R["score"] >= 0 ? "+" : "") . str_replace(' ', '&nbsp;', number_format($R["increase"], 0, ".", " ")); ?>%)</span><?php if($n0 != $n1) echo ","; ?>

<?php
			}
		}
	}
?>
						</div>
					</li>
<?php
}
?>
				</ol>
				<hr />
				<p><small><i>Copyright &copy; <a href="http://johan.gunnarsson.name/">Johan Gunnarsson</a> (johan.gunnarsson@gmail.com), 2012. Last updated <?php echo date("r"); ?>. <a href="">About Wikitrends</a>.</i></small></p>
				<p style="text-align: center;"><a rel="license" href="http://creativecommons.org/licenses/by/3.0/"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by/3.0/88x31.png" /></a><br /><span xmlns:dct="http://purl.org/dc/terms/" property="dct:title">Wikitrends</span> by <a xmlns:cc="http://creativecommons.org/ns#" href="http://toolserver.org/~johang/wikitrends" property="cc:attributionName" rel="cc:attributionURL">Johan Gunnarsson</a> is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by/3.0/">Creative Commons Attribution 3.0 Unported License</a>.</p>
			</div>
		</div>
	</body>
</html>

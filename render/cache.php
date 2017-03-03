<?php
$my = parse_ini_file("/data/project/wikitrends/replica.my.cnf");

define("CACHE_DB_HOST", "tools-db");
define("CACHE_DB_NAME", $my["user"] . "__cache");
define("CACHE_DB_USER", $my["user"]);
define("CACHE_DB_PASSWORD", $my["password"]);

$cache_hits = 0;
$cache_misses_by_expiry = 0;
$cache_misses_by_missing = 0;

$db = NULL;

function _cache_connect() {
	$db = mysqli_connect(
		CACHE_DB_HOST,
		CACHE_DB_USER,
		CACHE_DB_PASSWORD,
		CACHE_DB_NAME);

	if(!$db)
		die("mysqli_connect failed\n");

	mysqli_query($db, "
		CREATE TABLE IF NOT EXISTS cache (
			k CHAR(32) PRIMARY KEY,
			v MEDIUMBLOB,
			t TIMESTAMP);");

	return $db;
}

function cache_put($key, $value) {
	global $db;

	if(!$db)
		$db = _cache_connect();

	$h = mysqli_query($db, sprintf("
		REPLACE INTO cache
		VALUES ('%s', '%s', NOW());",
		md5($key),
		mysqli_escape_string($db, serialize($value))));
}

function cache_get($key, $age) {
	global $db;

	if(!$db)
		$db = _cache_connect();

	$r = mysqli_query($db, sprintf("
		SELECT v
		FROM cache
		WHERE k = '%s' AND t > DATE_SUB(NOW(), INTERVAL %d SECOND);",
		md5($key),
		$age));

	if($r) {
		$x = mysqli_fetch_assoc($r);

		if($x)
			return unserialize($x["v"]);
	}

	return NULL;
}

//var_dump(cache_put("ab|sv|Google", "TEST"));
//var_dump(cache_get("ab|sv|Google", 100));
//var_dump(cache_get("ab|sv|Google", 0));
?>

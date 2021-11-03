<?php
$url = array();

$url[1] = "http://test.foodconnection.jp/blog2/feed/";



/*********************************************************************************************
 * Script: rss.php
 * Package: RSS
 * Version: 0.3
 *
 * -------------------- DON'T EDIT BELOW IF YOU DON'T UNDERSTAND ABOUT IT --------------------
 *
 ********************************************************************************************/



$key = isset($_POST["key"]) ? $_POST["key"] : FALSE;

if (isset($url)) {
	if (is_array($url) && count($url) > 0) {
		if ($key && isset($url[$key]) && strlen($url[$key]) > 0) $fetchURL = $url[$key];
		else {
			$arrs = array_values($url);
			$first = array_shift($arrs);

			if (strlen($first) > 0) $fetchURL = $first;
		}
	} elseif (is_string($url) && strlen($url) > 0) $fetchURL = $url;
}

$xml = NULL;
if (isset($fetchURL) && strlen($fetchURL) > 0) $xml = file_get_contents($fetchURL);
echo $xml;
?>
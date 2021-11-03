<?php
/*
 * FCMailer - 2017
 *
 * ScriptName: release.php
 *
 * PackageName: FCMailer
 * Version: 3.5
 *
 * FoodConnection
 * http://foodconnection.jp/
 * http://foodconnection.vn/
 *
 * -------------------------
 *
 * DON'T CHANGE IT IF YOU DOES NOT UNDERSTAND !!!
 *
 */

if (!defined("ALO")) exit("No direct script access allowed");

if ($_GET["release"] && strlen($_GET["release"]) > 0 && preg_match("/^(.*?)\s*\b((?:\d+(?:\.\d+)*)?)$/", $_GET["release"])) {
	$version = "v".preg_replace("/^v/i", "", $_GET["release"]);

	$path = array(
		"core" => "./",
		"loader" => "loader/",
		"manage" => "FCMailer/"
	);

	$arrsCore = array(
		".htaccess",
		"index.php",
		"PHPMailer" => array(
			"class.phpmailer.php",
			"class.phpmaileroauth.php",
			"class.phpmaileroauthgoogle.php",
			"class.pop3.php",
			"class.smtp.php",
			"get_oauth_token.php",
			"PHPMailerAutoload.php",
		)
	);
	$arrsLoader = array(
		"autoload.php",
		"BrowserDetection.class.php",
		"FCMailer.php",
		"functions.php",
		"mailRender.php",
		"manage.php",
		"release.php",
		"SQLite.class.php",
		"",
	);
	$arrsManage = array(
		"login.php",
		"header.php",
		"footer.php",
		"home.php",
		"error.php",
		"assets" => array(
			"img" => array(
				"logo.png"
			),
			"css" => array(
				"reset.css",
				"error.css",
				"main.css",
				"main.responsive.css"
			),
			"js" => array(
				"jquery.min.js",
				"main.js"
			)
		)
	);

	$checksumsCore = _checksums($arrsCore, $path["core"]);
	$checksumsLoader = _checksums($arrsLoader, $path["loader"]);
	$checksumsManage = _checksums($arrsManage, $path["manage"]);

	$checksums = array(
		"date" => date("Y-m-d"),
		"time" => date("H:i:s"),
		"version" => $version,
		"hash" => array(
			"core" => $checksumsCore,
			"loader" => $checksumsLoader,
			"manage" => $checksumsManage
		)
	);

	$component = preg_replace("/\/+$/s", "", $path["loader"])."/component-".$version.".checksums";
	if (file_exists($component)) _error("Error", "Checksums component existed!");
	else {
		$handle = fopen($component, "a");
		fwrite($handle, json_encode($checksums));
		fclose($handle);
	}

	print_r($checksums);
} else _error();
?>
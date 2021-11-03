<?php
/*
 * FCMailer - 2017
 *
 * ScriptName: autoload.php
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


// path
$dirData = "@data/";
$dirHTML = "html/";
$localFile = "local.txt";

define("FC_EOL", "\n");



session_start();
header ("Content-Type: text/html;charset=UTF-8");

date_default_timezone_set("Asia/Ho_Chi_Minh");




// @ini_set("display_errors", TRUE);
// error_reporting(E_ALL);

// @ini_set("max_file_uploads", 100); // maximum number of files that can be uploaded via a single request



// change permissions
if (is_dir($dirData)) @chmod($dirData, 0777);
if (is_dir($dirHTML)) @chmod($dirHTML, 0777);



// libraries
require_once("functions.php");
require_once("mailRender.php");
require_once("SQLite.class.php");
require_once("BrowserDetection.class.php");



// configs
$configPrimary = _parseConfig($dirData."email.ini", TRUE);

$configFile = "email";
if (isset($_POST["multiple"]) && strlen($_POST["multiple"]) > 0 && $_POST["multiple"] != "false" && $_POST["multiple"] != "true") $configFile .= "-".$_POST["multiple"];
if (isset($_POST["language"]) && strlen($_POST["language"]) > 0 && $_POST["language"] != "false" && $_POST["language"] != "true") $configFile .= ".".$_POST["language"];
$configFile .= ".ini";

$configExt = _parseConfig($dirData.$configFile, TRUE);

if ($configExt === FALSE) {
	if ($configPrimary === FALSE) {
		header("HTTP/1.1 204 No Content");
		echo "ERROR";
		exit;
	} else $config = $configPrimary;
} else $config = $configExt;



// only Windows - localhost and sendmail_path has been set
if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN" && strlen(ini_get("sendmail_path")) > 0 && (strtolower($_SERVER["SERVER_NAME"]) == "localhost" || $_SERVER["SERVER_NAME"] == "127.0.0.1")) {
	$config["mail"]["sendmail_path"] = ini_get("sendmail_path");

	if (isset($config["gmail"])) unset($config["gmail"]);
}



// local
$localList = array(
	"email" => array(),
	"server" => array()
);
$localFile = $dirData.$localFile;
if (file_exists($localFile)) {
	$localContent = file_get_contents($localFile);
	$localContent = _eol($localContent);

	if (strlen($localContent) > 0) {
		$localArrs = explode(FC_EOL, $localContent);
		if (count($localArrs) > 0) {
			foreach ($localArrs as $line) {
				if (strlen($line) > 0) {
					$line = trim($line);
					$line = strtolower($line);
					$line = preg_replace("/^(ftp|https?):*\/*(www\.)?/i", "", $line);
					$domain = explode("/", $line);
					if (count($domain) > 0) $line = $domain[0];

					if (preg_match("/[\._a-z0-9-]+@[\._a-z0-9-]+\.[a-z]+/i", $line) && !in_array($line, $localList["email"])) $localList["email"][] = $line;
					else if ((preg_match("/^(((([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))|((([a-z]|[a-z][a-z0-9\-]*[a-z0-9])\.)+([a-z|[a-z][a-z0-9\-]*[a-z0-9])))$/i", $line) || $line == "localhost") && !in_array($line, $localList["server"])) $localList["server"][] = $line;
				}
			}
		}
	}
}

$isLocal = in_array(strtolower($_SERVER["SERVER_NAME"]), $localList["server"]) ? $_SERVER["SERVER_NAME"] : FALSE;



// detection
$detection = new BrowserDetection();



// sql
if (isset($configPrimary["login"]["site_name"])) {
	$db = new SQLite(_trim($configPrimary["login"]["site_name"]), $dirData, $isLocal, $detection);

	if ($db->executed !== TRUE) $db = $db->executed;
} else $db = FALSE;
?>
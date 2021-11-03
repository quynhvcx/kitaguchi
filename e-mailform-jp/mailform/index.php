<?php
/*
 * FCMailer - 2017
 *
 * ScriptName: index.php
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

define("ALO", "FCV");

require_once("loader/autoload.php");

if (isset($_GET["error"])) _error($_GET["error"]); // $_SERVER["REDIRECT_STATUS"]
elseif (isset($_GET["release"])) require_once("loader/release.php");
elseif (isset($_POST) && isset($_POST["module"]) && $_POST["module"] == "FCMailer") require_once("loader/FCMailer.php");
else require_once("loader/manage.php");
?>
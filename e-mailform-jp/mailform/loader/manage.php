<?php
/*
 * FCMailer - 2017
 *
 * ScriptName: manage.php
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

if ($db && isset($configPrimary["login"]["username"]) && isset($configPrimary["login"]["password"])) {
	$FCAjax = isset($_POST) && isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) === "xmlhttprequest" ? TRUE : FALSE;
	$query = isset($_GET["fc"]) ? $_GET["fc"] : false;

	$sessionLimit = 30; /// minutes
	$sessionLimit = $sessionLimit * 60; // to seconds
	$sessionActivity = $_SERVER["SERVER_NAME"]."-activity";

	$title = strlen($db->siteName) > 0 ? $db->siteName." - ".$db->alias : $db->alias;

	if (isset($_SESSION[$sessionActivity]) && (time() - $_SESSION[$sessionActivity] > $sessionLimit)) unset($_SESSION["logged"]);

	$json = FALSE;
	switch($query) {
		case "login":
			if ($FCAjax == FALSE) _error(401);

			$json = array(
				"status" => "error",
				"messages" => "Incorrect username or password"
			);

			if (isset($_POST["username"]) && isset($_POST["password"])) {
				if ($_POST["username"] == $configPrimary["login"]["username"] && $_POST["password"] == $configPrimary["login"]["password"]) {
					$_SESSION["logged"] = $title;

					$json["status"] = "success";
					$json["messages"] = "Login success";
				}

				sleep(rand(1, 3));
			}

			break;
		case "logout":
			if ($FCAjax == FALSE) _error(401);

			$_SESSION["logged"] = FALSE;
			unset($_SESSION["logged"]);

			break;
		case "remove":
			if ($FCAjax == FALSE) _error(401);

			$json = array(
				"status" => "error",
				"messages" => "An error has occurred"
			);

			if (isset($_POST["id"]) && $_POST["id"] > 0) {
				if ($db) {
					sleep(rand(1, 2));

					$db->delete($_POST["id"]);

					$json["status"] = "success";
					$json["messages"] = "Your request processed successfully";
				}
			}

			break;
		default:
			$chdir = getcwd();
			chdir(dirname(__DIR__)); // update current working directory

			require_once("FCMailer/header.php");

			if (isset($_SESSION["logged"]) && $_SESSION["logged"] == $title) require_once("FCMailer/home.php");
			else require_once("FCMailer/login.php");

			require_once("FCMailer/footer.php");

			chdir($chdir); // re-update current working directory
	}

	$_SESSION[$sessionActivity] = time();

	if (is_array($json)) echo json_encode($json);
} else _error(401);
?>
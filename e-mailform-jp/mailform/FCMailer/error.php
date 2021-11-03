<?php
/*
 * FCMailer - 2017
 *
 * ScriptName: error.php
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
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?=$title?></title>

		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />

		<meta name="robots" content="noindex, nofollow" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />

		<base href="<?=baseURL()?>FCMailer/" />

		<link type="image/png" href="assets/img/logo.png" rel="icon" />
		<link type="image/png" href="assets/img/logo.png" rel="shortcut icon" />

		<link type="text/css" href="assets/css/reset.css" rel="stylesheet" />
		<link type="text/css" href="assets/css/error.css" rel="stylesheet" />
	</head>
	<body>
		<div id="container">
			<div class="frozen"><span></span></div>
			<div class="main">
				<div class="title"><?=$title?></div>
				<div class="description"><?=$description?></div>
			</div>
			<div class="link">Back to <a href="<?=baseURL(TRUE)?>"><?=$_SERVER["SERVER_NAME"]?></a></div>
			<div class="copyright">Copyright &copy; 2017 <a href="http://foodconnection.vn/" target="_blank">FoodConnection</a>, All rights reserved.</div>
		</div>
	</body>
</html>
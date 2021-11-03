<?php
/*
 * FCMailer - 2017
 *
 * ScriptName: login.php
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

		<div id="login">
			<div class="title"><?=$db->alias?></div>
			<div class="notice"></div>
			<div class="main">
				<div class="block"><input type="text" name="username" value="" placeholder="Username" /></div>
				<div class="block"><input type="password" name="password" value="" placeholder="Password" /></div>
				<div class="block"><button type="submit">Login</button></div>
			</div>
		</div>

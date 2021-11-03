<?php
/*
 * FCMailer - 2017
 *
 * ScriptName: home.php
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

$result = $db->select();
?>

		<div id="wrapper">
			<div id="header">
				<div class="site">
					<div class="logo"><img src="assets/img/logo.png" alt="FoodConnection" /></div>
					<div class="title">
						<div class="name"><?=strlen($db->siteName) > 0 ? $db->siteName : "FoodConnection"?></div>
						<div class="sub"><?=$db->alias?></div>
					</div>
					<div class="logout">Logout</div>
				</div>
			</div>
			<div id="container">
				<?php
				if (count($result) > 0) {
				?>
				<div class="result">
					<table>
						<thead>
							<tr>
								<th class="col-center">&nbsp;</th>
								<th class="col-center">Messages</th>
								<th class="col-center">IP Address</th>
								<th class="col-center">Browser</th>
								<th class="col-center">Platform/OS</th>
								<th class="col-center">Date</th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($result as $row) {
								$message = "";
								foreach (json_decode($row["message"], true) as $msg) {
									$message .= "<b>".$msg["name"].":</b> ".$msg["value"]."<br />".FC_EOL;
								}
								$date = explode(" ", $row["added"], 2);
								$date = implode("<br />", $date);
							?>

							<tr data="<?=$row["id"]?>">
								<td class="col-remove col-center"><span class="remove">&times;</span></td>
								<td class="col-message col-left col-click">
									<div class="messages"><?=preg_replace("/[\r|\n]+/", "", $message)?></div>
								</td>
								<td class="col-ip col-center col-click"><?=$row["ip"]?></td>
								<td class="col-browser col-center col-click"><?=$row["browser"]?></td>
								<td class="col-os col-center col-click"><?=$row["os"]?></td>
								<td class="col-date col-center col-click"><?=$date?></td>
							</tr>
							<?php
							}
							?>
						</tbody>
					</table>
				</div>
				<?php
				}
				?>
				<div class="no-data<?=(count($result) > 0 ? "" : " active")?>">No data</div>
				<div class="notice"></div>
			</div>
		</div>

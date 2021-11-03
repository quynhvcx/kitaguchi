<?php
/*
 * FCMailer - 2017
 *
 * ScriptName: footer.php
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

		<script type="text/javascript" src="assets/js/jquery.min.js"></script>
		<script type="text/javascript" src="assets/js/main.js"></script>
		<script type="text/javascript">
			var base_url = "<?=baseURL()?>",
				script_name = "<?=basename($_SERVER["SCRIPT_FILENAME"])?>";
		</script>
	</body>
</html>
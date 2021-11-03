<?php
/*
 * FCMailer - 2017
 *
 * ScriptName: FCMailer.php
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

$default = array(
	"timezone" => "Asia/Tokyo",
	"text" => array(
		"an_error_has_occurred" => "An error has occurred",
		"no_data_received" => "No Data Received",
		"success" => "Mail has been sent successfully, thanks for your messages",
		"error" => "Message sending failed, please try again",
		"view_in_browser" => "View in browser",
		"debug" => "Debugger has been loaded"
	),
);

// $_POST["data"] = '[{"key":"lastname","txt":"Lastname","value":"Sellers@gmail.com"},{"key":"firstname","txt":"Firstname","value":"Ezra@hotmail.com"},{"key":"furigana-lastname","txt":"Phonetic lastname","value":"Walter"},{"key":"furigana-firstname","txt":"Phonetic firstname","value":"Claudia"},{"key":"email","txt":"Email","value":"cataceryz@hotmail.com"},{"key":"postal_code","txt":"Postal code","value":"Natus quis rerum sed consequatur commodi Nam sunt optio amet magnam irure voluptatum veniam voluptatibus proident repudiandae distinctio"},{"key":"state","txt":"State/Province","value":"Dolorem occaecat aliqua Cillum provident labore alias dolorum provident dolor ut temporibus animi illum vel assumenda"},{"key":"address1","txt":"City","value":"42 Milton Extension"},{"key":"address2","txt":"Address","value":"Quia cum ducimus impedit sint nihil sit dolor et ab consequatur est saepe eos quo sit aut veniam odit"},{"key":"phone","txt":"Phone","value":"+531-98-1325630"},{"key":"fax","txt":"Fax","value":"+267-17-8607639"},{"key":"mobile","txt":"Mobile","value":"TEL: +632-34-8975034"},{"key":"gender","txt":"Gender","value":"Female"},{"key":"pets","txt":"Which pets do you have?","value":"Dog, Cat, Fish"},{"key":"drink","txt":"What would you like to drink?","value":"Coffee"},{"key":"messages","txt":"Messages","value":"Ut sed voluptas et dolor voluptatem enim pariatur Vel corrupti exercitation cumque veniam iusto sint\nUt sed voluptas et dolor voluptatem enim pariatur Vel corrupti exercitation cumque veniam iusto sint"},{"key":"parse-select parse-radio parse-checkbox","txt":"Parse","value":"Quality\nContact Us\nEmail"},{"key":"parse-date","txt":"Parse Date","value":"2017/11/18"},{"key":"parse-time","txt":"Parse Time","value":"09:00"},{"key":"parse-datetime","txt":"Parse Datetime","value":null}]'; // fake

// $_POST["email"] = "lastname firstname email gender";

if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) === "xmlhttprequest") {
	$GLOBALS["FCMailer-Debug"] = FALSE;

	$json["status"] = "error";
	$json["messages"] = _txt("an_error_has_occurred");

	// set timezone
	$timezone = _cfg("mail.timezone", FALSE);
	if ($timezone) date_default_timezone_set($timezone);
	else date_default_timezone_set($default["timezone"]);

	if (isset($_POST["data"]) && strlen($_POST["data"]) > 0) {
		$data = $_POST["data"];
		$data = parseJSON($data); // convert JSON to array

		$emailTest = FALSE;
		if (isset($_POST["email-test"]) && strlen($_POST["email-test"]) > 0) {
			$emailTest = $_POST["email-test"];
			$emailTest = parseJSON($emailTest); // convert JSON to array

			if (!is_array($emailTest) || count($emailTest) < 1) $emailTest = FALSE;
		}

		$debug = isset($_POST["debug"]) && strtolower($_POST["debug"]) == "true" ? TRUE : FALSE; // $isLocal
		$keep = isset($_POST["keep"]) && strtolower($_POST["keep"]) == "true" ? TRUE : FALSE;
		$language = isset($_POST["language"]) && strlen($_POST["language"]) > 0 && $_POST["language"] != "false" && $_POST["language"] != "true" ? strtolower($_POST["language"]) : FALSE;
		$email = isset($_POST["email"]) && strlen($_POST["email"]) > 0 && $_POST["email"] != "false" && $_POST["email"] != "true" ? strtolower($_POST["email"]) : "email";
		$email = count(explode(" ", $email)) > 0 ? explode(" ", $email) : FALSE;

		if (is_array($data)) {
			if (count($data) > 0) {
				// sender
				$sender["name"] = _cfg("sender.name", FALSE);
				$sender["email"] = _cfg("sender.email", FALSE);
				$sender["no-reply"] = _cfg("sender.no_reply", FALSE);

				// CC
				$sender["cc"] = _cfg("sender.cc", FALSE);
				if (is_string($sender["cc"])) {
					if (preg_match("/[,|;]/", _trim($sender["cc"]))) {
						$sender["cc"] = _trim($sender["cc"]);
						$sender["cc"] = str_replace(";", ",", $sender["cc"]);
						$sender["cc"] = strtolower($sender["cc"]);
						$sender["cc"] = explode(",", $sender["cc"]);
					} else $sender["cc"] = array(_trim($sender["cc"]));
				}

				// BCC
				$sender["bcc"] = _cfg("sender.bcc", FALSE);
				if (is_string($sender["bcc"])) {
					if (preg_match("/[,|;]/", _trim($sender["bcc"]))) {
						$sender["bcc"] = _trim($sender["bcc"]);
						$sender["bcc"] = str_replace(";", ",", $sender["bcc"]);
						$sender["bcc"] = strtolower($sender["bcc"]);
						$sender["bcc"] = explode(",", $sender["bcc"]);
					} else $sender["bcc"] = array(_trim($sender["bcc"]));
				}

				// gmail account
				$username = _cfg("gmail.username", FALSE);
				$password = _cfg("gmail.password", FALSE);
				$ssl = _cfg("gmail.ssl", FALSE);

				if ($username && $password) {
					$gmail = array(
						"username" => $username,
						"password" => $password
					);
				} else $gmail = FALSE;

				$urlThanks = _cfg("mail.url", FALSE);



				// render - owner
				$ownerPath = $dirHTML."owner/";
				$ownerData = array(
					"active" => _cfg("owner.active", FALSE),
					"html" => TRUE,

					"subject" => _cfg("owner.subject"),
					"title" => _cfg("owner.title"),
					"header" => _cfg("owner.header"),
					"footer" => _cfg("owner.footer")
				);
				$ownerConfig = array(
					"EOL" => FC_EOL,
					"debug" => $debug,
					"referer" => _cfg("owner.referer", FALSE),
					"css" => _cfg("mail.css", FALSE),
					"email" => $email,
					"cache" => _cfg("mail.cache", FALSE) ? $ownerPath : FALSE,
					"minify" => _cfg("mail.minify", FALSE),
					"break" => "———————————————"
				);
				$ownerRender = new mailRender($ownerData, $ownerConfig);
				$ownerHTML = $ownerRender->render($data, $keep);
				$ownerRender->isHTML(FALSE);
				$ownerPlain = $ownerRender->render($data, $keep);
				$storage = $ownerRender->getStorage();
				$addresses = $ownerRender->getEmail();

				// render - customer
				$customerPath = $dirHTML."customer/";
				$customerData = array(
					"active" => _cfg("customer.active", FALSE),
					"html" => TRUE,

					"subject" => _cfg("customer.subject"),
					"title" => _cfg("customer.title"),
					"header" => _cfg("customer.header"),
					"footer" => _cfg("customer.footer")
				);
				$customerConfig = array(
					"EOL" => FC_EOL,
					"debug" => $debug,
					"referer" => _cfg("customer.referer", FALSE),
					"css" => _cfg("mail.css", FALSE),
					"cache" => _cfg("mail.cache", FALSE) ? $customerPath : FALSE,
					"minify" => _cfg("mail.minify", FALSE),
					"break" => "———————————————"
				);
				$customerRender = new mailRender($customerData, $customerConfig);
				$customerHTML = $customerRender->render($data, $keep);
				$customerRender->isHTML(FALSE);
				$customerPlain = $customerRender->render($data, $keep);



				// config mail - common
				$cfgMail = array(
					"language" => $language,
					"attachment" => isset($_FILES) && count($_FILES) > 0 ? $_FILES : FALSE,
					"gmail" => $gmail,
					"ssl" => $ssl,
					"sendmail_path" => _cfg("mail.sendmail_path", FALSE),
					"isHTML" => TRUE
				);

				// config mail - owner
				$ownerData["html"] = _cfg("owner.html", FALSE);
				$cfgOwner = array(
					"to" => array(
						"name" => $sender["name"],
						"email" => $sender["email"]
					),
					"cc" => $sender["cc"],
					"bcc" => $sender["bcc"],
					"reply" => $addresses,
					"no-reply" => $sender["no-reply"],
					"subject" => $ownerData["subject"],
					"isHTML" => $ownerData["html"],
					"html" => $ownerHTML,
					"plain" => $ownerPlain
				);
				$cfgOwner = array_merge($cfgMail, $cfgOwner);
				$mailerOwner = FCMailer($cfgOwner);

				if (is_array($addresses) && count($addresses) > 0) { // config mail - customer
					$customerData["html"] = _cfg("customer.html", FALSE);
					$customerTo = array();
					$cfgCustomer = array(
						"to" => $addresses,
						"reply" => array(
							"name" => $sender["name"],
							"email" => $sender["email"]
						),
						"no-reply" => $sender["no-reply"],
						"subject" => $customerData["subject"],
						"isHTML" => $customerData["html"],
						"html" => $customerHTML,
						"plain" => $customerPlain
					);
					$cfgCustomer = array_merge($cfgMail, $cfgCustomer);
					$mailerCustomer = FCMailer($cfgCustomer);
				}






				if ($debug) {
					$ownerHeader = array();
					$ownerHeader["From"] = array(
						$mailerOwner->From,
						$mailerOwner->FromName
					);
					$ownerHeader["To"] = $mailerOwner->getToAddresses();
					if ($mailerOwner->getReplyToAddresses()) $ownerHeader["Reply-To"] = $mailerOwner->getReplyToAddresses();
					if (count($mailerOwner->getCcAddresses()) > 0) $ownerHeader["CC"] = $mailerOwner->getCcAddresses();
					if (count($mailerOwner->getBccAddresses()) > 0) $ownerHeader["BCC"] = $mailerOwner->getBccAddresses();
					$ownerHeader["Content-Type"] = $ownerData["html"] ? "HTML" : "Plain text";



					// attachment
					$attach = array();
					$arrAttach = $mailerOwner->getAttachments();
					foreach ($arrAttach as $arr) {
						$imgType = array("png", "gif", "jpg", "jpeg");
						$file = base64_encode(file_get_contents($arr[0]));
						$ext = explode(".", $arr[2]);
						$ext = end($ext);
						$attach[] = array(
							"name" => $arr[2],
							"type" => $ext,
							"content" => in_array($ext, $imgType) ? "data:image/".$ext.";base64,".$file : FALSE
						);
					}
					$json["debug"] = true;
					$json["local"] = $isLocal;
					$json["email-test"] = $isLocal ? $localList["email"] : FALSE;
					if ($configExt === FALSE) {
						$json["config-loaded"] = "email.ini";
						$json["config-missing"] = $configFile;
					} else {
						$json["config-loaded"] = $configFile;
						$json["config-missing"] = FALSE;
					}
					$json["status"] = "success";
					$json["messages"] = _txt("debug");
					$json["url"] = $urlThanks;
					$json["attachment"] = $attach;
					$json["owner"] = array(
						"headers" => $ownerHeader,
						"subject" => $ownerData["subject"],
						"available" => $ownerData["active"],
						"isHTML" => $ownerData["html"],
						"html" => $ownerHTML,
						"plain" => $ownerPlain,
					);

					if (is_array($addresses) && count($addresses) > 0) {
						$customerHeader["From"] = array(
							$mailerCustomer->From,
							$mailerCustomer->FromName
						);
						$customerHeader["To"] = $mailerCustomer->getToAddresses();
						if ($mailerCustomer->getReplyToAddresses()) $customerHeader["Reply-To"] = $mailerCustomer->getReplyToAddresses();
						$customerHeader["Content-Type"] = $customerData["html"] ? "HTML" : "Plain text";

						$json["customer"] = array(
							"headers" => $customerHeader,
							"subject" => $customerData["subject"],
							"available" => $customerData["active"],
							"isHTML" => $customerData["html"],
							"html" => $customerHTML,
							"plain" => $customerPlain
						);
					}
				} else {
					$json["storage"] = FALSE;

					if (is_string($db)) $json["storage"] = $db;
					elseif ($db) $db->insert($storage);

					if (_cfg("mail.cache", FALSE)) {
						// owner - HTML files
						if (!is_dir($ownerPath)) {
							$ownerUmask = umask(0);
							mkdir($ownerPath, 0777, true);
							umask($ownerUmask);
						}

						$ownerPath = $ownerPath.$ownerRender->getUID().".html";
						$ownerHandle = fopen($ownerPath, "w");
						fwrite($ownerHandle, $ownerHTML);
						fclose($ownerHandle);

						// customer - HTML files
						if (!is_dir($customerPath)) {
							$customerUmask = umask(0);
							mkdir($customerPath, 0777, true);
							umask($customerUmask);
						}

						$customerPath = $customerPath.$customerRender->getUID().".html";
						$customerHandle = fopen($customerPath, "w");
						fwrite($customerHandle, $customerHTML);
						fclose($customerHandle);
					}

					if ($isLocal) {
						$mailerOwner->clearAddresses(); // clear To
						$mailerOwner->clearCCs(); // clear CC
						$mailerOwner->clearBCCs(); // clear BCC
						$mailerOwner->clearReplyTos(); // clear Reply-To
						$mailerOwner->clearAllRecipients(); // clear Recipients
						$mailerOwner->clearCustomHeaders(); // clear Custom Headers
						if (is_array($emailTest) && count($emailTest) > 0) {
							foreach ($emailTest as $email) {
								$mailerOwner->addAddress($email);
							}
						}

						// $mailerCustomer->clearAddresses(); // clear To
						// $mailerCustomer->clearCCs(); // clear CC
						// $mailerCustomer->clearBCCs(); // clear BCC
						// $mailerCustomer->clearReplyTos(); // clear Reply-To
						// $mailerCustomer->clearAllRecipients(); // clear Recipients
						// $mailerCustomer->clearCustomHeaders(); // clear Custom Headers
						// if (is_array($emailTest) && count($emailTest) > 0) {
							// foreach ($emailTest as $email) {
								// $mailerCustomer->addAddress($email);
							// }
						// }
					}

					$ownerSend = $ownerData["active"] ? $mailerOwner->send() : FALSE;
					$customerSend = isset($mailerCustomer) && is_array($addresses) && count($addresses) > 0 && $customerData["active"] ? $mailerCustomer->send() : FALSE;

					if ($ownerSend || $customerSend) {
						$json["status"] = "success";
						$json["messages"] = _txt("success");
						$json["url"] = $urlThanks;
					} else {
						$json["messages"] = _txt("error");
						$json["error"] = $mailerOwner->ErrorInfo;
						$json["SMTPDebug"] = $GLOBALS["FCMailer-Debug"];
					}
				}
			} else $json["messages"] = _txt("no_data_received");
		} elseif (is_string($data)) $json["messages"] = $data;

		echo json_encode($json);
		// echo json_encode($json, JSON_PRETTY_PRINT);
	} else _error(403);
} else _error(404);
?>
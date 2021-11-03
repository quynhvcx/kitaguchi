<?php
/*
 * FCMailer - 2017
 *
 * ScriptName: functions.php
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

if (!function_exists("http_response_code")) {
	function http_response_code($code = NULL) {
		if ($code !== NULL) {
			switch ($code) {
				case 100: $text = "Continue"; break;
				case 101: $text = "Switching Protocols"; break;
				case 200: $text = "OK"; break;
				case 201: $text = "Created"; break;
				case 202: $text = "Accepted"; break;
				case 203: $text = "Non-Authoritative Information"; break;
				case 204: $text = "No Content"; break;
				case 205: $text = "Reset Content"; break;
				case 206: $text = "Partial Content"; break;
				case 300: $text = "Multiple Choices"; break;
				case 301: $text = "Moved Permanently"; break;
				case 302: $text = "Moved Temporarily"; break;
				case 303: $text = "See Other"; break;
				case 304: $text = "Not Modified"; break;
				case 305: $text = "Use Proxy"; break;
				case 400: $text = "Bad Request"; break;
				case 401: $text = "Unauthorized"; break;
				case 402: $text = "Payment Required"; break;
				case 403: $text = "Forbidden"; break;
				case 404: $text = "Not Found"; break;
				case 405: $text = "Method Not Allowed"; break;
				case 406: $text = "Not Acceptable"; break;
				case 407: $text = "Proxy Authentication Required"; break;
				case 408: $text = "Request Time-out"; break;
				case 409: $text = "Conflict"; break;
				case 410: $text = "Gone"; break;
				case 411: $text = "Length Required"; break;
				case 412: $text = "Precondition Failed"; break;
				case 413: $text = "Request Entity Too Large"; break;
				case 414: $text = "Request-URI Too Large"; break;
				case 415: $text = "Unsupported Media Type"; break;
				case 500: $text = "Internal Server Error"; break;
				case 501: $text = "Not Implemented"; break;
				case 502: $text = "Bad Gateway"; break;
				case 503: $text = "Service Unavailable"; break;
				case 504: $text = "Gateway Time-out"; break;
				case 505: $text = "HTTP Version not supported"; break;
				default:
					exit("Unknown http status code \"".htmlentities($code)."\"");
				break;
			}

			$protocol = (isset($_SERVER["SERVER_PROTOCOL"]) ? $_SERVER["SERVER_PROTOCOL"] : "HTTP/1.0");

			header($protocol." ".$code." ".$text);

			$GLOBALS["http_response_code"] = $code;
		} else $code = (isset($GLOBALS["http_response_code"]) ? $GLOBALS["http_response_code"] : 200);

		return $code;
	}
}

if (!function_exists("_checksums")) {
	function _checksums($data, $path = NULL) {
		if ($path && strlen($path) > 0) $path = preg_replace("/\/+$/s", "", $path)."/";

		$checksums = array();
		foreach ($data as $i => $file) {
			if ($file) {
				if (is_array($file)) {
					if (count($file) > 0) $checksums[$i] = _checksums($file, $path.$i);
					else $checksums[$i] = array();
				} else $checksums[$file] = file_exists($path.$file) ? md5_file($path.$file) : FALSE;
			}
		}

		return $checksums;
	}
}

if (!function_exists("_parseConfig")) {
	function _parseConfig($filename, $process_sections = FALSE, $scanner_mode = INI_SCANNER_NORMAL, $lower = TRUE) {
		if (!file_exists($filename)) return FALSE;

		$content = file_get_contents($filename);
		if (empty($content)) return FALSE;

		// html
		preg_match_all("/<<<HTML(.+?[^HTML>>>])HTML>>>/is", $content, $matchHTML);

		if (isset($matchHTML[0]) && count($matchHTML[0]) > 0) {
			foreach ($matchHTML[0] as $match) {
				// $multiple = preg_replace("/\"/s", "&quot;", $match);
				$multiple = addslashes($match);
				$multiple = preg_replace("/<<<\s*(?:HTML)(\r\n|\n)+|(\r\n|\n)+\s*(?:HTML)\s*>>>/is", "", $multiple);
				$multiple = preg_replace("/(\r\n|\n)/s", "<br />", $multiple);
				$multiple = preg_replace("/\[/s", "&#091;", $multiple);
				$multiple = preg_replace("/\]/s", "&#093;", $multiple);
				$content = str_replace($match, $multiple, $content);
			}
		}

		// plain
		preg_match_all("/<<<(.+?[^>>>])>>>/is", $content, $matchPlain);

		if (isset($matchPlain[0]) && count($matchPlain[0]) > 0) {
			// print_r($matchPlain);
			foreach ($matchPlain[0] as $match) {
				// $multiple = preg_replace("/\"/s", "&quot;", $match);
				$multiple = htmlspecialchars($match);
				$multiple = preg_replace("/^&lt;&lt;&lt;/i", "<<<", $multiple);
				$multiple = preg_replace("/&gt;&gt;&gt;$/i", ">>>", $multiple);
				$multiple = addslashes($multiple);
				$multiple = preg_replace("/(<<<(\r\n|\n)+|(\r\n|\n)+>>>)/s", "", $multiple);
				$multiple = preg_replace("/\[/s", "&#091;", $multiple);
				$multiple = preg_replace("/\]/s", "&#093;", $multiple);

				// ; => &#59;
				// remove comment-out
				$breakLine = explode(FC_EOL, $multiple);
				foreach ($breakLine as $b) {
					$normalLine = preg_replace("/^;+\s*/", "", $b);
					$normalLine = preg_replace("/\s+/", " ", $normalLine);
					$normalLine = trim($normalLine);
					$multiple = str_replace($b, $normalLine, $multiple);
				}

				$multiple = _nl2br($multiple);
				$content = str_replace($match, $multiple, $content);
			}
		}

		$lines = array();
		foreach (explode(FC_EOL, $content) as $line) { // re-mark
			$sections = preg_match("/^\s*\[(.*?)\]/", $line, $match);
			if (count($match) > 0) {
				$key = preg_replace("/(\[|\])*/", "", $match[0]);
				$key = preg_quote($key, "/");
				$key = preg_replace("/[-|\s|　]+/s", "_", $key);
				$key = preg_replace("/\\\\/s", "", $key);
				$key = preg_replace("/_+/s", "_", $key);
				$key = strtolower($key);
				$lines[] = "[".$key."]";
			} else {
				$args = explode("=", $line, 2);

				if (count($args) > 1 && isset($args[0]) && preg_match("/^[^;]/", $args[0])) {
					$key = $args[0];
					$key = trim($key);
					$key = strtolower($key);
					$key = preg_quote($key, "/");
					$key = preg_replace("/\\\\-/", "_", $key);
					$key = preg_replace("/\"/s", "", $key);
					$key = preg_replace("/[-|\s|　]+/s", "_", $key);
					$key = preg_replace("/\_+/s", "_", $key);

					$val = $args[1];
					$val = trim($val);
					$val = preg_replace("/^;+\s*/", "", $val); // remove comment-out

					$boolean = array("true", "on", "yes", "false", "off", "no", "none", "null");
					if (!in_array(strtolower($val), $boolean)) {
						$val = preg_replace("/^(\"&quot;|&quot;\"|\\\")$/i", "\"", $val);
						$val = '"'.$val.'"'; // bugs BOOLEAN_FALSE
					}

					$lines[] = $key." = ".$val;
				} else $lines[] = preg_match("/^\[([a-z0-9-_\.\s]+)\]/i", $line) ? strtolower($line) : $line;
			}
		}

		$content = join(FC_EOL, $lines);
		// echo $content;
		// exit;

		$data = @parse_ini_string($content, $process_sections, $scanner_mode);
		return $data;
	}
}

if (!function_exists("_getConfig")) {
	function _getConfig($str, $prefix = FALSE) {
		global $config;

		$str = strtolower($str);

		if (strlen($prefix) > 0) $str = $prefix.".".$str;

		$keys = explode(".", $str);

		$ar = $config;
		$arr =& $ar;
		foreach ($keys as $key) {
			if (isset($arr[$key])) {
				if (is_string($arr[$key])) {
					if ($arr[$key] === FALSE) $arr = FALSE;
					elseif (strlen($arr[$key]) > 0) $arr =& $arr[$key];
					else $arr = NULL;
				} else $arr =& $arr[$key];
			} else $arr = NULL;
		}

		if (isset($arr)) return $arr;
	}
}

if (!function_exists("_cfg")) {
	function _cfg($str, $default = FALSE) {
		$cfg = _getConfig($str);
		if ($cfg === NULL || ($cfg !== FALSE && strlen(trim($cfg)) < 1)) $cfg = $default;

		return $cfg;
	}
}

if (!function_exists("_txt")) {
	function _txt($str) {
		global $default;

		$str = strtolower($str);

		if (_cfg("text.".$str) === NULL) {
			if (isset($default["text"][$str])) return $default["text"][$str];
			else return NULL;
		} else return _cfg("text.".$str);
	}
}

if (!function_exists("isJSON")) {
	function isJSON($str) {
		return !empty($str) && is_string($str) && is_array(json_decode($str, true)) && json_last_error() === JSON_ERROR_NONE;
	}
}

if (!function_exists("json_encode_utf8")) {
	function json_encode_utf8($data) {
		if (defined("JSON_UNESCAPED_UNICODE")) return json_encode($data, JSON_UNESCAPED_UNICODE);

		return preg_replace_callback("/(?<!\\\\)\\\\u([0-9a-f]{4})/i",
			function ($m) {
				$d = pack("H*", $m[1]);
				$r = mb_convert_encoding($d, "UTF8", "UTF-16BE");
				return $r !== "?" && $r !== "" ? $r : $m[0];
			}, json_encode($data)
		);
	}
}

if (!function_exists("parseJSON")) {
	function parseJSON($json) { // $assoc = TRUE
		$msg = FALSE;

		$data = json_decode($json, TRUE);

		switch (json_last_error()) {
			case JSON_ERROR_NONE:
				$msg = $data;
			break;
			case JSON_ERROR_DEPTH:
				$msg = "Maximum stack depth exceeded";
			break;
			case JSON_ERROR_STATE_MISMATCH:
				$msg = "Underflow or the modes mismatch";
			break;
			case JSON_ERROR_CTRL_CHAR:
				$msg = "Unexpected control character found";
			break;
			case JSON_ERROR_SYNTAX:
				$msg = "Syntax error, malformed JSON";
			break;
			case JSON_ERROR_UTF8:
				$msg = "Malformed UTF-8 characters, possibly incorrectly encoded";
			break;
			case JSON_ERROR_RECURSION:
				$msg = "Recursion detected";
			break;
			case JSON_ERROR_INF_OR_NAN:
				$msg = "Inf and NaN cannot be JSON encoded";
			break;
			case JSON_ERROR_UNSUPPORTED_TYPE:
				$msg = "Type is not supported";
			break;
			default:
				$msg = "Unknown error";
			break;
		}

		return $msg;
	}
}

if (!function_exists("baseURL")) {
	function baseURL($parent = FALSE) {
		if (isset($_SERVER["HTTP_HOST"])) {
			$base_url = (empty($_SERVER["HTTPS"]) OR strtolower($_SERVER["HTTPS"]) === "off") ? "http" : "https";
			$base_url .= "://". $_SERVER["HTTP_HOST"];
			if ($parent) {
				$path = $_SERVER["SCRIPT_NAME"];
				$path = rtrim($path, "/");
				$arrs = explode("/", $path);
				$arrs = array_slice($arrs, 0, -2);
				$path = implode("/", $arrs);

				$base_url .= $path;
				$base_url .= "/";
			} else $base_url .= str_replace(basename($_SERVER["SCRIPT_NAME"]), "", $_SERVER["SCRIPT_NAME"]);
		} else $base_url = "http://localhost/";

		return $base_url;
	}
}

if (!function_exists("minify")) {
	function minify($css) {
		$css = preg_replace("/\s+/", " ", $css); // normalize whitespaces
		$css = preg_replace("/(\s+)(\/\*(.*?)\*\/)(\s+)/", "$2", $css); // remove spaces before and after comment
		$css = preg_replace("~/\*(?![\!|\*])(.*?)\*/~", "", $css); // remove comment blocks, everything between /* and */, unless preserved with /*! ... */ or /** ... */
		$css = preg_replace("/;(?=\s*})/", "", $css); // remove ; before }
		$css = preg_replace("/(,|:|;|\{|}|\*\/|>) /", "\$1", $css); // remove space after , : ; { } */ >
		$css = preg_replace("/ (,|;|\{|}|\(|\)|>)/", "\$1", $css); // remove space before , ; { } ( ) >
		$css = preg_replace("/(:| )0\.([0-9]+)(%|em|ex|px|in|cm|mm|pt|pc)/i", "\${1}.\${2}\${3}", $css); // strips leading 0 on decimal values (converts 0.5px into .5px)
		$css = preg_replace("/(:| )(\.?)0(%|em|ex|px|in|cm|mm|pt|pc)/i", "\${1}0", $css); // strips units if value is 0 (converts 0px to 0)
		$css = preg_replace("/0 0 0 0/", "0", $css); // converts all zeros value into short-hand
		$css = preg_replace("/#([a-f0-9])\\1([a-f0-9])\\2([a-f0-9])\\3/i", "#\1\2\3", $css); // shortern 6-character hex color codes to 3-character where possible

		return trim($css);
	}
}

if (!function_exists("_clean")) {
	function _clean($str) {
		$bad = array(
			"content-type",
			"bcc:",
			"to:",
			"cc:",
			"href"
		);
		return str_replace($bad, "", $str);
	}
}

if (!function_exists("_tab")) {
	function _tab($num = 0) {
		$tab = "";
		if ($num > 0) {
			for ($i = 1; $i <= $num; $i++) {
				$tab .= "\t";
			}
		}
		return $tab;
	}
}

if (!function_exists("_trim")) {
	function _trim($str) {
		$str = preg_replace("/[\s|\t|\n|\r]+/", " ", $str);
		$str = preg_replace("/\s+/", " ", $str);
		$str = preg_replace("/[　]+/u", "　", $str);
		$str = preg_replace("/(^　|　$)+/u", "", $str);
		$str = trim($str);
		return $str;
	}
}

if (!function_exists("_nl2br")) {
	function _nl2br($str) {
		$str = trim($str);
		$str = nl2br($str);
		$str = preg_replace("/[\n|\r|\t]+/", "", $str);

		return $str;
	}
}

if (!function_exists("_eol")) {
	function _eol($str) {
		return preg_replace("/(\r\n|\n)/s", "\n", $str);
	}
}

if (!function_exists("prefixHTTP")) {
	function prefixHTTP($url) {
		if (!preg_match("#^https?://#", $url)) $url = "http://".$url;

		return $url;
	}
}

if (!function_exists("_error")) {
	function _error($code = FALSE, $description = FALSE) {
		if (!is_numeric($code) && $description) $title = $code;
		else {
			if (is_numeric($code)) http_response_code($code);

			switch($code) {
				case 400:
					$title = "400 Bad Request";
					$description = "The request can not be processed due to bad syntax";
					break;

				case 401:
					$title = "401 Unauthorized";
					$description = "The request has failed authentication";
					break;

				case 403:
					$title = "403 Forbidden";
					$description = "The server refuses to response to the request";
					break;

				case 404:
					$title = "404 Not Found";
					$description = "The resource requested can not be found.";
					break;

				case 500:
					$title = "500 Internal Server Error";
					$description = "There was an error which doesn't fit any other error message";
					break;

				case 502:
					$title = "502 Bad Gateway";
					$description = "The server was acting as a proxy and received a bad request.";
					break;

				case 504:
					$title = "504 Gateway Timeout";
					$description = "The server was acting as a proxy and the request timed out.";
					break;

				default:
					$title = "Error";
					$description = "An error has occurred";
			}
		}

		require_once("FCMailer/error.php");
		exit;
	}
}

if (!function_exists("ipAddress")) {
	function ipAddress() {
		$ipAddress = "UNKNOWN";
		if (isset($_SERVER)) {
			if (isset($_SERVER["HTTP_CLIENT_IP"])) $ipAddress = $_SERVER["HTTP_CLIENT_IP"];
			else if(isset($_SERVER["HTTP_X_FORWARDED_FOR"])) $ipAddress = $_SERVER["HTTP_X_FORWARDED_FOR"];
			else if(isset($_SERVER["HTTP_X_FORWARDED"])) $ipAddress = $_SERVER["HTTP_X_FORWARDED"];
			else if(isset($_SERVER["HTTP_FORWARDED_FOR"])) $ipAddress = $_SERVER["HTTP_FORWARDED_FOR"];
			else if(isset($_SERVER["HTTP_FORWARDED"])) $ipAddress = $_SERVER["HTTP_FORWARDED"];
			else if(isset($_SERVER["REMOTE_ADDR"])) $ipAddress = $_SERVER["REMOTE_ADDR"];
		} else {
			if (getenv("HTTP_CLIENT_IP")) $ipAddress = getenv("HTTP_CLIENT_IP");
			else if(getenv("HTTP_X_FORWARDED_FOR")) $ipAddress = getenv("HTTP_X_FORWARDED_FOR");
			else if(getenv("HTTP_X_FORWARDED")) $ipAddress = getenv("HTTP_X_FORWARDED");
			else if(getenv("HTTP_FORWARDED_FOR")) $ipAddress = getenv("HTTP_FORWARDED_FOR");
			else if(getenv("HTTP_FORWARDED")) $ipAddress = getenv("HTTP_FORWARDED");
			else if(getenv("REMOTE_ADDR")) $ipAddress = getenv("REMOTE_ADDR");
		}

		return $ipAddress;
	}
}

if (!function_exists("FCMailer")) {
	function FCMailer($cfg) {
		if (!class_exists("PHPMailer")) require_once("PHPMailer/PHPMailerAutoload.php");

		$mailer = new PHPMailer;

		$mailer->clearAddresses();
		$mailer->clearCCs();
		$mailer->clearBCCs();
		$mailer->clearReplyTos();
		$mailer->clearAllRecipients();
		$mailer->clearAttachments();
		$mailer->clearCustomHeaders();

		$mailer->XMailer = "PHP/".phpversion()." - FCMailer v3.6 • FoodConnection";

		// encoding
		$mailer->CharSet = "UTF-8";
		if (isset($cfg["language"]) && strlen($cfg["language"]) > 0 && $cfg["language"] != "jp") {
			mb_internal_encoding("UTF-8");
			$mailer->Encoding = "8bit";
			$mailer->setLanguage($cfg["language"]);

			if (isset($cfg["subject"]) && strlen($cfg["subject"]) > 0) $mailer->Subject = $cfg["subject"];
		} else {
			mb_language("japanese");
			mb_internal_encoding("ISO-2022-JP");
			$mailer->Encoding = "7bit";
			// $mailer->CharSet = "ISO-2022-JP";
			$mailer->setLanguage("jp");

			if (isset($cfg["subject"]) && strlen($cfg["subject"]) > 0) {
				$cfg["subject"] = mb_convert_encoding($cfg["subject"], "JIS", "auto");
				$mailer->Subject = mb_encode_mimeheader($cfg["subject"], "ISO-2022-JP");
			}
		}

		$noReply = FALSE;
		if (isset($cfg["no-reply"]) && strlen(trim($cfg["no-reply"])) > 0) {
			$noReply = strtolower($cfg["no-reply"]);
			if ($noReply == "true" || $noReply == "1") $noReply = "no-reply@".preg_replace("/^www\./i", "", $_SERVER["SERVER_NAME"]); // auto
			elseif (!preg_match("/[\._a-z0-9-]+@[\._a-z0-9-]+\.[a-z]+/i", $noReply)) $noReply = FALSE; // invalid email
		}

		// auth gmail
		if (isset($cfg["gmail"]["username"]) && isset($cfg["gmail"]["password"])) {
			$GLOBALS["FCMailer-Debug"] = "";
			$mailer->isSMTP();
			$mailer->SMTPDebug = 2;
			$mailer->Debugoutput = function($str, $level) {
				$GLOBALS["FCMailer-Debug"] .= $level.": ".$str.FC_EOL;
			};
			$mailer->Host = "smtp.gmail.com";
			if (isset($cfg["ssl"]) && $cfg["ssl"] === TRUE) {
				$mailer->Port = 465;
				$mailer->SMTPSecure = "ssl";
			} else {
				$mailer->Port = 587;
				$mailer->SMTPSecure = "tls";
			}
			$mailer->SMTPAuth = TRUE;
			$mailer->Username = $cfg["gmail"]["username"];
			$mailer->Password = $cfg["gmail"]["password"];

			if ($noReply) $mailer->setFrom($noReply);
			elseif (!isset($cfg["to"]) && !isset($cfg["to"]["email"])) $mailer->setFrom($cfg["gmail"]["username"]);
		} elseif (isset($cfg["sendmail_path"]) && strlen($cfg["sendmail_path"]) > 0) {
			$mailer->Sendmail = $cfg["sendmail_path"];
			@ini_set("sendmail_path", $cfg["sendmail_path"]);
			$mailer->isSendmail();
		}

		// to
		if (isset($cfg["to"])) { //  && strlen($cfg["sender"]["email"]) > 0
			if (is_string($cfg["to"]) && strlen($cfg["to"]) > 0) $mailer->addAddress($cfg["sender"]["email"]); // customer
			else {
				if (isset($cfg["to"]["email"])) {
					if ($noReply) $mailer->setFrom($noReply);

					$mailer->addAddress($cfg["to"]["email"], $cfg["to"]["name"] ? $cfg["to"]["name"] : NULL); // owner
				} else {
					foreach ($cfg["to"] as $i => $email) { // customer
						$mailer->addAddress($email);
						if ($i < 1) {
							if ($noReply) $mailer->setFrom($noReply);
							else $mailer->setFrom($email);
						}
					}
				}
			}
		}

		// reply-to
		if (isset($cfg["reply"])) {
			if (is_array($cfg["reply"]) && count($cfg["reply"]) > 0) {
				if (isset($cfg["reply"]["email"])) { // customer
					if ($noReply) {
						$mailer->setFrom($noReply);
						$mailer->addReplyTo($noReply);
					} else {
						$mailer->setFrom($cfg["reply"]["email"], $cfg["reply"]["name"] ? $cfg["reply"]["name"] : NULL);
					$mailer->addReplyTo($cfg["reply"]["email"], $cfg["reply"]["name"] ? $cfg["reply"]["name"] : NULL);
					}
				} else { // owner
					foreach ($cfg["reply"] as $i => $email) {
						$mailer->addReplyTo($email);
						if ($i < 1) {
							if ($noReply) $mailer->setFrom($noReply);
							else $mailer->setFrom($email);
						}
					}
				}
			} elseif (is_string($cfg["reply"]) && strlen($cfg["reply"]) > 0) { // owner
				$mailer->addReplyTo($cfg["reply"]);
				if ($noReply) $mailer->setFrom($noReply);
				else $mailer->setFrom($email);
			}
		}

		// CC
		if (isset($cfg["cc"]) && is_array($cfg["cc"]) && count($cfg["cc"]) > 0) {
			foreach ($cfg["cc"] as $cc) {
				$mailer->addCC(trim($cc));
			}

			// if (preg_match("/win32/i", @$_SERVER["SERVER_SOFTWARE"])) $mailer->addCustomHeader("CC", implode(",", $cfg["cc"])); // only win32 SMTP
		}

		// BCC
		if (isset($cfg["bcc"]) && is_array($cfg["bcc"]) && count($cfg["bcc"]) > 0) {
			foreach ($cfg["bcc"] as $bcc) {
				$mailer->addBCC(trim($bcc));
			}

			// if (preg_match("/win32/i", @$_SERVER["SERVER_SOFTWARE"])) $mailer->addCustomHeader("BCC", implode(",", $cfg["bcc"])); // only win32 SMTP
		}

		// attachment
		if (isset($cfg["attachment"]) && is_array($cfg["attachment"]) && count($cfg["attachment"]) > 0) {
			foreach ($cfg["attachment"] as $attachment) {
				if ($attachment["error"] === UPLOAD_ERR_OK) $mailer->AddAttachment($attachment["tmp_name"], $attachment["name"]);
			}
		}

		// message
		if (isset($cfg["plain"])) $cfg["plain"] = preg_replace("/(?:&lt;|<)?br\s*\/?(?:&gt;|>)/i", "\n", $cfg["plain"]);
		if (isset($cfg["isHTML"]) && $cfg["isHTML"] == FALSE) {
			$cfg["plain"] = preg_replace("/(^\w.+:\n)?(^>.*(\n|$))+/mi", "", $cfg["plain"]); // strip out quoted text

			$mailer->Body = $cfg["plain"];
			$mailer->isHTML(FALSE);
		} else {
			if (isset($cfg["html"]) && strlen($cfg["html"]) > 0) $mailer->MsgHTML($cfg["html"]);
			if (isset($cfg["plain"]) && strlen($cfg["plain"]) > 0) $mailer->AltBody = $cfg["plain"];
			$mailer->isHTML(TRUE);
		}

		return $mailer;
	}
}
?>
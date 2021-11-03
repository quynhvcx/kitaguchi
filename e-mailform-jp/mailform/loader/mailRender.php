<?php
/*
 * FCMailer - 2017
 *
 * ScriptName: mailRender.php
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

class mailRender {
	public function __construct($data = array(), $cfg = array()) {
		if (is_array($data) && count($data) > 0) {
			$this->data = $data;
			$this->isHTML = isset($data["html"]) ? $data["html"] : FALSE;

			$this->css = isset($cfg["css"]) ? $cfg["css"] : FALSE;
			$this->uid = time()."-".rand(11111, 99999);
			$this->EOL = isset($cfg["EOL"]) ? $cfg["EOL"] : FC_EOL;
			$this->email = isset($cfg["email"]) && is_array($cfg["email"]) && count($cfg["email"]) > 0 ? $cfg["email"] : array();
			$this->cache = isset($cfg["cache"]) ? $cfg["cache"] : FALSE;
			$this->debug = isset($cfg["debug"]) ? $cfg["debug"] : FALSE;
			$this->minify = isset($cfg["minify"]) ? $cfg["minify"] : FALSE;
			$this->referer = isset($cfg["referer"]) ? $cfg["referer"] : FALSE;
			$this->break = isset($cfg["break"]) ? $cfg["break"] : "———————————————";
		} else die("ERROR");

		$this->_address = array();
		$this->_header = "";
		$this->_title = "";
		$this->_body = "";
		$this->_footer = "";
		$this->_storage = FALSE;
	}

	public function isHTML($val) {
		$this->isHTML = $val;

		// reset;
		$this->_address = array();
		$this->_header = "";
		$this->_title = "";
		$this->_body = "";
		$this->_footer = "";
		$this->_storage = FALSE;
	}

	private function _css() {
		// CSS for mail content
		$css = "";
		$css .= _tab(3).'body {'.$this->EOL;
			$css .= _tab(4).'margin: 0;'.$this->EOL;
			$css .= _tab(4).'padding: 0;'.$this->EOL;
		$css .= _tab(3).'}'.$this->EOL;
		$css .= _tab(3).'#wrapper {'.$this->EOL;
			$css .= _tab(4).'margin: auto;'.$this->EOL;
			$css .= _tab(4).'font-size: 14px;'.$this->EOL;
			$css .= _tab(4).'line-height: 1.428571429;'.$this->EOL;
			$css .= _tab(4).'width: 100%;'.$this->EOL;
			$css .= _tab(4).'max-width: 1000px;'.$this->EOL;
		$css .= _tab(3).'}'.$this->EOL;
		$css .= _tab(3).'#wrapper > * {'.$this->EOL;
			$css .= _tab(4).'margin: 2em;'.$this->EOL;
		$css .= _tab(3).'}'.$this->EOL;
		$css .= _tab(3).'#wrapper h2 {'.$this->EOL;
			$css .= _tab(4).'margin: 0;'.$this->EOL;
			$css .= _tab(4).'padding-bottom: 1em;'.$this->EOL;
			$css .= _tab(4).'font-size: 175%;'.$this->EOL;
			$css .= _tab(4).'font-weight: bold;'.$this->EOL;
			$css .= _tab(4).'text-align: center;'.$this->EOL;
		$css .= _tab(3).'}'.$this->EOL;
		$css .= _tab(3).'table {'.$this->EOL;
			$css .= _tab(4).'border: 1px solid #909090;'.$this->EOL;
			$css .= _tab(4).'border-spacing: 0;'.$this->EOL;
			$css .= _tab(4).'border-collapse: collapse;'.$this->EOL;
		$css .= _tab(3).'}'.$this->EOL;
		$css .= _tab(3).'th {'.$this->EOL;
			$css .= _tab(4).'font-size: 150%;'.$this->EOL;
			$css .= _tab(4).'font-weight: bold;'.$this->EOL;
			$css .= _tab(4).'text-transform: uppercase;'.$this->EOL;
		$css .= _tab(3).'}'.$this->EOL;
		$css .= _tab(3).'td {'.$this->EOL;
			$css .= _tab(4).'padding: .5em;'.$this->EOL;
			$css .= _tab(4).'font-size: 90%;'.$this->EOL;
		$css .= _tab(3).'}'.$this->EOL;
		$css .= _tab(3).'hr {'.$this->EOL;
			$css .= _tab(4).'display: block;'.$this->EOL;
			$css .= _tab(4).'margin: auto;'.$this->EOL;
			$css .= _tab(4).'border: none;'.$this->EOL;
			$css .= _tab(4).'border-bottom: 1px dashed #909090;'.$this->EOL;
		$css .= _tab(3).'}'.$this->EOL;
		$css .= _tab(3).'.view-in-browser {'.$this->EOL;
			$css .= _tab(4).'color: #303030;'.$this->EOL;
			$css .= _tab(4).'font-size: 75%;'.$this->EOL;
			$css .= _tab(4).'text-align: right;'.$this->EOL;
		$css .= _tab(3).'}'.$this->EOL;
		$css .= _tab(3).'.view-in-browser a {'.$this->EOL;
			$css .= _tab(4).'color: #303030;'.$this->EOL;
		$css .= _tab(3).'}'.$this->EOL;
		$css .= _tab(3).'#header {'.$this->EOL;
			$css .= _tab(4).'padding-bottom: 2em;'.$this->EOL;
			$css .= _tab(4).'text-align: center;'.$this->EOL;
			$css .= _tab(4).'border-bottom: 1px dotted #303030;'.$this->EOL;
		$css .= _tab(3).'}'.$this->EOL;
		$css .= _tab(3).'#body {'.$this->EOL;
			$css .= _tab(4).'font-size: 100%;'.$this->EOL;
		$css .= _tab(3).'}'.$this->EOL;
		$css .= _tab(3).'#body .name {'.$this->EOL;
			$css .= _tab(4).'font-size: 100%;'.$this->EOL;
			$css .= _tab(4).'font-weight: bold;'.$this->EOL;
			$css .= _tab(4).'text-align: right;'.$this->EOL;
			$css .= _tab(4).'width: 30%;'.$this->EOL;
		$css .= _tab(3).'}'.$this->EOL;
		$css .= _tab(3).'#body .value {'.$this->EOL;
			$css .= _tab(4).'width: 70%;'.$this->EOL;
		$css .= _tab(3).'}'.$this->EOL;
		$css .= _tab(3).'#footer {'.$this->EOL;
			$css .= _tab(4).'padding-top: 2em;'.$this->EOL;
			$css .= _tab(4).'text-align: center;'.$this->EOL;
			$css .= _tab(4).'border-top: 1px dotted #303030;'.$this->EOL;
		$css .= _tab(3).'}'.$this->EOL;
		$css .= _tab(3).'#referer {'.$this->EOL;
			$css .= _tab(4).'margin-top: .5em;'.$this->EOL;
			$css .= _tab(4).'padding: 1em;'.$this->EOL;
			$css .= _tab(4).'color: #454545;'.$this->EOL;
			$css .= _tab(4).'background: #F9F9F9;'.$this->EOL;
			$css .= _tab(4).'border: 1px solid #DADADA;'.$this->EOL;
		$css .= _tab(3).'}'.$this->EOL;
		$css .= _tab(3).'#referer > * {'.$this->EOL;
			$css .= _tab(4).'margin: 0;'.$this->EOL;
		$css .= _tab(3).'}'.$this->EOL;
		$css .= _tab(3).'#referer > * + * {'.$this->EOL;
			$css .= _tab(4).'margin-top: .5em;'.$this->EOL;
		$css .= _tab(3).'}'.$this->EOL;
		$css .= _tab(3).'#referer a {'.$this->EOL;
			$css .= _tab(4).'color: #454545;'.$this->EOL;
		$css .= _tab(3).'}'.$this->EOL;
		$css .= _tab(3).'#referer b,'.$this->EOL;
		$css .= _tab(3).'#referer strong {'.$this->EOL;
			$css .= _tab(4).'color: #404040;'.$this->EOL;
		$css .= _tab(3).'}'.$this->EOL;
		$css .= _tab(3).'.logs {'.$this->EOL;
			$css .= _tab(4).'padding: 1em;'.$this->EOL;
			$css .= _tab(4).'font-size: 10px;'.$this->EOL;
			$css .= _tab(4).'text-align: center;'.$this->EOL;
		$css .= _tab(3).'}'.$this->EOL;

		return $css;
	}

	public function render($data, $cfg) {
		global $detection, $dirData;

		$browser = $detection->getName();
		if ($detection->getVersion() !== "unknown") {
			$browser .= " ";
			$browser .= "v";
			$browser .= $detection->getVersion();
		}

		$userAgent = $detection->getUserAgent();

		if ($detection->getPlatformVersion() == "unknown") $platform = $detection->getPlatform();
		else {
			$platform = $detection->getPlatformVersion();
			$platform .= " ";
			$platform .= $detection->is64bitPlatform() ? "x64" : "x32";
		}

		$httpReferer = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : baseURL();

		if ($this->data["title"]) $this->_title = $this->data["title"];
		if ($this->data["header"]) $this->_header = $this->data["header"];
		if ($this->data["footer"]) $this->_footer = $this->data["footer"];
		$this->_body = $this->parse($data, $cfg); // parse

		$logs = "[".date("Y-m-d H:i:s")."] FCMailer • End of message.";

		if ($this->isHTML) {
			$pathCSS = $dirData.$this->css;
			if ($this->css && file_exists($pathCSS)) $css = file_get_contents($pathCSS);
			else $css = $this->_css();
			if ($this->minify) $css = minify($css); // minify CSS

			$content = "";
			$content .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'.$this->EOL;
			$content .= '<html xmlns="http://www.w3.org/1999/xhtml">'.$this->EOL;
				$content .= _tab(1).'<head>'.$this->EOL;
					$content .= _tab(2).'<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />'.$this->EOL;
					$content .= _tab(2).'<meta name="viewport" content="width=device-width, initial-scale=1.0" />'.$this->EOL;
					$content .= _tab(2).'<title>'.($this->data["title"] ? $this->data["title"] : "FCMailer").'</title>'.$this->EOL;
					$content .= _tab(2).'<style type="text/css">'.$this->EOL;
						$content .= $css;
					$content .= _tab(2).'</style>'.$this->EOL;
				$content .= _tab(1).'</head>'.$this->EOL;
				$content .= _tab(1).'<body>'.$this->EOL;
					$content .= _tab(2).'<div id="wrapper">'.$this->EOL;
						// view in browser
						if ($this->debug == FALSE && $this->cache) {
							$content .= _tab(3).'<p class="view-in-browser">'.$this->EOL;
								$content .= _tab(4).'<a href="'.baseURL().$this->cache.$this->uid.'.html">'._txt("view_in_browser").'</a>'.$this->EOL;
							$content .= _tab(3).'</p>'.$this->EOL;
						}

						// header
						if ($this->data["header"]) $content .= _tab(3).'<div id="header">'.$this->_header.'</div>'.$this->EOL;

						// body
						$content .= _tab(3).'<div id="body">'.$this->EOL;
							$content .= _tab(4).'<table cellpadding="10" cellspacing="1" width="100%" border="1" align="center">'.$this->EOL;
								if ($this->data["title"]) {
									$content .= _tab(5).'<thead>'.$this->EOL;
										$content .= _tab(6).'<th colspan="2">'.$this->_title.'</th>'.$this->EOL;
									$content .= _tab(5).'</thead>'.$this->EOL;
								}
								$content .= _tab(5).'<tbody>'.$this->EOL;
									$content .= $this->_body;
								$content .= _tab(5).'</tbody>'.$this->EOL;
							$content .= _tab(4).'</table>'.$this->EOL;
						$content .= _tab(3).'</div>'.$this->EOL;

						// footer
						if ($this->data["footer"]) $content .= _tab(3).'<div id="footer">'.$this->_footer.'</div>'.$this->EOL;

						// referer
						if ($this->referer) {
							$content .= _tab(3).'<div id="referer">'.$this->EOL;
								$content .= _tab(4).'<p><strong>Host name:</strong> '.$_SERVER["HTTP_HOST"].'</p>'.$this->EOL;
								$content .= _tab(4).'<p><strong>IP address:</strong> '.ipAddress().'</p>'.$this->EOL;
								$content .= _tab(4).'<p><strong>User agent:</strong> '.$userAgent.'</p>'.$this->EOL;
								$content .= _tab(4).'<p><strong>Browser:</strong> '.$browser.'</p>'.$this->EOL;
								$content .= _tab(4).'<p><strong>OS:</strong> '.$platform.'</p>'.$this->EOL;
								$content .= _tab(4).'<p><strong>URL:</strong> <a href="'.$httpReferer.'" target="_blank">'.$httpReferer.'</a></p>'.$this->EOL;
							$content .= _tab(3).'</div>'.$this->EOL;
						}

						// view in browser
						if ($this->debug == FALSE && $this->cache) {
							$content .= _tab(3).'<p class="view-in-browser">'.$this->EOL;
								$content .= _tab(4).'<a href="'.baseURL().$this->cache.$this->uid.'.html">'._txt("view_in_browser").'</a>'.$this->EOL;
							$content .= _tab(3).'</p>'.$this->EOL;
						}

					$content .= _tab(2).'</div>'.$this->EOL;

					$content .= _tab(2).'<p class="logs">'.$logs.'</p>'.$this->EOL;
				$content .= _tab(1).'</body>'.$this->EOL;
			$content .= '</html>';

			$content = str_replace("{{date}}", date("Y-m-d"), $content);
			$content = str_replace("{{time}}", date("H:i:s"), $content);
			$content = str_replace("{{datetime}}", date("Y-m-d H:i:s"), $content);

			if ($this->minify) $content = preg_replace("/[\t|\n|\r]+/", "", $content); // clean HTML whitespaces
		} else {
			$content = "";

			if ($this->data["header"]) { // header
				$content .= $this->EOL;
				$content .= _trim($this->_header);
				$content .= $this->EOL;
				$content .= $this->EOL;
				$content .= $this->break;
				$content .= $this->EOL;
				$content .= $this->EOL;
			}

			if ($this->data["title"]) { // title
				$content .= _trim($this->_title);
				$content .= $this->EOL;
				$content .= $this->EOL;
				$content .= $this->break;
				$content .= $this->EOL;
				$content .= $this->EOL;
			}

			// body
			$content .= $this->_body;

			if ($this->data["footer"]) { // footer
				$content .= $this->EOL;
				$content .= $this->break;
				$content .= $this->EOL;
				$content .= $this->EOL;
				$content .= _trim($this->_footer);
				$content .= $this->EOL;
			}

			// referer
			if ($this->referer) {
				$content .= $this->EOL;
				$content .= $this->break;
				$content .= $this->EOL;
				$content .= $this->EOL;
				$content .= "〖Host name〗：　".$_SERVER["HTTP_HOST"].$this->EOL;
				$content .= "〖IP address〗：　".ipAddress().$this->EOL;
				$content .= "〖User agent〗：　".$userAgent.$this->EOL;
				$content .= "〖Browser〗：　".$browser.$this->EOL;
				$content .= "〖OS〗：　".$platform.$this->EOL;
				$content .= "〖URL〗：　".$httpReferer.$this->EOL;
				$content .= $this->EOL;
			}

			$content .= $this->EOL;
			$content .= $this->EOL;
			$content .= $this->EOL;
			$content .= $logs;

			$content = str_replace("{{date}}", date("Y-m-d"), $content);
			$content = str_replace("{{time}}", date("H:i:s"), $content);
			$content = str_replace("{{datetime}}", date("Y-m-d H:i:s"), $content);
			$content = preg_replace("/<br\s*\/?>/i", "\n", $content);
			$content = strip_tags($content);
		}

		return $content;
	}

	public function parse($data, $keep) {
		$content = "";

		$this->_storage = array(); // cleaning storages
		foreach ($data as $d) {
			$value = "";
			if (isset($d["value"])) {
				$value = $d["value"];

				if (in_array(strtolower($d["key"]), $this->email)) {
					preg_match("/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+\.[a-zA-Z]+/i", $value, $matchEmail); // valid email
					if (count($matchEmail) > 0 && isset($matchEmail[0]) && !in_array(trim(strtolower($matchEmail[0])), $this->_address)) $this->_address[] = trim(strtolower($matchEmail[0]));
				}

				$this->_title = str_replace("{{".$d["key"]."}}", $value, $this->_title);
				$this->_header = str_replace("{{".$d["key"]."}}", $value, $this->_header);
				$this->_footer = str_replace("{{".$d["key"]."}}", $value, $this->_footer);
			}

			if (is_array($value)) $value = implode(" ", $value); // bug unknown with checkboxes value

			if (strlen(trim(_trim($value))) > 0 || $keep === TRUE) {
				$d["txt"] = _trim($d["txt"]);

				if ($this->isHTML) {
					$value = preg_replace("/[\r\n|\n]/", "<br />", $value);
					$content .= _tab(6).'<tr>'.$this->EOL;
						$content .= _tab(7).'<td class="name">'.$d["txt"].'</td>'.$this->EOL;
						$content .= _tab(7).'<td class="value">'._trim($value).'</td>'.$this->EOL;
					$content .= _tab(6).'</tr>'.$this->EOL;
				} else {
					$spacing = "　";

					if (strlen($d["txt"]) > 0) $content .= '【'.$d["txt"].'】：';

					if (preg_match("/\n/i", $value)) {
						$spaces = $spacing;
						for ($s = 0; $s < 2; $s++) {
							// $spaces .= $spacing;
						}

						$value = nl2br($value);
						$value = _trim($value);
						$value = preg_replace("/<br\s*\/?>\s+/is", "<br />", $value);
						$value = preg_replace("/<br\s*\/?>/is", "<br />".$spaces, $value);

						$content .= "<br />";
						$content .= $spaces;
						$content .= $value;
					} else {
						$content .= $spacing;
						$content .= _trim($value);
					}

					$content .= $this->EOL;
				}

				$this->_storage[] = array(
					"name" => $d["txt"],
					"value" => _trim($value)
				);
			}
		}

		return $content;
	}

	public function getUID() {
		return $this->uid;
	}

	public function getEmail() {
		return count($this->_address) > 0 ? $this->_address : FALSE;
	}

	public function getStorage() {
		return is_array($this->_storage) && count($this->_storage) > 0 ? json_encode_utf8($this->_storage) : FALSE;
	}
}
?>
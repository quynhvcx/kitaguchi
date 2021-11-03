<?php
/*
 * FCMailer - 2017
 *
 * ScriptName: SQLite.class.php
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

class SQLite {
	private $db = null;
	private $detection = FALSE;
	public $siteName = "FCMailer";
	public $alias = "FCMailer";
	public $tableData = "data";
	public $tableConfig = "config";
	public $executed = TRUE;

	public function __construct($site_name = NULL, $dir = FALSE, $test = FALSE, $detection = FALSE) {
		$chdir = getcwd();
		chdir(dirname(__DIR__)); // update current working directory

		$path = $this->alias;
		if ($dir) $path = $dir.$path;
		if ($test) $path .= "-test";
		$path .= ".";
		$path .= "sqlite";

		if (!file_exists($path)) { // create file
			$handle = fopen($path, "w");
			fwrite($handle, NULL);
			fclose($handle);
		}

		if (!is_writable($dir)) $this->executed = $dir;
		elseif (!is_writable($path)) $this->executed = $path;

		if ($this->executed === TRUE) {
			$this->siteName = $site_name;
			if (strlen($site_name) < 1) $site_name = $this->alias;

			$this->tableData = $this->alias."_".$this->tableData;
			$this->tableConfig = $this->alias."_".$this->tableConfig;

			$this->db = new PDO("sqlite:".$path); // connection

			chdir($chdir); // re-update current working directory

			try { // create tables
				$this->db->exec("CREATE TABLE IF NOT EXISTS ".$this->tableData." (id INTEGER NOT NULL PRIMARY KEY, message TEXT NOT NULL, added DATETIME DEFAULT (DATETIME(CURRENT_TIMESTAMP, 'LOCALTIME')), modified DATETIME DEFAULT NULL, ip VARCHAR DEFAULT NULL, browser VARCHAR DEFAULT NULL, os VARCHAR DEFAULT NULL, referer_url VARCHAR DEFAULT NULL);");

				$columnIP = $this->db->query("SELECT ip FROM ".$this->tableData." LIMIT 1;");
				if ($columnIP === FALSE) $this->db->exec("ALTER TABLE ".$this->tableData." ADD COLUMN ip VARCHAR");

				$columnBrowser = $this->db->query("SELECT browser FROM ".$this->tableData." LIMIT 1;");
				if ($columnBrowser === FALSE) $this->db->exec("ALTER TABLE ".$this->tableData." ADD COLUMN browser VARCHAR");

				$columnOS = $this->db->query("SELECT os FROM ".$this->tableData." LIMIT 1;");
				if ($columnOS === FALSE) $this->db->exec("ALTER TABLE ".$this->tableData." ADD COLUMN os VARCHAR");

				$columnReferer = $this->db->query("SELECT referer_url FROM ".$this->tableData." LIMIT 1;");
				if ($columnReferer === FALSE) $this->db->exec("ALTER TABLE ".$this->tableData." ADD COLUMN referer_url VARCHAR");

				$query = $this->db->query("SELECT CASE WHEN tbl_name = '".$this->tableConfig."' THEN 1 ELSE 0 END FROM sqlite_master WHERE tbl_name = '".$this->tableConfig."' AND type = 'table';");
				$setup = is_array($query->fetch(PDO::FETCH_ASSOC)) ? FALSE : TRUE;

				$this->db->exec("CREATE TABLE IF NOT EXISTS ".$this->tableConfig." (key VARCHAR NOT NULL PRIMARY KEY, value VARCHAR NOT NULL);");
				if ($setup) {
					$config = array();
					$config["site_name"] = $site_name;
					$config["auto_increment"] = 1;

					foreach ($config as $key => $value) {
						$query = $this->db->prepare("INSERT INTO ".$this->tableConfig." VALUES (:key, :message);");
						$query->bindParam(":key", $key, PDO::PARAM_STR);
						$query->bindParam(":message", $value, PDO::PARAM_STR);
						$query->execute();
					}
				} else $this->db->exec("UPDATE ".$this->tableConfig." SET value = '".$site_name."' WHERE key = 'site_name';");
			} catch (Exception $e) {
				die("NOT SUPPORT");
			}

			if ($detection) $this->detection = $detection;
		}
	}

	private function autoIncrement($reset = FALSE) {
		if ($this->executed === TRUE) {
			if ($reset) {
				$this->db->exec("DROP TABLE ".$this->tableData."_backup;");
				$this->db->exec("CREATE TABLE IF NOT EXISTS ".$this->tableData."_backup (id INTEGER NOT NULL PRIMARY KEY, message TEXT NOT NULL, added DATETIME DEFAULT (DATETIME(CURRENT_TIMESTAMP, 'LOCALTIME')), modified DATETIME DEFAULT NULL, ip VARCHAR DEFAULT NULL, browser VARCHAR DEFAULT NULL, os VARCHAR DEFAULT NULL, referer_url VARCHAR DEFAULT NULL);");
				$query = $this->db->query("SELECT * FROM ".$this->tableData.";");
				$result = $query !== FALSE ? $query->fetchAll(PDO::FETCH_ASSOC) : array();
				$i = 1;
				foreach ($result as $row) {
					$this->db->query("INSERT INTO ".$this->tableData."_backup VALUES (".$i.", '".$row["message"]."', '".$row["added"]."', '".$row["modified"]."', '".$row["ip"]."', '".$row["browser"]."', '".$row["os"]."', '".$row["referer_url"]."');");

					$i++;
				}

				$this->db->exec("DROP TABLE ".$this->tableData.";");
				$this->db->exec("ALTER TABLE ".$this->tableData."_backup RENAME TO ".$this->tableData.";");
				$this->db->exec("UPDATE ".$this->tableConfig." SET value = '".$i."' WHERE key = 'auto_increment';");
			} else {
				$query = $this->db->query("SELECT value FROM ".$this->tableConfig." WHERE key = 'auto_increment';");
				$result = $query !== FALSE ? $query->fetch(PDO::FETCH_ASSOC) : array();

				$this->db->exec("UPDATE ".$this->tableConfig." SET value = value + 1 WHERE key = 'auto_increment';");

				return isset($result["value"]) && is_numeric($result["value"]) ? $result["value"] : 1;
			}
		} else return $this->executed;
	}

	public function select($id = 0) {
		if ($this->executed === TRUE) {
			if ($id) {
				$query = $this->db->query("SELECT * FROM ".$this->tableData." WHERE id = ".$id.";");

				$result = $query !== FALSE ? $query->fetch(PDO::FETCH_ASSOC) : array();
			} else {
				$query = $this->db->query("SELECT * FROM ".$this->tableData." ORDER BY id DESC;");

				$result = $query !== FALSE ? $query->fetchAll(PDO::FETCH_ASSOC) : array();
			}

			return $result;
		} else return $this->executed;
	}

	public function insert($message) {
		if ($this->executed === TRUE) {
			$id = $this->autoIncrement();

			$browser = NULL;
			$platform = NULL;
			$referer = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : baseURL();

			if ($this->detection) {
				$browser = $this->detection->getName();
				if ($this->detection->getVersion() !== "unknown") {
					$browser .= " ";
					$browser .= "v";
					$browser .= $this->detection->getVersion();
				}

				if ($this->detection->getPlatformVersion() == "unknown") $platform = $this->detection->getPlatform();
				else {
					$platform = $this->detection->getPlatformVersion();
					$platform .= " ";
					$platform .= $this->detection->is64bitPlatform() ? "x64" : "x32";
				}
			}

			$query = $this->db->exec("INSERT INTO ".$this->tableData." VALUES (".$id.", '".$message."', DATETIME(CURRENT_TIMESTAMP, 'LOCALTIME'), NULL, '".ipAddress()."', '".$browser."', '".$platform."', '".$referer."');");

			return $id;
		} else return $this->executed;
	}

	public function update($id, $message) {
		if ($this->executed === TRUE) {
			$query = $this->db->exec("UPDATE ".$this->tableData." SET message = '".$message."', modified = DATETIME(CURRENT_TIMESTAMP, 'LOCALTIME') WHERE id = ".$id.";");

			return $this;
		} else return $this->executed;
	}

	public function delete($id) {
		if ($this->executed === TRUE) {
			$this->db->exec("DELETE FROM ".$this->tableData." WHERE id = ".$id.";");

			return $this;
		} else return $this->executed;
	}

	public function truncate() {
		if ($this->executed === TRUE) {
			$this->db->exec("DELETE FROM ".$this->tableData);

			return $this;
		} else return $this->executed;
	}

	public function drop() {
		if ($this->executed === TRUE) {
			$this->db->exec("DROP TABLE ".$this->tableData);

			return $this;
		} else return $this->executed;
	}
}
?>
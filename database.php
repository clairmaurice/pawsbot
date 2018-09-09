<?php

Class Database {

	protected $host;
	protected $user;
	protected $password;
	protected $databasename;

	public function __construct() {
		require 'config.php';	
		$this->host = $config['dbhost'];
		$this->user = $config['dbuser'];
		$this->password = $config['dbpassword'];
		$this->databasename = $config['dbname'];
	}

	public function connect() {
		$mysqli = new mysqli($this->host, $this->user, $this->password, $this->databasename);
		if (!$mysqli->connect_errno) {
			return $mysqli;
		} else {
			echo 'MYSQL ERROR: '.$mysqli->connect_error.PHP_EOL;
			return false;
		}
	}

	public function execute($query, $getid=false) {
		$mysqli = $this->connect();

		if ($mysqli) {
			$result = $mysqli->query($query);
			if ($result) {
				if ($getid == true) {
					return $mysqli->insert_id;
				} else {
					return $result;
				}
			} else {
				echo 'MYSQL ERROR: '.$mysqli->error.PHP_EOL;
				return false;
			}
		}
	}

	public function getAssoc($query) {
		$res = $this->execute($query);

		if ($res) {
			$result = $res->fetch_assoc();
			return $result;
		} else {
			return false;
		}
	}


	public function getAllAssoc($query) {
		$res = $this->execute($query);

		if ($res) {
			$result = [];
			while($rez = $res->fetch_assoc()) {
				$result[] = $rez;
			}
			return $result;
		} else {
			return false;
		}
	}

	public function saveAndGetId($query) {
		$res = $this->execute($query, true);
		if ($res) {
			return $res;
		} else {
			return false;
		}
	}

	public function getAppId($token) {
		$query_get_token = 'SELECT id FROM `apps` WHERE token = "'.$token.'"';
		$res = $this->execute($query_get_token);
		
		if ($res) {
			// return token:
			$result = $res->fetch_assoc();
			if ($result['id'] != NULL) {
				return $result['id'];
			} else {
				// set token:
				$query_set_token = 'INSERT INTO `apps` (`token`) VALUES ("'.$token.'")';
				$this->execute($query_set_token);
				$res = $this->execute($query_get_token);
				$result = $res->fetch_assoc();
				return $result['id'];
			}
		} else {
			echo 'Wrong database connection';
		}
	}

	public function checkPetName($name) {
		$query_get_name = "select * from pawsfiles where nickname = '".$name."'";
		$res = $this->execute($query_get_name);
		
		if ($res) {
			$result = $res->fetch_assoc();
			if ($result['nickname'] == $name) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}


}
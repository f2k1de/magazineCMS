<?php

class DB {
	private $connection = NULL;
	private $result = NULL;
	private $counter=NULL;

	private $dbconfig;

	public function __construct(){
		$this->$dbconfig = require('config.php')['db'];
		$this->connection = new mysqli($this->dbconfig['host'], $this->dbconfig['user'], $this->dbconfig['password'], $this->dbconfig['database']);
		$this->connection->query("SET NAMES 'utf8'");
	}

	public function escape($string) {
		return $this->connection->real_escape_string($string);
	}

	public function disconnect(){	
		if (is_resource($this->connection===true))	
			$this->connection->close();
	}
	
	public function query($query) {
		$this->result = $this->connection->query($query);
		$this->counter = NULL;
	}

	public function fetchRow() {
		return $this->result->fetch_assoc();
	}

	public function fetchAllRows() {
		$array = array();
		while ($row = $this->result->fetch_assoc()) {
			$array[] = $row;
		}
		return $array;
	}

	public function count() {	
		if($this->counter===NULL && is_resource($this->result)===true){	
			$this->counter = $this->connection->num_rows($this->result);
		}
		return $this->counter;
	}
}

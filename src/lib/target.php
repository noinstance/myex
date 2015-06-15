<?php

class Target {

	private $database;
	private $username;
	private $password;

	public function __construct () {
	}

	public function getCreateScript() {
		
		$script = [];
		$script[] = "drop database if exists $this->database;";
		$script[] = "create database $this->database;";
		$script[] = "create user '$this->username'@'localhost' identified by '$this->password';";
		$script[] = "grant all privileges on $this->database.* to $this->username@localhost;";

		return implode("\n", $script);
	}

	public function setDatabase($database) {
		$this->database = $database;
	}

	public function setUsername($username) {
		$this->username = $username;
	}

	public function setPassword($password) {
		$this->password = $password;
	}

	public function getDatabase() {
		return $this->database;
	}

	public function getUsername() {
		return  $this->username;
	}

	public function getPassword() {
		return $this->password;
	}
}
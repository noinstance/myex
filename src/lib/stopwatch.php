<?php
class Stopwatch {

	private $startTime;
	private $endTime;

	public function start() {
		$this->startTime = time();
	}

	public function stop() {
		$this->endTime = time();
	}

	public function elapsed() {
		$elapsed = $this->endTime - $this->startTime;
		return gmdate("H:i:s", $elapsed);
	}
}
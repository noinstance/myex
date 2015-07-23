<?php
class Stopwatch {

	private $startTime;
	private $endTime;
	private $waiting = false;

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

	public function toggleWaiting() {
		if(!$this->waiting) {
			$this->startWaiting();
		} else {
			$this->stopWaiting();
		} 
	}

	private function startWaiting() {
		$this->waiting = true;

		$time_start = time();
		while($this->waiting) {
			
      		$time_end = time();
			$gap = $time_end - $time_start;

			if($gap >= 1) {
	          Logger::log(".", false);
	          $time_start = time();
	        }
		}
	}

	private function stopWaiting() {
		$this->waiting = false;
		logger::log('stop waiting');
	}
}
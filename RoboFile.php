<?php

use \Robo\Tasks;

/**
 * Robo task runner.
 */
class RoboFile extends Tasks {

	// Run all tests
	public function test() {
		// starts PHP server in background
		$this->taskServer(8000)
			->background()
			->dir('public')
			->run();

		// Run unit tests
		return $this->taskCodecept()
			->run();
	}
}

<?php

namespace Parvula\Console;

use Composer\Script\Event;

class DevTasks {

	/**
	 * Serve Parvula with Php's internal development server
	 * Warning: Do NOT use this server for production
	 *
	 * @param Event $event
	 */
	public static function runDevelopmentServer(Event $event) {
		$address = '0.0.0.0';
		$port = 8000;
		$command = 'php -S ' . $address . ':' . $port . ' -t public';

		if (!$event->isDevMode()) {
			echo 'Do not serve Parvula with this command in production mode' . PHP_EOL;
		}

		$address !== '0.0.0.0' && $address !== '127.0.0.1' ?: $address = 'localhost';
		echo 'Running dev server on http://'. $address . ':' . $port . PHP_EOL;
		echo exec($command);
	}
}

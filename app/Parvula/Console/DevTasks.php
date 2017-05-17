<?php

namespace Parvula\Console;

use Composer\Script\Event;

class DevTasks {

	/**
	 * Serve Parvula with Php's internal development server
	 * Warning: Do NOT use this server for production
	 *
	 * By default, the server will bind on 127.0.0.1:8000
	 *
	 * @param Event $event
	 */
	public static function runDevelopmentServer(Event $event) {
		$address = '127.0.0.1';
		$port = 8000;
		$args = $event->getArguments();
		if (count($args) === 1) {
			$port = $args[0];
		} elseif (count($args) > 1) {
			$address = $args[0];
			$port = $args[1];
		}

		$command = 'php -S ' . $address . ':' . $port . ' -t public';
		$io = $event->getIO();

		if (!$event->isDevMode()) {
			$io->write('Do not serve Parvula with this command in production mode');
		}

		$address !== '0.0.0.0' && $address !== '127.0.0.1' ?: $address = 'localhost';
		$io->write('Running dev server on http://'. $address . ':' . $port);
		$io->write(exec($command));
	}
}

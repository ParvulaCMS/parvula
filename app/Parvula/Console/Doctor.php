<?php

namespace Parvula\Console;

use Composer\Script\Event;

class Doctor {

    static protected $io;

	private static function test($str, $fn) {
		static::$io->write('# ' . $str);
		if (0 === ($res = $fn(0))) {
			static::$io->write('> OK');
		}
		static::$io->write('');
		return $res;
	}

	/**
	 * Try to see if everything is config in the right way
	 *
	 * @param Event $event
	 */
	public static function analyse(Event $event) {
		error_reporting(E_ALL);
		$_executeFromComposerScript = true;
		require 'app/bootstrap.php';

		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);

		static::$io = $event->getIO();

		$errors = 0;
		$checkAPI = true;
		$args = $event->getArguments();
		if (isset($args[1])) {
			$checkAPI = (bool) $args[1];
		}

		$mustExists = [
			_CONFIG_ . 'site.yml',
			_CONFIG_ . 'system.yml',
			_CONFIG_ . 'database.yml',
			_ROOT_ . 'composer.json',
			_PUBLIC_ . 'index.php'
		];

		$shouldBeWritable = [
			_DATA_ . 'config',
			_DATA_ . 'pages',
			_DATA_ . 'users'
		];

		$modulesNeeded = [
			'Core',
			'SPL',
			'json',
			'mbstring'
		];

		// Print some system and php info
		static::$io->write('# PHP DOCTOR');
		static::$io->write('');
		static::$io->write('PHP Version: ' . phpversion());
		static::$io->write('Zend Version: ' . zend_version());
		static::$io->write('Parvula Version: ' . _VERSION_);
		static::$io->write('System Info: ' . php_uname());
		static::$io->write('DIRECTORY_SEPARATOR: ' . DIRECTORY_SEPARATOR);
		static::$io->write('PHP_SHLIB_SUFFIX: ' . PHP_SHLIB_SUFFIX);
		static::$io->write('PATH_SEPARATOR: ' . PATH_SEPARATOR);
		static::$io->write('');

		$errors += static::test('Check PHP version', function ($errors) {
			if (!version_compare(phpversion(), '5.5.9', '>=')) {
				echo ('You PHP version is not compatible with Parvula');
				return 1;
			}
			return 0;
		});

		$errors += static::test('Check Composer configuration', function ($errors) {
			if (!is_dir(_VENDOR_)) {
				++$errors;
				static::$io->writeError('You must install the dependencies with `composer install` to run Parvula');
			}
			return $errors;
		});

		$errors += static::test('Check if files exist', function ($errors) use ($mustExists) {
			return array_reduce($mustExists, function ($errors, $path) {
				if (!is_readable($path)) {
					++$errors;
					static::$io->writeError($path . ' must exists');
				}
				return $errors;
			}, 0);
		});

		$errors += static::test('Check if needed modules are loaded', function () use ($modulesNeeded) {
			$modules = array_flip(get_loaded_extensions());
			return array_reduce($modulesNeeded, function ($errors, $module) use ($modules) {
				if (!isset($modules[$module])) {
					++$errors;
					static::$io->writeError('Module ' . $module . ' not found !');
				}
				return $errors;
			}, 0);
		});

		if ($checkAPI) {
			$dbConf = $app['config:database'];

			if ($dbConf->get('database') === 'mongodb') {
				// Mongo
				// TODO
			} else {
				// Flatfiles
				$errors += static::test('Check if folders are writable [API]', function () use ($shouldBeWritable) {
					return array_reduce($shouldBeWritable, function ($errors, $path) {
						if (!is_writable($path)) {
							++$errors;
							static::$io->writeError($path . ' should be writable');
						}
						return $errors;
					}, 0);
				});
			}
		}

		static::test('Check Parvula stability', function () {
			if (preg_match('/dev|alpha|beta|RC/', _VERSION_)) {
				static::$io->writeError('> You use a non stable version of Parvula');
				return 1;
			}
			return 0;
		});

		static::test('Check Parvula production mode', function () use ($config) {
			if ((bool) $config->get('debug', false)) {
				static::$io->write('> Parvula is in debug mode');
				return 1;
			}
			return 0;
		});

		if (!$errors) {
			static::$io->write('>> Everything is OK !');
		}
	}
}

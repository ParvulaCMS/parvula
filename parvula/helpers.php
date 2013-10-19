<?php

/**
 * Load aliases
 * @param array $aliases 
 * @return
 */
function loadAliases(array $aliases) {
	foreach ($aliases as $alias => $className) {
		class_alias($className, $alias);
	}
}

/**
 * Parse configuration data in object
 * @param string $configData 
 * @return StdClass Config object
 */
function parseConfigData($configData) {
	preg_match_all('/([^\s:]+)[\s:]+(.+)/', $configData, $matches);

	$conf = new \StdClass();
	for ($i = 0; $i <= count($matches); ++$i) { 
		$conf->{$matches[1][$i]} = trim($matches[2][$i]);
	}

	return $conf;
}

/**
 * Handle exception
 * @param Exception $e 
 * @return
 */
function exceptionHandler(Exception $e) {
	@header("Content-Type:text/plain");

	echo 'Caught ', basename(str_replace('\\', '/', get_class($e))),
	': "', $e->getMessage(), '" (', $e->getFile(), ':', $e->getLine(), ")\n\n",
	$e->getTraceAsString();

	exit;
}

set_exception_handler('exceptionHandler');


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
 * Handle exceptions
 * @param Exception $e
 * @return
 */
function exceptionHandler(Exception $e) {
	// @header("Content-Type:text/plain");

	echo "<h1 style='font-size:24px'>Error</h1>\n",
	"<pre style='background:#f8f8f8;padding:8px'>\n",
	'<b>Caught ', basename(str_replace('\\', '/', get_class($e))),
	': "', $e->getMessage(), '"</b> (', $e->getFile(), ':', $e->getLine(), ")\n\n",
	'<span style="color:#811">', $e->getTraceAsString(), "</span>",
	"\n</pre>";

	exit;
}

set_exception_handler('exceptionHandler');

/**
 * Unique ID for session
 * @return string
 */
function uidSession() {
	$ip = $_SERVER["REMOTE_ADDR"];

	return sha1(sha1('!#;' . $ip) . $_SERVER['HTTP_USER_AGENT']);
}

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

//@TODO cleaner
function getPlugin() {
	$plugins = [];
	if ($handle = opendir(PLUGINS)) {
	    while (false !== ($entry = readdir($handle))) {
	        if (strlen($entry) > 1 && $entry[0] !== "." && substr($entry, 0, 2) !== '__') {
				$plugins[] =  "Plugin\\" . $entry . "\\$entry";
	        }
	    }
	    closedir($handle);
	}

	return $plugins;
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
	$className = basename(str_replace('\\', '/', get_class($e)));

	echo "<h2 style='font-size:24px'>Error</h2>\n",
	"<pre style='background:#f8f8f8;padding:8px'>\n",
	'<b>Caught ', $className,': "', $e->getMessage(),
	'"</b> (', $e->getFile(), ':', $e->getLine(), ")\n\n",
	'<span style="color:#811">', $e->getTraceAsString(), "</span>",
	"\n</pre>";

	exit;
}

/**
 * Unique ID for session
 * @return string
 */
function uidSession() {
	$ip = $_SERVER["REMOTE_ADDR"];

	return sha1(sha1('!#;' . $ip) . $_SERVER['HTTP_USER_AGENT']);
}

/**
 * Check if we are admin
 * @return boolean
 */
function isParvulaAdmin() {
	if (session_id() === '') {
		session_start();
	}

	if (isset($_SESSION, $_SESSION['login']) && $_SESSION['login'] === true) {
		session_regenerate_id(true);
		$logged = $_SESSION['login'];

		$sid = uidSession();
		if(isset($_SESSION['id']) && $_SESSION['id'] !== $sid) {
			session_destroy();
			return false;
		}

		return true;
	} else {
		return false;
	}
}

/**
 * Alias for HTML::sEcho
 * @return string
 */
function sEcho() {
	return call_user_func_array("HTML::sEcho", func_get_args());
}

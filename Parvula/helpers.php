<?php

/**
 * Load aliases
 *
 * @param array $aliases
 * @return
 */
function loadAliases(array $aliases) {
	foreach ($aliases as $alias => $className) {
		class_alias($className, $alias);
	}
}

//@TODO cleaner
function getPluginList(array $except = []) {
	$plugins = [];
	if (is_dir(_PLUGINS_) && $handle = opendir(_PLUGINS_)) {
		while (false !== ($entry = readdir($handle))) {
			if (strlen($entry) > 1 && $entry[0] !== "." && $entry[0] !== "_"
				&& !in_array($entry, $except)) {
				$plugins[] =  "Plugin\\" . $entry . "\\$entry";
			}
		}
		closedir($handle);
	}

	return $plugins;
}

/**
 * Handle exceptions
 *
 * @deprecated
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

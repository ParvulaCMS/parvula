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
				&& is_dir(_PLUGINS_ . $entry) && !in_array($entry, $except)) {
				$plugins[] =  "Plugin\\" . $entry . "\\$entry";
			}
		}
		closedir($handle);
	}

	return $plugins;
}

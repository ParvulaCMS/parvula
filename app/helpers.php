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

/**
 * Get plugin fully qualified class name
 *
 * @param  string $pluginName
 * @return string
 */
function getPluginClassname($pluginName) {
	return 'Plugins\\' . $pluginName . '\\' . $pluginName;
}

//@TODO to clean
function getPluginList(array $except = []) {
	$plugins = [];
	if (is_dir(_PLUGINS_) && $handle = opendir(_PLUGINS_)) {
		while (false !== ($entry = readdir($handle))) {
			if (strlen($entry) > 1 && $entry[0] !== '.' && $entry[0] !== '_'
				&& is_dir(_PLUGINS_ . $entry) && !in_array($entry, $except)) {
				$plugins[] = getPluginClassname($entry);
			}
		}
		closedir($handle);
	}

	return $plugins;
}

/**
 * List pages and children
 *
 * @param $pages Array of Page
 * @param $options Array of options (options available: ul, li, level, liCallback)
 * @return string Html list of pages
 */
function listPagesAndChildren(array $pages, array $options, $level = 9) {
	$ul = isset($options['ul']) ? $options['ul'] : '';
	$li = isset($options['li']) ? $options['li'] : '';
	$liCallback = isset($options['liCallback']) ? $options['liCallback'] : null;
	$level = isset($options['level']) ? $options['level'] : 9;
	if ($level > 0) {
		$str = '<ul ' . $ul . '>' . PHP_EOL;
		foreach($pages as $page) {
			$anch = $page->title;
			if ($liCallback !== null) {
				$anch = $liCallback($page);
			}
			$str .= '<li '. $li. '>' . $anch;
			if ($page->getChildren()) {
				--$options['level'];
				$str .= listPagesAndChildren($page->getChildren(), $options);
				++$options['level'];
			}
			$str .= '</li>' . PHP_EOL;
		}
		return $str . '</ul>' . PHP_EOL;
	}
	return '';
}

/**
 * List parent pages
 *
 * @param $pages Array of Page
 * @return array of pages
 */
function listPagesRoot(array $pages) {
	return array_filter($pages, function($page) {
		return !$page->parent;
	});
}

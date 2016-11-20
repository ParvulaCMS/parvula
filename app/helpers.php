<?php

use Parvula\Parvula;
use Symfony\Component\Yaml\Yaml;

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
/**
 * Get plugins list
 * @param  array $except
 * @return array
 */
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

	// TEST - TODO clean parser
	$pluginsExtra = (array) Yaml::parse(file_get_contents(_PLUGINS_ . 'plugins.yml'));

	return array_merge($plugins, $pluginsExtra);
}

/**
 * List parent pages
 *
 * @depreciated
 * @param $pages Array of Page
 * @return array of pages
 */
function listPagesRoot(array $pages) {
	return array_filter($pages, function ($page) {
		return !$page->parent;
	});
}

/**
 * url will generates a fully qualified URL
 *
 * @param  string $path optional
 * @return string
 */
function url($path = '') {
	return Parvula::getRelativeURIToRoot($path);
}

/**
 * Get application instance
 *
 * @param  string $key optional
 * @return mixed
 */
function app($key = null) {
	$app = Parvula::getContainer();
	if ($key === null) {
		return $app;
	}
	return $app[$key];
}

/**
 * Get main app folder path
 *
 * @param  string $path optional
 * @return string
 */
function appPath($path = '') {
	return _APP_ . $path;
}

/**
 * Get themes folder path
 *
 * @param  string $path optional
 * @return string
 */
function themesPath($path = '') {
	return _THEMES_ . $path;
}

/**
 * Get plugins folder path
 *
 * @param  string $path optional
 * @return string
 */
function pluginsPath($path = '') {
	return _PLUGINS_ . $path;
}

/**
 * Get uploads folder path
 *
 * @param  string $path optional
 * @return string
 */
function uploadsPath($path = '') {
	return _UPLOADS_ . $path;
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
		foreach ($pages as $page) {
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

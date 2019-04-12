<?php

use Parvula\Parvula;

/**
 * Create multiple class aliases.
 *
 * @param array $aliases
 */
function classAliases(array $aliases) {
	foreach ($aliases as $alias => $className) {
		class_alias($className, $alias);
	}
}

/**
 * Get plugin fully qualified class name.
 *
 * @param  string $pluginName
 * @return string
 */
function getPluginClassname(string $pluginName) {
	return 'Plugins\\' . $pluginName . '\\' . $pluginName;
}

//@TODO to clean
/**
 * Get plugins list.
 *
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

	// Add extra plugins
	if (is_file(_PLUGINS_ . 'plugins.yml')) {
		$pluginsExtra = (array) app('fileParser')->read(_PLUGINS_ . 'plugins.yml');
		$plugins = array_merge($plugins, $pluginsExtra);
	}

	// Excluded plugins
	if (is_file(_PLUGINS_ . 'plugins-disabled.yml')) {
		$pluginsDisabled = (array) app('fileParser')->read(_PLUGINS_ . 'plugins-disabled.yml');
		$plugins = array_diff($plugins, $pluginsDisabled);
	}

	return $plugins;
}

/**
 * List parent pages.
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
 * Url will generates a fully qualified URL.
 *
 * @param  string $path optional
 * @return string
 */
function url(string $path = '') {
	$base = app('config')->get('urlBase');
	if ($base) {
		return $base . $path;
	}

	$prefix = app('config')->get('urlPrefix', '');

	return $prefix . Parvula::getRelativeURIToRoot($path);
}

/**
 * Get application instance.
 *
 * @param  string|null $key optional
 * @return mixed
 */
function app(?string $key = null) {
	$app = Parvula::getContainer();
	if ($key === null) {
		return $app;
	}

	return $app[$key];
}

/**
 * Get main app folder path.
 *
 * @param  string $path optional
 * @return string
 */
function appPath(string $path = '') {
	return _APP_ . $path;
}

/**
 * Get themes folder path.
 *
 * @param  string $path optional
 * @return string
 */
function themesPath(string $path = '') {
	return _THEMES_ . $path;
}

/**
 * Get plugins folder path.
 *
 * @param  string $path optional
 * @return string
 */
function pluginsPath(string $path = '') {
	return _PLUGINS_ . $path;
}

/**
 * Get uploads folder path.
 *
 * @param  string $path optional
 * @return string
 */
function uploadsPath(string $path = '') {
	return _UPLOADS_ . $path;
}

/**
 * List pages and children.
 *
 * @param array $pages Array of Page
 * @param array $options Array of options (options available: ul, li, level, liCallback)
 * @return string Html list of pages
 */
function listPagesAndChildren(array $pages, array $options) {
	$ul = $options['ul'] ?? '';
	$li = $options['li'] ?? '';
	$liCallback = $options['liCallback'] ?? null;
	$level = $options['level'] ?? 9;
	if ($level > 0) {
		$str = '<ul ' . $ul . '>' . PHP_EOL;
		foreach ($pages as $page) {
			$anch = $page->title;
			if ($liCallback !== null) {
				$anch = $liCallback($page);
			}
			$str .= '<li ' . $li . '>' . $anch;
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

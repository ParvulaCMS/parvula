<?php

// ----------------------------- //
// # Core config
// ----------------------------- //

return [
	// Show errors if debug is enabled
	'debug' => true,

	// Class aliases
	'aliases' => [
		'HTML' => 'Parvula\Core\Html',
		'Asset' => 'Parvula\Core\Config',
		'Asset' => 'Parvula\Core\Asset'
	],

	// Plugins to load
	'plugins' => [
		// 'Plugin\Gallery\Gallery'
	],

	// You can force this option with a boolean or leave it on 'auto' detection
	'URLRewriting' => 'auto',

	// Home page
	'homePage' => 'home',

	// Error page
	'errorPage' => '_404',

	// Extension for files in ./data
	'fileExtension' => 'md',

	// How to sort pages (php.net/manual/en/function.array-multisort.php)
	'typeOfSort' => SORT_ASC,

	// Sort pages from specific field (like title, index or whatYouWant)
	'sortField' => 'index',

	// Config file to read
	'userConfig' => 'site.conf',

	// Class to (un)serialize pages (must implements PageSerializerInterface)
	'defaultPageSerializer' => 'Parvula\Core\MarkdownPageSerializer',

	// API only - Class to (un)serialize pages (must implements PageSerializerInterface)
	'apiDefaultPageSerializer' => 'Parvula\Core\ParvulaPageSerializer',

	// Administration URL (/parvula-admin by default)
	'adminURL' => 'parvula-admin',

	// /adminFolderName (/admin by default) will redirect you to administration
	'adminAliasFolder' => true
];

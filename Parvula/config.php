<?php

// ----------------------------- //
//  System config
// ----------------------------- //

return [
	// Show errors if debug is enabled
	'debug' => true,

	// Class aliases
	'aliases' => [
		'HTML' => 'Parvula\Core\Html',
		'Conf' => 'Parvula\Core\Config',
		'Asset' => 'Parvula\Core\Asset',
		'Component' => 'Parvula\Core\Component'
	],

	// List of disabled plugins
	'disabledPlugins' => [

	],

	// You can force this option with a boolean or leave it on 'auto' detection
	'URLRewriting' => 'auto',

	// Default home page
	'homePage' => 'home',

	// Error page
	'errorPage' => '_404',

	// Extension for files in ./data/pages
	'fileExtension' => 'md',

	// How to sort pages (SORT_ASC, SORT_DESC) (php.net/manual/en/function.array-multisort.php)
	'typeOfSort' => SORT_ASC,

	// Sort pages from specific field (like title, index or whatYouWant)
	'sortField' => 'slug',

	// File extensions in 'media' folder
	'mediaExtensions' => ['jpg', 'jpeg', 'png', 'gif'],

	// User config file to read
	'userConfig' => 'site.conf.json',

	// Class to parse pages (must implements ContentParserInterface), can be null
	'contentParser' => '\Parvula\Core\ContentParser\Markdown',

	// Class to (un)serialize pages (must implements PageSerializerInterface)
	'pageSerializer' => '\Parvula\Core\PageSerializer\Parvula',
	// 'pageSerializer' => 'Parvula\Core\PageSerializer\ParvulaJson',

];

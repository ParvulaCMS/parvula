<script src="https://gist.github.com/<?= str_replace([':', ' '], '/', trim($section->slug)) ?>.js"></script>
<?php
return [
	'props' => [
		'slug' => [
			'type' => 'string',
			'required' => true
		]
	]
];

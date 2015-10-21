<?php

namespace Parvula\Core\PageSerializer;

use Parvula\Core\Page;

/**
 * HtmlPageSerializer class
 *
 * @package Parvula
 * @since 0.4.0
 * @author Fabien Sa
 * @license MIT License
 */
class Html extends JsonPageSerializer implements PageSerializerInterface {

	private $data = [];
	private $parvula = null;

	/**
	 * Serialize page
	 * @param Page $page
	 * @return boolean
	 */
	public function serialize(Page $page) {

		$data = parent::serialize($page);

		return $data;
	}

	/**
	 * Unserialize data
	 * @param string $data
	 * @return Page
	 */
	public function unserialize($filePath, $data = null) {
		// echo "<h1>A</h1>\n";
		//
		if($this->parvula === null) {
			$this->parvula = new Parvula;
		}

		$page = parent::unserialize($filePath, $data);

// ---------  TEMPLATE SYS [ALPHA]
		// foreach ($page as $key => $value) {
		// 	// echo "<h1>$key - $value</h1>" . PHP_EOL;
		// 	$page->content = str_replace('{{' . $key . '}}', $value, $page->content);
		// }

		// $page->content = preg_replace_callback(
		// 	"/\[{2}include (\w+)\]{2}/", "self::replaceInclude", $page->content);

		// $page->content = preg_replace_callback("/\[{2}editable ([\{\}\w]+)\]{2}/",
		// 	function($arr) {
		// 		// echo json_encode($pp);
		// 		// echo json_encode($page);
		// 		// echo "---EDITABLE: {$arr[0]} ---";

		// 		$parvula = new Parvula();
		// 		$page = $parvula->getPage($arr[1]);
		// 		// $page->left = "home";
		// 		// $vars += (array)$page;

		// 		return '<div file="' . $page->url . '" editable="true">' . $page->content . '</div>';
		// 	},
		// 	$page->content
		// );

		// foreach ($page as $key => $value) {
		// 	$page->content = str_replace('{{' . $key . '}}', $value, $page->content);
		// }

		// foreach ($page as $key => $value) {
		// 	// echo "<h1>$key - $value</h1>" . PHP_EOL;
		// 	$page->content = str_replace('{{' . $key . '}}', $value, $page->content);
		// }

// ---------

// // $vaaa = $this->unserialize("home");
// $parvula = new Parvula();
// $contentB = $parvula->getPage('home');
// 		$page->content = str_replace('{{include ' . 'home' . '}}', $contentB, $page->content);

		//
		// INCLUDE PAGE
		// -> iseditable sera en js (?)

		return $page;
	}

	// private function replaceInclude($matches) {
	//
	// 	// str_replace("", replace, subject)
	//
	// 	// $parvula = new Parvula();
	// 	$page = $this->parvula->getPage($matches[1]);
	// 	$this->data += (array)$page;
	// 	// $page = parent::unserialize($filePath, $data);
	//
	// 	return $page->content;
	// }

}

<?php

namespace Parvula\Core;

use Parvula\Core\Config;

/**
 * HtmlPageSerializer class
 *
 * @package Parvula
 * @since 0.4.0
 * @author Fabien Sa
 * @license MIT License
 */
class HtmlContentParser extends ParvulaHtmlJsonPageSerializer implements PageSerializerInterface {

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
		if($this->parvula === null) {
			$this->parvula = new Parvula;
		}

		$page = parent::unserialize($filePath, $data);

		return $page;
	}

	private function replaceInclude($matches) {
		// str_replace("", replace, subject)

		// $parvula = new Parvula();
		$page = $this->parvula->getPage($matches[1]);
		$this->data += (array)$page;
		// $page = parent::unserialize($filePath, $data);

		return $page->content;
	}

}

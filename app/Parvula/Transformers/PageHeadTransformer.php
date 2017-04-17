<?php

namespace Parvula\Transformers;

use Parvula\Models\Page;

class PageHeadTransformer extends Transformer {
	public function apply(Page $page) {
		unset($page->content);
		$arr = (array) $page->toArray(true);
		$arr['title'] = $page->title;
		if ($page->hasChildren()) {
			$arr['children'] = $page->children->map($this);
		} else {
			unset($arr['children']);
		}
		$arr['content'] = [
			'href' => '/pages/' . $page->slug
		];

		if ($page->sections !== []) {
			$arr['sections'] = [
				'href' => '/pages/' . $page->slug
			];
		} else {
			unset($arr['sections']);
		}

		return $arr;
	}
}

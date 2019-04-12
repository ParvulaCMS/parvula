<?php

namespace Parvula\Transformers;

use Parvula\Models\Page;

class PageHeadTransformer extends Transformer
{
	public function apply(Page $page) {
		$arr = $page->getMeta();

		$arr['content'] = [
			'href' => '/pages/' . $page->slug,
		];

		if ($page->hasChildren()) {
			$arr['children'] = $page->children->map($this);
		}

		if ($page->sections !== []) {
			$arr['sections'] = [
				'href' => '/pages/' . $page->slug,
			];
		}

		return $arr;
	}
}

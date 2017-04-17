<?php

namespace Parvula\Collections\CollectionTraits;

trait PageCollectionTrait {

	/**
	 * Show pages without a parent
	 *
	 * @return \Parvula\Collections\Collection
	 */
	public function withoutParent() {
		return $this->filter('parent', [false, null]);
	}

	/**
	 * Show visible pages
	 *
	 * @return \Parvula\Collections\Collection
	 */
	public function visible() {
		return $this->filter('hidden', [false, null]);
	}

	/**
	 * Show hiddden pages
	 *
	 * @return \Parvula\Collections\Collection
	 */
	public function hidden() {
		return $this->filter('hidden', [true]);
	}
}

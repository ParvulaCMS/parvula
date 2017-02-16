<?php

namespace Parvula\Collections\CollectionTraits;

trait PageCollectionTrait {

	/**
	 * Show pages without a parent
	 */
	public function withoutParent() {
		// TODO
		return $this->clone();
	}

	/**
	 * Show visible pages
	 */
	public function visible() {
		return $this->filter('hidden', [false, null]);
	}

	/**
	 * Show hiddden pages
	 */
	public function hidden() {
		return $this->filter('hidden', [true]);
	}
}

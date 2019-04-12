<?php

namespace Parvula\Collections\Traits;

/**
 * @mixin \Parvula\Collections\Collection
 */
trait PageCollectionTrait
{
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
	public function visible($showVisible = true) {
		if (!$showVisible) {
			return $this->hidden();
		}

		return $this->filter('hidden', [false, null]);
	}

	/**
	 * Show hidden pages
	 *
	 * @return \Parvula\Collections\Collection
	 */
	public function hidden() {
		return $this->filter('hidden', [true]);
	}
}

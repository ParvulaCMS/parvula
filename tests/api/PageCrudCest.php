<?php

class PageCrudCest extends APITest
{
	private $page1;
	private $page1Update;
	private $page1UpdateIncomplete;

	public function boot(APITester $I) {
		$this->page1 = [
			'slug'    => 'test1',
			'title'   => 'Test 1',
			'content' => '# My content'
		];

		$this->page1Update = [
			'slug'    => 'test1',
			'title'   => 'Test 1 updated',
			'content' => '# My updated content'
		];

		$this->page1UpdateIncomplete = [
			'slug'    => 'test1'
		];

		parent::boot($I);
	}

	// tests
	public function getOnePage(APITester $I) {
		$I->sendGET('/pages/home');
		$I->seeResponseCodeIs(200);
		$I->seeResponseContainsJson(['slug' => 'home']);
		$I->seeResponseJsonMatchesJsonPath('$.slug');
		$I->seeResponseJsonMatchesJsonPath('$.title');
	}

	public function getOnePageWithChildren(APITester $I) {
		$I->sendGET('/pages/parent');
		$I->seeResponseCodeIs(200);
		$I->seeResponseContainsJson(['slug' => 'parent']);
		$I->seeResponseJsonMatchesJsonPath('$.slug');
		$I->seeResponseJsonMatchesJsonPath('$.title');
		$I->seeResponseJsonMatchesJsonPath('$.children.[*]');
		$I->seeResponseJsonMatchesJsonPath('$.children.[0].slug');
		$I->seeResponseJsonMatchesJsonPath('$.children.[0].title');
	}

	public function failToGetOnePage(APITester $I) {
		$I->sendGET('/pages/notapage');
		$I->seeResponseCodeIs(404);
	}

	public function indexPagesOnlyIndex(APITester $I) {
		$I->sendGET('/pages?index');
		$I->seeResponseCodeIs(200);
		// Should be a flat array
		$I->seeResponseJsonMatchesJsonPath('$.[*]');
		$I->dontSeeResponseJsonMatchesJsonPath('$.[*].*');
	}

	public function indexPages(APITester $I) {
		$I->sendGET('/pages');
		$I->seeResponseCodeIs(200);
		// Should be deeper than a flat array
		$I->seeResponseJsonMatchesJsonPath('$.[*]');
		$I->seeResponseJsonMatchesJsonPath('$.[*].*');
		$I->seeResponseJsonMatchesJsonPath('$.[0].slug');
		$I->seeResponseJsonMatchesJsonPath('$.[0].title');
	}

	public function createANewPage(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendPOST('/pages', $this->page1);
		$I->seeResponseCodeIs(201);
	}

	public function cannotCreateANewPage(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendPOST('/pages', $this->page1);
		$I->seeResponseCodeIs(409);
	}

	public function updateAPage(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendPUT('/pages/' . $this->page1['slug'], $this->page1Update);
		$I->seeResponseCodeIs(200);
	}

	public function updateAPageWithIncompleteData(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendPUT('/pages/' . $this->page1['slug'], $this->page1UpdateIncomplete);
		$I->seeResponseCodeIs(400);
	}

	public function updateANoneExistingPage(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendPUT('/pages/nothing', $this->page1Update);
		$I->seeResponseCodeIs(500); // TODO 500 ?
	}

	public function deleteAPage(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendDelete('/pages/' . $this->page1['slug']);
		$I->seeResponseCodeIs(204);
	}

	public function cannotDeleteAPage(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendDelete('/pages/' . $this->page1['slug']);
		$I->seeResponseCodeIs(404);
	}
}

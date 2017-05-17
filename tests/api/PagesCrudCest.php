<?php

class PagesCrudCest extends APITest
{
	private $page1;
	private $page1Update;
	private $page1UpdateIncomplete;

	public function boot(APITester $I) {
		$page1Slug = 'test001' . substr(uniqid('', true), -5);
		$page2Slug = 'test002' . substr(uniqid('', true), -5);

		$this->page1 = [
			'slug'    => $page1Slug,
			'title'   => 'Test 1',
			'content' => '# My content'
		];

		$this->page2 = [
			'slug'    => $page2Slug,
			'title'   => 'Test 2',
			'content' => 'some *content*'
		];

		$this->page2child1 = [
			'slug'    => $page2Slug . '/child01',
			'title'   => 'A Child',
			'content' => 'some other content'
		];

		$this->page1Update = [
			'slug'    => $page1Slug,
			'title'   => 'Test 1 updated',
			'content' => '# My updated content'
		];

		$this->page1Patch = [[
			'op' => 'replace',
			'path' => '/content',
			'value' => 'Test 1 patched'
		]];

		$this->page1UpdateIncomplete = [
			'slug'    => $page1Slug
		];

		parent::boot($I);
	}

	// tests
	public function createANewPage(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendPOST('/pages', $this->page1);
		$I->seeResponseCodeIs(201);
	}

	public function createANewPageWithChild(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendPOST('/pages', $this->page2);
		$I->seeResponseCodeIs(201);

		$I->sendPOST('/pages', $this->page2child1);
		$I->seeResponseCodeIs(201);
	}

	public function getPageWithChild(APITester $I) {
		$slug = $this->page2['slug'];
		$I->sendGET('/pages/' . $slug . '?raw');
		$I->seeResponseCodeIs(200);

		$I->seeResponseContainsJson(['slug' => $slug]);
		$I->seeResponseJsonMatchesJsonPath('$.title');
		$I->seeResponseJsonMatchesJsonPath('$.children.[0].slug');
		$I->seeResponseJsonMatchesJsonPath('$.children.[0].title');
		$I->seeResponseJsonMatchesJsonPath('$.children.[0].content');
	}

	public function cannotCreateANewPage(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendPOST('/pages', $this->page1);
		$I->seeResponseCodeIs(409);
	}

	public function getOnePage(APITester $I) {
		$slug = $this->page1['slug'];
		$I->sendGET('/pages/' . $slug);
		$I->seeResponseCodeIs(200);
		$I->seeResponseContainsJson(['slug' => $slug]);
		$I->seeResponseJsonMatchesJsonPath('$.slug');
		$I->seeResponseJsonMatchesJsonPath('$.title');
	}

	public function getOnePageWithChildren(APITester $I) {
		$slug = $this->page2['slug'];
		$I->sendGET('/pages/' . $slug);
		$I->seeResponseCodeIs(200);
		$I->seeResponseContainsJson(['slug' => $slug]);
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

	public function updateAPage(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendPUT('/pages/' . $this->page1['slug'], $this->page1Update);
		$I->seeResponseCodeIs(200);
	}

	public function testUpdatedPage(APITester $I) {
		$slug = $this->page1['slug'];
		$I->sendGET('/pages/' . $slug . '?raw');
		$I->seeResponseCodeIs(200);

		$I->seeResponseContainsJson([
			'slug' => $this->page1Update['slug'],
			'title' => $this->page1Update['title']
		]);

		$I->seeResponseMatchesJsonType([
			'content' => 'string:regex(~' . preg_quote($this->page1Update['content']) . '\s*~)'
		]);
	}

	public function patchAPage(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendPATCH('/pages/' . $this->page1['slug'], $this->page1Patch);
		$I->seeResponseCodeIs(200);
	}

	public function testPatchedPage(APITester $I) {
		$slug = $this->page1['slug'];
		$I->sendGET('/pages/' . $slug . '?raw');
		$I->seeResponseCodeIs(200);

		$I->seeResponseContainsJson([
			'slug' => $this->page1Update['slug'],
			'title' => $this->page1Update['title']
		]);

		$I->seeResponseMatchesJsonType([
			'content' => 'string:regex(~' . preg_quote($this->page1Patch[0]['value']) . '\s*~)'
		]);
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

	public function deleteAPageAndAChild(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendDelete('/pages/' . $this->page2['slug']);
		$I->seeResponseCodeIs(204);

		$I->sendDelete('/pages/' . $this->page2child1['slug']);
		$I->seeResponseCodeIs(204);
	}

	public function cannotDeleteAPage(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendDelete('/pages/' . $this->page1['slug']);
		$I->seeResponseCodeIs(404);
	}
}

<?php


class PageCrudCest
{
	private $token;

	private $page1;

	public function _before(APITester $I) {
	}

	public function _after(APITester $I) {
		if (!empty($I->grabResponse())) {
			$I->seeResponseIsJson();
		}
	}

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

		$I->amHttpAuthenticated('admin', 'fofo');
		$I->sendGET('/login');
		$I->seeResponseCodeIs(201);
		$json = $I->grabResponse();

		$this->token = json_decode($json)->token;
	}

	// tests
	public function getOnePage(APITester $I) {
		$I->sendGET('/pages/home');
		$I->seeResponseCodeIs(200);
		$I->seeResponseContainsJson(['slug' => 'home']);
	}

	public function failToGetOnePage(APITester $I) {
		$I->sendGET('/pages/notapage');
		$I->seeResponseCodeIs(404);
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

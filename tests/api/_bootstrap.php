<?php
// Here you can initialize variables that will be available to your tests

class APITest
{
	protected $token;

	public function _after(APITester $I) {
		if (!empty($I->grabResponse())) {
			$I->seeResponseIsJson();
		}
	}

	public function boot(APITester $I) {
		$I->amHttpAuthenticated('admin', 'testpassword');
		$I->sendGET('/auth');
		$I->seeResponseCodeIs(201);
		$json = $I->grabResponse();

		$this->token = json_decode($json)->token;
	}
}

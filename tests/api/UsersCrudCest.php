<?php

class UsersCrudCest
{
	private $token;

	public function _after(APITester $I) {
		if (!empty($I->grabResponse())) {
			$I->seeResponseIsJson();
		}
	}

	public function boot(APITester $I) {
		$I->amHttpAuthenticated('admin', 'fofo');
		$I->sendGET('/login');
		$I->seeResponseCodeIs(201);
		$json = $I->grabResponse();

		$this->token = json_decode($json)->token;
	}

	public function testAuth(APITester $I) {
		$I->sendGET('/users');
		$I->seeResponseCodeIs(401);
	}

	public function indexUsers(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendGET('/users');
		$I->seeResponseCodeIs(200);
		$I->seeResponseJsonMatchesJsonPath('$.[*]');
	}

	public function showUsersNotFound(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendGET('/users/no_user');
		$I->seeResponseCodeIs(404);
	}

	public function showUser(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendGET('/users/admin');
		$I->seeResponseCodeIs(200);
		$I->seeResponseJsonMatchesJsonPath('$.[*]');
		$I->seeResponseJsonMatchesJsonPath('$.username');
		$I->dontSeeResponseJsonMatchesJsonPath('$.password');
	}

}

<?php

class UsersCrudCest extends APITest
{
	public function boot(APITester $I) {
		parent::boot($I);
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

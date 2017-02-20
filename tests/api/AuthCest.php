<?php

class AuthCest
{
	public function checkLoginFail(APITester $I) {
		$I->sendGET('/auth');
		$I->seeResponseCodeIs(400);
	}

	public function checkLoginWrongPassword(APITester $I) {
		$I->amHttpAuthenticated('admin', 'wrong_password');
		$I->sendGET('/auth');
		$I->seeResponseCodeIs(403);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['error' => 'BadCredentials']);
	}

	public function checkLogin(APITester $I) {
		$I->amHttpAuthenticated('admin', 'testpassword');
		$I->sendGET('/auth');
		$I->seeResponseCodeIs(201);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['status' => 'ok']);
		$I->seeResponseJsonMatchesJsonPath('$.token');
	}
}

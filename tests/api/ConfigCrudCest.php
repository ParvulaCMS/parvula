<?php

class ConfigCrudCest
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
		$I->sendGET('/config/site');
		$I->seeResponseCodeIs(401);
	}

	public function showConfigNotFound(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendGET('/config/noConfig');
		$I->seeResponseCodeIs(404);
	}

	public function showConfig(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendGET('/config/site');
		$I->seeResponseCodeIs(200);
		$I->seeResponseJsonMatchesJsonPath('$.[*]');
		$I->seeResponseJsonMatchesJsonPath('$.title');
		$I->seeResponseJsonMatchesJsonPath('$.theme');
	}

	// public function updateConfig(APITester $I) {
	// 	$I->amBearerAuthenticated($this->token);
	// 	$I->sendPUT('/config/site', [
	// 		'title' => ''
	// 	]);
	// 	$I->seeResponseCodeIs(200);
	// 	$I->seeResponseJsonMatchesJsonPath('$.[*]');
	// 	$I->seeResponseJsonMatchesJsonPath('$.title');
	// 	$I->seeResponseJsonMatchesJsonPath('$.theme');
	// }
}

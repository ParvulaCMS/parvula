<?php

class ConfigCrudCest extends APITest
{
	public function boot(APITester $I) {
		parent::boot($I);
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

	public function createConfig(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendPOST('/config/newConfigTest', [
			'foo' => 'bar'
		]);
		$I->seeResponseCodeIs(201);
	}

	public function showNewConfig(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendGET('/config/newConfigTest');
		$I->seeResponseCodeIs(200);
		$I->seeResponseJsonMatchesJsonPath('$.foo');
		$I->seeResponseContainsJson([
			'foo' => 'bar'
		]);
	}

	public function deleteNewConfig(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendDELETE('/config/newConfigTest');
		$I->seeResponseCodeIs(204);
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

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

	// Only valid with flat files TODO
	// public function showConfig(APITester $I) {
	// 	$I->amBearerAuthenticated($this->token);
	// 	$I->sendGET('/config/site');
	// 	$I->seeResponseCodeIs(200);
	// 	$I->seeResponseJsonMatchesJsonPath('$.[*]');
	// 	$I->seeResponseJsonMatchesJsonPath('$.title');
	// 	$I->seeResponseJsonMatchesJsonPath('$.theme');
	// }

	public function createConfig(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendPOST('/config', [
			'name' => 'newConfigTest',
			'data' => [
				'foo' => 'bar',
			],
		]);
		$I->seeResponseCodeIs(201);
	}

	public function shouldNotBeAbleToReCreateAConfig(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendPOST('/config', [
			'name' => 'newConfigTest',
			'data' => [
				'foo' => 'bar2',
			],
		]);
		$I->seeResponseCodeIs(409);
	}

	public function shouldNotProcessMalformedData(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendPOST('/config', [
			'title' => 'newConfigTest2',
			'data' => [],
		]);
		$I->seeResponseCodeIs(422);
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

	public function shouldGetAField(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendGET('/config/newConfigTest/foo');
		$I->seeResponseEquals('"bar"');
		$I->seeResponseCodeIs(200);
	}

	public function tryToGetAnNonexistentField(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendGET('/config/newConfigTest/nothing');
		$I->seeResponseCodeIs(404);
	}

	public function shouldUpdateAConfig(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendPUT('/config/newConfigTest', [
			'foo' => 'new bar put'
		]);
		$I->seeResponseCodeIs(204);
	}

	public function testUpdatedConfig(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendGET('/config/newConfigTest');
		$I->seeResponseCodeIs(200);
		$I->seeResponseJsonMatchesJsonPath('$.foo');
		$I->seeResponseContainsJson([
			'foo' => 'new bar put'
		]);
	}

	public function shouldPatchAConfig(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendPATCH('/config/newConfigTest', [[
			'op' => 'replace',
			'path' => '/foo',
			'value' => 'new bar'
		]]);
		$I->seeResponseCodeIs(204);
	}

	public function testPatchedConfig(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendGET('/config/newConfigTest');
		$I->seeResponseCodeIs(200);
		$I->seeResponseJsonMatchesJsonPath('$.foo');
		$I->seeResponseContainsJson([
			'foo' => 'new bar'
		]);
	}

	public function deleteNewConfig(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendDELETE('/config/newConfigTest');
		$I->seeResponseCodeIs(204);
	}

	public function shouldNotBePossibleToDeleteCoreConfig(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendDELETE('/config/system');
		$I->seeResponseCodeIs(404);
	}

	public function shouldNotBePossibleToDeleteCoreConfig2(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendDELETE('/config/database');
		$I->seeResponseCodeIs(404);
	}

	public function shouldNotBePossibleToDeleteInexistingConfig(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendDELETE('/config/foobartest');
		$I->seeResponseCodeIs(404);
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

<?php

class FilesCrudCest
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

	public function listFilesWithoutAuth(APITester $I) {
		$I->sendGET('/files');
		$I->seeResponseCodeIs(401);
	}

	public function listFiles(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendGET('/files');
		$I->seeResponseCodeIs(200);
		$I->seeResponseJsonMatchesJsonPath('$.[*]');
		$I->dontSeeResponseJsonMatchesJsonPath('$.[*].*');
	}
}

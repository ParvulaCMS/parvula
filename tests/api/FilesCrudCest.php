<?php

class FilesCrudCest extends APITest
{
	public function boot(APITester $I) {
		parent::boot($I);
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

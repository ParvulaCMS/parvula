<?php

class ThemesCrudCest extends APITest
{
	public function boot(APITester $I) {
		parent::boot($I);
	}

	public function testAuth(APITester $I) {
		$I->sendGET('/themes');
		$I->seeResponseCodeIs(401);
	}

	public function indexThemes(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendGET('/themes');
		$I->seeResponseCodeIs(200);
		$I->seeResponseJsonMatchesJsonPath('$.[*]');
		$I->dontSeeResponseJsonMatchesJsonPath('$.[*].*');
	}

	public function showThemeNotFound(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendGET('/themes/notheme');
		$I->seeResponseCodeIs(404);
	}

	public function showTheme(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendGET('/themes/galaxy');
		$I->seeResponseCodeIs(200);
		$I->seeResponseJsonMatchesJsonPath('$.[*]');
		$I->seeResponseJsonMatchesJsonPath('$.name');
		$I->seeResponseJsonMatchesJsonPath('$.layouts.[*]');
		$I->seeResponseJsonMatchesJsonPath('$.infos.[*]');
		$I->dontSeeResponseJsonMatchesJsonPath('$.layouts.[*].[*]');
	}
}

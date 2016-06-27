<?php

class ThemesCrudCest
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

<?php

class PagesSectionsCrudCest extends APITest
{
	private $page1;
	private $page1Update;
	private $page1UpdateIncomplete;

	public function boot(APITester $I) {
		$page1Slug = 'test001_s' . substr(uniqid('', true), -5);

		$this->page1 = [
			'slug'    => $page1Slug,
			'title'   => 'Test 1 Section',
			'content' => '# My content',
			'sections' => [
				[
					'name' => 'First section',
					'content' => '# Some content'
				],
				[
					'name' => 'Second section',
					'param1' => 'first',
					'content' => '# Some content 2'
				],
				[
					'name' => 'Third section',
					'param1' => 'first'
				]
			]
		];

		parent::boot($I);
	}

	public function createANewPageWithSection(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendPOST('/pages', $this->page1);
		$I->seeResponseCodeIs(201);
	}

	public function getOnePageWithSections(APITester $I) {
		$slug = $this->page1['slug'];
		$I->sendGET('/pages/' . $slug);
		$I->seeResponseCodeIs(200);
		$I->seeResponseContainsJson(['slug' => $slug]);
		$I->seeResponseJsonMatchesJsonPath('$.slug');
		$I->seeResponseJsonMatchesJsonPath('$.title');

		$I->seeResponseJsonMatchesJsonPath('$.sections.[*]');
		$I->seeResponseJsonMatchesJsonPath('$.sections.[0].name');
		$I->seeResponseJsonMatchesJsonPath('$.sections.[0].content');
		$I->dontSeeResponseJsonMatchesJsonPath('$.sections.[0].param1');

		$I->seeResponseJsonMatchesJsonPath('$.sections.[1].name');
		$I->seeResponseJsonMatchesJsonPath('$.sections.[1].param1');
		$I->seeResponseJsonMatchesJsonPath('$.sections.[1].content');

		$I->seeResponseJsonMatchesJsonPath('$.sections.[2].name');

		$I->dontSeeResponseJsonMatchesJsonPath('$.sections.[3].name');
	}

	public function deleteThePage(APITester $I) {
		$I->amBearerAuthenticated($this->token);
		$I->sendDelete('/pages/' . $this->page1['slug']);
		$I->seeResponseCodeIs(204);
	}
}

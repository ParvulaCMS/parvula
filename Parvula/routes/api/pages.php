<?php

namespace Parvula;

use Exception;
use Parvula\Core\Model\Page;
use Parvula\Core\Model\PagesFlatFiles;
use Parvula\Core\Exception\IOException;

if(!defined('ROOT')) exit;

$pages = $app['pages'];

//
// Public API
//

/*
 * Get all pages.
 * You can pass `?index` to just have the slugs
 *
 * GET /pages
 * return an array of Page
 *
 * 200 if success
 */
$router->get('/pages', function($req) use ($pages) {
	if (isset($req->query->index)) {
		// List of pages. Array<string> of slugs
		return apiResponse(true, $pages->index());
	}
	return apiResponse(true, $pages->all()->order(SORT_ASC, 'slug')->toArray());
});

/*
 * Get a specific page.
 * You can pass `?raw` to not parse the content.
 *
 * GET /pages/mypage
 * return a Page
 *
 * 200 if success
 */
$router->get('/pages/{slug:.+}', function($req) use ($pages) {
	return apiResponse(true, $pages->read($req->params->slug, !isset($req->query->raw)));
});

//
// Admin API
//
if(true === isParvulaAdmin()) {

	/*
	 * Create a new page.
	 * Need at least a `title` and a `slug`.
	 * Page MUST NOT exists.
	 *
	 * POST /pages/mypage
	 * title=My new title&slug=my_new_slug&content=Some content
	 *
	 * 200 if success
	 * 400 if no `title` or `slug`
	 * 409 if page already exists
	 * 404 if exception
	 */
	// TODO 'Location' header with link to /customers/{id} containing new ID.
	$router->post('/pages', function($req) use ($pages) {

		if (!isset($req->body->slug, $req->body->title)) {
			return apiResponse(400, 'This page need at least a slug and a title');
		}

		if ($pages->read($req->body->slug)) {
			return apiResponse(409, 'This page already exists');
		}

		$pageArr = (array) $req->body;

		try {
			$page = Page::pageFactory($pageArr);

			$res = $pages->create($req->body->slug, $page);
		} catch(Exception $e) {
			return apiResponse(404, $e->getMessage());
		}

		return apiResponse(201);
	});

	$router->map('PUT|DELETE', '/pages', function($req) {
		return apiResponse(404);
	});

	/*
	 * Update a page. Page MUST exists.
	 * Need at least a `title` and a `slug`
	 *
	 * PUT /pages/mypage
	 * title=My new title&slug=my_new_slug&content=Some content
	 *
	 * 200 if success
	 * 400 if no `title` or `slug`
	 * 404 if page does not exists or exception
	 */
	$router->put('/pages/{slug:.+}', function($req) use ($pages) {

		if (!isset($req->body->slug, $req->body->title)) {
			return apiResponse(400, 'This page need at least a `slug` and a `title`');
		}

		$pageArr = (array) $req->body;

		try {
			$page = Page::pageFactory($pageArr);

			$pages->update($req->params->slug, $page);
		} catch(Exception $e) {
			return apiResponse(404, $e->getMessage());
		}

		return apiResponse(200);
	});

	/*
	 * Update specific field(s) of a page.
	 *
	 * PATCH /pages/mypage
	 * title=My new title
	 *
	 * 200 if success
	 * 404 if exception
	 */
	$router->patch('/pages/{slug:.+}', function($req) use ($pages) {

		$pageArr = (array) $req->body;

		try {
			$pages->patch($req->params->slug, $pageArr);
		} catch(Exception $e) {
			return apiResponse(404, $e->getMessage());
		}

		return apiResponse(200);
	});

	/*
	 * Delete a page.
	 *
	 * DELETE /pages/mypage
	 *
	 * 200 if success
	 * 404 if not ok or exception
	 */
	$router->delete('/pages/{name:.+}', function($req) use ($pages) {
		try {
			$res = $pages->delete($req->params->name);
		} catch(\Exception $e) {
			return apiResponse(404, $e->getMessage());
		}

		return apiResponse($res);
	});

// } else {
	// @TODO
	// echo '{"message": "Not found or not logged"}';
}

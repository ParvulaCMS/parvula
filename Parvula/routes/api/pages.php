<?php

namespace Parvula;

use Exception;
use Parvula\Core\Model\Page;
use Parvula\Core\Model\PagesFlatFiles;
use Parvula\Core\Exception\IOException;

$pages = $app['pages'];

/**
 * @api {get} /pages Get all pages
 * @apiName Get all pages
 * @apiGroup Page
 *
 * @apiParam {string} [index] Optional You can pass `?index` to url to just have the slugs
 *
 * @apiSuccess (200) {array} pages An array of pages
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     [
 *       {"title": "home", "slug": "home", "content": "<h1>My home page</h1>..."},
 *       {"title": "about me", "slug": "about", "content": "..."}
 *     ]
 */
$router->get('', function ($req, $res) use ($pages) {
	if (isset($req->query->index)) {
		// List of pages. Array<string> of slugs
		return $res->send($pages->index());
	}
	return $res->send($pages->all()->order(SORT_ASC, 'slug')->toArray());
});

/**
 * @api {get} /pages/:slug Get a specific page
 * @apiName Get page
 * @apiGroup Page
 *
 * @apiParam {string} slug The slug of the page
 * @apiParam {string} [raw] Optional You can pass `?raw` to not parse the content.
 *
 * @apiSuccess (200) {Object} page A Page
 * @apiError (404) PageDoesNotExists This page does not exists
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "title": "Home page",
 *       "slug": "home",
 *       "content": "<h1>Home page<\/h1>"
 *     }
 *
 * @apiErrorExample {json} Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "PageDoesNotExists",
 *       "message": "This page does not exists"
 *     }
 */
$router->get('/{slug:.+}', function ($req, $res) use ($app, $pages) {
	if (isset($req->query->raw)) {
		$pages->setRenderer($app['pageRendererRAW']);
	}

	if (false === $result = $pages->read($req->params->slug)) {
		return $res->status(404)->send([
			'error' => 'PageDoesNotExists',
			'message' => 'This page does not exists'
		]);
	}

	return $res->send($result);
});

if($isAdmin()) {

	/**
	 * @api {post} /pages Create a new page
	 * @apiDescription Page **must not** exists
	 * @apiName Create page
	 * @apiGroup Page
	 *
	 * @apiParam {string} title Page title
	 * @apiParam {string} slug Page slug (eg. `/my-slug`)
	 * @apiParam {string} [content] Optional Page content
	 * @apiParam {mixed} [field] Optional Custom(s) field(s)
	 *
	 * @apiParamExample Request-Example:
	 *     title=My new title&slug=my_new_slug&content=Some content
	 *
	 * @apiSuccess (201) PageCreated Page was created
	 * @apiError (400) BadField This page need at least a slug and a title
	 * @apiError (404) PageException If exception
	 * @apiError (409) PageAlreadyExists Page already exists
	 *
	 * @apiErrorExample Error-Response:
	 *     HTTP/1.1 400 Bad Request
	 *     {
	 *       "error": "BadField",
	 *       "message": "This page need at least a slug and a title"
	 *     }
	 *
	 * @apiErrorExample Error-Response:
	 *     HTTP/1.1 409 Conflict
	 *     {
	 *       "error": "PageAlreadyExists",
	 *       "message": "This page already exists"
	 *     }
	 */
	// TODO 'Location' header with link to /pages/{id} containing new ID.
	$router->post('', function ($req, $res) use ($pages) {

		if (!isset($req->body->slug, $req->body->title)) {
			return $res->status(400)->send([
				'error' => 'BadField',
				'message' => 'This page need at least a slug and a title'
			]);
		}

		if ($pages->read($req->body->slug)) {
			return $res->status(409)->send([
				'error' => 'PageAlreadyExists',
				'message' => 'This page already exists'
			]);
		}

		$pageArr = (array) $req->body;

		try {
			$page = Page::pageFactory($pageArr);

			$result = $pages->create($page);
		} catch(Exception $e) {
			return $res->status(500)->send([
				'error' => 'PageException',
				'message' => $e->getMessage()
			]);
		}

		if (!$result) {
			return $res->status(500)->send([
				'error' => 'PageCannotBeCreated'
			]);
		}

		return $res->sendStatus(201);
	});

	$router->map('PUT|DELETE', '', function ($req, $res) {
		return $res->sendStatus(405); // Method Not Allowed
	});

	/**
	 * @api {put} /pages/:slug Update a page
	 * @apiDescription Page **must** exists to be updated
	 * @apiName Update page
	 * @apiGroup Page
	 *
	 * @apiParam {string} title Page title
	 * @apiParam {string} slug Page slug (eg. `/my-slug`)
	 * @apiParam {string} [content] Optional Page content
	 * @apiParam {mixed} [field] Optional Custom(s) field(s)
	 *
	 * @apiParamExample Request-Example:
	 *     title=My new title&slug=my_new_slug&content=Some content
	 *
	 * @apiSuccess (204) PageUpdated
	 * @apiError (400) BadField This page need at least a `slug` and a `title`
	 * @apiError (404) PageException If page does not exists or exception
	 */
	$router->put('/{slug:.+}', function ($req, $res) use ($pages) {

		if (!isset($req->body->slug, $req->body->title)) {
			return $res->status(400)->send([
				'error' => 'BadField',
				'message' => 'This page need at least a `slug` and a `title`'
			]);
		}

		$pageArr = (array) $req->body;

		try {
			$page = Page::pageFactory($pageArr);

			$pages->update($req->params->slug, $page);
		} catch(Exception $e) {
			return $res->status(500)->send([
				'error' => 'PageException',
				'message' => $e->getMessage()
			]);
		}

		$res->sendStatus(204);
	});

	/**
	 * @api {patch} /pages/:slug Update specific field(s) of a page
	 * @apiName Patch page
	 * @apiGroup Page
	 *
	 * @apiParamExample Request-Example:
	 *     title=My new title
	 *
	 * @apiParamExample Request-Example:
	 *     title=My new title&content=new content
	 *
	 * @apiSuccess (204) PagePatched
	 * @apiError (404) PageException If exception
	 */
	$router->patch('/{slug:.+}', function ($req, $res) use ($pages) {
		$pageArr = (array) $req->body;

		try {
			$pages->patch($req->params->slug, $pageArr);
		} catch(Exception $e) {
			return $res->status(500)->send([
				'error' => 'PageException',
				'message' => $e->getMessage()
			]);
		}

		return $res->sendStatus(204);
	});

	/*
	 * @api {delete} /page/:slug Delete a page.
	 * @apiName Delete page
	 * @apiGroup Page
	 *
	 * @apiSuccess (204) PagePatched
	 * @apiError (404) PageException If not ok or exception
	 */
	$router->delete('/{slug:.+}', function ($req, $res) use ($pages) {
		try {
			$result = $pages->delete($req->params->slug);
		} catch(Exception $e) {
			return $res->status(404)->send([
				'error' => 'PageException',
				'message' => $e->getMessage()
			]);
		}

		if (!$result) {
			return $res->status(500)->send([
				'error' => 'PageCannotBeDeleted'
			]);
		}

		return $res->sendStatus(204);
	});

// } else {
	// @TODO
	// echo '{"message": "Not found or not logged"}';
}

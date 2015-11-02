<?php

namespace Parvula;

use Exception;
use Parvula\Core\Model\Page;
use Parvula\Core\Model\PagesFlatFiles;
use Parvula\Core\Exception\IOException;

$pages = $app['pages'];

//
// Public API
//

/**
 * @api {get} /pages Get all pages
 * @apiName Get all pages
 * @apiGroup Page
 *
 * @apiParam {string} [index] Optional You can pass `?index` at the end to just have the slugs
 *
 * @apiSuccess {Page[]} pages An array of pages
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "status":"success",
 *       "data":[
 *         {"title": "home", "slug": "home", "content": "<h1>My home page</h1>..."},
 *         {"title": "about me", "slug": "about", "content": "..."}
 *       ]
*      }
 */
$router->get('/pages', function($req) use ($pages) {
	if (isset($req->query->index)) {
		// List of pages. Array<string> of slugs
		return apiResponse(true, $pages->index());
	}
	return apiResponse(true, $pages->all()->order(SORT_ASC, 'slug')->toArray());
});

/**
 * @api {get} /pages/:slug Get a specific page.
 * @apiName Get page
 * @apiGroup Page
 *
 * @apiParam {string} slug The slug of the page
 * @apiParam {string} [raw] Optional You can pass `?raw` to not parse the content.
 *
 * @apiSuccess {Page} page A Page
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *       "status": "success",
 *       "data": {"title":"Home page","slug":"home","content":"<h1>Home page<\/h1>"}
 *     }
 */
$router->get('/pages/{slug:.+}', function($req) use ($pages) {
	return apiResponse(true, $pages->read($req->params->slug, !isset($req->query->raw)));
});

//
// Admin API
//
// if(true === isParvulaAdmin()) {

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
	 * @apiSuccess PageCreated
	 *     HTTP/1.1 201 Created
	 *
	 * @apiError BadField No `title` or `slug`
	 *      HTTP/1.1 400
	 *
	 * @apiError Exception If exception
	 *      HTTP/1.1 404
	 *
	 * @apiError PageAlreadyExists Page already exists
	 *      HTTP/1.1 409
	 *
	 * @apiErrorExample Error-Response:
	 *     HTTP/1.1 400
	 *     {
	 *       "status": "error"
	 *       "message": "This page need at least a slug and a title"
	 *     }
	 *
	 * @apiErrorExample Error-Response:
	 *     HTTP/1.1 409
	 *     {
	 *       "status": "error"
	 *       "message": "This page already exists"
	 *     }
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

			$res = $pages->create($page);
		} catch(Exception $e) {
			return apiResponse(404, $e->getMessage());
		}

		return apiResponse(201);
	});

	$router->map('PUT|DELETE', '/pages', function($req) {
		return apiResponse(404);
	});

	/**
	 * @api {put} /pages/:slug Update a page
	 * @apiDescription Page MUST exists
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
	 * @apiSuccess PageCreated
	 *     HTTP/1.1 200 OK
	 *
	 * @apiError BadField No `title` or `slug`
	 *      HTTP/1.1 400
	 *
	 * @apiError PageAlreadyExists If page does not exists or exception
	 *      HTTP/1.1 404
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
	 * @apiSuccess PagePatched
	 *     HTTP/1.1 200 OK
	 *
	 * @apiError Exception If exception
	 *      HTTP/1.1 404
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
	 * @api {delete} /page/:slug Delete a page.
	 * @apiName Delete page
	 * @apiGroup Page
	 *
	 * @apiSuccess PagePatched
	 *     HTTP/1.1 200 OK
	 *
	 * @apiError Exception If not ok or exception
	 *      HTTP/1.1 404
	 */
	$router->delete('/pages/{slug:.+}', function($req) use ($pages) {
		try {
			$res = $pages->delete($req->params->slug);
		} catch(\Exception $e) {
			return apiResponse(404, $e->getMessage());
		}

		return apiResponse($res);
	});

// } else {
	// @TODO
	// echo '{"message": "Not found or not logged"}';
// }

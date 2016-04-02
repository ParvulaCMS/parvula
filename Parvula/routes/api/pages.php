<?php

namespace Parvula;

use Exception;
use Parvula\Core\Model\Page;
use Parvula\Core\Model\PagesFlatFiles;
use Parvula\Core\Exception\IOException;

$pages = $app['pages'];

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
$this->post('', function ($req, $res) use ($pages) {
	$parsedBody = $req->getParsedBody();

	if (!isset($parsedBody['slug'], $parsedBody['title'])) {
		return $this->api->json($res, [
			'error' => 'BadField',
			'message' => 'This page need at least a slug and a title'
		], 400);
	}

	if ($pages->read($parsedBody['slug'])) {
		return $this->api->json($res, [
			'error' => 'PageAlreadyExists',
			'message' => 'This page already exists'
		], 409);
	}

	$pageArr = (array) $parsedBody;

	try {
		$page = Page::pageFactory($pageArr);

		$result = $pages->create($page);
	} catch (Exception $e) {
		return $this->api->json($res, [
			'error' => 'PageException',
			'message' => $e->getMessage()
		], 500);
	}

	if (!$result) {
		return $this->api->json($res, [
			'error' => 'PageCannotBeCreated'
		], 500);
	}

	return $res->withStatus(201);
});

$this->map(['PUT', 'DELETE'], '', function ($req, $res) {
	return $res->withStatus(405); // Method Not Allowed
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
$this->put('/{slug:.+}', function ($req, $res, $args) use ($pages) {
	$parsedBody = $req->getParsedBody();

	if (!isset($parsedBody['slug'], $parsedBody['title'])) {
		return $this->api->json($res, [
			'error' => 'BadField',
			'message' => 'This page need at least a `slug` and a `title`'
		], 400);
	}

	$pageArr = (array) $parsedBody;

	try {
		$page = Page::pageFactory($pageArr);

		$pages->update($args['slug'], $page);
	} catch (Exception $e) {
		return $this->api->json($res, [
			'error' => 'PageException',
			'message' => $e->getMessage()
		], 500);
	}

	$res->withStatus(204);
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
$this->patch('/{slug:.+}', function ($req, $res, $args) use ($pages) {
	$pageArr = (array) $req->getParsedBody();

	try {
		$pages->patch($args['slug'], $pageArr);
	} catch (Exception $e) {
		return $this->api->json($res, [
			'error' => 'PageException',
			'message' => $e->getMessage()
		], 500);
	}

	return $res->withStatus(204);
});

/*
 * @api {delete} /page/:slug Delete a page.
 * @apiName Delete page
 * @apiGroup Page
 *
 * @apiSuccess (204) PagePatched
 * @apiError (404) PageException If not ok or exception
 */
$this->delete('/{slug:.+}', function ($req, $res, $args) use ($pages) {
	try {
		$result = $pages->delete($args['slug']);
	} catch (Exception $e) {
		return $this->api->json($res, [
			'error' => 'PageException',
			'message' => $e->getMessage()
		], 404);
	}

	if (!$result) {
		return $this->api->json($res, [
			'error' => 'PageCannotBeDeleted'
		], 500);
	}

	return $res->withStatus(204);
});

<?php

namespace Parvula;

use Exception;
use Parvula\Models\Page;
use Parvula\Models\PagesFlatFiles;
use Parvula\Exceptions\IOException;
use Rs\Json\Patch;
use Rs\Json\Patch\InvalidOperationException;
use Rs\Json\Patch\InvalidPatchDocumentJsonException;
use Rs\Json\Patch\InvalidTargetDocumentJsonException;

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
 * @apiParamExample {json} Request-Example:
 *     {
 *       "title": "My title",
 *       "slug": "my_new_slug",
 *       "content": "Some content"
 *     }
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
		$page = new Page($pageArr);

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
})->setName('pages.create');

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
 * @apiParamExample {json} Request-Example:
 *     {
 *       "title": "My updated title",
 *       "slug": "updated_slug",
 *       "content": "Some edited content"
 *     }
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
		$page = new Page($pageArr);

		$pages->update($args['slug'], $page);
	} catch (Exception $e) {
		return $this->api->json($res, [
			'error' => 'PageException',
			'message' => $e->getMessage()
		], 500);
	}

	$res->withStatus(204);
})->setName('pages.update');

/**
 * @api {patch} /pages/:slug Update specific field(s) of a page
 * @apiDescription For more details about json patch: https://tools.ietf.org/html/rfc6902
 * @apiName Patch page
 * @apiGroup Page
 *
 * @apiSuccess (204) PagePatched
 * @apiError (400) InvalidPatchDocumentJsonException
 * @apiError (400) InvalidTargetDocumentJsonException
 * @apiError (400) InvalidOperationException
 * @apiError (404) PageDoesNotExists If page does not exists
 * @apiError (404) PageException If exception
 */
$this->patch('/{slug:.+}', function ($req, $res, $args) use ($app, $pages) {
	$parsedBody = $req->getParsedBody();
	$bodyJson = json_encode($req->getParsedBody());

	$slug = $args['slug'];
	if (!isset($req->getQueryParams()['parse'])) {
		$pages->setRenderer($app['pageRendererRAW']);
	}
	$page = $pages->read($slug);

	if (!$page) {
		return $this->api->json($res, [
			'error' => 'PageDoesNotExists',
			'message' => 'Page does not exists'
		], 404);
	}

	try {
		$patch = new Patch($page, $bodyJson);

		$patchedDocument = $patch->apply();

		$newPage = new Page(json_decode($patchedDocument, true));

		$pages->update($slug, $newPage);
	} catch (InvalidPatchDocumentJsonException $e) {
		// Will be thrown when using invalid JSON in a patch document
		return $this->api->json($res, [
			'error' => 'InvalidPatchDocumentJsonException',
			'message' => $e->getMessage()
		], 400);
	} catch (InvalidTargetDocumentJsonException $e) {
		// Will be thrown when using invalid JSON in a target document
		return $this->api->json($res, [
			'error' => 'InvalidTargetDocumentJsonException',
			'message' => $e->getMessage()
		], 400);
	} catch (InvalidOperationException $e) {
		// Will be thrown when using an invalid JSON Pointer operation (i.e. missing property)
		return $this->api->json($res, [
			'error' => 'InvalidOperationException',
			'message' => $e->getMessage()
		], 400);
	} catch (Exception $e) {
		return $this->api->json($res, [
			'error' => 'InvalidOperationException',
			'message' => $e->getMessage()
		], 500);
	}
})->setName('pages.patch');

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
})->setName('pages.delete');

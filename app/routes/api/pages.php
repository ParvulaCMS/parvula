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
$this->post('', function ($req, $res) {
	$parsedBody = $req->getParsedBody();

	if (!isset($parsedBody['slug'], $parsedBody['title'])) {
		return $this->api->json($res, [
			'error' => 'BadField',
			'message' => 'This page need at least a slug and a title'
		], 400);
	}

	$pages = app('pages');
	if ($pages->find($parsedBody['slug'])) {
		return $this->api->json($res, [
			'error' => 'PageAlreadyExists',
			'message' => 'Page `' . htmlspecialchars($parsedBody['slug']) . '` already exists'
		], 409);
	}

	$pageArr = (array) $parsedBody;

	try {
		$result = $pages->create($pageArr);
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
$this->put('/{slug:.+}', function ($req, $res, $args) {
	$parsedBody = $req->getParsedBody();

	if (!isset($parsedBody['slug'], $parsedBody['title'])) {
		return $this->api->json($res, [
			'error' => 'BadField',
			'message' => 'This page need at least a `slug` and a `title`'
		], 400);
	}

	try {
		app('pages')->update($args['slug'], (array) $parsedBody);
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
 * @apiDescription Patch uses [Json patch (rfc6902)](https://tools.ietf.org/html/rfc6902)
 * @apiName Patch page
 * @apiGroup Page
 *
 * @apiSuccess (204) PagePatched
 * @apiError (400) InvalidPatchDocumentJsonException
 * @apiError (400) InvalidTargetDocumentJsonException
 * @apiError (400) InvalidOperationException
 * @apiError (404) PageDoesNotExists If page does not exists
 * @apiError (404) PageException
 * @apiError (500) ServerException
 */
$this->patch('/{slug:.+}', function ($req, $res, $args) {
	$parsedBody = $req->getParsedBody();
	$bodyJson = json_encode($req->getParsedBody());

	$slug = $args['slug'];
	$pages = app('pages');
	if (!isset($req->getQueryParams()['parse'])) {
		$pages->setRenderer(app('pageRendererRAW'));
	}
	$page = $pages->find($slug);

	if (!$page) {
		return $this->api->json($res, [
			'error' => 'PageDoesNotExists',
			'message' => 'Page does not exists'
		], 404);
	}

	try {
		// string concatenation convert page to json
		$patch = new Patch((string) $page, $bodyJson);

		$patchedDocument = $patch->apply();

		$pages->update($slug, json_decode($patchedDocument, true));
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
			'error' => 'ServerException',
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
 * @apiError (404) PageDoesNotExists If page does not exists and thus cannot be deleted
 * @apiError (404) PageException If not ok or exception
 */
$this->delete('/{slug:.+}', function ($req, $res, $args) {
	$pages = app('pages');
	$page = $pages->find($args['slug']);

	if (!$page) {
		return $this->api->json($res, [
			'error' => 'PageDoesNotExists',
			'message' => 'Page does not exists and thus cannot be deleted'
		], 404);
	}

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

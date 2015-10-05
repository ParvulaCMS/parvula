<?php

namespace Parvula;

use Parvula\Core\Page;
use Parvula\Core\Router;
use Parvula\Core\Config;
use Parvula\Core\Parvula;
use Parvula\Core\Model\Pages;
use Parvula\Core\Response;
use Parvula\Core\Exception\IOException;

if(!defined('ROOT')) exit;

$defaultPageSerializer = Config::defaultPageSerializer();

$pages = new Pages(new $defaultPageSerializer);

// Send API message @TODO Temp
function apiResponse($responseCode = 200, $data = null) {

	if (is_array($responseCode)) {
		$data = $responseCode;
		$responseCode = true;
	}

	if($responseCode === true) {
		$responseCode = 200;
	} else if($responseCode == false) {
		$responseCode = 400;
	}

	$res = [];
	if($responseCode >= 300) {
		$res['status'] = 'error';
		$res['message'] = $data;
	} else {
		$res['status'] = 'success';
		$res['data'] = $data;
	}

	header('Content-Type: application/json');
	http_response_code($responseCode);

	return json_encode($res);
}

//
// Public API
//

// Page object (`?raw` to not parse the content)
$router->get('/pages/::name', function($req) use ($pages) {
	return apiResponse(true, $pages->get($req->params->name, $req->query !== 'raw'));
});

// Array<Page> of Pages
$router->get('/pages', function($req) use ($pages) {
	return apiResponse(true, $pages->getAll());
});


$router->post('/login', function($req) {
	// print_r($req->body);
	//YYYYMMDD

	$username = $req->body['username'];
	$password = $req->body['password'];

	if($password === 'qwe') {
		createASession();
		echo apiResponse(true, 'login ok');
		// echo '{"status": "ok", "message": "login ok"}';
	} else {
		echo apiResponse(false, 'wrong login/password');
		// echo '{"status": "nok", "message": "wrong login/password'.$password.'"}';
	}


	//
	// $contentHash = $req->body['hash'];
	//
	// $public = $req->body['public'];
	//
	// $content = hash_hmac('sha256', $username, $contentHash);
	//
	// $secureHash = hash_hmac('sha256', $content, $password);


	// $publicHash  = $req->body['pub-hash'];
    // $contentHash = $req->body['hash'];
    // $privateHash  = 'qweqwe';
    // $content     = $request->getBody();

    // $hash = hash_hmac('sha256', $content, $privateHash);

    // if ($hash == $contentHash){
        // echo "match!\n";
    // }
});

//
// Admin API
//
if(true === isParvulaAdmin()) {

	// List of pages. Array<string> of pages paths
	$router->get('/pageslist', function($req) use ($pages) {
		return apiResponse(true, $pages->listPages());
	});

	// Delete page
	$router->delete('/pages/::name', function($req) use ($pages) {
		try {
			$res = $pages->delete($req->params->name);
		} catch(\Exception $e) {
			return apiResponse(404, $e->getMessage());
		}

		return apiResponse($res);
	});

	// Create page @TODO TEST
	$router->post('/pages/::name', function($req) use ($pages) {
		if(!isset($req->params->name) || trim($req->params->name) === '') {
			return false;
		}

		$page = Page::pageFactory($req->body);

		try {
			$res = $pages->set($page, $req->params->name);
		} catch(IOException $e) {
			return apiResponse(404, $e->getMessage());
		}

		return apiResponse(201);
	});

	// Update page
	$router->put('/pages/::name', function($req) use ($pages) {
		if(!isset($req->params->name) || trim($req->params->name) === '') {
			return false;
		}

		// Get old page and update new fields
		$page = $pages->get($req->params->name);
		foreach ($req->body as $key => $value) {
			$page->{$key} = $value;
		}

		$res = $pages->update($page, $req->params->name, new $defaultPageSerializer);
		return apiResponse($res);
	});

	// Upload file
	//TODO test - Security, etc.
	$router->post('/upload/images', function() {
		$uploadfile = IMAGES . basename($_FILES['file']['name']);

		// echo $uploadfile;
		// print_r($_FILES);

		if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
			return apiResponse(true, 'image was successfully uploaded');
		} else {
			return apiResponse(false);
		}
	});

	// ANTHO
	// return images
	$router->get('/upload/images', function() {

		$files = [];
		foreach (glob(DATA . 'images' . '/*') as $file) {

			// !! absolute URLs for src attr.
			$name = $_SERVER['SERVER_NAME'];
			if($name === 'localhost') $name = 'http://' . $name;

			$files[] = $name . dirname($_SERVER['PHP_SELF']) . '/' . $file;
		}

		echo json_encode(['status' => 'ok', 'files' => $files]);
	});

	// ANTHO
	// delete image
	$router->delete('/upload/images', function($req) {

		$filename = basename($req->body['filename']);
		$res = ['status' => 'error', 'message' => 'fail'];

		foreach (glob(DATA . 'images' . '/*') as $file) {

			if(basename($file) === $filename) {

				unlink($file);
				$res = ['status' => 'ok', 'message' => 'file deleted'];
				break;
			}
		}

		echo json_encode($res);
	});

	// ANTHO get template
	// @todo get preferences? Template, etc?
	$router->get('/template', function() {

		$siteConf = Parvula::getUserConfig();
		if($siteConf) {
			return json_encode(['status' => 'ok', 'template' => $siteConf->template]);
		} else {
			return apiResponse(false, 'system error');
		}
	});

	// @todo -> get available layout for template
	// ...

	// ANTHO change template
	// @todo check if it exists
	$router->post('/template', function($req) {

		$tmpl = basename($req->body['template']);

		$siteConf = Parvula::getUserConfig();
		$siteConf->template = $tmpl;

		file_put_contents(DATA . 'site.conf.json', json_encode($siteConf, JSON_PRETTY_PRINT));

		return apiResponse(true, 'template changed');
	});

	// Logout
	$router->any('/logout', function() {
		$res = session_destroy();
		session_unset();
		return apiResponse($res);
	});

} else {
	// @TODO
	// echo '{"message": "Not found or not logged"}';
}

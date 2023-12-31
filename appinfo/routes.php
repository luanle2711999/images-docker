<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Lê Công Luận <lecongluan99@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\SNextCloud\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
return [
	'resources' => [
		'note' => ['url' => '/notes'],
		'note_api' => ['url' => '/api/0.1/notes'],
	],
	'routes' => [
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
		['name' => 'note_api#preflighted_cors', 'url' => '/api/0.1/{path}',
			'verb' => 'OPTIONS', 'requirements' => ['path' => '.+']],
		['name' => 'file#getFileContent', 'url' => '/file/getFileContent', 'verb' => 'GET'],
		['name' => 'file#readFileContent', 'url' => '/file/readFileContent', 'verb' => 'GET'],
		['name' => 'file#readFile', 'url' => '/file/readFile', 'verb' => 'GET'],
		['name' => 'thumbnail#get', 'url' => '/thumbnail/get', 'verb' => 'GET'],
		['name' => 'file#getTestFile', 'url' => '/file/getTestFile', 'verb' => 'GET'],
		['name' => 'file#index', 'url' => '/file/index', 'verb' => 'GET'],
		['name' => 'file#getImageByFileId', 'url' => '/file/getImageByFileId', 'verb' => 'GET'],
	]
];

<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Lê Công Luận <lecongluan99@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\ThirdApp\Controller;

use OCA\ThirdApp\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IRequest;
use OCP\Util;

class PageController extends Controller {
	public function __construct(IRequest $request) {
		parent::__construct(Application::APP_ID, $request);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index(): TemplateResponse {
		Util::addScript(Application::APP_ID, 'thirdapp-main');

		$response = new TemplateResponse(Application::APP_ID, 'main');

		$this->addCsp($response);

		return $response;
	}

	/**
	 * This function adds content security policies to the app.
	 * This is needed to connect to the PointCab license center,
	 * executing build vue scripts and adding iframes.
	 */
	private function addCsp($response)
	{
		if (class_exists('OCP\AppFramework\Http\ContentSecurityPolicy')) {
			$csp = new \OCP\AppFramework\Http\ContentSecurityPolicy();

			// add connect domains for license servers
			$csp->addAllowedConnectDomain('https://pointcab.de/pclicserver/licenseverify4.php');
			$csp->addAllowedConnectDomain('http://license.pointcab-software.com/licenseverify4.php');

			// add self to execute scripts as full url. 'self' doesn't do it, for some reason
			$csp->addAllowedScriptDomain($this->full_url($_SERVER, true));

			// add frame domains for pointcab iframes
			$csp->addAllowedFrameDomain('https://pointcab-software.com');

			// add image src for logos
			$csp->addAllowedImageDomain('*');

			$csp->addAllowedFrameDomain('http://localhost:8080');
			$response->setContentSecurityPolicy($csp);
		}
	}
	/**
	 * This and the next function parse the current URL.
	 * This is needed for some part of the CSPs.
	 */
	private function url_origin($s, $use_forwarded_host = false)
	{
		$ssl      = (!empty($s['HTTPS']) && $s['HTTPS'] == 'on');
		$sp       = strtolower($s['SERVER_PROTOCOL']);
		$protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
		$port     = $s['SERVER_PORT'];
		$port     = ((!$ssl && $port == '80') || ($ssl && $port == '443')) ? '' : ':' . $port;
		$host     = ($use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
		$host     = isset($host) ? $host : $s['SERVER_NAME'] . $port;
		return $protocol . '://' . $host;
	}

	private function full_url($s, $use_forwarded_host = false)
	{
		//this would return e. g. http://localhost:8080/apps/nebula
		// return $this->url_origin($s, $use_forwarded_host) . $s['REQUEST_URI'];

		//this returns e. g. http://localhost:8080
		return $this->url_origin($s, $use_forwarded_host);
	}
}

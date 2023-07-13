<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Lê Công Luận <lecongluan99@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\ThirdApp\AppInfo;

use OCP\AppFramework\App;

class Application extends App {
	public const APP_ID = 'thirdapp';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}
}

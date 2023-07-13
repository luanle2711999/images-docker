<?php
declare(strict_types=1);
// SPDX-FileCopyrightText: Lê Công Luận <lecongluan99@gmail.com>
// SPDX-License-Identifier: AGPL-3.0-or-later

namespace OCA\ThirdApp\Tests\Unit\Controller;

use OCA\ThirdApp\Controller\NoteApiController;

class NoteApiControllerTest extends NoteControllerTest {
	public function setUp(): void {
		parent::setUp();
		$this->controller = new NoteApiController($this->request, $this->service, $this->userId);
	}
}

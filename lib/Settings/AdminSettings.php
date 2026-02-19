<?php

declare(strict_types=1);

namespace OCA\Tramita\Settings;

use OCA\Tramita\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;
use OCP\Util;

class AdminSettings implements ISettings {

	public function getForm(): TemplateResponse {
		Util::addScript(Application::APP_ID, 'tramita-admin');
		Util::addStyle(Application::APP_ID, 'tramita');
		return new TemplateResponse(Application::APP_ID, 'admin');
	}

	public function getSection(): string {
		return 'tramita';
	}

	public function getPriority(): int {
		return 10;
	}
}

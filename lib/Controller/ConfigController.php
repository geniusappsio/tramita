<?php

declare(strict_types=1);

namespace OCA\Tramita\Controller;

use OCA\Tramita\Service\LicenseService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class ConfigController extends Controller {

	public function __construct(
		string $appName,
		IRequest $request,
		private LicenseService $licenseService,
	) {
		parent::__construct($appName, $request);
	}

	#[NoAdminRequired]
	public function getLicenseInfo(): DataResponse {
		return new DataResponse($this->licenseService->getLicenseInfo());
	}

	public function setLicense(string $licenseKey): DataResponse {
		try {
			$license = $this->licenseService->activate($licenseKey);
			return new DataResponse([
				'success' => true,
				'license' => $this->licenseService->getLicenseInfo(),
			]);
		} catch (\Exception $e) {
			return new DataResponse(
				['error' => 'Failed to activate license', 'message' => $e->getMessage()],
				Http::STATUS_BAD_REQUEST
			);
		}
	}
}

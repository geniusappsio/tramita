<?php

declare(strict_types=1);

namespace OCA\Tramita\Middleware;

use OCA\Tramita\Controller\ConfigController;
use OCA\Tramita\Controller\PageController;
use OCA\Tramita\Exception\InvalidLicenseException;
use OCA\Tramita\Service\LicenseService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Middleware;

class LicenseMiddleware extends Middleware {

	public function __construct(
		private LicenseService $licenseService,
	) {
	}

	public function beforeController(mixed $controller, string $methodName): void {
		// Skip license check for page and config controllers
		if ($controller instanceof PageController || $controller instanceof ConfigController) {
			return;
		}

		if (!$this->licenseService->isValid()) {
			throw new InvalidLicenseException('License is invalid or expired');
		}
	}

	public function afterException(mixed $controller, string $methodName, \Exception $exception): JSONResponse {
		if ($exception instanceof InvalidLicenseException) {
			return new JSONResponse(
				['error' => 'License invalid', 'message' => $exception->getMessage()],
				Http::STATUS_PAYMENT_REQUIRED
			);
		}

		throw $exception;
	}
}

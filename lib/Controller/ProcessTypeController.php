<?php

declare(strict_types=1);

namespace OCA\Tramita\Controller;

use OCA\Tramita\Exception\ValidationException;
use OCA\Tramita\Service\ProcessTypeService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class ProcessTypeController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private ProcessTypeService $service,
		private string $userId,
	) {
		parent::__construct($appName, $request);
	}

	#[NoAdminRequired]
	public function index(): DataResponse {
		$processTypes = $this->service->findAll();
		return new DataResponse($processTypes);
	}

	#[NoAdminRequired]
	public function show(int $id): DataResponse {
		try {
			$processType = $this->service->findById($id);
			return new DataResponse($processType);
		} catch (DoesNotExistException $e) {
			return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		}
	}

	#[NoAdminRequired]
	public function create(
		string $name,
		string $prefix,
		string $groupId,
		?string $description = null,
		?string $color = null,
		?string $icon = null,
		?bool $isExternal = null,
		?array $settings = null,
	): DataResponse {
		try {
			$processType = $this->service->create(
				$name,
				$prefix,
				$groupId,
				$this->userId,
				$description,
				$color,
				$icon,
				$isExternal,
				$settings,
			);
			return new DataResponse($processType, Http::STATUS_CREATED);
		} catch (ValidationException $e) {
			return new DataResponse(
				['error' => $e->getMessage(), 'errors' => $e->getErrors()],
				Http::STATUS_BAD_REQUEST,
			);
		}
	}

	#[NoAdminRequired]
	public function update(
		int $id,
		?string $name = null,
		?string $description = null,
		?string $prefix = null,
		?string $color = null,
		?string $icon = null,
		?bool $isActive = null,
		?bool $isExternal = null,
		?int $sortOrder = null,
		?array $settings = null,
	): DataResponse {
		try {
			$processType = $this->service->update(
				$id,
				$name,
				$description,
				$prefix,
				$color,
				$icon,
				$isActive,
				$isExternal,
				$sortOrder,
				$settings,
			);
			return new DataResponse($processType);
		} catch (DoesNotExistException $e) {
			return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		} catch (ValidationException $e) {
			return new DataResponse(
				['error' => $e->getMessage(), 'errors' => $e->getErrors()],
				Http::STATUS_BAD_REQUEST,
			);
		}
	}

	#[NoAdminRequired]
	public function destroy(int $id): DataResponse {
		try {
			$processType = $this->service->delete($id);
			return new DataResponse($processType);
		} catch (DoesNotExistException $e) {
			return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		}
	}

	#[NoAdminRequired]
	public function restore(int $id): DataResponse {
		try {
			$processType = $this->service->restore($id);
			return new DataResponse($processType);
		} catch (DoesNotExistException $e) {
			return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		}
	}
}

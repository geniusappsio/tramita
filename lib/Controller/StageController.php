<?php

declare(strict_types=1);

namespace OCA\Tramita\Controller;

use OCA\Tramita\Exception\NotFoundException;
use OCA\Tramita\Exception\ValidationException;
use OCA\Tramita\Service\StageService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class StageController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private StageService $service,
		private string $userId,
	) {
		parent::__construct($appName, $request);
	}

	#[NoAdminRequired]
	public function index(int $procTypeId): DataResponse {
		$stages = $this->service->findByProcessType($procTypeId);
		return new DataResponse($stages);
	}

	#[NoAdminRequired]
	public function show(int $id): DataResponse {
		try {
			$stage = $this->service->findById($id);
			return new DataResponse($stage);
		} catch (NotFoundException $e) {
			return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		}
	}

	#[NoAdminRequired]
	public function create(
		int $procTypeId,
		string $name,
		?string $description = null,
		?string $color = null,
		?bool $isInitial = null,
		?bool $isFinal = null,
		?array $allowedNext = null,
		?array $autoAssign = null,
		?int $slaHours = null,
	): DataResponse {
		try {
			$stage = $this->service->create(
				$procTypeId,
				$name,
				$this->userId,
				$description,
				$color,
				$isInitial,
				$isFinal,
				$allowedNext,
				$autoAssign,
				$slaHours,
			);
			return new DataResponse($stage, Http::STATUS_CREATED);
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
		?string $color = null,
		?bool $isInitial = null,
		?bool $isFinal = null,
		?array $allowedNext = null,
		?array $autoAssign = null,
		?int $slaHours = null,
		?bool $isActive = null,
	): DataResponse {
		try {
			$stage = $this->service->update(
				$id,
				$name,
				$description,
				$color,
				$isInitial,
				$isFinal,
				$allowedNext,
				$autoAssign,
				$slaHours,
				$isActive,
			);
			return new DataResponse($stage);
		} catch (NotFoundException $e) {
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
			$stage = $this->service->delete($id);
			return new DataResponse($stage);
		} catch (NotFoundException $e) {
			return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		}
	}

	#[NoAdminRequired]
	public function reorder(int $procTypeId, array $stageIds): DataResponse {
		try {
			$this->service->reorder($procTypeId, $stageIds);
			return new DataResponse(['status' => 'ok']);
		} catch (NotFoundException $e) {
			return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		} catch (ValidationException $e) {
			return new DataResponse(
				['error' => $e->getMessage(), 'errors' => $e->getErrors()],
				Http::STATUS_BAD_REQUEST,
			);
		}
	}
}

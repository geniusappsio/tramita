<?php

declare(strict_types=1);

namespace OCA\Tramita\Controller;

use OCA\Tramita\Exception\NotFoundException;
use OCA\Tramita\Exception\ValidationException;
use OCA\Tramita\Service\LabelService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class LabelController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private LabelService $service,
		private string $userId,
	) {
		parent::__construct($appName, $request);
	}

	#[NoAdminRequired]
	public function index(string $groupId): DataResponse {
		$labels = $this->service->findByGroup($groupId);
		return new DataResponse($labels);
	}

	#[NoAdminRequired]
	public function show(int $id): DataResponse {
		try {
			$label = $this->service->findById($id);
			return new DataResponse($label);
		} catch (NotFoundException $e) {
			return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		}
	}

	#[NoAdminRequired]
	public function create(
		string $name,
		string $groupId,
		?string $color = null,
		?int $procTypeId = null,
	): DataResponse {
		try {
			$label = $this->service->create(
				$name,
				$groupId,
				$this->userId,
				$color,
				$procTypeId,
			);
			return new DataResponse($label, Http::STATUS_CREATED);
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
		?string $color = null,
		?int $procTypeId = null,
	): DataResponse {
		try {
			$label = $this->service->update(
				$id,
				$name,
				$color,
				$procTypeId,
			);
			return new DataResponse($label);
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
			$label = $this->service->delete($id);
			return new DataResponse($label);
		} catch (NotFoundException $e) {
			return new DataResponse(['error' => 'Not found'], Http::STATUS_NOT_FOUND);
		}
	}
}

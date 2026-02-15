<?php

declare(strict_types=1);

namespace OCA\Tramita\Controller;

use OCA\Tramita\Exception\NotFoundException;
use OCA\Tramita\Exception\ValidationException;
use OCA\Tramita\Service\FormTemplateService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class FormTemplateController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private FormTemplateService $service,
		private string $userId,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * List all form templates for a process type.
	 */
	#[NoAdminRequired]
	public function index(int $processTypeId): DataResponse {
		$templates = $this->service->findByProcessType($processTypeId);
		return new DataResponse($templates);
	}

	/**
	 * Get a single form template by ID.
	 */
	#[NoAdminRequired]
	public function show(int $id): DataResponse {
		try {
			$template = $this->service->findById($id);
			return new DataResponse($template);
		} catch (NotFoundException $e) {
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * Create a new form template.
	 */
	#[NoAdminRequired]
	public function create(
		int $processTypeId,
		string $name,
		?int $stageId = null,
		?string $description = null,
		?bool $isRequired = null,
		?array $settings = null,
	): DataResponse {
		try {
			$template = $this->service->create(
				$processTypeId,
				$name,
				$this->userId,
				$stageId,
				$description,
				$isRequired,
				$settings,
			);
			return new DataResponse($template, Http::STATUS_CREATED);
		} catch (ValidationException $e) {
			return new DataResponse(
				['error' => $e->getMessage(), 'errors' => $e->getErrors()],
				Http::STATUS_BAD_REQUEST,
			);
		}
	}

	/**
	 * Update an existing form template.
	 */
	#[NoAdminRequired]
	public function update(
		int $id,
		?string $name = null,
		?string $description = null,
		?int $stageId = null,
		?bool $isActive = null,
		?bool $isRequired = null,
		?int $sortOrder = null,
		?array $settings = null,
	): DataResponse {
		try {
			$template = $this->service->update(
				$id,
				$name,
				$description,
				$stageId,
				$isActive,
				$isRequired,
				$sortOrder,
				$settings,
			);
			return new DataResponse($template);
		} catch (NotFoundException $e) {
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_NOT_FOUND);
		} catch (ValidationException $e) {
			return new DataResponse(
				['error' => $e->getMessage(), 'errors' => $e->getErrors()],
				Http::STATUS_BAD_REQUEST,
			);
		}
	}

	/**
	 * Soft delete a form template.
	 */
	#[NoAdminRequired]
	public function destroy(int $id): DataResponse {
		try {
			$template = $this->service->delete($id);
			return new DataResponse($template);
		} catch (NotFoundException $e) {
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_NOT_FOUND);
		}
	}
}

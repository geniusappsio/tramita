<?php

declare(strict_types=1);

namespace OCA\Tramita\Controller;

use OCA\Tramita\Exception\NotFoundException;
use OCA\Tramita\Exception\ValidationException;
use OCA\Tramita\Service\FormFieldService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class FormFieldController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
		private FormFieldService $service,
		private string $userId,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * List all form fields for a template.
	 */
	#[NoAdminRequired]
	public function index(int $formTemplateId): DataResponse {
		$fields = $this->service->findByTemplate($formTemplateId);
		return new DataResponse($fields);
	}

	/**
	 * Get a single form field by ID.
	 */
	#[NoAdminRequired]
	public function show(int $id): DataResponse {
		try {
			$field = $this->service->findById($id);
			return new DataResponse($field);
		} catch (NotFoundException $e) {
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * Create a new form field.
	 */
	#[NoAdminRequired]
	public function create(
		int $formTemplateId,
		string $name,
		string $label,
		string $fieldType,
		?string $placeholder = null,
		?string $helpText = null,
		?string $defaultValue = null,
		?bool $isRequired = null,
		?bool $isReadonly = null,
		?bool $isHidden = null,
		?array $validation = null,
		?array $options = null,
		?string $width = null,
		?array $conditional = null,
	): DataResponse {
		try {
			$field = $this->service->create(
				$formTemplateId,
				$name,
				$label,
				$fieldType,
				$placeholder,
				$helpText,
				$defaultValue,
				$isRequired,
				$isReadonly,
				$isHidden,
				$validation,
				$options,
				$width,
				$conditional,
			);
			return new DataResponse($field, Http::STATUS_CREATED);
		} catch (ValidationException $e) {
			return new DataResponse(
				['error' => $e->getMessage(), 'errors' => $e->getErrors()],
				Http::STATUS_BAD_REQUEST,
			);
		}
	}

	/**
	 * Update an existing form field.
	 */
	#[NoAdminRequired]
	public function update(
		int $id,
		?string $name = null,
		?string $label = null,
		?string $fieldType = null,
		?string $placeholder = null,
		?string $helpText = null,
		?string $defaultValue = null,
		?bool $isRequired = null,
		?bool $isReadonly = null,
		?bool $isHidden = null,
		?array $validation = null,
		?array $options = null,
		?string $width = null,
		?array $conditional = null,
	): DataResponse {
		try {
			$field = $this->service->update(
				$id,
				$name,
				$label,
				$fieldType,
				$placeholder,
				$helpText,
				$defaultValue,
				$isRequired,
				$isReadonly,
				$isHidden,
				$validation,
				$options,
				$width,
				$conditional,
			);
			return new DataResponse($field);
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
	 * Soft delete a form field.
	 */
	#[NoAdminRequired]
	public function destroy(int $id): DataResponse {
		try {
			$field = $this->service->delete($id);
			return new DataResponse($field);
		} catch (NotFoundException $e) {
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * Reorder fields within a template.
	 */
	#[NoAdminRequired]
	public function reorder(int $formTemplateId, array $fieldIds): DataResponse {
		try {
			$this->service->reorder($formTemplateId, $fieldIds);
			$fields = $this->service->findByTemplate($formTemplateId);
			return new DataResponse($fields);
		} catch (NotFoundException $e) {
			return new DataResponse(['error' => $e->getMessage()], Http::STATUS_NOT_FOUND);
		}
	}
}

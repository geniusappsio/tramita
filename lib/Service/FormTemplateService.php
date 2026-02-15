<?php

declare(strict_types=1);

namespace OCA\Tramita\Service;

use OCA\Tramita\Db\FormTemplate;
use OCA\Tramita\Db\FormTemplateMapper;
use OCA\Tramita\Exception\NotFoundException;
use OCA\Tramita\Exception\ValidationException;
use OCP\AppFramework\Db\DoesNotExistException;

class FormTemplateService {
	private FormTemplateMapper $mapper;

	public function __construct(FormTemplateMapper $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * Find all form templates for a given process type.
	 *
	 * @param int $procTypeId
	 * @return FormTemplate[]
	 */
	public function findByProcessType(int $procTypeId): array {
		return $this->mapper->findByProcessType($procTypeId);
	}

	/**
	 * Find a form template by its ID.
	 *
	 * @param int $id
	 * @return FormTemplate
	 * @throws NotFoundException
	 */
	public function findById(int $id): FormTemplate {
		try {
			return $this->mapper->findById($id);
		} catch (DoesNotExistException $e) {
			throw new NotFoundException('Form template not found: ' . $id);
		}
	}

	/**
	 * Create a new form template.
	 *
	 * @param int $procTypeId
	 * @param string $name
	 * @param string $createdBy
	 * @param int|null $stageId
	 * @param string|null $description
	 * @param bool|null $isRequired
	 * @param array|null $settings
	 * @return FormTemplate
	 * @throws ValidationException
	 */
	public function create(
		int $procTypeId,
		string $name,
		string $createdBy,
		?int $stageId = null,
		?string $description = null,
		?bool $isRequired = null,
		?array $settings = null
	): FormTemplate {
		$this->validateName($name);

		// Determine next sort order
		$existing = $this->mapper->findByProcessType($procTypeId);
		$maxSort = 0;
		foreach ($existing as $tpl) {
			if ($tpl->getSortOrder() > $maxSort) {
				$maxSort = $tpl->getSortOrder();
			}
		}

		$now = new \DateTimeImmutable();

		$template = new FormTemplate();
		$template->setProcTypeId($procTypeId);
		$template->setName($name);
		$template->setCreatedBy($createdBy);
		$template->setStageId($stageId);
		$template->setDescription($description);
		$template->setIsRequired($isRequired ?? false);
		$template->setVersion(1);
		$template->setIsActive(true);
		$template->setSortOrder($maxSort + 1);
		$template->setSettings($settings !== null ? json_encode($settings) : null);
		$template->setCreatedAt($now->format('Y-m-d H:i:s'));
		$template->setUpdatedAt($now->format('Y-m-d H:i:s'));

		return $this->mapper->insert($template);
	}

	/**
	 * Update an existing form template. Only non-null parameters are updated.
	 *
	 * @param int $id
	 * @param string|null $name
	 * @param string|null $description
	 * @param int|null $stageId
	 * @param bool|null $isActive
	 * @param bool|null $isRequired
	 * @param int|null $sortOrder
	 * @param array|null $settings
	 * @return FormTemplate
	 * @throws NotFoundException
	 * @throws ValidationException
	 */
	public function update(
		int $id,
		?string $name = null,
		?string $description = null,
		?int $stageId = null,
		?bool $isActive = null,
		?bool $isRequired = null,
		?int $sortOrder = null,
		?array $settings = null
	): FormTemplate {
		$template = $this->findById($id);

		if ($name !== null) {
			$this->validateName($name);
			$template->setName($name);
		}

		if ($description !== null) {
			$template->setDescription($description);
		}

		if ($stageId !== null) {
			$template->setStageId($stageId);
		}

		if ($isActive !== null) {
			$template->setIsActive($isActive);
		}

		if ($isRequired !== null) {
			$template->setIsRequired($isRequired);
		}

		if ($sortOrder !== null) {
			$template->setSortOrder($sortOrder);
		}

		if ($settings !== null) {
			$template->setSettings(json_encode($settings));
		}

		$now = new \DateTimeImmutable();
		$template->setUpdatedAt($now->format('Y-m-d H:i:s'));

		return $this->mapper->update($template);
	}

	/**
	 * Soft delete a form template by setting deleted_at.
	 *
	 * @param int $id
	 * @return FormTemplate
	 * @throws NotFoundException
	 */
	public function delete(int $id): FormTemplate {
		$template = $this->findById($id);

		$now = new \DateTimeImmutable();
		$template->setDeletedAt($now->format('Y-m-d H:i:s'));

		return $this->mapper->update($template);
	}

	/**
	 * Validate that the name is not empty.
	 *
	 * @param string $name
	 * @throws ValidationException
	 */
	private function validateName(string $name): void {
		$errors = [];

		if (trim($name) === '') {
			$errors['name'] = 'Name is required';
		}

		if (!empty($errors)) {
			throw new ValidationException('Validation failed', $errors);
		}
	}
}

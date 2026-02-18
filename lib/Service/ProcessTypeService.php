<?php

declare(strict_types=1);

namespace OCA\Tramita\Service;

use OCA\Tramita\Db\ProcessType;
use OCA\Tramita\Db\ProcessTypeMapper;
use OCA\Tramita\Exception\NotFoundException;
use OCA\Tramita\Exception\ValidationException;

class ProcessTypeService {
	private ProcessTypeMapper $mapper;

	public function __construct(ProcessTypeMapper $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * Find all process types, optionally filtered by group.
	 *
	 * @param string|null $groupId Filter by group ID
	 * @return ProcessType[]
	 */
	public function findAll(?string $groupId = null): array {
		if ($groupId !== null) {
			return $this->mapper->findByGroupId($groupId);
		}
		return $this->mapper->findAll();
	}

	/**
	 * Find all active process types, optionally filtered by group.
	 *
	 * @param string|null $groupId Filter by group ID
	 * @return ProcessType[]
	 */
	public function findActive(?string $groupId = null): array {
		if ($groupId !== null) {
			return $this->mapper->findActiveByGroupId($groupId);
		}
		return $this->mapper->findActive();
	}

	/**
	 * Find a process type by its ID.
	 *
	 * @param int $id
	 * @return ProcessType
	 * @throws NotFoundException
	 */
	public function findById(int $id): ProcessType {
		try {
			return $this->mapper->findById($id);
		} catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
			throw new NotFoundException('Process type not found: ' . $id);
		}
	}

	/**
	 * Create a new process type.
	 *
	 * @param string $name
	 * @param string $prefix
	 * @param string $groupId
	 * @param string $createdBy
	 * @param string|null $description
	 * @param string|null $color
	 * @param string|null $icon
	 * @param array|null $settings
	 * @return ProcessType
	 * @throws ValidationException
	 */
	public function create(
		string $name,
		string $prefix,
		string $groupId,
		string $createdBy,
		?string $description = null,
		?string $color = null,
		?string $icon = null,
		?array $settings = null
	): ProcessType {
		$this->validateRequired($name, $prefix, $groupId);

		$now = new \DateTime();

		$processType = new ProcessType();
		$processType->setName($name);
		$processType->setSlug($this->generateSlug($name));
		$processType->setPrefix($prefix);
		$processType->setGroupId($groupId);
		$processType->setCreatedBy($createdBy);
		$processType->setDescription($description);
		$processType->setColor($color);
		$processType->setIcon($icon);
		$processType->setIsActive(true);
		$processType->setSortOrder(0);
		$processType->setSettings($settings !== null ? json_encode($settings) : null);
		$processType->setCreatedAt($now->format('Y-m-d H:i:s'));
		$processType->setUpdatedAt($now->format('Y-m-d H:i:s'));

		return $this->mapper->insert($processType);
	}

	/**
	 * Update an existing process type. Only non-null parameters are updated.
	 *
	 * @param int $id
	 * @param string|null $name
	 * @param string|null $description
	 * @param string|null $prefix
	 * @param string|null $color
	 * @param string|null $icon
	 * @param bool|null $isActive
	 * @param int|null $sortOrder
	 * @param array|null $settings
	 * @return ProcessType
	 * @throws NotFoundException
	 */
	public function update(
		int $id,
		?string $name = null,
		?string $description = null,
		?string $prefix = null,
		?string $color = null,
		?string $icon = null,
		?bool $isActive = null,
		?int $sortOrder = null,
		?array $settings = null
	): ProcessType {
		$processType = $this->findById($id);

		if ($name !== null) {
			$processType->setName($name);
			$processType->setSlug($this->generateSlug($name));
		}

		if ($description !== null) {
			$processType->setDescription($description);
		}

		if ($prefix !== null) {
			$processType->setPrefix($prefix);
		}

		if ($color !== null) {
			$processType->setColor($color);
		}

		if ($icon !== null) {
			$processType->setIcon($icon);
		}

		if ($isActive !== null) {
			$processType->setIsActive($isActive);
		}

		if ($sortOrder !== null) {
			$processType->setSortOrder($sortOrder);
		}

		if ($settings !== null) {
			$processType->setSettings(json_encode($settings));
		}

		$now = new \DateTime();
		$processType->setUpdatedAt($now->format('Y-m-d H:i:s'));

		return $this->mapper->update($processType);
	}

	/**
	 * Soft delete a process type by setting deleted_at.
	 *
	 * @param int $id
	 * @return ProcessType
	 * @throws NotFoundException
	 */
	public function delete(int $id): ProcessType {
		$processType = $this->findById($id);

		$now = new \DateTime();
		$processType->setDeletedAt($now->format('Y-m-d H:i:s'));

		return $this->mapper->update($processType);
	}

	/**
	 * Restore a soft-deleted process type by clearing deleted_at.
	 *
	 * @param int $id
	 * @return ProcessType
	 * @throws NotFoundException
	 */
	public function restore(int $id): ProcessType {
		$processType = $this->findById($id);

		$processType->setDeletedAt(null);

		return $this->mapper->update($processType);
	}

	/**
	 * Validate that required fields are not empty.
	 *
	 * @param string $name
	 * @param string $prefix
	 * @param string $groupId
	 * @throws ValidationException
	 */
	private function validateRequired(string $name, string $prefix, string $groupId): void {
		$errors = [];

		if (trim($name) === '') {
			$errors['name'] = 'Name is required';
		}

		if (trim($prefix) === '') {
			$errors['prefix'] = 'Prefix is required';
		}

		if (trim($groupId) === '') {
			$errors['groupId'] = 'Group ID is required';
		}

		if (!empty($errors)) {
			throw new ValidationException('Validation failed', $errors);
		}
	}

	/**
	 * Generate a URL-friendly slug from a name.
	 *
	 * Converts to lowercase, replaces accented characters with ASCII equivalents,
	 * replaces spaces and non-alphanumeric characters with hyphens, and collapses
	 * multiple hyphens into one.
	 *
	 * @param string $name
	 * @return string
	 */
	private function generateSlug(string $name): string {
		// Convert to lowercase
		$slug = mb_strtolower($name, 'UTF-8');

		// Transliterate accented characters to ASCII equivalents
		if (function_exists('transliterator_transliterate')) {
			$slug = transliterator_transliterate('Any-Latin; Latin-ASCII', $slug);
		} else {
			// Fallback: manual replacement of common accented characters
			$slug = strtr($slug, [
				"\xC3\xA1" => 'a', "\xC3\xA0" => 'a', "\xC3\xA2" => 'a', "\xC3\xA4" => 'a', "\xC3\xA3" => 'a',
				"\xC3\xA9" => 'e', "\xC3\xA8" => 'e', "\xC3\xAA" => 'e', "\xC3\xAB" => 'e',
				"\xC3\xAD" => 'i', "\xC3\xAC" => 'i', "\xC3\xAE" => 'i', "\xC3\xAF" => 'i',
				"\xC3\xB3" => 'o', "\xC3\xB2" => 'o', "\xC3\xB4" => 'o', "\xC3\xB6" => 'o', "\xC3\xB5" => 'o',
				"\xC3\xBA" => 'u', "\xC3\xB9" => 'u', "\xC3\xBB" => 'u', "\xC3\xBC" => 'u',
				"\xC3\xB1" => 'n', "\xC3\xA7" => 'c',
			]);
		}

		// Replace any non-alphanumeric character with a hyphen
		$slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

		// Trim leading/trailing hyphens
		$slug = trim($slug, '-');

		return $slug;
	}
}

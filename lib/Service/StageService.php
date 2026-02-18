<?php

declare(strict_types=1);

namespace OCA\Tramita\Service;

use OCA\Tramita\Db\Stage;
use OCA\Tramita\Db\StageMapper;
use OCA\Tramita\Exception\NotFoundException;
use OCA\Tramita\Exception\ValidationException;

class StageService {
	private StageMapper $mapper;

	public function __construct(StageMapper $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * Find all non-deleted stages for a given process type, ordered by sort_order.
	 *
	 * @param int $procTypeId
	 * @return Stage[]
	 */
	public function findByProcessType(int $procTypeId): array {
		return $this->mapper->findByProcessType($procTypeId);
	}

	/**
	 * Find a stage by its ID.
	 *
	 * @param int $id
	 * @return Stage
	 * @throws NotFoundException
	 */
	public function findById(int $id): Stage {
		try {
			return $this->mapper->findById($id);
		} catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
			throw new NotFoundException('Stage not found: ' . $id);
		}
	}

	/**
	 * Create a new stage for a process type.
	 *
	 * @param int $procTypeId
	 * @param string $name
	 * @param string $createdBy
	 * @param string|null $description
	 * @param string|null $color
	 * @param bool|null $isInitial
	 * @param bool|null $isFinal
	 * @param array|null $allowedNext
	 * @param array|null $autoAssign
	 * @param int|null $slaHours
	 * @return Stage
	 * @throws ValidationException
	 */
	public function create(
		int $procTypeId,
		string $name,
		string $createdBy,
		?string $description = null,
		?string $color = null,
		?bool $isInitial = null,
		?bool $isFinal = null,
		?array $allowedNext = null,
		?array $autoAssign = null,
		?int $slaHours = null
	): Stage {
		$this->validateName($name);

		// Calculate next sort_order as MAX+1 for this process type
		$existingStages = $this->mapper->findByProcessType($procTypeId);
		$sortOrder = count($existingStages);

		$now = new \DateTime();

		$stage = new Stage();
		$stage->setProcTypeId($procTypeId);
		$stage->setName($name);
		$stage->setSlug($this->generateSlug($name));
		$stage->setDescription($description);
		$stage->setColor($color);
		$stage->setSortOrder($sortOrder);
		$stage->setIsInitial($isInitial ?? false);
		$stage->setIsFinal($isFinal ?? false);
		$stage->setAllowedNext($allowedNext !== null ? json_encode($allowedNext) : null);
		$stage->setAutoAssign($autoAssign !== null ? json_encode($autoAssign) : null);
		$stage->setSlaHours($slaHours);
		$stage->setIsActive(true);
		$stage->setCreatedAt($now->format('Y-m-d H:i:s'));
		$stage->setUpdatedAt($now->format('Y-m-d H:i:s'));

		return $this->mapper->insert($stage);
	}

	/**
	 * Update an existing stage. Only non-null parameters are updated.
	 *
	 * @param int $id
	 * @param string|null $name
	 * @param string|null $description
	 * @param string|null $color
	 * @param bool|null $isInitial
	 * @param bool|null $isFinal
	 * @param array|null $allowedNext
	 * @param array|null $autoAssign
	 * @param int|null $slaHours
	 * @param bool|null $isActive
	 * @return Stage
	 * @throws NotFoundException
	 * @throws ValidationException
	 */
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
		?bool $isActive = null
	): Stage {
		$stage = $this->findById($id);

		if ($name !== null) {
			$this->validateName($name);
			$stage->setName($name);
			$stage->setSlug($this->generateSlug($name));
		}

		if ($description !== null) {
			$stage->setDescription($description);
		}

		if ($color !== null) {
			$stage->setColor($color);
		}

		if ($isInitial !== null) {
			$stage->setIsInitial($isInitial);
		}

		if ($isFinal !== null) {
			$stage->setIsFinal($isFinal);
		}

		if ($allowedNext !== null) {
			$stage->setAllowedNext(json_encode($allowedNext));
		}

		if ($autoAssign !== null) {
			$stage->setAutoAssign(json_encode($autoAssign));
		}

		if ($slaHours !== null) {
			$stage->setSlaHours($slaHours);
		}

		if ($isActive !== null) {
			$stage->setIsActive($isActive);
		}

		$now = new \DateTime();
		$stage->setUpdatedAt($now->format('Y-m-d H:i:s'));

		return $this->mapper->update($stage);
	}

	/**
	 * Soft delete a stage by setting deleted_at.
	 *
	 * @param int $id
	 * @return Stage
	 * @throws NotFoundException
	 */
	public function delete(int $id): Stage {
		$stage = $this->findById($id);

		$now = new \DateTime();
		$stage->setDeletedAt($now->format('Y-m-d H:i:s'));

		return $this->mapper->update($stage);
	}

	/**
	 * Reorder stages for a given process type.
	 *
	 * Receives an ordered array of stage IDs and updates sort_order for each (0, 1, 2, ...).
	 * Validates that all IDs belong to the given procTypeId.
	 *
	 * @param int $procTypeId
	 * @param array $stageIds
	 * @return void
	 * @throws ValidationException
	 * @throws NotFoundException
	 */
	public function reorder(int $procTypeId, array $stageIds): void {
		// Fetch all existing stages for this process type
		$existingStages = $this->mapper->findByProcessType($procTypeId);
		$existingIds = array_map(fn(Stage $s) => $s->getId(), $existingStages);

		// Validate all provided IDs belong to this process type
		foreach ($stageIds as $stageId) {
			if (!in_array($stageId, $existingIds, true)) {
				throw new ValidationException(
					'Invalid stage ID for this process type',
					['stageId' => "Stage ID {$stageId} does not belong to process type {$procTypeId}"]
				);
			}
		}

		// Update sort_order for each stage
		$now = new \DateTime();
		foreach ($stageIds as $order => $stageId) {
			$stage = $this->findById($stageId);
			$stage->setSortOrder($order);
			$stage->setUpdatedAt($now->format('Y-m-d H:i:s'));
			$this->mapper->update($stage);
		}
	}

	/**
	 * Validate that the name is not empty.
	 *
	 * @param string $name
	 * @throws ValidationException
	 */
	private function validateName(string $name): void {
		if (trim($name) === '') {
			throw new ValidationException('Validation failed', [
				'name' => 'Name is required',
			]);
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

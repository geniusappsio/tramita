<?php

declare(strict_types=1);

namespace OCA\Tramita\Service;

use OCA\Tramita\Db\Label;
use OCA\Tramita\Db\LabelMapper;
use OCA\Tramita\Db\RequestLabel;
use OCA\Tramita\Db\RequestLabelMapper;
use OCA\Tramita\Exception\NotFoundException;
use OCA\Tramita\Exception\ValidationException;

class LabelService {
	private LabelMapper $mapper;
	private RequestLabelMapper $requestLabelMapper;

	public function __construct(
		LabelMapper $mapper,
		RequestLabelMapper $requestLabelMapper
	) {
		$this->mapper = $mapper;
		$this->requestLabelMapper = $requestLabelMapper;
	}

	/**
	 * Find all non-deleted labels for a given group.
	 *
	 * @param string $groupId
	 * @return Label[]
	 */
	public function findByGroup(string $groupId): array {
		return $this->mapper->findByGroup($groupId);
	}

	/**
	 * Find a label by its ID.
	 *
	 * @param int $id
	 * @return Label
	 * @throws NotFoundException
	 */
	public function findById(int $id): Label {
		try {
			return $this->mapper->findById($id);
		} catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
			throw new NotFoundException('Label not found: ' . $id);
		}
	}

	/**
	 * Create a new label.
	 *
	 * @param string $name
	 * @param string $groupId
	 * @param string $createdBy
	 * @param string|null $color
	 * @param int|null $procTypeId
	 * @return Label
	 * @throws ValidationException
	 */
	public function create(
		string $name,
		string $groupId,
		string $createdBy,
		?string $color = null,
		?int $procTypeId = null
	): Label {
		$this->validateName($name);

		$now = new \DateTimeImmutable();

		$label = new Label();
		$label->setName($name);
		$label->setGroupId($groupId);
		$label->setCreatedBy($createdBy);
		$label->setColor($color ?? '#808080');
		$label->setProcTypeId($procTypeId);
		$label->setSortOrder(0);
		$label->setCreatedAt($now->format('Y-m-d H:i:s'));
		$label->setUpdatedAt($now->format('Y-m-d H:i:s'));

		return $this->mapper->insert($label);
	}

	/**
	 * Update an existing label. Only non-null parameters are updated.
	 *
	 * @param int $id
	 * @param string|null $name
	 * @param string|null $color
	 * @param int|null $procTypeId
	 * @return Label
	 * @throws NotFoundException
	 * @throws ValidationException
	 */
	public function update(
		int $id,
		?string $name = null,
		?string $color = null,
		?int $procTypeId = null
	): Label {
		$label = $this->findById($id);

		if ($name !== null) {
			$this->validateName($name);
			$label->setName($name);
		}

		if ($color !== null) {
			$label->setColor($color);
		}

		if ($procTypeId !== null) {
			$label->setProcTypeId($procTypeId);
		}

		$now = new \DateTimeImmutable();
		$label->setUpdatedAt($now->format('Y-m-d H:i:s'));

		return $this->mapper->update($label);
	}

	/**
	 * Soft delete a label by setting deleted_at.
	 *
	 * @param int $id
	 * @return Label
	 * @throws NotFoundException
	 */
	public function delete(int $id): Label {
		$label = $this->findById($id);

		$now = new \DateTimeImmutable();
		$label->setDeletedAt($now->format('Y-m-d H:i:s'));
		$label->setUpdatedAt($now->format('Y-m-d H:i:s'));

		return $this->mapper->update($label);
	}

	/**
	 * Add a label to a request (create junction record).
	 *
	 * @param int $requestId
	 * @param int $labelId
	 * @return RequestLabel
	 */
	public function addToRequest(int $requestId, int $labelId): RequestLabel {
		$now = new \DateTimeImmutable();

		$requestLabel = new RequestLabel();
		$requestLabel->setRequestId($requestId);
		$requestLabel->setLabelId($labelId);
		$requestLabel->setCreatedAt($now->format('Y-m-d H:i:s'));

		return $this->requestLabelMapper->insert($requestLabel);
	}

	/**
	 * Remove a label from a request (delete junction record).
	 *
	 * @param int $requestId
	 * @param int $labelId
	 * @return void
	 * @throws NotFoundException
	 */
	public function removeFromRequest(int $requestId, int $labelId): void {
		try {
			$requestLabel = $this->requestLabelMapper->findByRequestAndLabel($requestId, $labelId);
			$this->requestLabelMapper->delete($requestLabel);
		} catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
			throw new NotFoundException('Label not attached to this request');
		}
	}

	/**
	 * Find all labels attached to a request (join through request_labels).
	 *
	 * @param int $requestId
	 * @return Label[]
	 */
	public function findByRequest(int $requestId): array {
		$requestLabels = $this->requestLabelMapper->findByRequest($requestId);
		$labels = [];

		foreach ($requestLabels as $requestLabel) {
			try {
				$labels[] = $this->mapper->findById($requestLabel->getLabelId());
			} catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
				// Label was deleted, skip it
				continue;
			}
		}

		return $labels;
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
}

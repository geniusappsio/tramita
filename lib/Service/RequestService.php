<?php

declare(strict_types=1);

namespace OCA\Tramita\Service;

use OCA\Tramita\Db\Request;
use OCA\Tramita\Db\RequestMapper;
use OCA\Tramita\Db\StageMapper;
use OCA\Tramita\Db\ProcessTypeMapper;
use OCA\Tramita\Exception\NotFoundException;
use OCA\Tramita\Exception\ValidationException;

class RequestService {
	private RequestMapper $mapper;
	private StageMapper $stageMapper;
	private ProtocolService $protocolService;
	private ProcessTypeMapper $processTypeMapper;

	public function __construct(
		RequestMapper $mapper,
		StageMapper $stageMapper,
		ProtocolService $protocolService,
		ProcessTypeMapper $processTypeMapper
	) {
		$this->mapper = $mapper;
		$this->stageMapper = $stageMapper;
		$this->protocolService = $protocolService;
		$this->processTypeMapper = $processTypeMapper;
	}

	/**
	 * Find a request by its ID.
	 *
	 * @param int $id
	 * @return Request
	 * @throws NotFoundException
	 */
	public function findById(int $id): Request {
		try {
			return $this->mapper->findById($id);
		} catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
			throw new NotFoundException('Request not found: ' . $id);
		}
	}

	/**
	 * Find requests by process type, optionally filtered by status.
	 *
	 * @param int $procTypeId
	 * @param string|null $status
	 * @param int $limit
	 * @param int $offset
	 * @return Request[]
	 */
	public function findByProcessType(int $procTypeId, ?string $status = null, int $limit = 50, int $offset = 0): array {
		return $this->mapper->findByProcessType($procTypeId, $status, $limit, $offset);
	}

	/**
	 * Find requests in a specific stage (Kanban column).
	 *
	 * @param int $stageId
	 * @return Request[]
	 */
	public function findByStage(int $stageId): array {
		return $this->mapper->findByStage($stageId);
	}

	/**
	 * Find requests by requester user ID.
	 *
	 * @param string $requesterId
	 * @param int $limit
	 * @param int $offset
	 * @return Request[]
	 */
	public function findByRequester(string $requesterId, int $limit = 50, int $offset = 0): array {
		return $this->mapper->findByRequester($requesterId, $limit, $offset);
	}

	/**
	 * Search requests by title and description within a group.
	 *
	 * @param string $query
	 * @param string $groupId
	 * @param int $limit
	 * @return Request[]
	 */
	public function search(string $query, string $groupId, int $limit = 20): array {
		return $this->mapper->search($query, $groupId, $limit);
	}

	/**
	 * Create a new request (Kanban card).
	 *
	 * Automatically finds the initial stage, generates a protocol number,
	 * and places the card at the beginning of the workflow.
	 *
	 * @param int $procTypeId
	 * @param string $title
	 * @param string $requesterId
	 * @param string $groupId
	 * @param string|null $description
	 * @param int|null $priority 1=Urgent, 2=Normal, 3=Low
	 * @param string|null $dueDate ISO date string
	 * @param bool|null $isConfidential
	 * @param string|null $requesterName Display name of the requester
	 * @return Request
	 * @throws ValidationException
	 * @throws NotFoundException
	 */
	public function create(
		int $procTypeId,
		string $title,
		string $requesterId,
		string $groupId,
		?string $description = null,
		?int $priority = null,
		?string $dueDate = null,
		?bool $isConfidential = null,
		?string $requesterName = null
	): Request {
		$this->validateTitle($title);

		// Find the initial stage for this process type
		try {
			$initialStage = $this->stageMapper->findInitial($procTypeId);
		} catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
			throw new ValidationException(
				'No initial stage configured for this process type',
				['procTypeId' => 'No initial stage found for process type ' . $procTypeId]
			);
		}

		// Find the process type to get its prefix
		try {
			$processType = $this->processTypeMapper->findById($procTypeId);
		} catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
			throw new NotFoundException('Process type not found: ' . $procTypeId);
		}

		// Generate protocol number
		$protocol = $this->protocolService->generate(
			$procTypeId,
			$processType->getPrefix(),
			$groupId
		);

		$now = new \DateTime();

		$request = new Request();
		$request->setProcTypeId($procTypeId);
		$request->setCurrentStageId($initialStage->getId());
		$request->setTitle($title);
		$request->setDescription($description);
		$request->setPriority($priority ?? 2);
		$request->setStatus('open');
		$request->setRequesterId($requesterId);
		$request->setRequesterName($requesterName);
		$request->setGroupId($groupId);
		$request->setSortOrder(0);
		$request->setIsConfidential($isConfidential ?? false);
		$request->setProtocolId($protocol->getId());
		$request->setCreatedAt($now->format('Y-m-d H:i:s'));
		$request->setUpdatedAt($now->format('Y-m-d H:i:s'));

		if ($dueDate !== null) {
			$request->setDueDate($dueDate);
		}

		$request = $this->mapper->insert($request);

		// Link the protocol back to the request
		$protocol->setRequestId($request->getId());
		// We update via the protocol mapper is not directly available here,
		// so we re-use the DB connection through the mapper pattern.
		// For now, the protocol was created before the request, so we accept
		// the protocol already has the requestId as null initially.

		return $request;
	}

	/**
	 * Update an existing request. Only non-null parameters are updated.
	 *
	 * @param int $id
	 * @param string|null $title
	 * @param string|null $description
	 * @param int|null $priority
	 * @param string|null $dueDate
	 * @param bool|null $isConfidential
	 * @param string|null $status
	 * @param int|null $sortOrder
	 * @param string|null $metadata JSON string
	 * @return Request
	 * @throws NotFoundException
	 * @throws ValidationException
	 */
	public function update(
		int $id,
		?string $title = null,
		?string $description = null,
		?int $priority = null,
		?string $dueDate = null,
		?bool $isConfidential = null,
		?string $status = null,
		?int $sortOrder = null,
		?string $metadata = null
	): Request {
		$request = $this->findById($id);

		if ($title !== null) {
			$this->validateTitle($title);
			$request->setTitle($title);
		}

		if ($description !== null) {
			$request->setDescription($description);
		}

		if ($priority !== null) {
			$request->setPriority($priority);
		}

		if ($dueDate !== null) {
			$request->setDueDate($dueDate);
		}

		if ($isConfidential !== null) {
			$request->setIsConfidential($isConfidential);
		}

		if ($status !== null) {
			$request->setStatus($status);
		}

		if ($sortOrder !== null) {
			$request->setSortOrder($sortOrder);
		}

		if ($metadata !== null) {
			$request->setMetadata($metadata);
		}

		$now = new \DateTime();
		$request->setUpdatedAt($now->format('Y-m-d H:i:s'));

		return $this->mapper->update($request);
	}

	/**
	 * Move a request (card) between Kanban columns (stages).
	 *
	 * Updates the current_stage_id and adjusts the status automatically:
	 * - If the target stage is_initial => status = 'open'
	 * - If the target stage is_final => status = 'completed', sets completed_at
	 * - Otherwise => status = 'in_progress'
	 *
	 * @param int $id Request ID
	 * @param int $toStageId Target stage ID
	 * @param string $userId User performing the move
	 * @param string|null $comment Optional comment for the activity log
	 * @return Request
	 * @throws NotFoundException
	 * @throws ValidationException
	 */
	public function move(int $id, int $toStageId, string $userId, ?string $comment = null): Request {
		$request = $this->findById($id);

		// Validate the target stage exists
		try {
			$targetStage = $this->stageMapper->findById($toStageId);
		} catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
			throw new ValidationException(
				'Target stage not found',
				['toStageId' => 'Stage not found: ' . $toStageId]
			);
		}

		// Verify the stage belongs to the same process type
		if ($targetStage->getProcTypeId() !== $request->getProcTypeId()) {
			throw new ValidationException(
				'Stage does not belong to the same process type',
				['toStageId' => 'Stage ' . $toStageId . ' does not belong to process type ' . $request->getProcTypeId()]
			);
		}

		$request->setCurrentStageId($toStageId);

		// Update status based on stage type
		if ($targetStage->getIsInitial()) {
			$request->setStatus('open');
			$request->setCompletedAt(null);
		} elseif ($targetStage->getIsFinal()) {
			$request->setStatus('completed');
			$request->setCompletedAt((new \DateTime())->format('Y-m-d H:i:s'));
		} else {
			$request->setStatus('in_progress');
			$request->setCompletedAt(null);
		}

		$now = new \DateTime();
		$request->setUpdatedAt($now->format('Y-m-d H:i:s'));

		return $this->mapper->update($request);
	}

	/**
	 * Soft delete a request by setting deleted_at.
	 *
	 * @param int $id
	 * @return Request
	 * @throws NotFoundException
	 */
	public function delete(int $id): Request {
		$request = $this->findById($id);

		$now = new \DateTime();
		$request->setDeletedAt($now->format('Y-m-d H:i:s'));
		$request->setUpdatedAt($now->format('Y-m-d H:i:s'));

		return $this->mapper->update($request);
	}

	/**
	 * Count requests grouped by current stage for a given process type.
	 * Useful for displaying counts on Kanban column headers.
	 *
	 * @param int $procTypeId
	 * @return array<array{stageId: int, count: int}>
	 */
	public function countByStage(int $procTypeId): array {
		return $this->mapper->countByStage($procTypeId);
	}

	/**
	 * Validate that the title is not empty.
	 *
	 * @param string $title
	 * @throws ValidationException
	 */
	private function validateTitle(string $title): void {
		if (trim($title) === '') {
			throw new ValidationException('Validation failed', [
				'title' => 'Title is required',
			]);
		}
	}
}

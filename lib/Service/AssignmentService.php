<?php

declare(strict_types=1);

namespace OCA\Tramita\Service;

use OCA\Tramita\Db\Assignment;
use OCA\Tramita\Db\AssignmentMapper;
use OCA\Tramita\Exception\NotFoundException;

class AssignmentService {
	private AssignmentMapper $mapper;

	public function __construct(AssignmentMapper $mapper) {
		$this->mapper = $mapper;
	}

	/**
	 * Find active assignments for a given request.
	 *
	 * @param int $requestId
	 * @return Assignment[]
	 */
	public function findByRequest(int $requestId): array {
		return $this->mapper->findByRequest($requestId, true);
	}

	/**
	 * Assign a user to a request.
	 *
	 * @param int $requestId
	 * @param string $userId
	 * @param string $assignedBy
	 * @param string $role
	 * @return Assignment
	 */
	public function assign(
		int $requestId,
		string $userId,
		string $assignedBy,
		string $role = 'assigned'
	): Assignment {
		$now = new \DateTime();

		$assignment = new Assignment();
		$assignment->setRequestId($requestId);
		$assignment->setUserId($userId);
		$assignment->setAssignedBy($assignedBy);
		$assignment->setRole($role);
		$assignment->setIsActive(true);
		$assignment->setAssignedAt($now->format('Y-m-d H:i:s'));

		return $this->mapper->insert($assignment);
	}

	/**
	 * Unassign a user from a request by setting isActive=false.
	 *
	 * @param int $requestId
	 * @param string $userId
	 * @param string $role
	 * @return Assignment
	 * @throws NotFoundException
	 */
	public function unassign(
		int $requestId,
		string $userId,
		string $role = 'assigned'
	): Assignment {
		try {
			$assignment = $this->mapper->findByRequestAndUser($requestId, $userId, $role);
		} catch (\OCP\AppFramework\Db\DoesNotExistException $e) {
			throw new NotFoundException('Assignment not found');
		}

		$now = new \DateTime();
		$assignment->setIsActive(false);
		$assignment->setUnassignedAt($now->format('Y-m-d H:i:s'));

		return $this->mapper->update($assignment);
	}

	/**
	 * Find active assignments for a given user.
	 *
	 * @param string $userId
	 * @return Assignment[]
	 */
	public function findByUser(string $userId): array {
		return $this->mapper->findByUser($userId, true);
	}
}

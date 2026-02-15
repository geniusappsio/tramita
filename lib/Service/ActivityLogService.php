<?php

declare(strict_types=1);

namespace OCA\Tramita\Service;

use OCA\Tramita\Db\ActivityLog;
use OCA\Tramita\Db\ActivityLogMapper;

class ActivityLogService {

	public function __construct(
		private ActivityLogMapper $activityLogMapper,
	) {
	}

	/**
	 * Create an immutable activity log entry (append-only).
	 */
	public function log(
		string $userId,
		string $action,
		string $entityType,
		?int $entityId = null,
		?int $requestId = null,
		?string $oldValue = null,
		?string $newValue = null,
		?array $details = null,
		?string $ipAddress = null,
	): ActivityLog {
		$entry = new ActivityLog();
		$entry->setUserId($userId);
		$entry->setAction($action);
		$entry->setEntityType($entityType);
		$entry->setEntityId($entityId);
		$entry->setRequestId($requestId);
		$entry->setOldValue($oldValue);
		$entry->setNewValue($newValue);
		$entry->setDetails($details !== null ? json_encode($details) : null);
		$entry->setIpAddress($ipAddress);
		$entry->setCreatedAt(new \DateTimeImmutable());

		return $this->activityLogMapper->insert($entry);
	}

	/**
	 * Find activity log entries for a specific request.
	 *
	 * @return ActivityLog[]
	 */
	public function findByRequest(
		int $requestId,
		int $limit = 50,
		int $offset = 0,
	): array {
		return $this->activityLogMapper->findByRequest($requestId, $limit, $offset);
	}
}

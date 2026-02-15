<?php

declare(strict_types=1);

namespace OCA\Tramita\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Assignment extends Entity implements JsonSerializable {

	protected int $requestId = 0;
	protected string $userId = '';
	protected string $role = 'assigned';
	protected string $assignedBy = '';
	protected ?\DateTimeImmutable $assignedAt = null;
	protected ?\DateTimeImmutable $unassignedAt = null;
	protected bool $isActive = true;

	public function __construct() {
		$this->addType('requestId', 'integer');
		$this->addType('userId', 'string');
		$this->addType('role', 'string');
		$this->addType('assignedBy', 'string');
		$this->addType('assignedAt', 'datetime');
		$this->addType('unassignedAt', 'datetime');
		$this->addType('isActive', 'boolean');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'requestId' => $this->requestId,
			'userId' => $this->userId,
			'role' => $this->role,
			'assignedBy' => $this->assignedBy,
			'assignedAt' => $this->assignedAt?->format(\DateTimeInterface::ATOM),
			'unassignedAt' => $this->unassignedAt?->format(\DateTimeInterface::ATOM),
			'isActive' => $this->isActive,
		];
	}
}

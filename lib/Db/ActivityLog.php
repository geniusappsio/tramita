<?php

declare(strict_types=1);

namespace OCA\Tramita\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class ActivityLog extends Entity implements JsonSerializable {

	protected ?int $requestId = null;
	protected string $userId = '';
	protected string $action = '';
	protected string $entityType = '';
	protected ?int $entityId = null;
	protected ?string $oldValue = null;
	protected ?string $newValue = null;
	protected ?string $details = null;
	protected ?string $ipAddress = null;
	protected ?\DateTime $createdAt = null;

	public function __construct() {
		$this->addType('requestId', 'integer');
		$this->addType('userId', 'string');
		$this->addType('action', 'string');
		$this->addType('entityType', 'string');
		$this->addType('entityId', 'integer');
		$this->addType('oldValue', 'string');
		$this->addType('newValue', 'string');
		$this->addType('details', 'string');
		$this->addType('ipAddress', 'string');
		$this->addType('createdAt', 'datetime');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'requestId' => $this->requestId,
			'userId' => $this->userId,
			'action' => $this->action,
			'entityType' => $this->entityType,
			'entityId' => $this->entityId,
			'oldValue' => $this->oldValue,
			'newValue' => $this->newValue,
			'details' => json_decode($this->details ?? '[]', true),
			'ipAddress' => $this->ipAddress,
			'createdAt' => $this->createdAt?->format(\DateTime::ATOM),
		];
	}
}

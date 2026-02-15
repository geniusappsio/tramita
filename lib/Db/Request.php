<?php

declare(strict_types=1);

namespace OCA\Tramita\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Request extends Entity implements JsonSerializable {

	protected ?int $protocolId = null;
	protected int $procTypeId = 0;
	protected int $currentStageId = 0;
	protected string $title = '';
	protected ?string $description = null;
	protected int $priority = 2;
	protected string $status = 'open';
	protected ?\DateTimeImmutable $dueDate = null;
	protected ?\DateTimeImmutable $completedAt = null;
	protected string $requesterId = '';
	protected ?string $requesterName = null;
	protected string $groupId = '';
	protected int $sortOrder = 0;
	protected ?string $metadata = null;
	protected bool $isConfidential = false;
	protected ?\DateTimeImmutable $createdAt = null;
	protected ?\DateTimeImmutable $updatedAt = null;
	protected ?\DateTimeImmutable $deletedAt = null;

	public function __construct() {
		$this->addType('protocolId', 'integer');
		$this->addType('procTypeId', 'integer');
		$this->addType('currentStageId', 'integer');
		$this->addType('title', 'string');
		$this->addType('description', 'string');
		$this->addType('priority', 'integer');
		$this->addType('status', 'string');
		$this->addType('dueDate', 'datetime');
		$this->addType('completedAt', 'datetime');
		$this->addType('requesterId', 'string');
		$this->addType('requesterName', 'string');
		$this->addType('groupId', 'string');
		$this->addType('sortOrder', 'integer');
		$this->addType('metadata', 'string');
		$this->addType('isConfidential', 'boolean');
		$this->addType('createdAt', 'datetime');
		$this->addType('updatedAt', 'datetime');
		$this->addType('deletedAt', 'datetime');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'protocolId' => $this->protocolId,
			'procTypeId' => $this->procTypeId,
			'currentStageId' => $this->currentStageId,
			'title' => $this->title,
			'description' => $this->description,
			'priority' => $this->priority,
			'status' => $this->status,
			'dueDate' => $this->dueDate?->format(\DateTimeInterface::ATOM),
			'completedAt' => $this->completedAt?->format(\DateTimeInterface::ATOM),
			'requesterId' => $this->requesterId,
			'requesterName' => $this->requesterName,
			'groupId' => $this->groupId,
			'sortOrder' => $this->sortOrder,
			'metadata' => json_decode($this->metadata ?? '[]', true),
			'isConfidential' => $this->isConfidential,
			'createdAt' => $this->createdAt?->format(\DateTimeInterface::ATOM),
			'updatedAt' => $this->updatedAt?->format(\DateTimeInterface::ATOM),
			'deletedAt' => $this->deletedAt?->format(\DateTimeInterface::ATOM),
		];
	}
}

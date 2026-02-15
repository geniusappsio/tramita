<?php

declare(strict_types=1);

namespace OCA\Tramita\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class NotifPref extends Entity implements JsonSerializable {

	protected string $userId = '';
	protected string $eventType = '';
	protected string $channel = 'app';
	protected bool $isEnabled = true;
	protected ?\DateTimeImmutable $createdAt = null;
	protected ?\DateTimeImmutable $updatedAt = null;

	public function __construct() {
		$this->addType('userId', 'string');
		$this->addType('eventType', 'string');
		$this->addType('channel', 'string');
		$this->addType('isEnabled', 'boolean');
		$this->addType('createdAt', 'datetime');
		$this->addType('updatedAt', 'datetime');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'userId' => $this->userId,
			'eventType' => $this->eventType,
			'channel' => $this->channel,
			'isEnabled' => $this->isEnabled,
			'createdAt' => $this->createdAt?->format(\DateTimeInterface::ATOM),
			'updatedAt' => $this->updatedAt?->format(\DateTimeInterface::ATOM),
		];
	}
}

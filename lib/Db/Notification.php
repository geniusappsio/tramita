<?php

declare(strict_types=1);

namespace OCA\Tramita\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Notification extends Entity implements JsonSerializable {

	protected string $userId = '';
	protected ?int $requestId = null;
	protected string $type = '';
	protected string $title = '';
	protected ?string $message = null;
	protected ?string $link = null;
	protected bool $isRead = false;
	protected ?\DateTimeImmutable $readAt = null;
	protected ?\DateTimeImmutable $createdAt = null;

	public function __construct() {
		$this->addType('userId', 'string');
		$this->addType('requestId', 'integer');
		$this->addType('type', 'string');
		$this->addType('title', 'string');
		$this->addType('message', 'string');
		$this->addType('link', 'string');
		$this->addType('isRead', 'boolean');
		$this->addType('readAt', 'datetime');
		$this->addType('createdAt', 'datetime');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'userId' => $this->userId,
			'requestId' => $this->requestId,
			'type' => $this->type,
			'title' => $this->title,
			'message' => $this->message,
			'link' => $this->link,
			'isRead' => $this->isRead,
			'readAt' => $this->readAt?->format(\DateTimeInterface::ATOM),
			'createdAt' => $this->createdAt?->format(\DateTimeInterface::ATOM),
		];
	}
}

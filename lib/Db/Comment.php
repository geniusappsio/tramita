<?php

declare(strict_types=1);

namespace OCA\Tramita\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Comment extends Entity implements JsonSerializable {

	protected int $requestId = 0;
	protected ?int $parentId = null;
	protected string $userId = '';
	protected string $content = '';
	protected bool $isSystem = false;
	protected ?string $mentions = null;
	protected ?\DateTime $createdAt = null;
	protected ?\DateTime $updatedAt = null;
	protected ?\DateTime $deletedAt = null;

	public function __construct() {
		$this->addType('requestId', 'integer');
		$this->addType('parentId', 'integer');
		$this->addType('userId', 'string');
		$this->addType('content', 'string');
		$this->addType('isSystem', 'boolean');
		$this->addType('mentions', 'string');
		$this->addType('createdAt', 'datetime');
		$this->addType('updatedAt', 'datetime');
		$this->addType('deletedAt', 'datetime');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'requestId' => $this->requestId,
			'parentId' => $this->parentId,
			'userId' => $this->userId,
			'content' => $this->content,
			'isSystem' => $this->isSystem,
			'mentions' => json_decode($this->mentions ?? '[]', true),
			'createdAt' => $this->createdAt?->format(\DateTime::ATOM),
			'updatedAt' => $this->updatedAt?->format(\DateTime::ATOM),
			'deletedAt' => $this->deletedAt?->format(\DateTime::ATOM),
		];
	}
}

<?php

declare(strict_types=1);

namespace OCA\Tramita\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class StageTransition extends Entity implements JsonSerializable {

	protected int $requestId = 0;
	protected ?int $fromStageId = null;
	protected int $toStageId = 0;
	protected string $userId = '';
	protected ?string $comment = null;
	protected ?int $durationSecs = null;
	protected ?\DateTimeImmutable $createdAt = null;

	public function __construct() {
		$this->addType('requestId', 'integer');
		$this->addType('fromStageId', 'integer');
		$this->addType('toStageId', 'integer');
		$this->addType('userId', 'string');
		$this->addType('comment', 'string');
		$this->addType('durationSecs', 'integer');
		$this->addType('createdAt', 'datetime');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'requestId' => $this->requestId,
			'fromStageId' => $this->fromStageId,
			'toStageId' => $this->toStageId,
			'userId' => $this->userId,
			'comment' => $this->comment,
			'durationSecs' => $this->durationSecs,
			'createdAt' => $this->createdAt?->format(\DateTimeInterface::ATOM),
		];
	}
}

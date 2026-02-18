<?php

declare(strict_types=1);

namespace OCA\Tramita\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class RequestLabel extends Entity implements JsonSerializable {

	protected int $requestId = 0;
	protected int $labelId = 0;
	protected ?\DateTime $createdAt = null;

	public function __construct() {
		$this->addType('requestId', 'integer');
		$this->addType('labelId', 'integer');
		$this->addType('createdAt', 'datetime');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'requestId' => $this->requestId,
			'labelId' => $this->labelId,
			'createdAt' => $this->createdAt?->format(\DateTime::ATOM),
		];
	}
}

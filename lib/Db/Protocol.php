<?php

declare(strict_types=1);

namespace OCA\Tramita\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Protocol extends Entity implements JsonSerializable {

	protected int $year = 0;
	protected int $sequence = 0;
	protected string $prefix = '';
	protected string $fullNumber = '';
	protected int $procTypeId = 0;
	protected ?int $requestId = null;
	protected string $groupId = '';
	protected ?\DateTimeImmutable $createdAt = null;

	public function __construct() {
		$this->addType('year', 'integer');
		$this->addType('sequence', 'integer');
		$this->addType('prefix', 'string');
		$this->addType('fullNumber', 'string');
		$this->addType('procTypeId', 'integer');
		$this->addType('requestId', 'integer');
		$this->addType('groupId', 'string');
		$this->addType('createdAt', 'datetime');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'year' => $this->year,
			'sequence' => $this->sequence,
			'prefix' => $this->prefix,
			'fullNumber' => $this->fullNumber,
			'procTypeId' => $this->procTypeId,
			'requestId' => $this->requestId,
			'groupId' => $this->groupId,
			'createdAt' => $this->createdAt?->format(\DateTimeInterface::ATOM),
		];
	}
}

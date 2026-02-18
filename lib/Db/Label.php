<?php

declare(strict_types=1);

namespace OCA\Tramita\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Label extends Entity implements JsonSerializable {

	protected string $name = '';
	protected string $color = '#808080';
	protected string $groupId = '';
	protected ?int $procTypeId = null;
	protected int $sortOrder = 0;
	protected string $createdBy = '';
	protected ?\DateTime $createdAt = null;
	protected ?\DateTime $updatedAt = null;
	protected ?\DateTime $deletedAt = null;

	public function __construct() {
		$this->addType('name', 'string');
		$this->addType('color', 'string');
		$this->addType('groupId', 'string');
		$this->addType('procTypeId', 'integer');
		$this->addType('sortOrder', 'integer');
		$this->addType('createdBy', 'string');
		$this->addType('createdAt', 'datetime');
		$this->addType('updatedAt', 'datetime');
		$this->addType('deletedAt', 'datetime');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'name' => $this->name,
			'color' => $this->color,
			'groupId' => $this->groupId,
			'procTypeId' => $this->procTypeId,
			'sortOrder' => $this->sortOrder,
			'createdBy' => $this->createdBy,
			'createdAt' => $this->createdAt?->format(\DateTime::ATOM),
			'updatedAt' => $this->updatedAt?->format(\DateTime::ATOM),
			'deletedAt' => $this->deletedAt?->format(\DateTime::ATOM),
		];
	}
}

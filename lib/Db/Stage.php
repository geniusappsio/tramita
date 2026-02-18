<?php

declare(strict_types=1);

namespace OCA\Tramita\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class Stage extends Entity implements JsonSerializable {

	protected int $procTypeId = 0;
	protected string $name = '';
	protected string $slug = '';
	protected ?string $description = null;
	protected ?string $color = null;
	protected int $sortOrder = 0;
	protected bool $isInitial = false;
	protected bool $isFinal = false;
	protected ?string $allowedNext = null;
	protected ?string $autoAssign = null;
	protected ?int $slaHours = null;
	protected bool $isActive = true;
	protected ?\DateTime $createdAt = null;
	protected ?\DateTime $updatedAt = null;
	protected ?\DateTime $deletedAt = null;

	public function __construct() {
		$this->addType('procTypeId', 'integer');
		$this->addType('name', 'string');
		$this->addType('slug', 'string');
		$this->addType('description', 'string');
		$this->addType('color', 'string');
		$this->addType('sortOrder', 'integer');
		$this->addType('isInitial', 'boolean');
		$this->addType('isFinal', 'boolean');
		$this->addType('allowedNext', 'string');
		$this->addType('autoAssign', 'string');
		$this->addType('slaHours', 'integer');
		$this->addType('isActive', 'boolean');
		$this->addType('createdAt', 'datetime');
		$this->addType('updatedAt', 'datetime');
		$this->addType('deletedAt', 'datetime');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'procTypeId' => $this->procTypeId,
			'name' => $this->name,
			'slug' => $this->slug,
			'description' => $this->description,
			'color' => $this->color,
			'sortOrder' => $this->sortOrder,
			'isInitial' => $this->isInitial,
			'isFinal' => $this->isFinal,
			'allowedNext' => json_decode($this->allowedNext ?? '[]', true),
			'autoAssign' => json_decode($this->autoAssign ?? '[]', true),
			'slaHours' => $this->slaHours,
			'isActive' => $this->isActive,
			'createdAt' => $this->createdAt?->format(\DateTime::ATOM),
			'updatedAt' => $this->updatedAt?->format(\DateTime::ATOM),
			'deletedAt' => $this->deletedAt?->format(\DateTime::ATOM),
		];
	}
}

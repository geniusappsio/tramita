<?php

declare(strict_types=1);

namespace OCA\Tramita\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class FormTemplate extends Entity implements JsonSerializable {

	protected int $procTypeId = 0;
	protected ?int $stageId = null;
	protected string $name = '';
	protected ?string $description = null;
	protected int $version = 1;
	protected bool $isActive = true;
	protected bool $isRequired = false;
	protected int $sortOrder = 0;
	protected ?string $settings = null;
	protected string $createdBy = '';
	protected ?\DateTime $createdAt = null;
	protected ?\DateTime $updatedAt = null;
	protected ?\DateTime $deletedAt = null;

	public function __construct() {
		$this->addType('procTypeId', 'integer');
		$this->addType('stageId', 'integer');
		$this->addType('name', 'string');
		$this->addType('description', 'string');
		$this->addType('version', 'integer');
		$this->addType('isActive', 'boolean');
		$this->addType('isRequired', 'boolean');
		$this->addType('sortOrder', 'integer');
		$this->addType('settings', 'string');
		$this->addType('createdBy', 'string');
		$this->addType('createdAt', 'datetime');
		$this->addType('updatedAt', 'datetime');
		$this->addType('deletedAt', 'datetime');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'procTypeId' => $this->procTypeId,
			'stageId' => $this->stageId,
			'name' => $this->name,
			'description' => $this->description,
			'version' => $this->version,
			'isActive' => $this->isActive,
			'isRequired' => $this->isRequired,
			'sortOrder' => $this->sortOrder,
			'settings' => json_decode($this->settings ?? '[]', true),
			'createdBy' => $this->createdBy,
			'createdAt' => $this->createdAt?->format(\DateTime::ATOM),
			'updatedAt' => $this->updatedAt?->format(\DateTime::ATOM),
			'deletedAt' => $this->deletedAt?->format(\DateTime::ATOM),
		];
	}
}

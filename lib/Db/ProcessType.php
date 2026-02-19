<?php

declare(strict_types=1);

namespace OCA\Tramita\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class ProcessType extends Entity implements JsonSerializable {

	protected string $name = '';
	protected string $slug = '';
	protected ?string $description = null;
	protected string $prefix = '';
	protected ?string $color = null;
	protected ?string $icon = null;
	protected string $groupId = '';
	protected bool $isActive = true;
	protected bool $isExternal = false;
	protected int $sortOrder = 0;
	protected ?string $settings = null;
	protected string $createdBy = '';
	protected ?\DateTime $createdAt = null;
	protected ?\DateTime $updatedAt = null;
	protected ?\DateTime $deletedAt = null;

	public function __construct() {
		$this->addType('name', 'string');
		$this->addType('slug', 'string');
		$this->addType('description', 'string');
		$this->addType('prefix', 'string');
		$this->addType('color', 'string');
		$this->addType('icon', 'string');
		$this->addType('groupId', 'string');
		$this->addType('isActive', 'boolean');
		$this->addType('isExternal', 'boolean');
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
			'name' => $this->name,
			'slug' => $this->slug,
			'description' => $this->description,
			'prefix' => $this->prefix,
			'color' => $this->color,
			'icon' => $this->icon,
			'groupId' => $this->groupId,
			'isActive' => $this->isActive,
			'isExternal' => $this->isExternal,
			'sortOrder' => $this->sortOrder,
			'settings' => json_decode($this->settings ?? '[]', true),
			'createdBy' => $this->createdBy,
			'createdAt' => $this->createdAt?->format(\DateTime::ATOM),
			'updatedAt' => $this->updatedAt?->format(\DateTime::ATOM),
			'deletedAt' => $this->deletedAt?->format(\DateTime::ATOM),
		];
	}
}

<?php

declare(strict_types=1);

namespace OCA\Tramita\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class FormField extends Entity implements JsonSerializable {

	protected int $templateId = 0;
	protected string $name = '';
	protected string $label = '';
	protected string $fieldType = '';
	protected ?string $placeholder = null;
	protected ?string $helpText = null;
	protected ?string $defaultValue = null;
	protected bool $isRequired = false;
	protected bool $isReadonly = false;
	protected bool $isHidden = false;
	protected ?string $validation = null;
	protected ?string $options = null;
	protected int $sortOrder = 0;
	protected ?string $width = 'full';
	protected ?string $conditional = null;
	protected ?\DateTimeImmutable $createdAt = null;
	protected ?\DateTimeImmutable $updatedAt = null;
	protected ?\DateTimeImmutable $deletedAt = null;

	public function __construct() {
		$this->addType('templateId', 'integer');
		$this->addType('name', 'string');
		$this->addType('label', 'string');
		$this->addType('fieldType', 'string');
		$this->addType('placeholder', 'string');
		$this->addType('helpText', 'string');
		$this->addType('defaultValue', 'string');
		$this->addType('isRequired', 'boolean');
		$this->addType('isReadonly', 'boolean');
		$this->addType('isHidden', 'boolean');
		$this->addType('validation', 'string');
		$this->addType('options', 'string');
		$this->addType('sortOrder', 'integer');
		$this->addType('width', 'string');
		$this->addType('conditional', 'string');
		$this->addType('createdAt', 'datetime');
		$this->addType('updatedAt', 'datetime');
		$this->addType('deletedAt', 'datetime');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'templateId' => $this->templateId,
			'name' => $this->name,
			'label' => $this->label,
			'fieldType' => $this->fieldType,
			'placeholder' => $this->placeholder,
			'helpText' => $this->helpText,
			'defaultValue' => $this->defaultValue,
			'isRequired' => $this->isRequired,
			'isReadonly' => $this->isReadonly,
			'isHidden' => $this->isHidden,
			'validation' => json_decode($this->validation ?? '[]', true),
			'options' => json_decode($this->options ?? '[]', true),
			'sortOrder' => $this->sortOrder,
			'width' => $this->width,
			'conditional' => json_decode($this->conditional ?? '[]', true),
			'createdAt' => $this->createdAt?->format(\DateTimeInterface::ATOM),
			'updatedAt' => $this->updatedAt?->format(\DateTimeInterface::ATOM),
			'deletedAt' => $this->deletedAt?->format(\DateTimeInterface::ATOM),
		];
	}
}

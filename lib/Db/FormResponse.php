<?php

declare(strict_types=1);

namespace OCA\Tramita\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class FormResponse extends Entity implements JsonSerializable {

	protected int $requestId = 0;
	protected int $templateId = 0;
	protected int $fieldId = 0;
	protected ?string $valueText = null;
	protected ?int $valueInt = null;
	protected ?string $valueDecimal = null;
	protected ?\DateTime $valueDate = null;
	protected ?string $valueJson = null;
	protected string $submittedBy = '';
	protected ?\DateTime $createdAt = null;
	protected ?\DateTime $updatedAt = null;

	public function __construct() {
		$this->addType('requestId', 'integer');
		$this->addType('templateId', 'integer');
		$this->addType('fieldId', 'integer');
		$this->addType('valueText', 'string');
		$this->addType('valueInt', 'integer');
		$this->addType('valueDecimal', 'string');
		$this->addType('valueDate', 'datetime');
		$this->addType('valueJson', 'string');
		$this->addType('submittedBy', 'string');
		$this->addType('createdAt', 'datetime');
		$this->addType('updatedAt', 'datetime');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'requestId' => $this->requestId,
			'templateId' => $this->templateId,
			'fieldId' => $this->fieldId,
			'valueText' => $this->valueText,
			'valueInt' => $this->valueInt,
			'valueDecimal' => $this->valueDecimal,
			'valueDate' => $this->valueDate?->format(\DateTime::ATOM),
			'valueJson' => json_decode($this->valueJson ?? '[]', true),
			'submittedBy' => $this->submittedBy,
			'createdAt' => $this->createdAt?->format(\DateTime::ATOM),
			'updatedAt' => $this->updatedAt?->format(\DateTime::ATOM),
		];
	}
}

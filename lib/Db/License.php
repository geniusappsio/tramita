<?php

declare(strict_types=1);

namespace OCA\Tramita\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

class License extends Entity implements JsonSerializable {

	protected string $licenseKey = '';
	protected string $instanceId = '';
	protected string $status = 'trial';
	protected ?string $licensedTo = null;
	protected ?\DateTimeImmutable $validUntil = null;
	protected int $maxUsers = 0;
	protected ?string $features = null;
	protected ?\DateTimeImmutable $lastCheck = null;
	protected ?\DateTimeImmutable $createdAt = null;
	protected ?\DateTimeImmutable $updatedAt = null;

	public function __construct() {
		$this->addType('licenseKey', 'string');
		$this->addType('instanceId', 'string');
		$this->addType('status', 'string');
		$this->addType('licensedTo', 'string');
		$this->addType('validUntil', 'datetime');
		$this->addType('maxUsers', 'integer');
		$this->addType('features', 'string');
		$this->addType('lastCheck', 'datetime');
		$this->addType('createdAt', 'datetime');
		$this->addType('updatedAt', 'datetime');
	}

	public function jsonSerialize(): array {
		return [
			'id' => $this->id,
			'licenseKey' => $this->licenseKey,
			'instanceId' => $this->instanceId,
			'status' => $this->status,
			'licensedTo' => $this->licensedTo,
			'validUntil' => $this->validUntil?->format(\DateTimeInterface::ATOM),
			'maxUsers' => $this->maxUsers,
			'features' => json_decode($this->features ?? '[]', true),
			'lastCheck' => $this->lastCheck?->format(\DateTimeInterface::ATOM),
			'createdAt' => $this->createdAt?->format(\DateTimeInterface::ATOM),
			'updatedAt' => $this->updatedAt?->format(\DateTimeInterface::ATOM),
		];
	}
}

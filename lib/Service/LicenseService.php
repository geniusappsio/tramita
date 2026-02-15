<?php

declare(strict_types=1);

namespace OCA\Tramita\Service;

use OCA\Tramita\Db\License;
use OCA\Tramita\Db\LicenseMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\Http\Client\IClientService;
use OCP\IConfig;

class LicenseService {

	private const VALIDATION_URL = 'https://license.geniusapps.com.br/api/v1/validate';
	private const CACHE_TTL = 86400; // 24 hours
	private const GRACE_PERIOD_DAYS = 7;

	public function __construct(
		private LicenseMapper $mapper,
		private IConfig $config,
		private IClientService $clientService,
	) {
	}

	public function getInstanceId(): string {
		return $this->config->getSystemValueString('instanceid', '');
	}

	public function getCurrentLicense(): ?License {
		try {
			return $this->mapper->findByInstanceId($this->getInstanceId());
		} catch (DoesNotExistException $e) {
			return null;
		}
	}

	public function isValid(): bool {
		$license = $this->getCurrentLicense();
		if ($license === null) {
			return false;
		}

		// Check cached validation
		$lastCheck = $this->config->getAppValue('tramita', 'license_last_check', '0');
		$lastStatus = $this->config->getAppValue('tramita', 'license_last_status', 'invalid');

		if ((time() - (int) $lastCheck) < self::CACHE_TTL) {
			return $lastStatus === 'valid';
		}

		// Validate remotely
		return $this->validateRemote($license);
	}

	public function activate(string $licenseKey): License {
		$instanceId = $this->getInstanceId();

		// Try to find existing
		$license = $this->getCurrentLicense();
		$now = new \DateTimeImmutable();

		if ($license === null) {
			$license = new License();
			$license->setInstanceId($instanceId);
			$license->setCreatedAt($now);
		}

		$license->setLicenseKey($licenseKey);
		$license->setStatus('pending');
		$license->setUpdatedAt($now);

		if ($license->getId() === null) {
			$license = $this->mapper->insert($license);
		} else {
			$license = $this->mapper->update($license);
		}

		// Validate immediately
		$this->validateRemote($license);

		return $this->getCurrentLicense() ?? $license;
	}

	public function getLicenseInfo(): array {
		$license = $this->getCurrentLicense();
		return [
			'hasLicense' => $license !== null,
			'status' => $license?->getStatus() ?? 'none',
			'licensedTo' => $license?->getLicensedTo(),
			'validUntil' => $license?->getValidUntil()?->format(\DateTimeInterface::ATOM),
			'maxUsers' => $license?->getMaxUsers() ?? 0,
			'isValid' => $this->isValid(),
		];
	}

	private function validateRemote(License $license): bool {
		try {
			$client = $this->clientService->newClient();
			$response = $client->post(self::VALIDATION_URL, [
				'json' => [
					'license_key' => $license->getLicenseKey(),
					'instance_id' => $license->getInstanceId(),
					'app_version' => $this->config->getAppValue('tramita', 'installed_version', '1.0.0'),
				],
				'timeout' => 10,
			]);

			$data = json_decode($response->getBody(), true);
			$now = new \DateTimeImmutable();

			if ($data['valid'] ?? false) {
				$license->setStatus('active');
				$license->setLicensedTo($data['licensed_to'] ?? null);
				if (isset($data['expires'])) {
					$license->setValidUntil(new \DateTimeImmutable($data['expires']));
				}
				if (isset($data['max_users'])) {
					$license->setMaxUsers((int) $data['max_users']);
				}
				if (isset($data['features'])) {
					$license->setFeatures(json_encode($data['features']));
				}
				$license->setLastCheck($now);
				$license->setUpdatedAt($now);
				$this->mapper->update($license);

				$this->config->setAppValue('tramita', 'license_last_check', (string) time());
				$this->config->setAppValue('tramita', 'license_last_status', 'valid');
				return true;
			} else {
				$license->setStatus('invalid');
				$license->setLastCheck($now);
				$license->setUpdatedAt($now);
				$this->mapper->update($license);

				$this->config->setAppValue('tramita', 'license_last_check', (string) time());
				$this->config->setAppValue('tramita', 'license_last_status', 'invalid');
				return false;
			}
		} catch (\Exception $e) {
			// Server unreachable - apply grace period
			$lastCheck = $this->config->getAppValue('tramita', 'license_last_check', '0');
			$daysSinceCheck = (time() - (int) $lastCheck) / 86400;

			if ($daysSinceCheck <= self::GRACE_PERIOD_DAYS) {
				$lastStatus = $this->config->getAppValue('tramita', 'license_last_status', 'invalid');
				return $lastStatus === 'valid';
			}

			return false;
		}
	}
}

<?php

declare(strict_types=1);

namespace OCA\Tramita\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<License>
 */
class LicenseMapper extends QBMapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'tramita_licenses', License::class);
	}

	/**
	 * Find a license by its instance ID.
	 *
	 * @param string $instanceId The Nextcloud instance identifier
	 * @return License
	 * @throws DoesNotExistException if no license is found for the given instance
	 * @throws MultipleObjectsReturnedException if more than one license matches
	 */
	public function findByInstanceId(string $instanceId): License {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('instance_id', $qb->createNamedParameter($instanceId, IQueryBuilder::PARAM_STR)));

		return $this->findEntity($qb);
	}

	/**
	 * Find all active licenses (status = 'active' and valid_until is in the future or null).
	 *
	 * @return License[]
	 */
	public function findActive(): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('status', $qb->createNamedParameter('active', IQueryBuilder::PARAM_STR)))
			->andWhere(
				$qb->expr()->orX(
					$qb->expr()->isNull('valid_until'),
					$qb->expr()->gte('valid_until', $qb->createFunction('NOW()'))
				)
			);

		return $this->findEntities($qb);
	}
}

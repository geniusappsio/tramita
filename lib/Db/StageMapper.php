<?php

declare(strict_types=1);

namespace OCA\Tramita\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<Stage>
 */
class StageMapper extends QBMapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'tramita_stages', Stage::class);
	}

	/**
	 * Find a stage by its ID (excluding soft-deleted records).
	 *
	 * @param int $id
	 * @return Stage
	 * @throws DoesNotExistException if the stage does not exist or is soft-deleted
	 * @throws MultipleObjectsReturnedException if more than one record matches
	 */
	public function findById(int $id): Stage {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->isNull('deleted_at'));

		return $this->findEntity($qb);
	}

	/**
	 * Find all non-deleted stages for a given process type, ordered by sort_order.
	 *
	 * @param int $procTypeId The process type ID
	 * @return Stage[]
	 */
	public function findByProcessType(int $procTypeId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('proc_type_id', $qb->createNamedParameter($procTypeId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->isNull('deleted_at'))
			->orderBy('sort_order', 'ASC');

		return $this->findEntities($qb);
	}

	/**
	 * Find the initial stage for a given process type.
	 *
	 * @param int $procTypeId The process type ID
	 * @return Stage
	 * @throws DoesNotExistException if no initial stage is configured
	 * @throws MultipleObjectsReturnedException if more than one initial stage exists
	 */
	public function findInitial(int $procTypeId): Stage {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('proc_type_id', $qb->createNamedParameter($procTypeId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->eq('is_initial', $qb->createNamedParameter(true, IQueryBuilder::PARAM_BOOL)))
			->andWhere($qb->expr()->isNull('deleted_at'));

		return $this->findEntity($qb);
	}

	/**
	 * Find all active and non-deleted stages for a given process type, ordered by sort_order.
	 *
	 * @param int $procTypeId The process type ID
	 * @return Stage[]
	 */
	public function findActive(int $procTypeId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('proc_type_id', $qb->createNamedParameter($procTypeId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->isNull('deleted_at'))
			->andWhere($qb->expr()->eq('is_active', $qb->createNamedParameter(true, IQueryBuilder::PARAM_BOOL)))
			->orderBy('sort_order', 'ASC');

		return $this->findEntities($qb);
	}
}

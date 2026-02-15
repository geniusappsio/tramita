<?php

declare(strict_types=1);

namespace OCA\Tramita\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<ProcessType>
 */
class ProcessTypeMapper extends QBMapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'tramita_proc_types', ProcessType::class);
	}

	/**
	 * Find a process type by its ID (excluding soft-deleted records).
	 *
	 * @param int $id
	 * @return ProcessType
	 * @throws DoesNotExistException if the process type does not exist or is soft-deleted
	 * @throws MultipleObjectsReturnedException if more than one record matches
	 */
	public function findById(int $id): ProcessType {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->isNull('deleted_at'));

		return $this->findEntity($qb);
	}

	/**
	 * Find all non-deleted process types, optionally filtered by group.
	 *
	 * @param string|null $groupId Optional group ID to filter by
	 * @return ProcessType[]
	 */
	public function findAll(?string $groupId = null): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->isNull('deleted_at'))
			->orderBy('sort_order', 'ASC');

		if ($groupId !== null) {
			$qb->andWhere($qb->expr()->eq('group_id', $qb->createNamedParameter($groupId, IQueryBuilder::PARAM_STR)));
		}

		return $this->findEntities($qb);
	}

	/**
	 * Find a process type by its slug within a specific group.
	 *
	 * @param string $slug The unique slug identifier
	 * @param string $groupId The group the process type belongs to
	 * @return ProcessType
	 * @throws DoesNotExistException if no matching process type is found
	 * @throws MultipleObjectsReturnedException if more than one record matches
	 */
	public function findBySlug(string $slug, string $groupId): ProcessType {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('slug', $qb->createNamedParameter($slug, IQueryBuilder::PARAM_STR)))
			->andWhere($qb->expr()->eq('group_id', $qb->createNamedParameter($groupId, IQueryBuilder::PARAM_STR)))
			->andWhere($qb->expr()->isNull('deleted_at'));

		return $this->findEntity($qb);
	}

	/**
	 * Find all active and non-deleted process types, optionally filtered by group.
	 *
	 * @param string|null $groupId Optional group ID to filter by
	 * @return ProcessType[]
	 */
	public function findActive(?string $groupId = null): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->isNull('deleted_at'))
			->andWhere($qb->expr()->eq('is_active', $qb->createNamedParameter(true, IQueryBuilder::PARAM_BOOL)))
			->orderBy('sort_order', 'ASC');

		if ($groupId !== null) {
			$qb->andWhere($qb->expr()->eq('group_id', $qb->createNamedParameter($groupId, IQueryBuilder::PARAM_STR)));
		}

		return $this->findEntities($qb);
	}
}

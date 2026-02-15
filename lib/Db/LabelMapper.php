<?php

declare(strict_types=1);

namespace OCA\Tramita\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<Label>
 */
class LabelMapper extends QBMapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'tramita_labels', Label::class);
	}

	/**
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function findById(int $id): Label {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->isNull('deleted_at'));

		return $this->findEntity($qb);
	}

	/**
	 * @return Label[]
	 */
	public function findByGroup(string $groupId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('group_id', $qb->createNamedParameter($groupId, IQueryBuilder::PARAM_STR)))
			->andWhere($qb->expr()->isNull('deleted_at'));

		return $this->findEntities($qb);
	}

	/**
	 * @return Label[]
	 */
	public function findByProcessType(int $procTypeId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('proc_type_id', $qb->createNamedParameter($procTypeId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->isNull('deleted_at'));

		return $this->findEntities($qb);
	}

	/**
	 * @return Label[]
	 */
	public function findActive(string $groupId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('group_id', $qb->createNamedParameter($groupId, IQueryBuilder::PARAM_STR)))
			->andWhere($qb->expr()->isNull('deleted_at'))
			->orderBy('sort_order', 'ASC');

		return $this->findEntities($qb);
	}
}

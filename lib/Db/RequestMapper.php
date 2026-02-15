<?php

declare(strict_types=1);

namespace OCA\Tramita\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<Request>
 */
class RequestMapper extends QBMapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'tramita_requests', Request::class);
	}

	/**
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function findById(int $id): Request {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->isNull('deleted_at')
			);

		return $this->findEntity($qb);
	}

	/**
	 * @return Request[]
	 */
	public function findByProcessType(int $procTypeId, ?string $status = null, int $limit = 50, int $offset = 0): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('process_type_id', $qb->createNamedParameter($procTypeId, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->isNull('deleted_at')
			);

		if ($status !== null) {
			$qb->andWhere(
				$qb->expr()->eq('status', $qb->createNamedParameter($status, IQueryBuilder::PARAM_STR))
			);
		}

		$qb->orderBy('created_at', 'DESC')
			->setMaxResults($limit)
			->setFirstResult($offset);

		return $this->findEntities($qb);
	}

	/**
	 * @return Request[]
	 */
	public function findByStage(int $stageId): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('current_stage_id', $qb->createNamedParameter($stageId, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->isNull('deleted_at')
			)
			->orderBy('sort_order', 'ASC');

		return $this->findEntities($qb);
	}

	/**
	 * @return Request[]
	 */
	public function findByRequester(string $requesterId, int $limit = 50, int $offset = 0): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('requester_id', $qb->createNamedParameter($requesterId, IQueryBuilder::PARAM_STR))
			)
			->andWhere(
				$qb->expr()->isNull('deleted_at')
			)
			->orderBy('created_at', 'DESC')
			->setMaxResults($limit)
			->setFirstResult($offset);

		return $this->findEntities($qb);
	}

	/**
	 * @return Request[]
	 */
	public function findByGroup(string $groupId, ?string $status = null, int $limit = 50, int $offset = 0): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('group_id', $qb->createNamedParameter($groupId, IQueryBuilder::PARAM_STR))
			)
			->andWhere(
				$qb->expr()->isNull('deleted_at')
			);

		if ($status !== null) {
			$qb->andWhere(
				$qb->expr()->eq('status', $qb->createNamedParameter($status, IQueryBuilder::PARAM_STR))
			);
		}

		$qb->orderBy('created_at', 'DESC')
			->setMaxResults($limit)
			->setFirstResult($offset);

		return $this->findEntities($qb);
	}

	/**
	 * Search requests by title and description.
	 *
	 * @return Request[]
	 */
	public function search(string $query, string $groupId, int $limit = 20): array {
		$qb = $this->db->getQueryBuilder();

		$searchPattern = '%' . $this->db->escapeLikeParameter($query) . '%';

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('group_id', $qb->createNamedParameter($groupId, IQueryBuilder::PARAM_STR))
			)
			->andWhere(
				$qb->expr()->isNull('deleted_at')
			)
			->andWhere(
				$qb->expr()->orX(
					$qb->expr()->iLike('title', $qb->createNamedParameter($searchPattern, IQueryBuilder::PARAM_STR)),
					$qb->expr()->iLike('description', $qb->createNamedParameter($searchPattern, IQueryBuilder::PARAM_STR))
				)
			)
			->orderBy('created_at', 'DESC')
			->setMaxResults($limit);

		return $this->findEntities($qb);
	}

	/**
	 * Count requests grouped by current stage for a given process type.
	 *
	 * @return array<array{stageId: int, count: int}>
	 */
	public function countByStage(int $procTypeId): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('current_stage_id')
			->selectAlias($qb->func()->count('id'), 'count')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('process_type_id', $qb->createNamedParameter($procTypeId, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->isNull('deleted_at')
			)
			->groupBy('current_stage_id');

		$result = $qb->executeQuery();
		$rows = $result->fetchAll();
		$result->closeCursor();

		$counts = [];
		foreach ($rows as $row) {
			$counts[] = [
				'stageId' => (int) $row['current_stage_id'],
				'count' => (int) $row['count'],
			];
		}

		return $counts;
	}
}

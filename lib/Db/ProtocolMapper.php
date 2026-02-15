<?php

declare(strict_types=1);

namespace OCA\Tramita\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<Protocol>
 */
class ProtocolMapper extends QBMapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'tramita_protocols', Protocol::class);
	}

	/**
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function findById(int $id): Protocol {
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
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function findByFullNumber(string $fullNumber): Protocol {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('full_number', $qb->createNamedParameter($fullNumber, IQueryBuilder::PARAM_STR))
			)
			->andWhere(
				$qb->expr()->isNull('deleted_at')
			);

		return $this->findEntity($qb);
	}

	/**
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function findByRequest(int $requestId): Protocol {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('request_id', $qb->createNamedParameter($requestId, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->isNull('deleted_at')
			);

		return $this->findEntity($qb);
	}

	/**
	 * Get the next sequence number for a given year, prefix, and group.
	 */
	public function getNextSequence(int $year, string $prefix, string $groupId): int {
		$qb = $this->db->getQueryBuilder();

		$qb->select($qb->func()->max('sequence'))
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('year', $qb->createNamedParameter($year, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->eq('prefix', $qb->createNamedParameter($prefix, IQueryBuilder::PARAM_STR))
			)
			->andWhere(
				$qb->expr()->eq('group_id', $qb->createNamedParameter($groupId, IQueryBuilder::PARAM_STR))
			);

		$result = $qb->executeQuery();
		$maxSequence = $result->fetchOne();
		$result->closeCursor();

		return ($maxSequence === false || $maxSequence === null) ? 1 : ((int) $maxSequence) + 1;
	}
}

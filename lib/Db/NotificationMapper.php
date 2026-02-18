<?php

declare(strict_types=1);

namespace OCA\Tramita\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<Notification>
 */
class NotificationMapper extends QBMapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'tramita_notifications', Notification::class);
	}

	/**
	 * @return Notification[]
	 */
	public function findByUser(string $userId, bool $unreadOnly = false, int $limit = 50, int $offset = 0): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId, IQueryBuilder::PARAM_STR)))
			->orderBy('created_at', 'DESC')
			->setMaxResults($limit)
			->setFirstResult($offset);

		if ($unreadOnly) {
			$qb->andWhere($qb->expr()->eq('is_read', $qb->createNamedParameter(false, IQueryBuilder::PARAM_BOOL)));
		}

		return $this->findEntities($qb);
	}

	/**
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function findById(int $id): Notification {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));

		return $this->findEntity($qb);
	}

	public function countUnread(string $userId): int {
		$qb = $this->db->getQueryBuilder();
		$qb->select($qb->createFunction('COUNT(*)'))
			->from($this->getTableName())
			->where($qb->expr()->eq('user_id', $qb->createNamedParameter($userId)))
			->andWhere($qb->expr()->eq('is_read', $qb->createNamedParameter(false, IQueryBuilder::PARAM_BOOL)));
		$result = $qb->executeQuery();
		$count = (int) $result->fetchOne();
		$result->closeCursor();
		return $count;
	}

	public function markAsRead(int $id): void {
		$qb = $this->db->getQueryBuilder();
		$qb->update($this->getTableName())
			->set('is_read', $qb->createNamedParameter(true, IQueryBuilder::PARAM_BOOL))
			->set('read_at', $qb->createNamedParameter(new \DateTime(), IQueryBuilder::PARAM_DATETIME_MUTABLE))
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
		$qb->executeStatement();
	}
}

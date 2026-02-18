<?php

declare(strict_types=1);

namespace OCA\Tramita\Service;

use OCA\Tramita\Db\Protocol;
use OCA\Tramita\Db\ProtocolMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class ProtocolService {
	private ProtocolMapper $mapper;
	private IDBConnection $db;

	public function __construct(ProtocolMapper $mapper, IDBConnection $db) {
		$this->mapper = $mapper;
		$this->db = $db;
	}

	/**
	 * Generate a unique sequential protocol number in the format PREFIX-YYYY/NNNNNN.
	 *
	 * Uses a database transaction to guarantee atomicity of the sequence counter.
	 *
	 * @param int $procTypeId The process type this protocol belongs to
	 * @param string $prefix Short prefix for the protocol (e.g. "MEM", "OFI")
	 * @param string $groupId The group (organization/department) that owns this protocol
	 * @param int|null $requestId Optional request to link this protocol to
	 * @return Protocol The newly created protocol entity
	 * @throws \Exception If the transaction fails
	 */
	public function generate(int $procTypeId, string $prefix, string $groupId, ?int $requestId = null): Protocol {
		$this->db->beginTransaction();
		try {
			$year = (int) date('Y');

			// Get next sequence number atomically
			$qb = $this->db->getQueryBuilder();
			$qb->select($qb->createFunction('MAX(sequence) as max_seq'))
				->from('tramita_protocols')
				->where($qb->expr()->eq('year', $qb->createNamedParameter($year, IQueryBuilder::PARAM_INT)))
				->andWhere($qb->expr()->eq('prefix', $qb->createNamedParameter($prefix)))
				->andWhere($qb->expr()->eq('group_id', $qb->createNamedParameter($groupId)));

			$result = $qb->executeQuery();
			$row = $result->fetch();
			$result->closeCursor();

			$nextSeq = ($row && $row['max_seq'] !== null) ? ((int) $row['max_seq'] + 1) : 1;
			$fullNumber = sprintf('%s-%d/%06d', $prefix, $year, $nextSeq);

			$protocol = new Protocol();
			$protocol->setYear($year);
			$protocol->setSequence($nextSeq);
			$protocol->setPrefix($prefix);
			$protocol->setFullNumber($fullNumber);
			$protocol->setProcTypeId($procTypeId);
			$protocol->setRequestId($requestId);
			$protocol->setGroupId($groupId);
			$protocol->setCreatedAt(new \DateTime());

			$protocol = $this->mapper->insert($protocol);

			$this->db->commit();
			return $protocol;
		} catch (\Exception $e) {
			$this->db->rollBack();
			throw $e;
		}
	}

	/**
	 * Find a protocol by its linked request ID.
	 *
	 * @param int $requestId
	 * @return Protocol
	 * @throws \OCP\AppFramework\Db\DoesNotExistException
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 */
	public function findByRequest(int $requestId): Protocol {
		return $this->mapper->findByRequest($requestId);
	}

	/**
	 * Find a protocol by its full number string.
	 *
	 * @param string $fullNumber e.g. "MEM-2026/000001"
	 * @return Protocol
	 * @throws \OCP\AppFramework\Db\DoesNotExistException
	 * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 */
	public function findByFullNumber(string $fullNumber): Protocol {
		return $this->mapper->findByFullNumber($fullNumber);
	}
}

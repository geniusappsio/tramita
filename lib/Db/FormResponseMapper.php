<?php

declare(strict_types=1);

namespace OCA\Tramita\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<FormResponse>
 */
class FormResponseMapper extends QBMapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'tramita_form_responses', FormResponse::class);
	}

	/**
	 * Find all form responses for a request.
	 *
	 * @return FormResponse[]
	 */
	public function findByRequest(int $requestId): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('request_id', $qb->createNamedParameter($requestId, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->isNull('deleted_at')
			);

		return $this->findEntities($qb);
	}

	/**
	 * Find all form responses for a request filtered by template.
	 *
	 * @return FormResponse[]
	 */
	public function findByRequestAndTemplate(int $requestId, int $templateId): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('request_id', $qb->createNamedParameter($requestId, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->eq('template_id', $qb->createNamedParameter($templateId, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->isNull('deleted_at')
			);

		return $this->findEntities($qb);
	}

	/**
	 * Find a single form response for a request and field.
	 *
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function findByRequestAndField(int $requestId, int $fieldId): FormResponse {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('request_id', $qb->createNamedParameter($requestId, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->eq('field_id', $qb->createNamedParameter($fieldId, IQueryBuilder::PARAM_INT))
			)
			->andWhere(
				$qb->expr()->isNull('deleted_at')
			);

		return $this->findEntity($qb);
	}
}

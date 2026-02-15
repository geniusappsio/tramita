<?php

declare(strict_types=1);

namespace OCA\Tramita\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<FormTemplate>
 */
class FormTemplateMapper extends QBMapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'tramita_form_templates', FormTemplate::class);
	}

	/**
	 * Find a form template by its ID (excluding soft-deleted records).
	 *
	 * @param int $id
	 * @return FormTemplate
	 * @throws DoesNotExistException if the template does not exist or is soft-deleted
	 * @throws MultipleObjectsReturnedException if more than one record matches
	 */
	public function findById(int $id): FormTemplate {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->isNull('deleted_at'));

		return $this->findEntity($qb);
	}

	/**
	 * Find all non-deleted form templates for a given process type, ordered by sort_order.
	 *
	 * @param int $procTypeId The process type ID
	 * @return FormTemplate[]
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
	 * Find all non-deleted form templates assigned to a specific stage, ordered by sort_order.
	 *
	 * @param int $stageId The stage ID
	 * @return FormTemplate[]
	 */
	public function findByStage(int $stageId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('stage_id', $qb->createNamedParameter($stageId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->isNull('deleted_at'))
			->orderBy('sort_order', 'ASC');

		return $this->findEntities($qb);
	}

	/**
	 * Find all active and non-deleted form templates for a given process type, ordered by sort_order.
	 *
	 * @param int $procTypeId The process type ID
	 * @return FormTemplate[]
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

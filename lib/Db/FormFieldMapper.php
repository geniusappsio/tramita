<?php

declare(strict_types=1);

namespace OCA\Tramita\Db;

use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @extends QBMapper<FormField>
 */
class FormFieldMapper extends QBMapper {

	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'tramita_form_fields', FormField::class);
	}

	/**
	 * Find a form field by its ID (excluding soft-deleted records).
	 *
	 * @param int $id
	 * @return FormField
	 * @throws DoesNotExistException if the field does not exist or is soft-deleted
	 * @throws MultipleObjectsReturnedException if more than one record matches
	 */
	public function findById(int $id): FormField {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('id', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->isNull('deleted_at'));

		return $this->findEntity($qb);
	}

	/**
	 * Find all non-deleted form fields for a given template, ordered by sort_order.
	 *
	 * @param int $templateId The form template ID
	 * @return FormField[]
	 */
	public function findByTemplate(int $templateId): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('template_id', $qb->createNamedParameter($templateId, IQueryBuilder::PARAM_INT)))
			->andWhere($qb->expr()->isNull('deleted_at'))
			->orderBy('sort_order', 'ASC');

		return $this->findEntities($qb);
	}
}

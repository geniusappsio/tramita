<?php

declare(strict_types=1);

namespace OCA\Tramita\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Migration 2: Request tables
 * - tramita_protocols
 * - tramita_requests
 * - tramita_form_responses
 */
class Version0100Date20260214120100 extends SimpleMigrationStep {

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		// === tramita_protocols ===
		if (!$schema->hasTable('tramita_protocols')) {
			$table = $schema->createTable('tramita_protocols');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('year', Types::INTEGER, [
				'notnull' => true,
			]);
			$table->addColumn('sequence', Types::BIGINT, [
				'notnull' => true,
			]);
			$table->addColumn('prefix', Types::STRING, [
				'notnull' => true,
				'length' => 16,
			]);
			$table->addColumn('full_number', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('proc_type_id', Types::BIGINT, [
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('request_id', Types::BIGINT, [
				'notnull' => false,
				'unsigned' => true,
			]);
			$table->addColumn('group_id', Types::STRING, [
				'notnull' => true,
				'length' => 256,
			]);
			$table->addColumn('created_at', Types::DATETIME, [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['full_number'], 'tramita_proto_full_uniq');
			$table->addUniqueIndex(['year', 'prefix', 'sequence', 'group_id'], 'tramita_proto_seq_uniq');
			$table->addIndex(['year', 'prefix'], 'tramita_proto_year_idx');
			$table->addIndex(['request_id'], 'tramita_proto_req_idx');
			$table->addIndex(['proc_type_id'], 'tramita_proto_ptype_idx');
		}

		// === tramita_requests ===
		if (!$schema->hasTable('tramita_requests')) {
			$table = $schema->createTable('tramita_requests');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('protocol_id', Types::BIGINT, [
				'notnull' => false,
				'unsigned' => true,
			]);
			$table->addColumn('proc_type_id', Types::BIGINT, [
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('current_stage_id', Types::BIGINT, [
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('title', Types::STRING, [
				'notnull' => true,
				'length' => 512,
			]);
			$table->addColumn('description', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('priority', Types::SMALLINT, [
				'notnull' => true,
				'default' => 2,
			]);
			$table->addColumn('status', Types::STRING, [
				'notnull' => true,
				'length' => 32,
				'default' => 'open',
			]);
			$table->addColumn('due_date', Types::DATETIME, [
				'notnull' => false,
			]);
			$table->addColumn('completed_at', Types::DATETIME, [
				'notnull' => false,
			]);
			$table->addColumn('requester_id', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('requester_name', Types::STRING, [
				'notnull' => false,
				'length' => 256,
			]);
			$table->addColumn('group_id', Types::STRING, [
				'notnull' => true,
				'length' => 256,
			]);
			$table->addColumn('sort_order', Types::INTEGER, [
				'notnull' => true,
				'default' => 0,
			]);
			$table->addColumn('metadata', Types::JSON, [
				'notnull' => false,
			]);
			$table->addColumn('is_confidential', Types::BOOLEAN, [
				'notnull' => true,
				'default' => false,
			]);
			$table->addColumn('created_at', Types::DATETIME, [
				'notnull' => true,
			]);
			$table->addColumn('updated_at', Types::DATETIME, [
				'notnull' => true,
			]);
			$table->addColumn('deleted_at', Types::DATETIME, [
				'notnull' => false,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['proc_type_id'], 'tramita_req_ptype_idx');
			$table->addIndex(['current_stage_id'], 'tramita_req_stage_idx');
			$table->addIndex(['protocol_id'], 'tramita_req_proto_idx');
			$table->addIndex(['status'], 'tramita_req_status_idx');
			$table->addIndex(['requester_id'], 'tramita_req_requester_idx');
			$table->addIndex(['group_id'], 'tramita_req_group_idx');
			$table->addIndex(['priority', 'status'], 'tramita_req_priority_idx');
			$table->addIndex(['due_date'], 'tramita_req_due_idx');
			$table->addIndex(['deleted_at'], 'tramita_req_deleted_idx');
			$table->addIndex(['current_stage_id', 'sort_order'], 'tramita_req_sort_idx');
			$table->addIndex(['created_at'], 'tramita_req_created_idx');
		}

		// === tramita_form_responses ===
		if (!$schema->hasTable('tramita_form_responses')) {
			$table = $schema->createTable('tramita_form_responses');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('request_id', Types::BIGINT, [
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('template_id', Types::BIGINT, [
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('field_id', Types::BIGINT, [
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('value_text', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('value_int', Types::BIGINT, [
				'notnull' => false,
			]);
			$table->addColumn('value_decimal', Types::DECIMAL, [
				'notnull' => false,
				'precision' => 15,
				'scale' => 4,
			]);
			$table->addColumn('value_date', Types::DATETIME, [
				'notnull' => false,
			]);
			$table->addColumn('value_json', Types::JSON, [
				'notnull' => false,
			]);
			$table->addColumn('submitted_by', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('created_at', Types::DATETIME, [
				'notnull' => true,
			]);
			$table->addColumn('updated_at', Types::DATETIME, [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['request_id', 'template_id', 'field_id'], 'tramita_fresp_rft_uniq');
			$table->addIndex(['request_id'], 'tramita_fresp_req_idx');
			$table->addIndex(['template_id'], 'tramita_fresp_tpl_idx');
			$table->addIndex(['field_id'], 'tramita_fresp_field_idx');
		}

		return $schema;
	}
}

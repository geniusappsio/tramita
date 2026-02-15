<?php

declare(strict_types=1);

namespace OCA\Tramita\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Migration 1: Core tables
 * - tramita_licenses
 * - tramita_proc_types
 * - tramita_stages
 * - tramita_form_templates
 * - tramita_form_fields
 */
class Version0100Date20260214120000 extends SimpleMigrationStep {

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		// === tramita_licenses ===
		if (!$schema->hasTable('tramita_licenses')) {
			$table = $schema->createTable('tramita_licenses');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('license_key', Types::STRING, [
				'notnull' => true,
				'length' => 512,
			]);
			$table->addColumn('instance_id', Types::STRING, [
				'notnull' => true,
				'length' => 256,
			]);
			$table->addColumn('status', Types::STRING, [
				'notnull' => true,
				'length' => 32,
				'default' => 'trial',
			]);
			$table->addColumn('licensed_to', Types::STRING, [
				'notnull' => false,
				'length' => 256,
			]);
			$table->addColumn('valid_until', Types::DATETIME, [
				'notnull' => false,
			]);
			$table->addColumn('max_users', Types::INTEGER, [
				'notnull' => true,
				'default' => 0,
			]);
			$table->addColumn('features', Types::JSON, [
				'notnull' => false,
			]);
			$table->addColumn('last_check', Types::DATETIME, [
				'notnull' => false,
			]);
			$table->addColumn('created_at', Types::DATETIME, [
				'notnull' => true,
			]);
			$table->addColumn('updated_at', Types::DATETIME, [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['instance_id', 'license_key'], 'tramita_lic_instkey_uniq');
			$table->addIndex(['status'], 'tramita_lic_status_idx');
		}

		// === tramita_proc_types ===
		if (!$schema->hasTable('tramita_proc_types')) {
			$table = $schema->createTable('tramita_proc_types');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('name', Types::STRING, [
				'notnull' => true,
				'length' => 256,
			]);
			$table->addColumn('slug', Types::STRING, [
				'notnull' => true,
				'length' => 128,
			]);
			$table->addColumn('description', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('prefix', Types::STRING, [
				'notnull' => true,
				'length' => 16,
			]);
			$table->addColumn('color', Types::STRING, [
				'notnull' => false,
				'length' => 7,
			]);
			$table->addColumn('icon', Types::STRING, [
				'notnull' => false,
				'length' => 128,
			]);
			$table->addColumn('group_id', Types::STRING, [
				'notnull' => true,
				'length' => 256,
			]);
			$table->addColumn('is_active', Types::BOOLEAN, [
				'notnull' => true,
				'default' => true,
			]);
			$table->addColumn('sort_order', Types::INTEGER, [
				'notnull' => true,
				'default' => 0,
			]);
			$table->addColumn('settings', Types::JSON, [
				'notnull' => false,
			]);
			$table->addColumn('created_by', Types::STRING, [
				'notnull' => true,
				'length' => 64,
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
			$table->addUniqueIndex(['slug', 'group_id'], 'tramita_ptype_slug_grp_uniq');
			$table->addIndex(['group_id'], 'tramita_ptype_group_idx');
			$table->addIndex(['is_active'], 'tramita_ptype_active_idx');
			$table->addIndex(['deleted_at'], 'tramita_ptype_deleted_idx');
		}

		// === tramita_stages ===
		if (!$schema->hasTable('tramita_stages')) {
			$table = $schema->createTable('tramita_stages');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('proc_type_id', Types::BIGINT, [
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('name', Types::STRING, [
				'notnull' => true,
				'length' => 256,
			]);
			$table->addColumn('slug', Types::STRING, [
				'notnull' => true,
				'length' => 128,
			]);
			$table->addColumn('description', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('color', Types::STRING, [
				'notnull' => false,
				'length' => 7,
			]);
			$table->addColumn('sort_order', Types::INTEGER, [
				'notnull' => true,
				'default' => 0,
			]);
			$table->addColumn('is_initial', Types::BOOLEAN, [
				'notnull' => true,
				'default' => false,
			]);
			$table->addColumn('is_final', Types::BOOLEAN, [
				'notnull' => true,
				'default' => false,
			]);
			$table->addColumn('allowed_next', Types::JSON, [
				'notnull' => false,
			]);
			$table->addColumn('auto_assign', Types::JSON, [
				'notnull' => false,
			]);
			$table->addColumn('sla_hours', Types::INTEGER, [
				'notnull' => false,
			]);
			$table->addColumn('is_active', Types::BOOLEAN, [
				'notnull' => true,
				'default' => true,
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
			$table->addUniqueIndex(['proc_type_id', 'slug'], 'tramita_stage_slug_pt_uniq');
			$table->addIndex(['proc_type_id'], 'tramita_stage_ptype_idx');
			$table->addIndex(['proc_type_id', 'sort_order'], 'tramita_stage_order_idx');
			$table->addIndex(['deleted_at'], 'tramita_stage_deleted_idx');
		}

		// === tramita_form_templates ===
		if (!$schema->hasTable('tramita_form_templates')) {
			$table = $schema->createTable('tramita_form_templates');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('proc_type_id', Types::BIGINT, [
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('stage_id', Types::BIGINT, [
				'notnull' => false,
				'unsigned' => true,
			]);
			$table->addColumn('name', Types::STRING, [
				'notnull' => true,
				'length' => 256,
			]);
			$table->addColumn('description', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('version', Types::INTEGER, [
				'notnull' => true,
				'default' => 1,
			]);
			$table->addColumn('is_active', Types::BOOLEAN, [
				'notnull' => true,
				'default' => true,
			]);
			$table->addColumn('is_required', Types::BOOLEAN, [
				'notnull' => true,
				'default' => false,
			]);
			$table->addColumn('sort_order', Types::INTEGER, [
				'notnull' => true,
				'default' => 0,
			]);
			$table->addColumn('settings', Types::JSON, [
				'notnull' => false,
			]);
			$table->addColumn('created_by', Types::STRING, [
				'notnull' => true,
				'length' => 64,
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
			$table->addIndex(['proc_type_id'], 'tramita_ftpl_ptype_idx');
			$table->addIndex(['stage_id'], 'tramita_ftpl_stage_idx');
			$table->addIndex(['deleted_at'], 'tramita_ftpl_deleted_idx');
		}

		// === tramita_form_fields ===
		if (!$schema->hasTable('tramita_form_fields')) {
			$table = $schema->createTable('tramita_form_fields');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('template_id', Types::BIGINT, [
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('name', Types::STRING, [
				'notnull' => true,
				'length' => 128,
			]);
			$table->addColumn('label', Types::STRING, [
				'notnull' => true,
				'length' => 256,
			]);
			$table->addColumn('field_type', Types::STRING, [
				'notnull' => true,
				'length' => 32,
			]);
			$table->addColumn('placeholder', Types::STRING, [
				'notnull' => false,
				'length' => 256,
			]);
			$table->addColumn('help_text', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('default_value', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('is_required', Types::BOOLEAN, [
				'notnull' => true,
				'default' => false,
			]);
			$table->addColumn('is_readonly', Types::BOOLEAN, [
				'notnull' => true,
				'default' => false,
			]);
			$table->addColumn('is_hidden', Types::BOOLEAN, [
				'notnull' => true,
				'default' => false,
			]);
			$table->addColumn('validation', Types::JSON, [
				'notnull' => false,
			]);
			$table->addColumn('options', Types::JSON, [
				'notnull' => false,
			]);
			$table->addColumn('sort_order', Types::INTEGER, [
				'notnull' => true,
				'default' => 0,
			]);
			$table->addColumn('width', Types::STRING, [
				'notnull' => false,
				'length' => 16,
				'default' => 'full',
			]);
			$table->addColumn('conditional', Types::JSON, [
				'notnull' => false,
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
			$table->addUniqueIndex(['template_id', 'name'], 'tramita_ffield_name_tpl_uniq');
			$table->addIndex(['template_id'], 'tramita_ffield_tpl_idx');
			$table->addIndex(['template_id', 'sort_order'], 'tramita_ffield_order_idx');
			$table->addIndex(['deleted_at'], 'tramita_ffield_deleted_idx');
		}

		return $schema;
	}
}

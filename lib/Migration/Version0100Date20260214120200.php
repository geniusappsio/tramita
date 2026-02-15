<?php

declare(strict_types=1);

namespace OCA\Tramita\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Migration 3: Relationship and tracking tables
 * - tramita_labels
 * - tramita_request_labels
 * - tramita_assignments
 * - tramita_comments
 * - tramita_activity_log
 * - tramita_stage_transitions
 * - tramita_notifications
 * - tramita_notif_prefs
 */
class Version0100Date20260214120200 extends SimpleMigrationStep {

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		// === tramita_labels ===
		if (!$schema->hasTable('tramita_labels')) {
			$table = $schema->createTable('tramita_labels');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('name', Types::STRING, [
				'notnull' => true,
				'length' => 128,
			]);
			$table->addColumn('color', Types::STRING, [
				'notnull' => true,
				'length' => 7,
				'default' => '#808080',
			]);
			$table->addColumn('group_id', Types::STRING, [
				'notnull' => true,
				'length' => 256,
			]);
			$table->addColumn('proc_type_id', Types::BIGINT, [
				'notnull' => false,
				'unsigned' => true,
			]);
			$table->addColumn('sort_order', Types::INTEGER, [
				'notnull' => true,
				'default' => 0,
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
			$table->addIndex(['group_id'], 'tramita_label_group_idx');
			$table->addIndex(['proc_type_id'], 'tramita_label_ptype_idx');
			$table->addIndex(['deleted_at'], 'tramita_label_deleted_idx');
		}

		// === tramita_request_labels ===
		if (!$schema->hasTable('tramita_request_labels')) {
			$table = $schema->createTable('tramita_request_labels');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('request_id', Types::BIGINT, [
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('label_id', Types::BIGINT, [
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('created_at', Types::DATETIME, [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['request_id', 'label_id'], 'tramita_rlabel_req_lbl_uniq');
			$table->addIndex(['request_id'], 'tramita_rlabel_req_idx');
			$table->addIndex(['label_id'], 'tramita_rlabel_lbl_idx');
		}

		// === tramita_assignments ===
		if (!$schema->hasTable('tramita_assignments')) {
			$table = $schema->createTable('tramita_assignments');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('request_id', Types::BIGINT, [
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('user_id', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('role', Types::STRING, [
				'notnull' => true,
				'length' => 32,
				'default' => 'assigned',
			]);
			$table->addColumn('assigned_by', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('assigned_at', Types::DATETIME, [
				'notnull' => true,
			]);
			$table->addColumn('unassigned_at', Types::DATETIME, [
				'notnull' => false,
			]);
			$table->addColumn('is_active', Types::BOOLEAN, [
				'notnull' => true,
				'default' => true,
			]);
			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['request_id', 'user_id', 'role'], 'tramita_assign_req_usr_uniq');
			$table->addIndex(['request_id'], 'tramita_assign_req_idx');
			$table->addIndex(['user_id'], 'tramita_assign_user_idx');
			$table->addIndex(['is_active'], 'tramita_assign_active_idx');
		}

		// === tramita_comments ===
		if (!$schema->hasTable('tramita_comments')) {
			$table = $schema->createTable('tramita_comments');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('request_id', Types::BIGINT, [
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('parent_id', Types::BIGINT, [
				'notnull' => false,
				'unsigned' => true,
			]);
			$table->addColumn('user_id', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('content', Types::TEXT, [
				'notnull' => true,
			]);
			$table->addColumn('is_system', Types::BOOLEAN, [
				'notnull' => true,
				'default' => false,
			]);
			$table->addColumn('mentions', Types::JSON, [
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
			$table->addIndex(['request_id'], 'tramita_comment_req_idx');
			$table->addIndex(['user_id'], 'tramita_comment_user_idx');
			$table->addIndex(['parent_id'], 'tramita_comment_parent_idx');
			$table->addIndex(['created_at'], 'tramita_comment_created_idx');
			$table->addIndex(['deleted_at'], 'tramita_comment_deleted_idx');
		}

		// === tramita_activity_log ===
		if (!$schema->hasTable('tramita_activity_log')) {
			$table = $schema->createTable('tramita_activity_log');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('request_id', Types::BIGINT, [
				'notnull' => false,
				'unsigned' => true,
			]);
			$table->addColumn('user_id', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('action', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('entity_type', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('entity_id', Types::BIGINT, [
				'notnull' => false,
				'unsigned' => true,
			]);
			$table->addColumn('old_value', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('new_value', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('details', Types::JSON, [
				'notnull' => false,
			]);
			$table->addColumn('ip_address', Types::STRING, [
				'notnull' => false,
				'length' => 45,
			]);
			$table->addColumn('created_at', Types::DATETIME, [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['request_id'], 'tramita_actlog_req_idx');
			$table->addIndex(['user_id'], 'tramita_actlog_user_idx');
			$table->addIndex(['action'], 'tramita_actlog_action_idx');
			$table->addIndex(['entity_type', 'entity_id'], 'tramita_actlog_entity_idx');
			$table->addIndex(['created_at'], 'tramita_actlog_created_idx');
			$table->addIndex(['request_id', 'created_at'], 'tramita_actlog_req_created_idx');
		}

		// === tramita_stage_transitions ===
		if (!$schema->hasTable('tramita_stage_transitions')) {
			$table = $schema->createTable('tramita_stage_transitions');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('request_id', Types::BIGINT, [
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('from_stage_id', Types::BIGINT, [
				'notnull' => false,
				'unsigned' => true,
			]);
			$table->addColumn('to_stage_id', Types::BIGINT, [
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('user_id', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('comment', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('duration_secs', Types::BIGINT, [
				'notnull' => false,
			]);
			$table->addColumn('created_at', Types::DATETIME, [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['request_id'], 'tramita_strans_req_idx');
			$table->addIndex(['from_stage_id'], 'tramita_strans_from_idx');
			$table->addIndex(['to_stage_id'], 'tramita_strans_to_idx');
			$table->addIndex(['user_id'], 'tramita_strans_user_idx');
			$table->addIndex(['created_at'], 'tramita_strans_created_idx');
		}

		// === tramita_notifications ===
		if (!$schema->hasTable('tramita_notifications')) {
			$table = $schema->createTable('tramita_notifications');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('user_id', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('request_id', Types::BIGINT, [
				'notnull' => false,
				'unsigned' => true,
			]);
			$table->addColumn('type', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('title', Types::STRING, [
				'notnull' => true,
				'length' => 512,
			]);
			$table->addColumn('message', Types::TEXT, [
				'notnull' => false,
			]);
			$table->addColumn('link', Types::STRING, [
				'notnull' => false,
				'length' => 1024,
			]);
			$table->addColumn('is_read', Types::BOOLEAN, [
				'notnull' => true,
				'default' => false,
			]);
			$table->addColumn('read_at', Types::DATETIME, [
				'notnull' => false,
			]);
			$table->addColumn('created_at', Types::DATETIME, [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['user_id'], 'tramita_notif_user_idx');
			$table->addIndex(['request_id'], 'tramita_notif_req_idx');
			$table->addIndex(['user_id', 'is_read'], 'tramita_notif_read_idx');
			$table->addIndex(['type'], 'tramita_notif_type_idx');
			$table->addIndex(['created_at'], 'tramita_notif_created_idx');
		}

		// === tramita_notif_prefs ===
		if (!$schema->hasTable('tramita_notif_prefs')) {
			$table = $schema->createTable('tramita_notif_prefs');
			$table->addColumn('id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'unsigned' => true,
			]);
			$table->addColumn('user_id', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('event_type', Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('channel', Types::STRING, [
				'notnull' => true,
				'length' => 32,
				'default' => 'app',
			]);
			$table->addColumn('is_enabled', Types::BOOLEAN, [
				'notnull' => true,
				'default' => true,
			]);
			$table->addColumn('created_at', Types::DATETIME, [
				'notnull' => true,
			]);
			$table->addColumn('updated_at', Types::DATETIME, [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['user_id', 'event_type'], 'tramita_npref_user_evt_uniq');
			$table->addIndex(['user_id'], 'tramita_npref_user_idx');
		}

		return $schema;
	}
}

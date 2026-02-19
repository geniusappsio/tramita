<?php

declare(strict_types=1);

namespace OCA\Tramita\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Migration 4: Adicionar campo is_external em tramita_proc_types
 */
class Version0100Date20260219120000 extends SimpleMigrationStep {

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		$table = $schema->getTable('tramita_proc_types');

		if (!$table->hasColumn('is_external')) {
			$table->addColumn('is_external', Types::BOOLEAN, [
				'notnull' => true,
				'default' => false,
			]);
		}

		return $schema;
	}
}

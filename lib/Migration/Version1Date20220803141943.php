<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2022 Your name <your@email.com>
 *
 * @author Your name <your@email.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\LockPick\Migration;

use Closure;
use Doctrine\DBAL\Types\Types;
use OC\DB\SchemaWrapper;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version1Date20220803141943 extends SimpleMigrationStep {
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var SchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable("lockpick_traces")) {
			$table = $schema->createTable("lockpick_traces");
			$table->addColumn('trace_id', Types::BIGINT, [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 20,
			]);
			$table->addColumn('request_id', 'string', [
				'notnull' => true,
				'length' => 255,
			]);
			$table->addColumn('trace', 'text', [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['trace_id']);
			$table->addIndex(['request_id']);
		}

		return $schema;
	}
}

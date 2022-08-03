<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2022 Robin Appelman <robin@icewind.nl>
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\LockPick;

use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

class TraceStore {
	private IDBConnection $connection;

	public function __construct(IDBConnection $connection) {
		$this->connection = $connection;
	}

	public function store(string $request, array $trace): void {
		$query = $this->connection->getQueryBuilder();
		$query->insert('lockpick_traces')
			->values([
				'request_id' => $query->createNamedParameter($request),
				'trace' => $query->createNamedParameter(json_encode($trace))
			]);
		$query->executeStatement();
	}

	/**
	 * @return LockTrace[]|null
	 * @throws \OCP\DB\Exception
	 */
	public function last(): ?array {
		$query = $this->connection->getQueryBuilder();
		$query->select('trace')
			->from('lockpick_traces')
			->orderBy('trace_id', 'DESC')
			->setMaxResults(1);
		$trace = $query->executeQuery()->fetchOne();
		if ($trace) {
			$raw = json_decode($trace, true);
			return array_map(function(array $item) {
				return new LockTrace($item['type'], $item['trace']);
			}, $raw);
		} else {
			return null;
		}
	}

	/**
	 * @return array<int, string>
	 */
	public function all(): array {
		$query = $this->connection->getQueryBuilder();
		$query->select('trace_id', 'request_id')
			->from('lockpick_traces')
			->orderBy('trace_id', 'ASC');
		$rows = $query->executeQuery()->fetchAll();
		$keys = array_map(function(array $row) {
			return $row['trace_id'];
		}, $rows);
		$values = array_map(function(array $row) {
			return $row['request_id'];
		}, $rows);
		return array_combine($keys, $values);
	}

	/**
	 * @return LockTrace[]|null
	 * @throws \OCP\DB\Exception
	 */
	public function get(int $id): ?array {
		$query = $this->connection->getQueryBuilder();
		$query->select('trace')
			->from('lockpick_traces')
			->where($query->expr()->eq('trace_id', $query->createNamedParameter($id, IQueryBuilder::PARAM_INT)));
		$trace = $query->executeQuery()->fetchOne();
		if ($trace) {
			$raw = json_decode($trace, true);
			return array_map(function(array $item) {
				return new LockTrace($item['type'], $item['trace']);
			}, $raw);
		} else {
			return null;
		}
	}

	public function clear(): void {
		$query = $this->connection->getQueryBuilder();
		$query->delete('lockpick_traces');
		$query->executeStatement();
	}
}

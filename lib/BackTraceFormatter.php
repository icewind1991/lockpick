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


class BackTraceFormatter {
	private function filePrefix(array $backtrace): string {
		$files = array_map(function (array $item) {
			return $item['file'] ?? '';
		}, $backtrace);
		if (count($files) < 2) {
			return $files[0] ?? '';
		}
		$i = 0;
		while (isset($files[0][$i]) && array_reduce($files, function ($every, $item) use ($files, $i) {
				return $every && $item[$i] == $files[0][$i];
			}, true)) {
			$i++;
		}
		return substr($files[0], 0, $i);
	}

	public function format(array $backtrace, string $indent = ""): string {
		$prefixLength = strlen($this->filePrefix($backtrace));
		$backtrace = array_map(function ($trace) use ($prefixLength) {
			return [
				'line' => isset($trace['file']) ? (substr($trace['file'], $prefixLength) . ' ' . $trace['line']) : '--',
				'call' => isset($trace['class']) ? ($trace['class'] . $trace['type'] . $trace['function']) : $trace['function'],
			];
		}, $backtrace);
		$callLength = max(array_map(function ($item) {
			return strlen($item['call']);
		}, $backtrace));

		$output = "";
		foreach ($backtrace as $trace) {
			if ($output !== "") {
				$output .= "\n";
			}
			$output .= $indent . str_pad($trace['call'], $callLength) . ' - ' . $trace['line'];
		}

		return $output;
	}
}

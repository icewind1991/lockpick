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

use OCP\IRequest;
use OCP\Lock\ILockingProvider;
use OCP\Lock\LockedException;

class DebugLockingProvider implements ILockingProvider {
	private ILockingProvider $inner;
	private TraceStore $store;
	private IRequest $request;
	private array $openTraces = [];
	private array $pathMap = [];

	public function __construct(ILockingProvider $inner, IRequest $request, TraceStore $store) {
		$this->inner = $inner;
		$this->request = $request;
		$this->store = $store;
	}

	/**
	 * Normalize path to the "readable" form if possible
	 */
	private function getPath(string $path, string $readablePath = null): string {
		if ($readablePath) {
			$this->pathMap[$path] = $readablePath;
			return $readablePath;
		} elseif (isset($this->pathMap[$path])) {
			return $this->pathMap[$path];
		} else {
			return $path;
		}
	}

	private function openTrace(string $path, int $type): void {
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		$trace = $this->unwindTrace($trace);
		if (!isset($this->openTraces[$path])) {
			$this->openTraces[$path] = [];
		}
		$this->openTraces[$path][] = [
			'trace' => $trace,
			'type' => $type,
		];
	}

	/**
	 * Remove the bits from the backtrace that are from this debug handling
	 */
	private function unwindTrace(array $backtrace): array {
		while($backtrace[0]['class'] === self::class) {
			array_shift($backtrace);
		}
		return $backtrace;
	}

	private function closeTrace(string $path, int $type): void {
		// todo find a way to better link acquire/release pairs


		if (!isset($this->openTraces[$path])) {
			return;
		}

		foreach ($this->openTraces[$path] as $id => $trace) {
			if ($trace['type'] === $type) {
				unset($this->openTraces[$path][$id]);
				break;
			}
		}
		$this->openTraces[$path] = array_values($this->openTraces[$path]);
	}

	private function logConflict(string $path, int $type): void {
		$path = $this->getPath($path);

		// conflict comes from other request
		// todo store traces in memcache so we can debug cross-requests conflicts
		if (!isset($this->openTraces[$path])) {
			return;
		}

		$this->openTrace($path, $type);

		$this->store->store($this->request->getId(), $this->openTraces[$path]);
	}

	public function acquireLock(string $path, int $type, ?string $readablePath = null): void {
		try {
			$this->inner->acquireLock($path, $type, $readablePath);
			$this->openTrace($this->getPath($path, $readablePath), $type);
		} catch (LockedException $e) {
			$this->logConflict($path, $type);
			throw $e;
		}
	}

	public function changeLock(string $path, int $targetType): void {
		try {
			$this->inner->changeLock($path, $targetType);
			$this->closeTrace($this->getPath($path), 3 - $targetType);
			$this->openTrace($this->getPath($path), $targetType);
		} catch (LockedException $e) {
			$this->logConflict($path, $targetType);
			throw $e;
		}
	}

	public function releaseLock(string $path, int $type): void {
		$this->inner->releaseLock($path, $type);
		$this->closeTrace($this->getPath($path), $type);
	}

	public function releaseAll(): void {
		$this->inner->releaseAll();
		$this->openTraces = [];
	}

	public function isLocked(string $path, int $type): bool {
		return $this->inner->isLocked($path, $type);
	}
}

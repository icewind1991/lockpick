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

namespace OCA\LockPick\AppInfo;

use OC\Lock\MemcacheLockingProvider;
use OC\Lock\NoopLockingProvider;
use OCA\LockPick\DebugLockingProvider;
use OCA\LockPick\TraceStore;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\ICacheFactory;
use OCP\IConfig;
use OCP\IRequest;
use OCP\Lock\ILockingProvider;
use Psr\Container\ContainerInterface;

class Application extends App implements IBootstrap {
	public function __construct(array $urlParams = []) {
		parent::__construct('lockpick', $urlParams);

		// we are overly aggressive and rude in how we register our locking provider to ensure it gets picked up early
		\OC::$server->registerService(ILockingProvider::class, function (ContainerInterface $c) {
			$config = $c->get(IConfig::class);
			$ttl = $config->getSystemValue('filelocking.ttl', 3600);
			if ($config->getSystemValue('filelocking.enabled', true) or (defined('PHPUNIT_RUN') && PHPUNIT_RUN)) {
				/** @var \OC\Memcache\Factory $memcacheFactory */
				$memcacheFactory = $c->get(ICacheFactory::class);
				$memcache = $memcacheFactory->createLocking('lock');
				$inner = new MemcacheLockingProvider($memcache, $ttl);
				return new DebugLockingProvider($inner, $c->get(IRequest::class), $c->get(TraceStore::class));
			}
			return new NoopLockingProvider();
		});
	}

	public function register(IRegistrationContext $context): void {
		// noop
	}

	public function boot(IBootContext $context): void {
		// noop
	}
}

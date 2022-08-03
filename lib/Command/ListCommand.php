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

namespace OCA\LockPick\Command;

use OC\Core\Command\Base;
use OCA\LockPick\TraceStore;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListCommand extends Base {
	private TraceStore $store;

	public function __construct(TraceStore $store) {
		parent::__construct();

		$this->store = $store;
	}

	protected function configure() {
		$this
			->setName('lockpick:list')
			->setDescription('List stored conflict traces');
		parent::configure();
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$traces = $this->store->all();

		foreach ($traces as $id => $request) {
			$output->writeln("$id - $request");
		}
		return 0;
	}
}


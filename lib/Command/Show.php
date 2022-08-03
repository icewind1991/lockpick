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
use OCA\LockPick\BackTraceFormatter;
use OCA\LockPick\TraceStore;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Show extends Base {
	private TraceStore $store;
	private BackTraceFormatter $traceFormatter;

	public function __construct(TraceStore $store, BackTraceFormatter $traceFormatter) {
		parent::__construct();

		$this->store = $store;
		$this->traceFormatter = $traceFormatter;
	}

	protected function configure() {
		$this
			->setName('lockpick:show')
			->setDescription('Show the latest detected lock conflict')
			->addArgument('trace_id', InputArgument::OPTIONAL, "Id of the trace to show, default to the latest trace");
		parent::configure();
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$id = $input->getArgument('trace_id');
		if ($id) {
			$traces = $this->store->get((int)$id);

			if (!is_array($traces)) {
				$output->writeln("Trace not found");
				return 1;
			}
		} else {
			$traces = $this->store->last();

			if (!is_array($traces)) {
				$output->writeln("No conflict stored");
				return 1;
			}
		}

		$output->writeln("<info>Conflict detected between " . count($traces) . " locks</info>");
		$first = true;
		foreach ($traces as $item) {
			if (!$first) {
				$output->writeln("");
			}
			$first = false;
			$output->writeln($item->getTypeName() . " lock from");
			$output->writeln($this->traceFormatter->format($item->getBacktrace(), "  "));
		}

		return 0;
	}
}

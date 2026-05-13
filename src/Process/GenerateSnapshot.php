<?php

namespace BlueSpice\ExtendedStatistics\Process;

use BlueSpice\ExtendedStatistics\SnapshotGenerator;
use MWStake\MediaWiki\Component\ProcessManager\IProcessStep;

class GenerateSnapshot implements IProcessStep {

	/**
	 * @param SnapshotGenerator $generator
	 * @param string $interval
	 */
	public function __construct(
		private readonly SnapshotGenerator $generator,
		private readonly string $interval
	) {
	}

	/**
	 * @param array $data
	 * @return array
	 */
	public function execute( $data = [] ): array {
		return $this->generator->generateSnapshot( $this->interval );
	}
}

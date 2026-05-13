<?php

use BlueSpice\ExtendedStatistics\SnapshotDate;
use BlueSpice\ExtendedStatistics\SnapshotGenerator;
use MediaWiki\Maintenance\Maintenance;

require_once dirname( dirname( dirname( __DIR__ ) ) ) . '/maintenance/Maintenance.php';

class GenerateSnapshot extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->requireExtension( 'BlueSpiceExtendedStatistics' );
		$this->addOption(
			'interval',
			'Interval for which to generate snapshot (day, week, month or year)',
			false
		);
		$this->addOption(
			'regenerate',
			'If set, snapshots will be regenerated if already existing',
			false
		);
		$this->addOption(
			'skip-timecheck',
			'if not set, it will show system time, letting user check if it matches reality'
		);
	}

	public function execute() {
		if ( !$this->hasOption( 'skip-timecheck' ) ) {
			$this->timecheck();
		}
		/** @var SnapshotGenerator $generator */
		$generator = $this->getServiceContainer()->getService( 'BlueSpice.ExtendedStatistics.SnapshotGenerator' );
		$start = microtime( true );

		$interval = $this->getOption( 'interval', 'day' );
		$regenerate = $this->getOption( 'regenerate', false );
		$res = $generator->generateSnapshot( $interval, $regenerate );
		foreach ( $res['providers'] as $providerKey => $providerRes ) {
			$this->output( "Provider: $providerKey..." );
			if ( $providerRes ) {
				$this->output( "Success\n" );
			} else {
				$this->output( "Failed\n" );
			}

		}
		$end = microtime( true );
		$this->output( "Complete! Took:" . ( round( $end - $start, 2 ) ) . "\n" );
	}

	/**
	 * @return SnapshotDate
	 */
	private function getYesterday() {
		$date = new SnapshotDate();
		return $date->sub( new DateInterval( 'P1D' ) );
	}

	private function timecheck() {
		$date = $this->getYesterday();
		$this->output(
			"Yesterday: " . $date->getFloor()->mwTimestamp() . '-' .
			$date->getCeiling()->mwTimestamp() . "\n"
		);
		$this->output(
			"If this time does not match yesterday 00:00:00 to yesterday 23:59:59 abort this...\n"
		);
		$this->countDown( 9 );
	}
}

$maintClass = GenerateSnapshot::class;
require_once RUN_MAINTENANCE_IF_MAIN;

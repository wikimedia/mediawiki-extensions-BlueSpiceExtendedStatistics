<?php

require_once dirname( dirname( dirname( __DIR__ ) ) ) . '/maintenance/Maintenance.php';

use BlueSpice\Services;
use BlueSpice\Timestamp;
use BlueSpice\ExtendedStatistics\Entity\Snapshot;

class StatisticsSnapshotCreation extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->requireExtension( 'BlueSpiceExtendedStatistics' );
	}

	public function execute() {
		$ts = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
		$yesterday = DateInterval::createFromDateString( 'yesterday' );
		$factory = Services::getInstance()->getService(
			'BSExtendedStatisticsSnapshotFactory'
		);
		/** @var Snapshot $snapshot */
		$snapshot = $factory->newFromTimestamp(
			new Timestamp( $ts->add( $yesterday ) )
		);

		/** @var Status $status */
		$status = $snapshot->save(
			Services::getInstance()->getService( 'BSUtilityFactory' )->getMaintenanceUser()->getUser()
		);
		if ( !$status->isOK() ) {
			$this->error( $status->getMessage( false, false, 'en' ) );
		}
	}
}

$maintClass = StatisticsSnapshotCreation::class;
require_once RUN_MAINTENANCE_IF_MAIN;

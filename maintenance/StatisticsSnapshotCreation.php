<?php

require_once dirname( dirname( dirname( __DIR__ ) ) ) . '/maintenance/Maintenance.php';

use BlueSpice\ExtendedStatistics\Entity\Collection;
use BlueSpice\ExtendedStatistics\Entity\Snapshot;
use BlueSpice\Timestamp;
use MediaWiki\MediaWikiServices;

class StatisticsSnapshotCreation extends Maintenance {

	public function __construct() {
		parent::__construct();
		$this->requireExtension( 'BlueSpiceExtendedStatistics' );
	}

	public function execute() {
		$ts = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
		$yesterday = DateInterval::createFromDateString( 'yesterday' );
		$factory = MediaWikiServices::getInstance()->getService(
			'BSExtendedStatisticsSnapshotFactory'
		);
		/** @var Snapshot $snapshot */
		$snapshot = $factory->newFromTimestamp(
			new Timestamp( $ts->add( $yesterday ) )
		);
		if ( !$snapshot ) {
			$this->error( 'snapshot could not be created from Timestamp' );
			return;
		}

		$type = null;
		$GLOBALS['wgHooks']['BSEntitySetValuesByObject'][] = function ( $entity ) use( &$type ) {
			if ( !$entity instanceof Collection ) {
				return;
			}
			if ( $entity->get( $entity::ATTR_TYPE, '' ) !== $type ) {
				$type = $entity->get( $entity::ATTR_TYPE, '' );
				$this->output( "\n$type: " );
			}
			$this->output( "." );
		};
		/** @var Status $status */
		$status = $snapshot->save(
			MediaWikiServices::getInstance()->getService( 'BSUtilityFactory' )
				->getMaintenanceUser()->getUser()
		);
		if ( !$status->isOK() ) {
			$this->error( $status->getMessage( false, false, 'en' ) );
		}
		$this->output( "\nOK" );
	}
}

$maintClass = StatisticsSnapshotCreation::class;
require_once RUN_MAINTENANCE_IF_MAIN;

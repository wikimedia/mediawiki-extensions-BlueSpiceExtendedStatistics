<?php

namespace BlueSpice\ExtendedStatistics\RunJobsTriggerHandler;

use DateTime;
use DateTimeZone;
use DateInterval;
use Config;
use Status;
use Wikimedia\Rdbms\LoadBalancer;
use BlueSpice\INotifier;
use BlueSpice\Services;
use BlueSpice\Timestamp;
use BlueSpice\UtilityFactory;
use BlueSpice\RunJobsTriggerHandler;
use BlueSpice\RunJobsTriggerHandler\Interval;
use BlueSpice\RunJobsTriggerHandler\Interval\OnceADay;
use BlueSpice\ExtendedStatistics\SnapshotFactory;

class CreateDailySnapshot extends RunJobsTriggerHandler {

	/**
	 *
	 * @var SnapshotFactory
	 */
	protected $snapshotFactory = null;

	/**
	 *
	 * @var UtilityFactory
	 */
	protected $util = null;

	/**
	 * @param Config $config
	 * @param LoadBalancer $loadBalancer
	 * @param INotifier $notifier
	 * @param SnapshotFactory $snapshotFactory
	 * @param UtilityFactory $util
	 */
	public function __construct( $config, $loadBalancer, $notifier,
		SnapshotFactory $snapshotFactory, UtilityFactory $util ) {
		parent::__construct( $config, $loadBalancer, $notifier );
		$this->snapshotFactory = $snapshotFactory;
		$this->util = $util;
	}

	/**
	 * @param Config $config
	 * @param LoadBalancer $loadBalancer
	 * @param INotifier $notifier
	 * @param SnapshotFactory|null $snapshotFactory
	 * @param UtilityFactory|null $util
	 * @return IRunJobsTriggerHandler
	 */
	public static function factory( $config, $loadBalancer, $notifier,
		SnapshotFactory $snapshotFactory = null, UtilityFactory $util = null ) {
		if ( !$snapshotFactory ) {
			$snapshotFactory = Services::getInstance()->getService(
				'BSExtendedStatisticsSnapshotFactory'
			);
		}
		if ( !$util ) {
			$util = Services::getInstance()->getBSUtilityFactory();
		}
		return new static(
			$config,
			$loadBalancer,
			$notifier,
			$snapshotFactory,
			$util
		);
	}

	/**
	 *
	 * @return Status
	 */
	protected function doRun() {
		$status = Status::newGood();
		$ts = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
		$yesterday = DateInterval::createFromDateString( 'yesterday' );
		$snapshot = $this->snapshotFactory->newFromTimestamp(
			new Timestamp( $ts->add( $yesterday ) )
		);
		if ( !$snapshot ) {
			$status->fatal( 'snapshot could not be created from Timestamp' );
			return $status;
		}
		$snapshot->setUnsavedChanges();
		$status->merge(
			$snapshot->save( $this->util->getMaintenanceUser()->getUser() ),
			true
		);
		return $status;
	}

	/**
	 *
	 * @return Interval
	 */
	public function getInterval() {
		return new OnceADay();
	}

}

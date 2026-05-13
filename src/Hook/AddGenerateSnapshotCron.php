<?php

namespace BlueSpice\ExtendedStatistics\Hook;

use BlueSpice\ExtendedStatistics\Process\GenerateSnapshot;
use BlueSpice\ExtendedStatistics\Snapshot;
use MediaWiki\Hook\SetupAfterCacheHook;
use MediaWiki\MediaWikiServices;
use MWStake\MediaWiki\Component\ProcessManager\ManagedProcess;
use MWStake\MediaWiki\Component\WikiCron\WikiCronManager;

class AddGenerateSnapshotCron implements SetupAfterCacheHook {

	public function onSetupAfterCache() {
		if ( defined( 'MW_PHPUNIT_TEST' ) || defined( 'MW_QUIBBLE_CI' ) ) {
			return;
		}
		/** @var WikiCronManager $cronManager */
		$cronManager = MediaWikiServices::getInstance()->getService( 'MWStake.WikiCronManager' );
		$cronManager->registerCron( 'bs-extendedstatistics-generate-snapshot-day', '9 0 * * *', new ManagedProcess( [
			'export' => [
				'class' => GenerateSnapshot::class,
				'services' => [ 'BlueSpice.ExtendedStatistics.SnapshotGenerator' ],
				'args' => [ Snapshot::INTERVAL_DAY ],
			]
		] ) );

		$cronManager->registerCron( 'bs-extendedstatistics-generate-snapshot-week', '9 0 * * 0', new ManagedProcess( [
			'export' => [
				'class' => GenerateSnapshot::class,
				'services' => [ 'BlueSpice.ExtendedStatistics.SnapshotGenerator' ],
				'args' => [ Snapshot::INTERVAL_WEEK ],
			]
		] ) );

		$cronManager->registerCron( 'bs-extendedstatistics-generate-snapshot-month', '9 0 1 * *', new ManagedProcess( [
			'export' => [
				'class' => GenerateSnapshot::class,
				'services' => [ 'BlueSpice.ExtendedStatistics.SnapshotGenerator' ],
				'args' => [ Snapshot::INTERVAL_MONTH ],
			]
		] ) );

		$cronManager->registerCron( 'bs-extendedstatistics-generate-snapshot-year', '9 0 1 1 *', new ManagedProcess( [
			'export' => [
				'class' => GenerateSnapshot::class,
				'services' => [ 'BlueSpice.ExtendedStatistics.SnapshotGenerator' ],
				'args' => [ Snapshot::INTERVAL_YEAR ],
			]
		] ) );
	}
}

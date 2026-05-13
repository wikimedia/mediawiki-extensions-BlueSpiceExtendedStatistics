<?php

namespace BlueSpice\ExtendedStatistics;

use DateInterval;

class SnapshotGenerator {

	/**
	 * @param AttributeRegistryFactory $providerFactory
	 * @param ISnapshotStore $snapshotStore
	 */
	public function __construct(
		private readonly AttributeRegistryFactory $providerFactory,
		private readonly ISnapshotStore $snapshotStore
	) {
	}

	/**
	 * @param string $interval
	 * @param bool $regenerate
	 * @return array
	 */
	public function generateSnapshot( string $interval = Snapshot::INTERVAL_DAY, bool $regenerate = false ): array {
		if ( $interval === Snapshot::INTERVAL_DAY ) {
			return [
				'interval' => 'day',
				'providers' => $this->generateForDate( $this->getYesterday(), $regenerate ),
			];
		} else {
			return [
				'interval' => $interval,
				'providers' => $this->aggregate( $interval ),
			];
		}
	}

	/**
	 * @return SnapshotDate
	 */
	private function getYesterday() {
		$date = new SnapshotDate();
		return $date->sub( new DateInterval( 'P1D' ) );
	}

	/**
	 * @param SnapshotDate $date
	 * @param bool $regenerate
	 * @return array
	 */
	private function generateForDate( SnapshotDate $date, bool $regenerate ): array {
		$providers = [];
		/**
		 * @var string $key
		 * @var ISnapshotProvider $provider
		 */
		foreach ( $this->providerFactory->getAll() as $key => $provider ) {
			if ( $this->snapshotStore->hasSnapshot( $date, $key ) && !$regenerate ) {
				continue;
			}

			$snapshot = $provider->generateSnapshot( $date );
			$status = $this->snapshotStore->persistSnapshot( $snapshot );
			$secondaryData = $provider->getSecondaryData( $snapshot );
			if ( is_array( $secondaryData ) ) {
				$this->snapshotStore->persistSecondaryData( $snapshot, $secondaryData );
			}

			$providers[$key] = $status;
		}
		return $providers;
	}

	/**
	 * @param string $interval
	 * @return array
	 */
	private function aggregate( string $interval ): array {
		$range = null;
		switch ( $interval ) {
			case Snapshot::INTERVAL_WEEK:
				$range = SnapshotDateRange::newLastWeek();
				break;
			case Snapshot::INTERVAL_MONTH:
				$range = SnapshotDateRange::newLastMonth();
				break;
			case Snapshot::INTERVAL_YEAR:
				$range = SnapshotDateRange::newLastYear();
				break;
		}

		$providers = [];
		/**
		 * @var string $key
		 * @var ISnapshotProvider $provider
		 */
		foreach ( $this->providerFactory->getAll() as $key => $provider ) {
			$snapshots = $this->snapshotStore->getSnapshotForRange( $range, $key );
			if ( empty( $snapshots ) ) {
				continue;
			}
			$aggregated = $provider->aggregate( $snapshots, $interval, $range->getFrom() );
			$providers[$key] = $this->snapshotStore->persistSnapshot( $aggregated );
		}

		return $providers;
	}

}

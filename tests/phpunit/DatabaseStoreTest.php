<?php

namespace BlueSpice\ExtendedStatistics\Tests;

use BlueSpice\ExtendedStatistics\ISnapshotStore;
use BlueSpice\ExtendedStatistics\Snapshot;
use BlueSpice\ExtendedStatistics\SnapshotDate;
use BlueSpice\ExtendedStatistics\SnapshotDateRange;
use BlueSpice\ExtendedStatistics\SnapshotFactory;
use BlueSpice\ExtendedStatistics\SnapshotStore\DatabaseStore;
use PHPUnit\Framework\TestCase;
use Wikimedia\Rdbms\LoadBalancer;

class DatabaseStoreTest extends TestCase {
	/** @var IDatabase */
	private IDatabase $dbMock;

	/** @var ISnapshotStore */
	private ISnapshotStore $snapshotStore;

	public function setUp(): void {
		parent::setUp();

		$this->dbMock = $this->createMock( IDatabase::class );
		$loadBalancerMock = $this->createMock( LoadBalancer::class );
		$loadBalancerMock->method( 'getConnection' )->willReturn( $this->dbMock );
		$this->snapshotStore = new DatabaseStore( $loadBalancerMock, new SnapshotFactory() );
	}

	/**
	 * @covers \BlueSpice\ExtendedStatistics\SnapshotStore\DatabaseStore::getSnapshotForRange
	 * @return void
	 */
	public function testGetSnapshotForRangeQuery(): void {
		$conds = [
			"ess_timestamp >= mockTimestamp AND ess_timestamp <= mockTimestamp",
			"ess_type = mockQuotes",
			"ess_interval = mockQuotes"
		];
		$this->dbMock->method( 'timestamp' )->willReturn( 'mockTimestamp' );
		$this->dbMock->method( 'addQuotes' )->willReturn( 'mockQuotes' );
		$this->dbMock->expects( $this->once() )->method( 'select' )->with(
			DatabaseStore::TABLE,
			DatabaseStore::FIELDS,
			$conds
		)->willReturn( [] );
		$snapshotDateRange = new SnapshotDateRange( new SnapshotDate(), new SnapshotDate() );
		$this->snapshotStore->getSnapshotForRange( $snapshotDateRange );
	}

	/**
	 * @covers \BlueSpice\ExtendedStatistics\SnapshotStore\DatabaseStore::hasSnapshot
	 * @return void
	 * @throws Exception
	 */
	public function testHasSnapshot(): void {
		$this->dbMock->expects( $this->once() )->method( 'select' )->willReturn( [
			$this->mockDatabaseSnapshot(
				'a',
				1
			)
		] );
		$this->assertTrue( $this->snapshotStore->hasSnapshot( new SnapshotDate(), 'type' ) );
	}

	/**
	 * @covers \BlueSpice\ExtendedStatistics\SnapshotStore\DatabaseStore::hasSnapshot
	 * @return void
	 * @throws Exception
	 */
	public function testHasNoSnapshot(): void {
		$this->dbMock->expects( $this->once() )->method( 'select' )->willReturn( [] );
		$this->assertFalse( $this->snapshotStore->hasSnapshot( new SnapshotDate(), 'type' ) );
	}

	/**
	 * @param string $title
	 * @param int $hits
	 *
	 * @return stdClass
	 */
	private function mockDatabaseSnapshot( string $title, int $hits ): stdClass {
		$snapshotMock = $this->createMock( stdClass::class );
		$snapshotMock->ess_timestamp = 0;
		$snapshotMock->ess_data = json_encode( [] );
		$snapshotMock->ess_type = 'type';
		$snapshotMock->ess_interval = Snapshot::INTERVAL_DAY;

		return $snapshotMock;
	}
}

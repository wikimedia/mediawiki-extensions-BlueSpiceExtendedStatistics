<?php

namespace BlueSpice\ExtendedStatistics;

use Config;
use BlueSpice\Services;
use BlueSpice\EntityFactory;
use BlueSpice\Data\RecordSet;
use BlueSpice\Data\IRecord;
use BlueSpice\ExtendedStatistics\Entity\Collection;
use BlueSpice\ExtendedStatistics\Entity\Snapshot;

abstract class DataCollector implements IDataCollector {

	/**
	 *
	 * @var string
	 */
	protected $type = '';

	/**
	 *
	 * @var Snapshot
	 */
	protected $snapshot = null;

	/**
	 *
	 * @var EntityFactory
	 */
	protected $factory = null;

	/**
	 *
	 * @var Config
	 */
	protected $config = null;

	/**
	 *
	 * @param string $type
	 * @param Snapshot $snapshot
	 * @param Config $config
	 * @param EntityFactory $factory
	 */
	protected function __construct( $type, Snapshot $snapshot, Config $config,
		EntityFactory $factory ) {
		$this->type = $type;
		$this->snapshot = $snapshot;
		$this->factory = $factory;
		$this->config = $config;
	}

	/**
	 *
	 * @param string $type
	 * @param Services $services
	 * @param Snapshot $snapshot
	 * @param Config|null $config
	 * @param EntityFactory|null $factory
	 * @return DataCollector
	 */
	public static function factory( $type, Services $services, Snapshot $snapshot,
		Config $config = null, EntityFactory $factory = null ) {
		if ( !$config ) {
			$config = $snapshot->getConfig();
		}
		if ( !$factory ) {
			$factory = $services->getService( 'BSEntityFactory' );
		}
		return new static( $type, $snapshot, $config, $factory );
	}

	/**
	 * @return Collection[]|false
	 */
	public function collect() {
		$collection = [];
		if ( $this->skipProcessing() ) {
			return [];
		}
		try {
			$recordSet = $this->doCollect();
		} catch ( Exception $e ) {
			return false;
		}
		foreach ( $recordSet->getRecords() as $record ) {
			$entity = $this->makeCollection( $record );
			if ( !$entity ) {
				continue;
			}
			$collection[] = $entity;
		}
		return $collection;
	}

	/**
	 *
	 * @param IRecord $record
	 * @return Collection|null
	 */
	protected function makeCollection( IRecord $record ) {
		$entity = $this->factory->newFromObject( $this->map( $record ) );
		if ( !$entity instanceof Collection ) {
			return null;
		}
		return $entity;
	}

	/**
	 *
	 * @param IRecord $record
	 * @return \stdClass
	 */
	abstract protected function map( IRecord $record );

	/**
	 *
	 * @return bool
	 */
	protected function skipProcessing() {
		return false;
	}

	/**
	 * @return RecordSet
	 */
	abstract protected function doCollect();

}

<?php

namespace BlueSpice\ExtendedStatistics\DataCollector\StoreSourced;

use Config;
use BlueSpice\Services;
use BlueSpice\Data\IStore;
use BlueSpice\EntityFactory;
use BlueSpice\ExtendedStatistics\SnapshotFactory;
use BlueSpice\ExtendedStatistics\Entity\Snapshot;

abstract class NamespaceCollector extends SnapshotDiffCollector {

	/**
	 *
	 * @var array
	 */
	protected $namespaces = null;

	/**
	 * Helper method
	 *
	 * @param Snapshot $snapshot
	 * @param Services $services
	 * @return array
	 */
	public static function getNamespaces( Snapshot $snapshot, Services $services ) {
		$version = $snapshot->getConfig()->get( 'Version' );
		if ( false && version_compare( $version, '1.34', '>=' ) ) {
			$namespaces = $services->getNamespaceInfo()->getContentNamespaces();
		} else {
			$namespaces = Services::getInstance()->getNamespaceInfo()->getCanonicalNamespaces();
		}
		foreach ( $namespaces as $idx => $canonical ) {
			if ( $idx >= 0 ) {
				continue;
			}
			unset( $namespaces[$idx] );
		}
		return $namespaces;
	}

	/**
	 *
	 * @param string $type
	 * @param Snapshot $snapshot
	 * @param Config $config
	 * @param EntityFactory $factory
	 * @param IStore $store
	 * @param SnapshotFactory $snapshotFactory
	 * @param array $namespaces
	 */
	protected function __construct( $type, Snapshot $snapshot, Config $config,
		EntityFactory $factory, IStore $store, SnapshotFactory $snapshotFactory,
		array $namespaces ) {
		parent::__construct( $type, $snapshot, $config, $factory, $store, $snapshotFactory );
		$this->namespaces = $namespaces;
	}
}

<?php

namespace BlueSpice\ExtendedStatistics\Special;

use BlueSpice\ExtensionAttributeBasedRegistry;
use BlueSpice\Services;
use BlueSpice\Special\ExtJSBase;
use BlueSpice\ExtendedStatistics\EntityConfig\Collection;

class AggregatedStatistic extends ExtJSBase {
	public function __construct() {
		parent::__construct(
			'AggregatedStatistic',
			'extendedstatistics-viewspecialpage-aggregated'
		);
	}

	/**
	 *
	 * @return string
	 */
	protected function getId() {
		return 'bs-extendedstatistics-special-snapshotstatistics';
	}

	/**
	 *
	 * @return array
	 */
	protected function getModules() {
		$modules = [ "ext.bluespice.snapshotstatistics" ];
		foreach ( $this->getCollectionConfigs() as $config ) {
			$modules = array_merge( $modules, $config->get( 'Modules' ) );
		}
		return array_unique( array_values( $modules ) );
	}

	/**
	 * @return array [ $name => $value ]
	 */
	protected function getJSVars() {
		$configs = [];
		foreach ( $this->getCollectionConfigs() as $config ) {
			$configs[$config->getType()] = $config->jsonSerialize();
		}
		return [ 'bsgExtendedStatisticsCollectionConfigs' => $configs ];
	}

	/**
	 *
	 * @return Collection[]
	 */
	protected function getCollectionConfigs() {
		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceFoundationEntityRegistry'
		);
		$configFactory = Services::getInstance()->getBSEntityConfigFactory();
		$configs = [];
		foreach ( $registry->getAllKeys() as $type ) {
			$config = $configFactory->newFromType( $type );
			if ( !$config ) {
				continue;
			}
			if ( !$config->get( 'IsCollection' ) ) {
				continue;
			}
			$configs[] = $config;
		}
		return $configs;
	}

}
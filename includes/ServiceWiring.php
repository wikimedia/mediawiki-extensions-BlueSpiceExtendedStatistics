<?php

use MediaWiki\MediaWikiServices;
use BlueSpice\ExtensionAttributeBasedRegistry;
use BlueSpice\ExtendedStatistics\SnapshotFactory;
use BlueSpice\ExtendedStatistics\DataCollectorFactory;

return [

	'BSExtendedStatisticsDataCollectorFactory' => function ( MediaWikiServices $services ) {
		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceExtendedStatisticsSnapshotDataCollectorRegistry'
		);
		return new DataCollectorFactory(
			$registry,
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},

	'BSExtendedStatisticsSnapshotFactory' => function ( MediaWikiServices $services ) {
		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceFoundationEntityRegistry'
		);
		return new SnapshotFactory(
			$registry,
			$services->getService( 'BSEntityConfigFactory' ),
			$services->getConfigFactory()->makeConfig( 'bsg' )
		);
	},
];

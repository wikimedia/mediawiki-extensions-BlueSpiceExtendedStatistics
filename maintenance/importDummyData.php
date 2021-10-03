<?php

use BlueSpice\ExtendedStatistics\ISnapshotProvider;
use BlueSpice\ExtendedStatistics\Snapshot;
use BlueSpice\ExtendedStatistics\SnapshotDate;
use MediaWiki\MediaWikiServices;

require_once dirname( dirname( dirname( __DIR__ ) ) ) . '/maintenance/Maintenance.php';

class ImportDummyData extends Maintenance {
	/** @var \BlueSpice\ExtendedStatistics\AttributeRegistryFactory */
	private $providerFactory;
	/** @var \BlueSpice\ExtendedStatistics\ISnapshotStore */
	private $snapshotStore;
	/** @var NamespaceInfo */
	private $namespaceInfo;

	private $collection = [
		'week' => [],
		'month' => [],
		'year' => []
	];

	private $terms = [
		'income tax', 'quickbooks', 'account', 'accountant', 'cma', 'tax return',
		'payroll', 'bookkeeping', 'chartered accountant', 'accountant salary'
	];

	public function __construct() {
		parent::__construct();
		$this->requireExtension( 'BlueSpiceExtendedStatistics' );
		$this->addOption( 'category', 'Categories to use, comma-separated', true );
		$this->addOption( 'page', 'Pages to use, comma-separated', true );
		$this->addOption( 'namespace', 'Namespaces to use, comma-separated', true );
		$this->addOption( 'user', 'Users to use, comma-separated', true );
		$this->addOption( 'days', 'Number of days to mock', false, true );
	}

	public function execute() {
		$this->setServices();

		$namespaces = array_map( function ( $ns ) {
			$ns = (int)$ns;
			if ( $ns === 0 ) {
				return '-';
			}
			return $this->namespaceInfo->getCanonicalName( $ns );
		}, explode( ',', $this->getOption( 'namespace' ) ) );
		$this->mOptions['namespace'] = implode( ',', $namespaces );
		$this->mOptions['term'] = implode( ',', $this->terms );

		foreach ( $this->providerFactory->getAll() as $key => $provider ) {
			$this->processProvider( $key, $provider );
		}
	}

	private function processProvider( $key, ISnapshotProvider $provider ) {
		$file = __DIR__ . '/../doc/snapshotData/' . $key . '.json';
		if ( !file_exists( $file ) ) {
			$this->output( "File $file does not exist!\n" );
			return;
		}
		$template = json_decode( file_get_contents( $file ), 1 );
		if ( !$template ) {
			$this->output( "Template for $key not readable!\n" );
			return;
		}

		$days = (int)$this->getOption( 'days', 365 );
		$today = new DateTime( 'now' );
		$date = ( clone $today )->sub( new DateInterval( "P{$days}D" ) );
		while ( $date->format( 'Ymd' ) !== $today->format( 'Ymd' ) ) {
			$date->add( new DateInterval( "P1D" ) );
			if ( (int)$date->format( 'N' ) === 1 ) {
				$this->insertAggregate(
					$provider, Snapshot::INTERVAL_WEEK,
					( clone $date )->sub( new DateInterval( 'P1W' ) ), Snapshot::INTERVAL_MONTH
				);
			}
			if ( (int)$date->format( 'j' ) === 1 ) {
				$this->insertAggregate(
					$provider, Snapshot::INTERVAL_MONTH,
					( clone $date )->sub( new DateInterval( 'P1M' ) ), Snapshot::INTERVAL_YEAR
				);
			}
			if ( $date->format( 'jn' ) === "11" ) {
				$this->insertAggregate(
					$provider, Snapshot::INTERVAL_YEAR,
					( clone $date )->sub( new DateInterval( 'P1Y' ) )
				);
			}
			$snapshotDate = SnapshotDate::newFromFormat( $date->format( 'Ymd' ), 'Ymd' );
			$snapshot = new Snapshot(
				$snapshotDate, $key, $this->processTemplate( $template ), Snapshot::INTERVAL_DAY
			);
			$this->snapshotStore->persistSnapshot( $snapshot );
			if ( !isset( $this->collection[Snapshot::INTERVAL_WEEK][$key] ) ) {
				$this->collection[Snapshot::INTERVAL_WEEK][$key] = [];
			}
			$this->collection[Snapshot::INTERVAL_WEEK][$key][] = $snapshot;
		}
	}

	private function processTemplate( $template ) {
		return $this->processValue( $template['members'] );
	}

	private function processValue( $value ) {
		if ( $this->isRandInt( $value ) ) {
			return $this->randInt( $value );
		}
		$data = [];
		if ( is_array( $value ) ) {
			if ( isset( $value['key'] ) ) {
				$parsed = $this->parseKey( $value['key'] );
				if ( is_string( $parsed ) ) {
					$data[$parsed] = $this->processValue( $value['value'] );
				}
				if ( is_array( $parsed ) ) {
					foreach ( $parsed as $key ) {
						$data[$key] = $this->processValue( $value['value' ] );
					}
				}
			} else {
				foreach ( $value as $k => $v ) {
					$data[$k] = $this->processValue( $v );
				}
			}
		}

		return $data;
	}

	/**
	 * @param string $value
	 * @return bool
	 */
	private function isRandInt( $value ) {
		return is_string( $value ) && strpos( $value, '{{randint' ) === 0;
	}

	/**
	 * @param string $value
	 * @return int
	 */
	private function randInt( $value ) {
		$value = trim( $value, '{}' );
		$bits = explode( '|', $value );
		if ( count( $bits ) === 1 ) {
			return rand( 0, 100000 );
		}
		$limits = $bits[1];
		[ $low, $high ] = explode( ',', $limits );
		return rand( (int)$low, (int)$high );
	}

	private function setServices() {
		$services = MediaWikiServices::getInstance();
		$this->providerFactory = $services->getService(
			'ExtendedStatisticsSnapshotProviderFactory'
		);
		$this->snapshotStore = $services->getService( 'ExtendedStatisticsSnapshotStore' );
		$this->namespaceInfo = $services->getNamespaceInfo();
	}

	/**
	 * @param string $key
	 * @return false|string|string[]|null
	 */
	private function parseKey( $key ) {
		$matches = [];
		if ( preg_match( '/\{\{\{(.*?)\}\}\}/', $key, $matches ) ) {
			$options = explode( ',', $this->getOption( $matches[1] ) );
			shuffle( $options );
			return $options;
		}

		return is_string( $key ) ? $key : null;
	}

	/**
	 * @param ISnapshotProvider $provider
	 * @param string $interval
	 * @param DateTime $date
	 * @param string|null $nextInterval
	 */
	private function insertAggregate(
		ISnapshotProvider $provider, $interval, DateTime $date, $nextInterval = null
	) {
		if ( !empty( $this->collection[$interval][$provider->getType()] ) ) {
			$new = $provider->aggregate(
				$this->collection[$interval][$provider->getType()], $interval,
				SnapshotDate::newFromFormat( $date->format( 'Ymd' ), 'Ymd' )
			);
			$this->snapshotStore->persistSnapshot( $new );
			$this->collection[$interval][$provider->getType()] = [];
			if ( $nextInterval ) {
				$this->collection[$nextInterval][$provider->getType()][] = $new;
			}

		}
	}
}

$maintClass = "ImportDummyData";
require_once RUN_MAINTENANCE_IF_MAIN;

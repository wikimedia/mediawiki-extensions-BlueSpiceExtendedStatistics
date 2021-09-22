<?php

namespace BlueSpice\ExtendedStatistics\Api;

use ApiBase;
use ApiQuery;
use BlueSpice\ExtendedStatistics\AttributeRegistryFactory;
use BlueSpice\ExtendedStatistics\IReport;
use BlueSpice\ExtendedStatistics\ISnapshotProvider;
use BlueSpice\ExtendedStatistics\ISnapshotStore;
use BlueSpice\ExtendedStatistics\SnapshotDate;
use BlueSpice\ExtendedStatistics\SnapshotDateRange;
use DateInterval;
use InvalidArgumentException;
use MediaWiki\MediaWikiServices;

class ApiQueryReports extends \ApiQueryBase {
	/** @var AttributeRegistryFactory */
	private $providerFactory;
	/** @var ISnapshotStore */
	private $snapshotStore;
	/** @var AttributeRegistryFactory */
	private $reportFactory;
	/** @var array */
	private $filter = [];
	/** @var int */
	private $limit = 20;

	/**
	 * @param ApiQuery $query
	 * @param string $moduleName
	 */
	public function __construct( ApiQuery $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'esr' );
		$services = MediaWikiServices::getInstance();
		$this->providerFactory = $services->getService( 'ExtendedStatisticsSnapshotProviderFactory' );
		$this->snapshotStore = $services->getService( 'ExtendedStatisticsSnapshotStore' );
		$this->reportFactory = $services->getService( 'ExtendedStatisticsReportFactory' );
	}

	/**
	 * @throws \MWException
	 * @throws \ReflectionException
	 */
	public function execute() {
		$this->filter = $this->getFilter();
		$type = $this->getType();
		/** @var IReport $report */
		$report = $this->reportFactory->get( $type );
		$snapshotType = $report->getSnapshotKey();
		$snapshots = $this->getFiltered( $snapshotType );
		if ( count( $snapshots ) > $this->limit ) {
			$snapshots = array_slice( $snapshots, -$this->limit, $this->limit, true );
		}
		$data = $report->getClientData( $snapshots, $this->filter, $this->limit );
		$result = $this->getResult();
		$result->addValue( [ 'query', $this->getModuleName() ], $type, $data );

		$result->addIndexedTagName( [ 'query', $this->getModuleName() ], 's' );
	}

	/**
	 * @param int $flags
	 * @return array[]
	 */
	public function getAllowedParams( $flags = 0 ) {
		return [
			'filter' => [
				ApiBase::PARAM_REQUIRED => false,
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_HELP_MSG => 'apihelp-query+statistics-reports-param-filter'
			],
			'type' => [
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_HELP_MSG => 'apihelp-query+statistics-reports-param-type'
			],
			'aggregate' => [
				ApiBase::PARAM_REQUIRED => false,
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_HELP_MSG => 'apihelp-query+statistics-reports-param-aggregate'
			]
		];
	}

	/**
	 * @return string[]
	 */
	protected function getExamplesMessages() {
		return [
			'action=query&meta=statistics-reports&esrfilter=' .
			'{"date":{"dateStart":"20210914","dateEnd":"20210915"}}&esrtype=mytype'
			=> 'apihelp-query+statistics-reports-example',
		];
	}

	private function getFilter() {
		$param = $this->getParameter( 'filter' );
		$parsed = json_decode( $param, 1 );
		if ( !is_array( $parsed ) ) {
			$parsed = [];
		}
		$this->ensureFilters( $parsed );

		return $parsed;
	}

	private function getFiltered( $type ) {
		$range = SnapshotDateRange::newFromFilterData( $this->filter['date'], 'Y-m-d' );
		unset( $this->filter['date'] );
		$interval = $this->filter['interval'];
		unset( $this->filter['interval'] );
		/** @var ISnapshotProvider $provider */
		$provider = $this->providerFactory->get( $type );
		$snapshots = $this->snapshotStore->getSnapshotForRange( $range, $type, $interval );

		if ( $this->shouldAggregate() ) {
			return [ $provider->aggregate( $snapshots ) ];
		}
		return $snapshots;
	}

	private function getType() {
		$param = $this->getParameter( 'type' );
		if ( !$this->reportFactory->hasType( $param ) ) {
			throw new InvalidArgumentException(
				'Report of type ' . $param . ' not found'
			);
		}

		return $param;
	}

	/**
	 * @return array
	 */
	private function defaultDateFilter() {
		$today = new SnapshotDate();
		$todayTs = $today->format( 'Y-m-d' );
		$today->sub( new DateInterval( 'P10D' ) );
		$startTs = $today->format( 'Y-m-d' );

		return [
			'dateStart' => $startTs,
			'dateEnd' => $todayTs,
		];
	}

	private function shouldAggregate() {
		return (bool)$this->getParameter( 'aggregate' );
	}

	/**
	 * Make sure required filters are set
	 *
	 * @param array &$parsed
	 */
	private function ensureFilters( &$parsed ) {
		if (
			!isset( $parsed['date'] ) ||
			!isset( $parsed['date']['dateStart'] ) || !isset( $parsed['date']['dateEnd'] )
		) {
			$parsed['date'] = $this->defaultDateFilter();
		}
		if ( !isset( $parsed['interval'] ) ) {
			$parsed['interval'] = 'day';
		}
	}

}

<?php

namespace BlueSpice\ExtendedStatistics\Special;

use Html;
use SpecialPage;

class AggregatedStatistic extends SpecialPage {

	public function __construct() {
		parent::__construct(
			'AggregatedStatistic',
			'extendedstatistics-viewspecialpage-aggregated'
		);
	}

	/**
	 *
	 * @param string $param
	 */
	public function execute( $param ) {
		$request = $this->getRequest();
		$output = $this->getOutput();

		$output->addModules( [
			'ext.bluespice.extendedstatistics.d3',
			'ext.bluespice.aggregatedstatistics'
		] );

		$output->enableOOUI();

		$output->addHTML( Html::element( 'div', [
			'id' => 'bs-extendedstatistics-special-aggregatedstatistics'
		] ) );
		$this->setHeaders();
	}
}

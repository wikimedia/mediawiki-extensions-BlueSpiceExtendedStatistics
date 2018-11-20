<?php

namespace BlueSpice\ExtendedStatistics\Hook\BeforePageDisplay;

class AddResources extends \BlueSpice\Hook\BeforePageDisplay {

	protected function doProcess() {
		$this->out->addModuleStyles( 'ext.bluespice.statistics.styles' );
		$title = $this->out->getTitle();
		if( $title->isSpecial( 'AdminDashboard' ) || $title->isSpecial( 'UserDashboard' ) ) {
			$this->out->addModules( 'ext.bluespice.statisticsPortlets' );
		}
		return true;
	}

}

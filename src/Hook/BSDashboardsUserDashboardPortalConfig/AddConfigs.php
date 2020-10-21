<?php

namespace BlueSpice\ExtendedStatistics\Hook\BSDashboardsUserDashboardPortalConfig;

use BlueSpice\Dashboards\Hook\BSDashboardsUserDashboardPortalConfig;

class AddConfigs extends BSDashboardsUserDashboardPortalConfig {

	protected function doProcess() {
		$this->portalConfig[1][] = [
			'type'  => 'BS.Statistics.StatisticsPortletNumberOfUsers',
			'config' => [
				'title' => $this->msg( 'bs-statistics-portlet-numberofusers' )->plain(),
				'inputPeriod' => 'week',
			],
			'modules'  => [ 'ext.bluespice.statisticsPortlets' ],
		];
		$this->portalConfig[1][] = [
			'type'  => 'BS.Statistics.StatisticsPortletNumberOfEdits',
			'config' => [
				'title' => $this->msg( 'bs-statistics-portlet-numberofedits' )->plain(),
				'inputPeriod' => 'week',
			],
			'modules'  => [ 'ext.bluespice.statisticsPortlets' ],
		];
		$this->portalConfig[1][] = [
			'type'  => 'BS.Statistics.StatisticsPortletNumberOfArticles',
			'config' => [
				'title' => $this->msg( 'bs-statistics-portlet-numberofarticles' )->plain(),
				'inputPeriod' => 'week',
			],
			'modules'  => [ 'ext.bluespice.statisticsPortlets' ],
		];
		$this->portalConfig[1][] = [
			'type'  => 'BS.Statistics.StatisticsPortletNumberOfPages',
			'config' => [
				'title' => $this->msg( 'bs-statistics-portlet-numberofpages' )->plain(),
				'inputPeriod' => 'week',
			],
			'modules'  => [ 'ext.bluespice.statisticsPortlets' ],
		];
	}

}

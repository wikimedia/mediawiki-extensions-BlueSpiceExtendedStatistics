<?php

class BSApiStatisticsAvailableDiagramsStore extends BSApiExtJSStoreBase {
	protected function makeData( $sQuery = '' ) {
		$aData = [];

		foreach ( ExtendedStatistics::getAvailableDiagrams() as $oDiagram ) {
			$aFilterKeys = [];
			foreach ( $oDiagram->getFilters() as $key => $oFilter ) {
				$aFilterKeys[] = $key;
			}

			$oTemplate = new stdClass();
			$oTemplate->key = $oDiagram->getDiagramKey();
			$oTemplate->displaytitle = $oDiagram->getTitle();
			$oTemplate->listable = $oDiagram->isListable();
			$oTemplate->filters = $aFilterKeys;
			$oTemplate->isDefault = $oDiagram->getIsDefault();
			$aData[] = $oTemplate;
		}

		return $aData;
	}
}

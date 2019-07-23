<?php

namespace BlueSpice\ExtendedStatistics\Data\Entity\Collection;

use BlueSpice\Data\RecordSet;
use BlueSpice\Entity;

class Writer extends \BlueSpice\Data\Entity\Writer {

	/**
	 *
	 * @return Schema
	 */
	public function getSchema() {
		return new Schema();
	}

	/**
	 *
	 * @param RecordSet $dataSet
	 * @return RecordSet
	 */
	public function remove( $dataSet ) {
		throw new Exception( 'Removing entity store is not supported yet' );
	}

	public function writeEntity( Entity $entity ) {
	}

}

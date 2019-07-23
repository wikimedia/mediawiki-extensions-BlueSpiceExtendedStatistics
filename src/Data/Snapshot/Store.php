<?php

namespace BlueSpice\ExtendedStatistics\Data\Snapshot;

use IContextSource;
use BlueSpice\Services;

class Store implements \BlueSpice\Data\IStore, \BlueSpice\Data\Entity\IStore {

	/**
	 * @param IContextSource|null $context
	 * @return Reader
	 */
	public function getReader( IContextSource $context = null ) {
		return new Reader(
			Services::getInstance()->getDBLoadBalancer()
		);
	}

	/**
	 * @param IContextSource|null $context
	 * @return Writer
	 */
	public function getWriter( IContextSource $context = null ) {
		return new Writer(
			$this->getReader(),
			Services::getInstance()->getDBLoadBalancer()
		);
	}
}

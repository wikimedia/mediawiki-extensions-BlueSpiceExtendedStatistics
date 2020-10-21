<?php
/**
 * Statistics Extension for BlueSpice
 *
 * Adds statistical analysis to pages.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit https://bluespice.com
 *
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @author     Tobias Weichart <weichart@hallowelt.com>
 * @author     Patric Wirth
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

/**
 * Main class for Statistics extension
 * @package BlueSpice_Extensions
 * @subpackage Statistics
 */
class ExtendedStatistics extends BsExtensionMW {

	/**
	 * Collects all available diagrams
	 * @var array List of strings
	 */
	protected static $aAvailableDiagramClasses = [];
	/**
	 * Contains all available diagrams
	 * @var array List of diagram objects
	 */
	protected static $aAvailableDiagrams = null;
	/**
	 * Contains all available filters
	 * @var array List of filter objects.
	 */
	protected static $aAvailableFilters = [];

	/**
	 * Initialization of Statistics extension
	 */
	protected function initExt() {
		self::addAvailableFilter( 'FilterUsers' );
		self::addAvailableFilter( 'FilterNamespace' );
		self::addAvailableFilter( 'FilterCategory' );
		self::addAvailableFilter( 'FilterSearchScope' );

		self::addAvailableDiagramClass( 'BsDiagramNumberOfUsers' );
		self::addAvailableDiagramClass( 'BsDiagramNumberOfPages' );
		self::addAvailableDiagramClass( 'BsDiagramNumberOfArticles' );
		self::addAvailableDiagramClass( 'BsDiagramNumberOfEdits' );
		self::addAvailableDiagramClass( 'BsDiagramEditsPerUser' );
		self::addAvailableDiagramClass( 'BsDiagramSearches' );
	}

	/**
	 * Registers available diagrams
	 * @param string $sDiagramClass Name of class.
	 */
	public static function addAvailableDiagramClass( $sDiagramClass ) {
		if ( strpos( $sDiagramClass, 'Bs' ) !== 0 ) {
			$sDiagramClassName = 'Bs' . $sDiagramClass;
		} else {
			$sDiagramClassName = $sDiagramClass;
		}

		self::$aAvailableDiagramClasses[$sDiagramClassName] = $sDiagramClassName;
	}

	/**
	 * Returns list of available diagrams.
	 * @return array List of diagram objects.
	 */
	public static function getAvailableDiagrams() {
		self::loadAvailableDiagrams();
		return self::$aAvailableDiagrams;
	}

	/**
	 * Loads all available diagrams, i.e. instanciate all classes
	 * @return array List of available diagrams
	 */
	protected static function loadAvailableDiagrams() {
		if ( self::$aAvailableDiagrams !== null ) {
			return self::$aAvailableDiagrams;
		}
		self::$aAvailableDiagrams = [];
		foreach ( self::$aAvailableDiagramClasses as $sDiagramClass ) {
			self::$aAvailableDiagrams[$sDiagramClass] = new $sDiagramClass();
		}
		return self::$aAvailableDiagrams;
	}

	/**
	 * Get instance for a particluar diagram class.
	 * @param string $sDiagramClass Name of diagram
	 * @return BsDiagram
	 */
	public static function getDiagram( $sDiagramClass ) {
		self::loadAvailableDiagrams();
		return self::$aAvailableDiagrams[$sDiagramClass];
	}

	/**
	 * Registers a filter
	 * @param string $sFilterClass Name of filter class
	 */
	public static function addAvailableFilter( $sFilterClass ) {
		if ( strpos( $sFilterClass, 'Bs' ) !== 0 ) {
			$sFilterClassName = 'Bs' . $sFilterClass;
		} else {
			$sFilterClassName = $sFilterClass;
		}
	}

	/**
	 * Returns list of available filters
	 * @return array Names of filtesr.
	 */
	public static function getAvailableFilters() {
		return self::$aAvailableFilters;
	}

	/**
	 * Get a particular filter
	 * @param string $sFilterClass Name of filter
	 * @return BsStatisticsFilter Filter object
	 */
	public static function getFilter( $sFilterClass ) {
		if ( isset( self::$aFilterDiagrams[$sFilterClass] ) ) {
			return self::$aFilterDiagrams[$sFilterClass];
		} else {
			return null;
		}
	}

}

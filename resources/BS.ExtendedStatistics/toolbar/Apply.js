/**
 * SnapshotStatistics Apply Button Toolbar
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Oleksandr Pinchuk <intracomof@gmail.com>
 * @package    BlueSpice_Extensions
 * @subpackage Statistics
 * @copyright  Copyright (C) 2019 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GPL-3.0-only
 * @filesource
 */

Ext.define( 'BS.ExtendedStatistics.toolbar.Apply', {
	extend: 'Ext.Toolbar',
	header: false,
	requires: [
		'Ext.Button',
		'BS.store.BSApi',
		'Ext.Toolbar',
		'Ext.data.Store'
	],
	border: 0,
	filters: {},
	charts: {},
	MAIN_NAMESPACE_VALUE: 'main',

	initComponent: function () {
		var me = this;

		me.applyFilterSettings = function() {
			var filters = me.filters.getValues();
			var filtersToSend = [];
			var xFieldObj = { name: 'date', label: 'date' };
			var yFieldArr = me.filters.currentSourceConfig.get('series');

			var aggregation = [];
			if( filters['aggregation[property]'] !== 'none' ) {
				if ( filters['aggregation[property]'] !== 'timestampcreated' ) {
					xFieldObj.name = filters['aggregation[property]'];
					xFieldObj.label = me.filters.dynamicFiltersConfig[xFieldObj.name]['label'];
				}
				aggregation = me.buildAggregation( me.filters.getValues() );
			}

			var startDate = Ext.Date.format( me.filters.stringToDate( filters.startDate ), 'Ymdhis' );
			var endDate = Ext.Date.format( me.filters.stringToDate( filters.endDate ), 'Ymdhis' );

			filtersToSend.push( { property: 'type', type: 'string', value: filters.datasource, comparison: 'eq' } );
			filtersToSend.push( { property: 'timestampcreated', type: 'date', value: startDate, comparison: "gt" } );
			filtersToSend.push( { property: 'timestampcreated', type: 'date', value: endDate, comparison: "lt" } );

			// dynamicFiltersConfig stores full configuration for Filters of current Source
			// and we need this to populate filtersToSend with `type` property
			Object.keys( me.filters.dynamicFiltersConfig).forEach( function( key ) {
				var value = filters[key];
				if ( value ) {
					// this workaround is needed
					// because main namespace has empty value
					if ( key === 'namespacename' && value.toLowerCase() === me.MAIN_NAMESPACE_VALUE ) {
						value = "";
					}
					filtersToSend.push( me.buildDynamicFilter( key, value ) );
				}
			});

			var responseFields = Object.keys( me.filters.currentSourceConfig.get( 'attributes' ) );
			responseFields.push( {
				name: 'date',
				calculate: function( data ) {
					return Ext.Date.format( me.filters.stringToDate( data.timestampcreated, 'Ymdhis' ), "m/d/Y" );
				}
			} );

			var dataStore = new BS.store.BSApi( {
				apiAction: 'bs-extendedstatistics-collection-store',
				proxy: {
					extraParams: {
						limit: -1,
						aggregate: Ext.encode(aggregation),
						filter: Ext.encode(filtersToSend)
					}
				},
				fields: responseFields,
				autoLoad: true
			} );

			dataStore.on( 'load', function() {
				me.charts.loadCharts( dataStore, xFieldObj, yFieldArr );
			}, this );
		};

		me.applyButton = new Ext.Button( {
			cls: 'x-btn-progressive',
			text: mw.message( 'bs-statistics-button-label-apply' ).plain(),
			listeners: {
				click: me.applyFilterSettings
			}
		} );

		me.items = [
			'->',
			me.applyButton
		];

		me.buildDynamicFilter = function( filterName, value) {
			var filter = {
				property: filterName,
				type: me.filters.dynamicFiltersConfig[filterName]['type'],
				value: value
			};
			return filter;
		};

		me.buildAggregation = function( filters ) {
			var aggregate = [];
			aggregate.push( {
				property: filters['aggregation[property]'],
				type: filters['aggregation[type]'],
				targets: filters['targets']
			} );

			return aggregate;
		};

		this.callParent();
	}

} );

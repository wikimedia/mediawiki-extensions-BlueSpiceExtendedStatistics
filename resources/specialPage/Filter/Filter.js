( function( mw, $, bs ) {
	bs.util.registerNamespace( 'bs.aggregatedStatistics.filter' );

	bs.aggregatedStatistics.filter.Filter = function ( ) {
		bs.aggregatedStatistics.filter.Filter.super.call( this );
		OO.EventEmitter.call( this );

		this.init();
	};

	OO.inheritClass( bs.aggregatedStatistics.filter.Filter, OO.ui.Widget );
	OO.mixinClass( bs.aggregatedStatistics.filter.Filter, OO.EventEmitter );

	bs.aggregatedStatistics.filter.Filter.prototype.init = function () {
		// STUB
	};

	bs.aggregatedStatistics.filter.Filter.prototype.onFilter = function () {
		// STUB
	};

	bs.aggregatedStatistics.filter.Filter.prototype.getValue = function () {
		// STUB
	};

	bs.aggregatedStatistics.filter.Filter.prototype.getName = function () {
		return '';
	};

})( mediaWiki, jQuery, blueSpice );

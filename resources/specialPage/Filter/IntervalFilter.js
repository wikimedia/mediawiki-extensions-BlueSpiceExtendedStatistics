( function( mw, $, bs ) {
	bs.util.registerNamespace( 'bs.aggregatedStatistics.filter' );

	bs.aggregatedStatistics.filter.IntervalFilter = function ( ) {
		bs.aggregatedStatistics.filter.IntervalFilter.super.call( this );
	};

	OO.inheritClass( bs.aggregatedStatistics.filter.IntervalFilter, bs.aggregatedStatistics.filter.Filter );

	bs.aggregatedStatistics.filter.IntervalFilter.prototype.init = function () {
		var intervalDays = new OO.ui.MenuOptionWidget( {
			data: 'days',
			label: 'Days'
		} );
		var intervalWeek = new OO.ui.MenuOptionWidget( {
			data: 'week',
			label: 'Week'
		} );
		var intervalMonth = new OO.ui.MenuOptionWidget( {
			data: 'month',
			label: 'Month'
		} );
		var intervalYear = new OO.ui.MenuOptionWidget( {
			data: 'years',
			label: 'Year'
		} );
		this.intervalWidget = new OO.ui.DropdownWidget( {
			label: 'Select interval',
			menu: { items: [ intervalDays, intervalWeek, intervalMonth, intervalYear ] }
		} );

		this.intervalWidget.on( 'select', this.onFilter.bind( this ) );

		this.$element.append( this.intervalWidget.$element );
	};

	bs.aggregatedStatistics.filter.IntervalFilter.prototype.onFilter = function () {
		this.emit( 'change', this.getValue() );
	};

	bs.aggregatedStatistics.filter.IntervalFilter.prototype.getValue = function () {
		return [ this.getName() , { interval: this.intervalWidget.getMenu().findSelectedItem().getData() } ];
	};

	bs.aggregatedStatistics.filter.IntervalFilter.prototype.getName = function () {
		return 'interval';
	};

	bs.aggregatedStatistics.filterRegistry.register( 'interval', bs.aggregatedStatistics.filter.IntervalFilter );

})( mediaWiki, jQuery, blueSpice );

( function( mw, $, bs ) {
	bs.util.registerNamespace( 'bs.aggregatedStatistics.filter' );

	bs.aggregatedStatistics.filter.DateFilter = function () {
		bs.aggregatedStatistics.filter.DateFilter.super.call( this );
	};

	OO.inheritClass( bs.aggregatedStatistics.filter.DateFilter, bs.aggregatedStatistics.filter.Filter );

	bs.aggregatedStatistics.filter.DateFilter.prototype.init = function () {
		this.datePickerStart = new mw.widgets.DateInputWidget();
		this.datePickerEnd = new mw.widgets.DateInputWidget();
		this.datePickerStart.on( 'change', this.onFilter.bind( this ) );

		this.datePickerEnd.on( 'change',
			this.onFilter.bind( this ) );

		var datePickerLayout = new OO.ui.HorizontalLayout( {
			items: [
				new OO.ui.FieldLayout( this.datePickerStart, { label: 'Select start date', align: 'top' } ),
				new OO.ui.FieldLayout( this.datePickerEnd, { label: 'Select end date', align: 'top' } )
			]
		} );
		this.$element.append( datePickerLayout.$element );
	};

	bs.aggregatedStatistics.filter.DateFilter.prototype.onFilter = function () {
		this.emit( 'change', this.getValue());
	};

	bs.aggregatedStatistics.filter.DateFilter.prototype.getValue = function () {
		return [ this.getName(), { dateStart: this.datePickerStart.getValue(),
			dateEnd: this.datePickerEnd.getValue() } ];
	};

	bs.aggregatedStatistics.filter.DateFilter.prototype.getName = function () {
		return 'date';
	};

	bs.aggregatedStatistics.filterRegistry.register( 'date', bs.aggregatedStatistics.filter.DateFilter );

})( mediaWiki, jQuery, blueSpice );

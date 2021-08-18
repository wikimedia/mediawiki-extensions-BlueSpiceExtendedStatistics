(function ( mw, $, bs) {

	bs.util.registerNamespace( 'bs.aggregatedStatistics' );

	bs.aggregatedStatistics.statisticsItem.Item = function ( cfg ) {
		bs.aggregatedStatistics.statisticsItem.Item.parent.call( this, {} );
		this.filters = this.getFilters( cfg );
		this.value = {};
		if (this.filters.length > 0){
			//var filterLayout = new OO.ui.HorizontalLayout();
			for( var i = 0; i < this.filters.length; i++ ) {
				this.filters[i].connect( this, { 
					'change': function( e ) {
						this.onFilterChange( e );
					}
				}
				);
				//filterLayout.addItems( [ this.filters[i] ] );
				this.$element.append( this.filters[i].$element );
			}
			//this.$element.append( filterLayout.$element );
		}

		this.chart = this.getChart();
		this.setChartData();
		this.$element.append( this.chart.$element._groups[0] );
	};

	OO.inheritClass( bs.aggregatedStatistics.statisticsItem.Item, OO.ui.Widget );

	bs.aggregatedStatistics.statisticsItem.Item.static.tagName = 'div';

	// PUBLIC
	bs.aggregatedStatistics.statisticsItem.Item.prototype.getName = function () {
		return '';
	};

	bs.aggregatedStatistics.statisticsItem.Item.prototype.getLabel = function () {
		return '';
	};

	// PRIVATE
	bs.aggregatedStatistics.statisticsItem.Item.prototype.getData = function ( filterData ) {
		// Do API call with params filterData and this.getName()
	};

	bs.aggregatedStatistics.statisticsItem.Item.prototype.getFilters = function ( cfg ) {
		return [];
	};

	bs.aggregatedStatistics.statisticsItem.Item.prototype.getChart = function () {
		return '';
	};

	bs.aggregatedStatistics.statisticsItem.Item.prototype.setChartData = function () {
		//this.getData( filterData ).done( function( data ) {
		
			this.chart.updateData( this.getData() );
		//}.bind( this ) );
		// TODO: Handle fail
	};

	bs.aggregatedStatistics.statisticsItem.Item.prototype.onFilterChange = function ( e ) {
		for ( var key in bs.aggregatedStatistics.filterRegistry.registry ) {
			if ( e[0] === key ){
				this.value[key] = e[1];
			}
		}
		this.setChartData();
	};

} )( mediaWiki, jQuery , blueSpice);
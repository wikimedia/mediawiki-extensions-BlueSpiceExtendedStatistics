( function( mw, $, bs ) {
	bs.util.registerNamespace( 'bs.aggregatedStatistics.charts' );

	bs.aggregatedStatistics.charts.Barchart = function() {
		bs.aggregatedStatistics.charts.Barchart.super.call( this );
		this.width = 600;
		this.height = 300;
	};

	OO.inheritClass( bs.aggregatedStatistics.charts.Barchart, OO.ui.Widget );

	bs.aggregatedStatistics.charts.Barchart.prototype.updateData = function ( data ) {
		this.data = data;
		this.viewchart();
	}

	bs.aggregatedStatistics.charts.Barchart.prototype.viewchart = function () {
		xScale = d3.scaleBand()
			.domain( d3.range( this.data.length ) )
			.range( [ 40, this.width ] )
			.padding( 0.5 );

		yScale = d3.scaleLinear()
			.domain( [ 0, d3.max( this.data, d => d.value ) ] ).nice()
			.range( [ this.height - 30, 30 ] )

		this.xAxis = g => g
			.attr( "transform", `translate(0, ${ this.height - 30 } )` )
			.call( d3.axisBottom( xScale ).tickFormat( i => this.data[i].name ).tickSizeOuter( 0 ) )
			.selectAll( "text" )
			.attr( "text-anchor", "end" )
			.attr( "transform", "rotate(-65)" )
			.attr( "font-size", "9px" );

		this.yAxis = g => g
			.attr( "transform", `translate( ${40} , 0 )`)
			.call( d3.axisLeft( yScale ).ticks( null, this.data.format ) );

		this.chart = d3.create( "svg" )
				.append( "svg" )
				.attr( "viewBox", [ 0, 0, this.width, this.height + 90 ] );

		this.chart.selectAll( "g" )
				.data( this.data )
				.enter()
				.append( "rect" )
				.attr( "x", ( d, i ) => xScale( i ) )
				.attr( "y", d => yScale( d.value ) )
				.attr( "height", d => yScale( 0 ) - yScale( d.value ) )
				.attr( "width", xScale.bandwidth() )
				.attr( "fill", "#eaecf0" );

		this.chart.append( "g" ).call( this.xAxis );

		this.chart.append( "g" ).call( this.yAxis );

		this.$element =  this.chart;
	};

} )( mediaWiki, jQuery, blueSpice );

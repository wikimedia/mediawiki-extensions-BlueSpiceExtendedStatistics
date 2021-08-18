( function( mw, $, bs ) {

	bs.util.registerNamespace( 'bs.aggregatedStatistics.charts' );

	bs.aggregatedStatistics.charts.Groupchart = function ( cfg ) {
		bs.aggregatedStatistics.charts.Groupchart.super.call( this, cfg );
		this.width = 600;
		this.height = 300;
		this.names = [];
	};

	OO.inheritClass( bs.aggregatedStatistics.charts.Groupchart, OO.ui.Widget );

	bs.aggregatedStatistics.charts.Groupchart.prototype.updateData = function ( data ) {
		this.data = data;
		this.names = Object.keys( this.data[0] ).filter( function ( key ) {
			return key !== "name" && key !== "values";
		});
		names = this.names;
		this.data.forEach( function ( d ) {
			d.values = names.map( function ( name ) {
				return { name:name, values: d[name] }
			});
		});
		this.viewchart();
	};

	bs.aggregatedStatistics.charts.Groupchart.prototype.viewchart = function () {
		xScale = d3.scaleBand()
			.domain( this.data.map( d => d.name ) )
			.rangeRound( [ 40, this.width - 10 ] )
			.paddingInner( 0.1 );

		xInner = d3.scaleBand()
			.domain( this.names )
			.rangeRound( [ 0, xScale.bandwidth() ])
			.padding( 0.05 );

		yScale = d3.scaleLinear()
			.domain( [ 0, d3.max( this.data, function ( d ) { return d3.max( d.values, function(d) { return d.values; } ); } ) ] ).nice()
			.rangeRound( [ this.height - 30, 30 ] );

		this.color = d3.scaleOrdinal()
			.range( [ "#98abc5", "#8a89a6", "#7b6888", "#6b486b", "#a05d56", "#d0743c", "#ff8c00" ] );

		this.xAxis = g => g
			.attr( "transform", `translate(0, ${ this.height - 30 } )` )
			.call( d3.axisBottom( xScale ).tickSizeOuter( 0 ) );

		this.yAxis = g => g
			.attr( "transform", `translate( ${40} , 0 )`)
			.call( d3.axisLeft( yScale ).ticks( null, "s" ) );

		this.chart = d3.create( "svg" )
				.append( "svg" )
				.attr( "viewBox", [ 0, 0, this.width, this.height ] );
		this.chart.append( "g" )
				.call( this.xAxis );

		this.chart.append( "g" )
				.call( this.yAxis );

		this.chart.append( "g" )
				.selectAll( "g" )
				.data( this.data )
				.join( "g" )
				.attr( "transform", d => `translate( ${xScale( d.name ) } ,0)`)
				.selectAll( "rect" )
				.data( function ( d ) { return d.values } )
				.join( "rect" )
				.attr( "x", d => xInner( d.name ) )
				.attr( "y", d => yScale( d.values ) )
				.attr( "width", xInner.bandwidth() )
				.attr( "height", d =>  yScale( 0 ) - yScale( d.values ) )
				.attr( "fill", d => this.color( d.name ) );

		var legend= this.chart.selectAll( ".legend" )
				.data( this.names ).enter().append( "g" )
				.attr( "class", "legend" )
				.attr( "transform", function( d, i ) { return "translate( 0, " + i * 20 + ")"; } );

		legend.append( "rect" )
				.attr( "x", this.width - 18 )
				.attr( "width", 18 )
				.attr( "height", 18 )
				.style( "fill",  d => this.color( d ) );

		legend.append( "text" )
				.attr( "x", this.width - 24 )
				.attr( "y", 9 )
				.attr( "dy" , ".35em" )
				.style( "text-anchor", "end" )
				.style( "font-size", "9px" )
				.text( function ( d ) { return d; });

		this.$element = this.chart;
	};

})( mediaWiki, jQuery, blueSpice );

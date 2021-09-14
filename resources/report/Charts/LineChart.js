( function( mw, $, bs ) {
	bs.util.registerNamespace( 'bs.aggregatedStatistics.charts' );

	bs.aggregatedStatistics.charts.LineChart = function() {
		bs.aggregatedStatistics.charts.LineChart.super.call( this );
		this.width = 600;
		this.height = 300;
	};

	OO.inheritClass( bs.aggregatedStatistics.charts.LineChart, OO.ui.Widget );

	bs.aggregatedStatistics.charts.LineChart.prototype.setAxisLabels = function ( labels  ) {
		this.labels = Object.values( labels );
	}

	bs.aggregatedStatistics.charts.LineChart.prototype.updateData = function ( data ) {
		
		parseTime = d3.timeParse("%Y-%m-%d");

		data.forEach( function ( d ) {
			date = d.name;
			d.name = parseTime( date );
		});

		this.sumData = d3.group( data, d => d.line );
		this.data = data;
		this.viewchart();
	};

	bs.aggregatedStatistics.charts.LineChart.prototype.viewchart = function () {

		xScale = d3.scaleTime()
		.domain( d3.extent( this.data, function(d) { return d.name; } ) )
		.rangeRound( [ 50, this.width - 40 ] );

		yScale = d3.scaleLinear()
		.domain( [ 0, d3.max( this.data, d => d.value ) ] ).nice()
		.rangeRound( [ this.height - 30, 30 ] );

		var tickLength = 0;
		var mapItem = this.sumData.entries().next().value;
		tickLength = mapItem[1].length;

		this.xAxis = g => g
		.attr( "transform", `translate(0, ${ this.height - 30 } )` )
		.call( d3.axisBottom( xScale ).ticks( tickLength ).tickSizeOuter( 0 ) )
		.selectAll( "text" )
		.attr( "text-anchor", "end" )
		.attr( "transform", "rotate(-65)" )
		.attr( "font-size", "9px" );

		this.yAxis = g => g
		.attr( "transform", `translate( ${50} , 0 )`)
		.call( d3.axisLeft( yScale ));

		colorLine = d3.scaleOrdinal()
			.range( [ "#25401E", "#520610", "#84A63C","#B8D943","#EAF2AC",
			"#56732C", "#D95062","#D10F29","#521E25","#9E0B1F",
			"#2B283C", "#3E4557", "#D3BC76", "#D5AA46", "#DBC797",
			"#BFBFBF","#A6A6A6","#737373","#404040","#0D0D0D" ] );

		colorLabel = d3.scaleOrdinal()
			.range( [ "#25401E", "#520610", "#84A63C","#B8D943","#EAF2AC",
			"#56732C", "#D95062","#D10F29","#521E25","#9E0B1F",
			"#2B283C", "#3E4557", "#D3BC76", "#D5AA46", "#DBC797", 
			"#BFBFBF","#A6A6A6","#737373","#404040","#0D0D0D" ] );

		this.chart = d3.create( "svg" )
		.append( "svg" )
		.attr( "viewBox", [ 0, 0, this.width, this.height + 90 ] );

		this.chart.append( "g" ).call( this.xAxis );
		this.chart.append( "g" ).call( this.yAxis );

		this.chart.append("text")
		.attr("transform", "rotate(-90)")
		.attr("x", 0 - (this.height / 2) )
		.attr("y",  10 )
		.attr("text-anchor", "middle")
		.style( "font-size", "9px" )
		.text( this.labels[0] );

		this.chart.selectAll(".line")
		.data(this.sumData)
		.enter()
		.append("g")
		.append("path")
        .attr("fill", "none")
        .attr("stroke", function(d) { return colorLine( d );})
        .attr("stroke-width", 1.5)
        .attr("d", function(d) {
          return d3.line()
            .x(function(d) { return xScale(d.name); })
            .y(function(d) { return yScale(d.value); })
            (d[1])
        })
		var lines = this.chart.selectAll(".line")
		.data( this.sumData )
		.enter()
		.append( "text" )
		.attr("class", "serie-label")
		.attr( "transform" , function(d) {
			return "translate( " + ( xScale(d[1][d[1].length -1 ].name) ) + ","
			+ ( yScale(d[1][d[1].length -1 ].value) + 5) + ")";
		})
		.attr( "x", 3)
		.attr( "dy" , ".35em" )
		.text( function(d) { return d[1][d[1].length -1 ].line })
		.attr( "text-anchor", "middle" )
		.style( "fill", function(d) { return colorLabel( d );})
		.style( "font-size", "9px" );
		
		this.$element =  this.chart;
	};

} )( mediaWiki, jQuery, blueSpice );

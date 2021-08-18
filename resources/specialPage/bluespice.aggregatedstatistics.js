( function( mw, $, bs ) {
	var $mainContainer = $( '#bs-extendedstatistics-special-aggregatedstatistics' ),
		registry = bs.aggregatedStatistics.statisticsItemRegistry;
	console.log("aggregatedstatistics", registry);
	var options = [];
	for ( var key in registry.registry ) {
		var item = new registry.registry[key]();
		options.push( new OO.ui.MenuOptionWidget( { data: key, label: item.getLabel() } ) );
	};

	var dropdown = new OO.ui.DropdownWidget( {
		menu: { items: options },
		id: "statistic-selector"
	});
	$mainContainer.append( dropdown.$element );
	var $itemContainer = $( '<div>' );
	$mainContainer.append( $itemContainer );

	dropdown.getMenu().connect( this, {
		select: function( selected ) {
		   // TODO: sanity check, if value is not null or stuff
		   if ( selected instanceof OO.ui.MenuOptionWidget ) {
			   var item = new ( bs.aggregatedStatistics.statisticsItemRegistry.lookup( selected.getData() ) )();
			   $itemContainer.html( item.$element );
		   }
		}
   } );

	dropdown.getMenu().selectItemByData( Object.keys( registry.registry )[0] );

} )( mediaWiki, jQuery, blueSpice );
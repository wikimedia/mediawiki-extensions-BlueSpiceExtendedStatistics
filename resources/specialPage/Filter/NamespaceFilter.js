( function( mw, $, bs ) {
	bs.util.registerNamespace( 'bs.aggregatedStatistics.filter' );

	bs.aggregatedStatistics.filter.NamespaceFilter = function ( ) {
		bs.aggregatedStatistics.filter.NamespaceFilter.super.call( this );
	};

	OO.inheritClass( bs.aggregatedStatistics.filter.NamespaceFilter, bs.aggregatedStatistics.filter.Filter );

	bs.aggregatedStatistics.filter.NamespaceFilter.prototype.init = function () {

		var ids = mw.config.get( 'wgContentNamespaces' );
		var names = mw.config.get( 'wgFormattedNamespaces' );
		var namespaces = [];
		for ( i in ids ) {
			for (j in names ) {
				if ( ids[i] == j ) {
					if( ids[i] == 0 ) {
						namespaces.push( { data: ids[i], label: 'Main' } );
					} else {
						namespaces.push( { data: ids[i], label: names[j] } );
					}
				}
			}
		}

		this.namespaceSelect = new OO.ui.MenuTagMultiselectWidget( {
			inputPosition: 'outline',
			placeholder: 'Add namespaces',
			allowArbitrary: false,
			options: namespaces,
			selected:namespaces
		} );

		this.namespaceSelect.on( 'change', this.onFilter.bind( this ) );

		var namespaceLayout = new OO.ui.FieldLayout( this.namespaceSelect, { label: 'Set namespaces', align: 'top' } );
		this.$element.append( namespaceLayout.$element );
	};

	bs.aggregatedStatistics.filter.NamespaceFilter.prototype.onFilter = function () {
		this.emit( 'change', this.getValue() );
	};

	bs.aggregatedStatistics.filter.NamespaceFilter.prototype.getValue = function () {
		return [ this.getName() , { namespaces: this.namespaceSelect.getValue() } ];
	};

	bs.aggregatedStatistics.filter.NamespaceFilter.prototype.getName = function () {
		return 'namespaces';
	}

	bs.aggregatedStatistics.filterRegistry.register( 'namespaces', bs.aggregatedStatistics.filter.NamespaceFilter );

})( mediaWiki, jQuery, blueSpice );

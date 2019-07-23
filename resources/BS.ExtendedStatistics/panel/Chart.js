Ext.define( 'BS.ExtendedStatistics.panel.Chart', {
	extend: 'Ext.panel.Panel',
	header: false,
	requires: [ 'BS.store.BSApi', 'Ext.chart.axis.Axis' ],
	bsPayload: {},

	initComponent: function() {
		this.dockedItems = [{
			xtype: 'toolbar',
			dock: 'top',
			items: [
				'->',
				this.makeExportButton()
			]
		}];

		this.store = new BS.store.BSApi( {
			apiAction: 'bs-extendedstatistics-collection-store',
			proxy: {
				limit: -1
			},
			fields: [
				'type',
				'assignedpages',
				'unassignedpages',
				'timestampcreated',
				'timestamptouched',
				'namespacename',
				'assignedpagesaggregated',
				'unassignedpagesaggregated'
			],
			autoLoad: true
		} );
		this.crtMain = new Ext.chart.CartesianChart( {
			theme: 'blue',
			engine: 'Ext.draw.engine.Svg',
			height: 500,
			store: this.store,
			axes: [{
				title: mw.message( 'bs-statistics-label-count' ).plain(),
				type: 'numeric',
				position: 'left',
				grid: true,
				minimum: this.getMinValueForYAxis(),
				maximum: this.getMaxValueForYAxis(),
			}, {
				title: 'test',
				type: 'category',
				position: 'bottom',
				grid: true,
				label: {
					rotate: {
						degrees: -45
					}
				}
			}],
			series: [{
				type: 'line',
				xField: 'timestampcreated',
				yField: 'assignedpages',
				style: {
					lineWidth: 2
				},
				marker: {
					radius: 4,
					lineWidth: 2
				},
				label: {
					field: 'hits'
				}
			}]
		} );

		this.items = [
			this.crtMain
		];

		return this.callParent( arguments );
	},

	getMaxValueForYAxis: function() {
		return 99;
		var maxValue = 0;
		for( var i = 0; i < this.bsPayload.data.length; i++ ) {
			if( this.bsPayload.data[i].hits > maxValue ) {
				maxValue = this.bsPayload.data[i].hits;
			}
		}

		return maxValue + 2;
	},

	getMinValueForYAxis: function() {
		return 0;
		var minValue = this.bsPayload.data[1].hits;
		for( var i = 0; i < this.bsPayload.data.length; i++ ) {
			if( this.bsPayload.data[i].hits < minValue ) {
				minValue = this.bsPayload.data[i].hits;
			}
		}

		return minValue - 2;
	},

	makeExportButton: function() {
		this.muExport = this.makeExportMenu();
		this.btnExport = new Ext.Button( {
			text: mw.message( 'bs-statistics-button-label-export' ).plain(),
			menu: this.muExport,
			id: 'bs-statistics-mainpanel-exportmenu'
		} );
		return this.btnExport;
	},

	makeExportMenu: function() {
		var menu = new Ext.menu.Menu();

		menu.add( {
			text: 'SVG',
			value: 'image/svg+xml'
		} );
		if( mw.config.get( 'BsExtendedStatisticsAllowPNGExport', false ) === true ) {
			menu.add( {
				text: 'PNG',
				value: 'image/png'
			} );
		}
		menu.on( 'click', this.onClickmuExport, this );
		return menu;
	},

	onClickmuExport: function( menu, item, e, eOpts ) {
		var url = '';
		if(item.value == 'image/png') {
			url =  mw.util.getUrl( 'Special:ExtendedStatistics/export-png' );
		}
		else {
			url = mw.util.getUrl( 'Special:ExtendedStatistics/export-svg' );
		}

		this.crtMain.download( {
			url: url,
			format: item.value,
			width: this.crtMain.getWidth(),
			height: this.crtMain.getHeight()
		} );
	}
});
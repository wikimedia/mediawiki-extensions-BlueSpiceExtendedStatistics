Ext.define( 'BS.Statistics.panel.List', {
	extend: 'Ext.grid.Panel',
	headerPosition: 'bottom',

	bsPayload: {},

	initComponent: function() {
		this.setTitle( this.bsPayload.label );

		this.store = new Ext.data.JsonStore( {
			fields: this.bsPayload.data.fields,
			data: this.bsPayload.data.list.items
		} );

		this.columns = {
			items: this.bsPayload.data.columns,
			defaults: {
				flex: 1
			}
		};

		this.dockedItems = [{
			xtype: 'pagingtoolbar',
			store: this.store,
			dock: 'bottom',
			displayInfo: true
		}];

		return this.callParent( arguments );
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

		menu.add({
			text: 'CSV',
			value: 'csv'
		});

		menu.on( 'click', this.onClickmuExport, this );
		return menu;
	},

	onClickmuExport: function( menu, item, e, eOpts ) {
		this.saveDocumentAs( {
			type: 'xlsx',
			title: this.getTitle(),
			fileName: this.getTitle() + '.xlsx'
		} );
	}
});
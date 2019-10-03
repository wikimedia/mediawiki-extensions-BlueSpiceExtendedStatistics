
Ext.define( 'BS.ExtendedStatistics.panel.Main', {
	extend: 'Ext.Panel',
	requires: [
		'Ext.Array',
		'Ext.Panel',
		'Ext.Button',
		'Ext.data.Store',
		'BS.ExtendedStatistics.store.CollectionConfigs',
		'BS.ExtendedStatistics.panel.Filter',
		'BS.ExtendedStatistics.panel.Chart'
	],
	layout: 'border',
	border: false,
	height: 950,

	initComponent: function() {
		this.collectionConfigsStore = new BS.ExtendedStatistics.store.CollectionConfigs();
		this.collectionConfigsStore.init();

		this.pnlCharts = new BS.ExtendedStatistics.panel.Chart( {
			title: 'Chart',
			collapsible: false,
			region: 'center',
			margins: '5 0 0 0'
		});

		this.applyToolbar = new BS.ExtendedStatistics.toolbar.Apply( {
			title: 'Apply',
			collapsible: false,
			region: 'right',
			margins: '5 0 0 0',
			charts: this.pnlCharts
		});

		this.pnlFilter = new BS.ExtendedStatistics.panel.Filter( {
			title: 'Filters',
			collapsible: false,
			region: 'center',
			margins: '5 0 0 0',
			collectionStore: this.collectionConfigsStore,
			applyButtonToolbar: this.applyToolbar
		});

		this.dockedItems = [
			this.pnlFilter
		];

		this.items = [
			this.pnlCharts
		];

		this.callParent();
	},
} );
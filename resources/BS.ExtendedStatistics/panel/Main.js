
Ext.define( 'BS.ExtendedStatistics.panel.Main', {
	extend: 'Ext.Panel',
	requires: [
		'Ext.Panel',
		'Ext.Toolbar',
		'Ext.picker.Date',
		'BS.ExtendedStatistics.panel.Chart',
		'BS.ExtendedStatistics.panel.Series'
	],
	layout: 'border',
	border: true,
	height: 800,

	initComponent: function() {
		this.pnlMain = new BS.ExtendedStatistics.panel.Chart( {
			title: 'Chart',
			collapsible: false,
			region: 'center',
			margins: '5 0 0 0'
		});
		this.pnlSeries = new BS.ExtendedStatistics.panel.Series( {
			title: 'series',
			region: 'south'
		} );

		var date = new Date();
		date.setDate(date.getDate() - 1);
		this.filterTimespanEnd = new Ext.picker.Date ( {
			name: 'end',
			value: date
		} );
		date.setDate(date.getDate() - 365);
		this.filterTimespanStart = new Ext.picker.Date ( {
			name: 'start',
			value: date
		} );

		this.items = [
			this.pnlMain,
			this.pnlSeries
		];
		this.tbar = new Ext.Toolbar({
			style: {
				backgroundColor: '#FFFFFF',
				backgroundImage: 'none'
			},
			items: this.makeTbarItems()
		});
		this.callParent();
	},

	makeTbarItems: function() {
		return [
			'->',
			this.filterTimespanStart,
			this.filterTimespanEnd
		];
	}
} );

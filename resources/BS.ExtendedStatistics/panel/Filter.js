Ext.define( 'BS.ExtendedStatistics.panel.Filter', {
	extend: 'Ext.form.Panel',
	requires: [
		'Ext.picker.Date',
		'Ext.form.Panel',
		'Ext.form.field.Date',
		'Ext.form.field.Tag',
		'Ext.form.field.Text',
		'Ext.form.field.Number',
		'Ext.form.field.ComboBox',
		'Ext.form.field.Checkbox',
		'Ext.form.CheckboxGroup',
		'BS.form.SimpleSelectBox',
		'BS.store.ApiUser',
		'BS.store.LocalNamespaces',
		'BS.store.ApiCategory',
		'Ext.form.field.Hidden',
		'BS.ExtendedStatistics.toolbar.Apply',
		'Ext.toolbar.Toolbar'
	],
	header: false,
	border: true,
	clientValidation: true,
	collectionStore: {},
	applyButtonToolbar: {},
	dynamicFiltersConfig: {}, // Key => Object Storage of current set of Filters
	currentSourceConfig: {},

	initComponent: function() {
		this.dataSourceSelect = new Ext.form.field.ComboBox( {
			store: this.collectionStore,
			fieldLabel: mw.message( 'bs-statistics-datasource-label' ).plain(),
			name: 'datasource',
			displayField: 'displaytitle',
			valueField: 'key',
			typeAhead: true,
			mode: 'local',
			triggerAction: 'all',
			lastQuery: '',
			forceSelection: true,
			allowBlank: false,
			editable: true,
			labelAlign: 'top',
			style: 'padding: 5px',
		} );

		this.dataSourceSelect.on( 'select', this.onDataSourceSelect, this );

		// <From-To DatePickers>
		var startDate = new Date();
		startDate.setDate( startDate.getDate() - 1 );
		this.filterTimespanEnd = new Ext.form.field.Date ( {
			labelAlign: 'top',
			fieldLabel: 'End Date',
			name: 'endDate',
			value: startDate,
			style: 'padding: 5px',
			maxValue: new Date(),
		} );

		var endDate = new Date();
		endDate.setDate( endDate.getDate() - 365 );
		this.filterTimespanStart = new Ext.form.field.Date ( {
			labelAlign: 'top',
			name: 'startDate',
			fieldLabel: 'Start Date',
			value: endDate,
			maxValue: new Date(),
			style: 'padding: 5px'
		} );
		// </From-To DatePickers>

		// Filters that don't generates dynamically (not depend on source)
		var mainFilters = {
			xtype: 'fieldcontainer',
			layout: 'hbox',
			align: 'stretch',
			items: [
				this.dataSourceSelect,
				this.filterTimespanStart,
				this.filterTimespanEnd
			]
		};

		// Filters that depend on source
		this.dynamicFiltersConfig = {};
		this.dynamicFilters = new Ext.toolbar.Toolbar( {
			html: '<b>Filters</b>',
			items: []
		} );

		// Aggregation properties (depend on source)
		this.aggregationModeToolbar = new Ext.toolbar.Toolbar( {
			html: '<b>Aggregation Mode</b>',
			items: [ ]
		} );

		// Aggregation Field (depends on aggregation mode)
		this.aggregationField = new Ext.toolbar.Toolbar( {
			html: '<b>Aggregation Field</b>',
			items: []
		} );

		this.targets = new Ext.form.CheckboxGroup( {
			name: 'targets',
			fieldLabel: '<b>Targets</b>',
			columns: 3,
			vertical: true,
			style: 'padding: 10px',
			items: []
		} );

		// select first source from the list and activate needed filters
		this.dataSourceSelect.select( this.collectionStore.getAt( 0 ) );
		this.onDataSourceSelect(
			this.collectionStore.getAt( 0 ).get( 'key' ),
			this.collectionStore.getAt(0)
		);

		// pass Filter information to ApplyButton to make proper request
		this.applyButtonToolbar.filters = this;

		this.items = [
			mainFilters,
			{
				xtype: 'fieldcontainer',
				layout: 'hbox',
				align: 'stretch',
				items: [
					this.dynamicFilters,
					this.aggregationModeToolbar,
					this.aggregationField
				]
			},
			this.targets,
			this.applyButtonToolbar
		];

		this.callParent();
	},

	/** Here we need to display needed Filters & Agregation modes for selected source **/
	onDataSourceSelect: function( field, record ) {
		this.currentSourceConfig = record;
		var me = this;
		var filters = record.get( 'filters' );
		me.dynamicFilters.removeAll();
		me.dynamicFiltersConfig = {};

		var aggregationModeDataArr = [];

		filters.forEach( function( filterConf ) {
			// possible aggregation modes for selected source
			aggregationModeDataArr.push( {
				value: filterConf.name,
				name: filterConf.label,
				filterConf: filterConf
			} );

			// Key => Object Storage of current set of Filters
			me.dynamicFiltersConfig[filterConf.name] = filterConf;
			// possible filters for selected source
			me.dynamicFilters.add( me.generateFilterByConf( filterConf ) );
		});

		me.generateAggregationModeSelect( aggregationModeDataArr );
	},

	generateAggregationModeSelect: function( aggregationModeDataArr ) {
		this.aggregationModeToolbar.removeAll();

		aggregationModeDataArr.unshift( {
			value: 'timestampcreated',
			name: 'Date',
			filterConf: {}
		} );
		aggregationModeDataArr.unshift( { value: 'none', name: 'None', filterConf: {} } );

		this.aggregationModeSelect = new BS.form.SimpleSelectBox( {
			bsData: aggregationModeDataArr,
			value: aggregationModeDataArr[0].value,
			mode: 'local',
			name: 'aggregation[property]',
			editable: false,
			style: 'margin: 90px 0 0 0'
		} );

		this.aggregationModeSelect.on( 'select', this.onAggregationModeSelect, this );

		this.onAggregationModeSelect(
			this.aggregationModeSelect.store.getAt( 0 ).get( 'key' ),
			this.aggregationModeSelect.store.getAt( 0 )
		);
		this.aggregationModeToolbar.add( this.aggregationModeSelect );
	},

	onAggregationModeSelect: function( field, record ) {
		this.aggregationField.removeAll();
		var aggregationFilter;

		switch ( record.get('value') ) {
			case 'none':
				this.targets.hide();
				return;
			case 'timestampcreated':
				aggregationFilter = this.generateIntervalFilterSelect();
				break;
			default:
				var filterConf = record.get('filterConf');

				this.aggregationField.add( new Ext.form.field.Hidden( {
					name: 'aggregation[type]',
					value: filterConf.type
				} ));
				break;
		}

		this.aggregationField.add( aggregationFilter );
		this.generateTargetsCheckbox( record.get( 'value' ) );
	},

	generateFilterByConf: function( filterConf ) {
		var filterObj;
		switch( filterConf.type ) {
			case 'int':
				filterObj = new Ext.form.field.Number( {
					labelAlign: 'top',
					name: filterConf.name,
					fieldLabel: filterConf.label
				} );
				break;
			case 'string':
				filterObj = new Ext.form.field.Text( {
					labelAlign: 'top',
					name: filterConf.name,
					fieldLabel: filterConf.label,
					style: 'margin: 30px 0 0 0'
				} );
				break;
			case 'boolean':
				filterObj = new Ext.form.field.Checkbox( {
					name: filterConf.name,
					labelAlign: 'top',
					fieldLabel: filterConf.label,
					items: [
						{
							name: filterConf.name,
							boxLabel: filterConf.label,
							inputValue: true
						}
					]
				} );
				break;
			case 'date':
				filterObj = new Ext.form.field.Date( {
					labelAlign: 'top',
					name: filterConf.name,
					fieldLabel: filterConf.label
				} );
				break;
		}

		return filterObj;
	},

	generateIntervalFilterSelect: function() {
		return new BS.form.SimpleSelectBox( {
			bsData: [
				{ value: 'daily', name: mw.message( 'bs-statistics-day' ).plain() },
				{ value: 'weekly', name: mw.message( 'bs-statistics-week' ).plain() },
				{ value: 'monthly', name: mw.message( 'bs-statistics-month' ).plain() },
				{ value: 'yearly', name: mw.message( 'bs-statistics-year' ).plain() }
			],
			value: 'daily',
			fieldLabel: mw.message( 'bs-statistics-grain' ).plain(),
			labelAlign: 'top',
			name: 'aggregation[type]',
			editable: false,
			style: 'margin: 30px 0 0 0'
		} );
	},

	generateTargetsCheckbox: function( except ) {
		var me = this;
		me.targets.show();
		me.targets.removeAll();

		this.currentSourceConfig.get('targets').forEach( function( target ) {
			if ( target !== except ) {
				me.targets.add( {
					boxLabel  : target,
					inputValue: target,
					value: true
				} );
			}
		});
	},

	stringToDate: function( date, format ) {
		date = date.toString();
		format = format || 'd-m-Y';
		var datePattern = '';
		// Pretty pedestrian - but will do
		switch( format ) {
			case 'd-m-Y':
				datePattern = /(\d{2})\.(\d{2})\.(\d{4})/;
				date = date.replace( datePattern, '$3-$2-$1' );
				break;
			case 'Ymdhis':
				datePattern = /(\d{4})(\d{2})(\d{2})(.*)/;
				date = date.replace( datePattern, '$1-$2-$3' );
				break;
		}

		return new Date( date );
	}

} );
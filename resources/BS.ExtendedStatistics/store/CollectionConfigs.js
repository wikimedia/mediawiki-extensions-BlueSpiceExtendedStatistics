Ext.define( 'BS.ExtendedStatistics.store.CollectionConfigs', {
    extend: 'Ext.data.Store',
    fields: [ 'displaytitle', 'key', 'filters', 'attributes', 'series' ],
    data: [],

    init: function() {
        var me = this;
        var collectionConfigs = mw.config.get( 'bsgExtendedStatisticsCollectionConfigs' );
        var dataSourcesKeys = Object.keys( collectionConfigs );

        dataSourcesKeys.forEach( function( sourceKey ) {
            var attrDefObjects = collectionConfigs[sourceKey]['AttributeDefinitions'];
            var messageObjects = collectionConfigs[sourceKey]['VarMessageKeys'];
            var attrKeys = Object.keys(attrDefObjects);
            var defaultAttrKeys = Object.keys(
                collectionConfigs[sourceKey]['DefaultAttributeDefinitions']
            );
            var primaryAttrKeys = Object.keys(
                collectionConfigs[sourceKey]['PrimaryAttributeDefinitions']
            );

            // Attr - (DefaultAttr + PrimaryAttr) = custom fields, by which dataset can be filtered
            var filterFields = Ext.Array.difference(
                attrKeys,
                Ext.Array.merge( defaultAttrKeys, primaryAttrKeys )
            );

            // Attr - DefaultAttr + timestampcreated = possible targets
            var targetsArr = Ext.Array.difference( attrKeys, defaultAttrKeys );
            targetsArr.push( 'timestampcreated' );

            // load modules i18n
            mw.loader.using( collectionConfigs[sourceKey]['Modules'] );

            var filters = [];

            attrKeys.forEach(function( filterName ) {
                if ( filterFields.includes( filterName ) && !filterName.includes( 'aggregated' ) ) {
                    // check if there is a message key for needed filter
                    var labelMsg = filterName;
                    if ( messageObjects.hasOwnProperty( filterName ) ) {
                        labelMsg = mw.message( messageObjects[ filterName ] ).plain();
                    }
                    filters.push(
                        Object.assign(
                            {
                                name: filterName,
                                label: labelMsg
                            },
                            attrDefObjects[ filterName ]
                        )
                    );
                }
            });

            var seriesArr = [];
            primaryAttrKeys.forEach( function( primaryKey ) {
                var labelMsg = primaryKey;
                if ( messageObjects.hasOwnProperty(primaryKey) ) {
                    labelMsg = mw.message( messageObjects[ primaryKey ] ).plain();
                }
                seriesArr.push( {
                    name: primaryKey,
                    label: labelMsg
                } );
            });

            me.add( {
                displaytitle: mw.message( collectionConfigs[sourceKey]['TypeMessageKey'] ).plain(),
                key: sourceKey,
                filters: filters,
                attributes: collectionConfigs[sourceKey]['AttributeDefinitions'],
                series: seriesArr,
                targets: targetsArr
            } );
        } );

    }
} );
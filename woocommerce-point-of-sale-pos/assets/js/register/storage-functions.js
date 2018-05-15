var product_schema = {
    //version: 1,
    //autoSchema: false, // must be false when version is defined
    stores: [{
        name: 'products',
        keyPath: 'id',
        indexes: [
            {
                name: 'title',
                keyPath: 'title'
            },
            {
                name: 'barcode',
                keyPath: 'barcode'
            }
        ]
    }]
};

var db = new ydn.db.Storage( 'WC-Point-Of-Sale-YDN', product_schema );

function insertData( db, productsData )
{
    jQuery.each( productsData, function( i, data ) {
        if( typeof data.id != 'undefined' && typeof data.f_title != 'undefined' ) {
            if( typeof data.variations != 'undefined' && data.type == 'variable' ) {
                jQuery.each( data.variations, function( j, var_data ) {

                    var_data.attributes     = JSON.stringify(var_data.attributes);
                    var_data.parent_attr    = JSON.stringify(var_data.parent_attr);
                    var_data.categories_ids = JSON.stringify(var_data.categories_ids);

                    if(typeof var_data.parent_id == 'undefined')
                        var_data.parent_id = 0;
                    if( var_data.regular_price == 'null' || var_data.regular_price == null)
                        var_data.regular_price = '';
                    if( var_data.sale_price == 'null' || var_data.sale_price == null)
                        var_data.sale_price = var_data.regular_price;

                    db.put( 'products', var_data ).fail( function(e) {
                        throw e;
                    });
                });

            } else {

                data.attributes     = JSON.stringify( data.attributes );
                data.parent_attr    = JSON.stringify( data.parent_attr );
                data.categories_ids = JSON.stringify( data.categories_ids );

                if(typeof data.parent_id == 'undefined')
                    data.parent_id = 0;

                if(typeof data.barcode == 'undefined') data.barcode = '';
                var title = data.title;
                if( typeof title == 'undefined' )
                    title = '';
                if( data.regular_price == 'null' || data.regular_price == null)
                    data.regular_price = '';
                if( data.sale_price == 'null' || data.sale_price == null)
                    data.sale_price = data.regular_price;

                db.put( 'products', data ).fail(function(e) {
                    throw e;
                });
            }
        } else {
            return 'Error inset product to data base :'+ JSON.stringify( data );
        }
    });
}

function getFullValuesByKey( store_name, key ) {
    db.get( store_name, key ).always( function( record ) {
        return JSON.stringify( record );
    });
}

function searchByKey( store_name, condition, key ) {
    var q = db.from( store_name ).where('title', condition, key);
    var limit = 10000;
    q.list( limit ).done( function( objs ) {
        return JSON.stringify( objs );
    });
}
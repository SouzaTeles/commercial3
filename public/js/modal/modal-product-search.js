$(document).ready(function(){
    $.each(Budget.budget.items,function(k,item){
        ModalProductSearch.deny.push(item.product_id);
    });
    ModalProductSearch.events();
    ModalProductSearch.table.draw();
});

ModalProductSearch = {
    deny: [],
    products: [],
    selected: [],
    table: global.table({
        selector: '#modal-table-products',
        scrollY: 320,
        scrollCollapse: 1,
        noControls: [0,1,8],
        order: [[4,'asc']]
    }),
    add: function(){
        if( ModalProductSearch.selected.length == 1 ){
            Item.item = ModalProductSearch.selected[0];
            Item.data2form();
            $('#modal-product-search').modal('hide');
        } else {
            ModalProductSearch.showSelected();
            ModalProductSearch.select();
        }
    },
    events: function(){
        var $modal = $('#modal-product-search');
        $modal.find('form').on('submit',function(e){
            e.preventDefault();
            e.stopPropagation();
            if( !$('#modal_product_name').val().length ){
                global.validateMessage('Informe a palavra chave para realizar a pesquisa.');
                return;
            }
            ModalProductSearch.getList();
        });
        $('#modal_product_active').bootstrapToggle({
            width: '100%',
            on: '<i class="fa fa-check"></i> Somente ativos',
            off: '<i class="fa fa-times"></i> Ativos e inativos',
            onstyle: 'blue',
            offstyle: 'gray-dark'
        });
        $('#button-selected').click(function(){
            ModalProductSearch.showSelected();
        });
    },
    get: function(key,success){
        var product = ModalProductSearch.products[key];
        global.post({
            url: global.uri.uri_public_api + 'product.php?action=get',
            data: {
                get_unit: 1,
                get_product_cost: 1,
                get_product_stock: 1,
                get_product_prices: 1,
                product_id: product.product_id,
                company_id: Company.company.company_id
            },
            dataType: 'json'
        }, function(product){
            ModalProductSearch.selected.push({
                budget_item_id: null,
                external_id: null,
                image: product.image,
                ncm_id: product.ncm_id,
                icms_id: product.icms_id,
                price_id: product.prices[0].price_id,
                product_id: product.product_id,
                product_code: product.product_code,
                product_name: product.product_name,
                product_discount: product.product_discount,
                product_commission: product.product_commission,
                product_weight_net: product.product_weight_net,
                product_weight_gross: product.product_weight_gross,
                product_cfop: product.product_cfop,
                product_cfop_extra: product.product_cfop_extra,
                budget_item_quantity: 1,
                budget_item_value: product.prices[0].price_value,
                budget_item_value_unitary: product.prices[0].price_value,
                budget_item_aliquot_discount: 0,
                budget_item_value_discount: 0,
                budget_item_cost: product.cost ? product.cost.cost_value : 0,
                budget_item_value_total: product.prices[0].price_value,
                stock_value: product.stock ? product.stock.stock_value : 0,
                stock_date: product.stock ? product.stock.stock_date : null,
                unit_code: product.unit.unit_code,
                unit_type: product.unit.unit_type,
                prices: product.prices
            });
            if( !!success ) success();
        });
    },
    getList: function(){
        global.post({
            url: global.uri.uri_public_api + 'product.php?action=getList',
            data: {
                company_id: Company.company.company_id,
                product_name: $('#modal_product_name').val(),
                product_active: $('#modal_product_active').prop('checked') ? 1 : 0
            },
            dataType: 'json'
        },function(data){
            ModalProductSearch.products = data;
            ModalProductSearch.showList();
            ModalProductSearch.legend();
        });
    },
    info: function(product){
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-product-info',
            data: product,
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'big',
                icon: 'fa-info-circle',
                id: 'modal-product-info',
                class: 'modal-product-info',
                title: 'Informações do Produto',
                html: html,
                buttons: [{
                    icon: 'fa-check',
                    title: 'Ok'
                }]
            })
        });
    },
    legend: function(){
        var $modal = $('#modal-product-search');
        var found = ModalProductSearch.products.length;
        $modal.find('.found').html('<i class="fa fa-cubes"></i> ' + found + ' Produto' + ( found != 1 ? 's' : '' ) + ' localizado' + ( found != 1 ? 's' : '' ))
    },
    select: function(){
        $('#modal-table-products').find('input').prop('checked',false);
        $.each(ModalProductSearch.selected,function(key,item){
            $('#modal-table-products').find('input[data-id="' + item.product_id + '"]').prop('checked',true);
        });
        $('#button-selected').text( ModalProductSearch.selected.length ? (ModalProductSearch.selected.length + ' selecionado' + (ModalProductSearch.selected.length > 1 ? 's' : '')) : 'Nenhum selecionado' )
    },
    showList: function(){
        ModalProductSearch.table.clear();
        $.each( ModalProductSearch.products, function(key, product){
            var row = ModalProductSearch.table.row.add([
                '<input data-id="' + product.product_id + '"' + ( product.product_active == 'N' ? 'disabled ' : '' ) + 'type="checkbox" data-key="' + key + '" />',
                '<div class="product-search-cover"' + ( !!product.image ? (' style="background-image:url(' + product.image + ')"') : '' ) + '></div>',
                product.product_code,
                product.product_classification ? product.product_classification : '--',
                product.product_name,
                '<span>' + product.product_price + '</span>R$ ' + global.float2Br(product.product_price),
                product.unit_code,
                product.unit_code == 'I' ? parseInt(product.product_stock) : global.float2Br(product.product_stock,'0',3),
                '<button data-key="' + key + '" class="btn btn-empty-orange"><i class="fa fa-info-circle"></i></button>'
            ]).node();
            if( product.product_active == 'Y' ){
                $(row).on('dblclick',function(){
                    if( $(this).find('input').prop('checked') ){
                        var del = -1;
                        var find = $(this).find('input').attr('data-id');
                        $.each(ModalProductSearch.selected,function(k,item){
                            if( item.product_id == find ) del = k;
                        });
                        ModalProductSearch.selected.splice(del,1);
                        ModalProductSearch.select();
                    } else {
                        if(ModalProductSearch.deny.indexOf(ModalProductSearch.products[key].product_id) != -1){
                            global.validateMessage('O produto já foi adicionado ao orçamento.');
                        } else {
                            ModalProductSearch.get(key, function(){
                                ModalProductSearch.add();
                            });
                        }
                    }
                });
                $(row).find('input').on('change',function(){
                    if( this.checked ) {
                        if(ModalProductSearch.deny.indexOf(ModalProductSearch.products[key].product_id) != -1){
                            this.checked = false;
                            global.validateMessage('O produto já foi adicionado ao orçamento.');
                        } else{
                            ModalProductSearch.get(key, function(){
                                ModalProductSearch.showSelected();
                                ModalProductSearch.select();
                            });
                        }
                    } else {
                        var del = -1;
                        var find = $(this).find('input').attr('data-id');
                        $.each(ModalProductSearch.selected,function(k,item){
                            if(item.product_id == find) del = k;
                        });
                        ModalProductSearch.selected.splice(del,1);
                        ModalProductSearch.select();
                    }
                });
                $(row).find('button').click(function(){
                    var product = ModalProductSearch.products[$(this).attr('data-key')];
                    ModalProductSearch.info({
                        image: product.image,
                        product_id: product.product_id,
                        product_code: product.product_code,
                        product_name: product.product_name,
                        unit_code: product.unit_code
                    });
                });
            }
            $(row).addClass(product.product_active == 'N' ? 'txt-gray' : ( product.product_stock <= 0 ? 'txt-red-light' : ''));
        });
        ModalProductSearch.table.draw();
        ModalProductSearch.select();
    },
    showSelected: function(){
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-product-search-selected',
            dataType: 'html'
        },function(html){
            global.modal({
                icon: 'fa-check',
                id: 'modal-product-search-selected',
                class: 'modal-product-search-selected',
                title: 'Produtos selecionados',
                html: html,
                buttons: [{
                    icon: 'fa-check',
                    title: 'Ok'
                }],
                shown: function(){
                    $('#modal-product-search-selected input').last().focus().select();
                },
                hidden: function(){
                    $('#modal_product_name').focus().select();
                }
            });
        });
    }
};
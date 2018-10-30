$(document).ready(function(){
    ModalProductSearchSelected.events();
    ModalProductSearchSelected.showList();
});

ModalProductSearchSelected = {
    table: global.table({
        noOrder: 1,
        scrollY: 186,
        scrollCollapse: 1,
        noControls: [0,1,2,3,4,5],
        selector: '#modal-table-products-selected'
    }),
    events: function(){
        ModalProductSearchSelected.table.on('draw',function(){
            $('#modal-table-products-selected').find('input').on('keyup',function(e){
                var key = $(this).attr('data-key');
                var qt = $(this).val().length ? parseFloat(global.br2Float($(this).val())) : 1;
                var value = qt * ModalProductSearch.selected[key].budget_item_value_unitary;
                ModalProductSearch.selected[key].budget_item_quantity = qt;
                ModalProductSearch.selected[key].budget_item_value = value;
                ModalProductSearch.selected[key].budget_item_value_total = value;
                $(this).parent().next().next().text('R$ ' + global.float2Br(value));
                var keycode = e.keyCode || e.which;
                if( keycode == '13' ){
                    $('#modal-product-search-selected').modal('hide');
                }
            });
            $('#modal-table-products-selected').find('button').click(function(){
                ModalProductSearch.selected.splice($(this).attr('data-key'),1);
                ModalProductSearchSelected.showList();
                ModalProductSearch.select();
            });
            global.mask();
        });
    },
    showList: function(){
        ModalProductSearchSelected.table.clear();
        $.each( ModalProductSearch.selected, function(key, item){
            var row = ModalProductSearchSelected.table.row.add([
                item.product_code,
                item.product_name,
                '<input data-key="' + key + '" data-value="' + item.budget_item_quantity + '" class="text-center" type="text" data-to-mask="' + (item.unit_type == 'F' ? 'decimal4' : 'integer') + '" value="' + global.float2Br(item.budget_item_quantity,1,4) + '"/>',
                'R$ ' + global.float2Br(item.budget_item_value_unitary),
                'R$ ' + global.float2Br(item.budget_item_value_total),
                '<button data-key="' + key + '" class="btn btn-empty"><i class="fa fa-trash-o txt-red-light"></i></button>'
            ]).node();
            if(item.product_stock <= 0){
                $(row).addClass('txt-red-light');
            }
        });
        ModalProductSearchSelected.table.draw();
    }
};
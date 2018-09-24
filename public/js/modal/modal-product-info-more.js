$(document).ready(function(){
    ModalProductInfoMore.getInfo();
});

ModalProductInfoMore = {
    data: [],
    budgets: [],
    table: global.table({
        scrollX: 1,
        scrollY: 186,
        scrollCollapse: 1,
        order: [[0,'asc']],
        selector: '#table-product-info-more',
    }),
    getInfo: function(){
        global.post({
            url: global.uri.uri_public_api + 'product.php?action=getInfoBuy',
            data: ModalProductInfoMore.data,
            dataType: 'json'
        },function(data){
            ModalProductInfoMore.data = data;
            ModalProductInfoMore.showInfo();
        });
    },
    showInfo: function(){
        ModalProductInfoMore.table.clear();
        $.each( ModalProductInfoMore.data, function(key, item){
            ModalProductInfoMore.table.row.add([
                item.budget_code,
                item.provider_code + ' - ' + item.provider_name,
                '<span>' + ('0000000000' + global.float2Br(item.required,0,2)).slice(-10) + '</span>' + global.float2Br(item.required,0,3) + ' ' + ModalProductInfo.product.unit_code,
                '<span>' + item.budget_date + '</span>' + global.date2Br(item.budget_date),
                '<span>' + item.budget_delivery + '</span>' + global.date2Br(item.budget_delivery),
                '--'
            ]);
        });
        ModalProductInfoMore.table.draw();
    }
};
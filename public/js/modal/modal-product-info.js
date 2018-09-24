$(document).ready(function(){
    ModalProductInfo.show();
    ModalProductInfo.getInfo();
});

ModalProductInfo = {
    info: [],
    product: {},
    table: global.table({
        selector: '#table-product-info',
        scrollY: 186,
        scrollCollapse: 1,
        order: [[0,'asc']],
        noControls: [3]
    }),
    getInfo: function(){
        global.post({
            url: global.uri.uri_public_api + 'product.php?action=getInfo',
            data: { product_id: ModalProductInfo.product.product_id },
            dataType: 'json'
        },function(data){
            ModalProductInfo.info = data;
            ModalProductInfo.showInfo();
        })
    },
    moreInfo: function(key){
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-product-info-more',
            data: {
                company_id: parseInt(ModalProductInfo.info[key].company_code),
                product_id: ModalProductInfo.product.product_id
            },
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'big',
                icon: 'fa-file-text-o',
                id: 'modal-product-info-more',
                class: 'modal-product-info-more',
                title: 'Pedidos de Compra',
                html: html,
                buttons: [{
                    icon: 'fa-check',
                    title: 'Ok'
                }]
            })
        });
    },
    show: function(){
        var $modal = $('#modal-product-info');
        if( !!ModalProductInfo.product.image ){
            $modal.find('.image').css('background-image','url(' + ModalProductInfo.product.image + ')');
        }
        $modal.find('.name').text(ModalProductInfo.product.product_name);
        $modal.find('.code').html('Código<br/>' + ModalProductInfo.product.product_code);
        $modal.find('.unit').html('Tipo<br/>' + ModalProductInfo.product.unit_code);
    },
    showInfo: function(){
        ModalProductInfo.table.clear();
        $.each( ModalProductInfo.info, function(key, info){
            var row = ModalProductInfo.table.row.add([
                info.company_code,
                '<span>' + ('0000000000' + global.float2Br(info.stock,0,2)).slice(-10) + '</span>' + global.float2Br(info.stock,0,3) + ' ' + ModalProductInfo.product.unit_code,
                '<span>' + ('0000000000' + global.float2Br(info.bought,0,2)).slice(-10) + '</span>' + global.float2Br(info.bought,0,3) + ' ' + ModalProductInfo.product.unit_code,
                '<button data-key="' + key + '" ' + ( info.bought == 0 ? 'disabled ' : '' ) + 'class="btn btn-empty-blue"><i class="fa fa-info-circle"></i></button>'
            ]).node();
            $(row).addClass(info.stock <= 0 ? 'txt-red-light' : (info.sale_active == 'N' ? 'txt-red-light' : '')).find('button').click(function(){
                ModalProductInfo.moreInfo($(this).attr('data-key'));
            });
        });
        ModalProductInfo.table.draw();
    }
};
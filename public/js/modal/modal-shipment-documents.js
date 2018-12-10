$(document).ready(function(){
    ModalShipmentDocuments.showList();
});

ModalShipmentDocuments = {
    table: global.table({
        selector: '#modal-table-shipment-documents',
        scrollX: 1,
        scrollY: 186,
        scrollCollapse: 1,
        order: [[4,'desc']]
    }),
    showList: function(){
        ModalShipmentDocuments.table.clear();
        $.each( ModalShipmentDocuments.documents, function(key, document){
            var row = ModalShipmentDocuments.table.row.add([
                document.document_type,
                document.person_name,
                document.city_name + '/' + document.district_name,
                global.formatDistance(document.document_distance),
                '<span>' + document.document_date + '</span>' + document.document_date_br
            ]).node();
            $(row).addClass(document.document_canceled == 'S' ? 'txt-gray-light' : '').on('dblclick',function(){
                if( document.document_canceled == 'Y' ){
                    global.validateMessage('Não será possível adicionar o documento, pois o mesmo está cancelado.');
                    return;
                }
                if( !!document.shipment_code ){
                    global.validateMessage('Não será possível adicionar o documento, pois o mesmo já está vinculado ao mapa de carregamento' + document.shipment_code + '.');
                    return;
                }
                ModalShipmentDocuments.success(document);
                $('#modal-shipment-documents').modal('hide');
            });
        });
        ModalShipmentDocuments.table.draw();
    }
};
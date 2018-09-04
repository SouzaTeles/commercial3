$(document).ready(function(){

});

ModalPersonInfo = {
    person: {},
    table: global.table({
        selector: '#table-person-receivable',
        scrollY: 186,
        scrollCollapse: 1,
        order: [[6,'desc']]
    }),
    show: function(){
        var $modal = $('#modal-person-info');
        if( !!ModalPersonInfo.person.image ){
            $modal.find('.image').css('background-image','url(' + ModalPersonInfo.person.image + ')');
        }
        $modal.find('.name').text(ModalPersonInfo.person.person_name);
        $modal.find('.code').html('Código<br/>' + ModalPersonInfo.person.person_code);
        $modal.find('.type').html('Tipo<br/>' + ModalPersonInfo.person.person_type);
        $modal.find('.birth').html('Aniversário<br/>' + (ModalPersonInfo.person.person_birth ? global.date2Br(ModalPersonInfo.person.person_birth) : '--'));
        ModalPersonInfo.showReceivable();
        $modal.find('.limite').html('<i class="fa fa-credit-card"></i> R$ ' + global.float2Br(ModalPersonInfo.person.person_credit_limit));
        $modal.find('.expired').html('<i class="fa fa-files-o"></i> ' + ModalPersonInfo.person.credit_limit.expired_quantity);
        $modal.find('.expiring').html('<i class="fa fa-files-o"></i> ' + ModalPersonInfo.person.credit_limit.expiring_quantity);
        $modal.find('.balance').html('<i class="fa fa-money"></i> R$ ' + global.float2Br(ModalPersonInfo.person.credit_limit.balance));
        global.tooltip();
    },
    showReceivable: function(){
        ModalPersonInfo.table.clear();
        $.each( ModalPersonInfo.person.credit_limit.receivable, function(key, receivable){
            var row = ModalPersonInfo.table.row.add([
                receivable.modality_name ? receivable.modality_name : '--',
                receivable.receivable_code,
                '<span>' + receivable.receivable_date + '</span>' + global.date2Br(receivable.receivable_date),
                '<span>' + receivable.receivable_deadline + '</span>' + global.date2Br(receivable.receivable_deadline),
                '<span>' + receivable.receivable_value + '</span>R$ ' + global.float2Br(receivable.receivable_value),
                '<span>' + receivable.receivable_dropped + '</span>R$ ' + global.float2Br(receivable.receivable_dropped),
                receivable.receivable_delay
            ]).node();
            if( receivable.receivable_delay > parseInt(global.config.credit.debit_day_limit) ){
                $(row).addClass('txt-red');
            } else if( receivable.receivable_delay > 0 ){
                $(row).addClass('txt-red-light');
            }
        });
        ModalPersonInfo.table.draw();
    }
};
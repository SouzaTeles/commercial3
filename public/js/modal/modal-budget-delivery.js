$(document).ready(function(){
    ModalDelivery.events();
});

ModalDelivery = {
    delivery: {
        budget_delivery_: 'N',
        budget_delivery_date: '',
        budget_note_document: ''
    },
    data2form: function(){
        $('#modal_budget_delivery').bootstrapToggle(ModalDelivery.delivery.budget_delivery == 'Y' ? 'on' : 'off');
        $('#modal_budget_delivery_date').val(global.date2Br(ModalDelivery.delivery.budget_delivery_date));
        $('#modal_budget_note_document').val(ModalDelivery.delivery.budget_note_document);
    },
    events: function(){
        $('#modal_budget_delivery').on('change',function(){
            $('#modal_budget_delivery_date').
            prop('disabled',!$(this).prop('checked')).
            val($(this).prop('checked') ? global.date2Br(global.dateAddDays(global.today(),3)) : '');
        });
        $('#modal_budget_delivery_date').datepicker({
            format: 'dd/mm/yyyy',
            zIndex: 1091
        }).blur(function(){
            if( $(this).val().length != 10 ){
                $(this).val(global.date2Br(global.dateAddDays(global.today(),3)));
            }
        });
        global.mask();
        global.toggle();
    },
    form2data: function(){
        ModalDelivery.delivery.budget_delivery = $('#modal_budget_delivery').prop('checked') ? 'Y' : 'N';
        ModalDelivery.delivery.budget_delivery_date = global.date2Us($('#modal_budget_delivery_date').val());
        ModalDelivery.delivery.budget_note_document = $('#modal_budget_note_document').val();
    }
};
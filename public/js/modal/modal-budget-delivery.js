$(document).ready(function(){
    ModalDelivery.events();
});

ModalDelivery = {
    delivery: {
        delivery: 'N',
        date: ''
    },
    data2form: function(){
        $('#modal_budget_delivery').bootstrapToggle(ModalDelivery.delivery.delivery == 'Y' ? 'on' : 'off');
        $('#modal_budget_delivery_date').val(global.date2Br(ModalDelivery.delivery.date));
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
        ModalDelivery.delivery.delivery = $('#modal_budget_delivery').prop('checked') ? 'Y' : 'N';
        ModalDelivery.delivery.date = global.date2Us($('#modal_budget_delivery_date').val());
    }
};
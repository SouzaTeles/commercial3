$(document).ready(function(){
    ModalBudgetDiscount.show();
    ModalBudgetDiscount.events();
});

ModalBudgetDiscount = {
    aliquot_discount: 0,
    value_discount: 0,
    authorized: function(data){
        $('#modal-budget-discount').modal('hide');
        Budget.budget.authorization.push(data.authorization_id);
        Budget.discountAliquot({
            value_discount: ModalBudgetDiscount.value_discount,
            aliquot_discount: ModalBudgetDiscount.aliquot_discount
        });
    },
    discountAuthorization: function(){
        if(ModalBudgetDiscount.aliquot_discount == 0){
            $('#modal-budget-discount').modal('hide');
            Budget.discountAliquot({
                value_discount: ModalBudgetDiscount.value_discount,
                aliquot_discount: ModalBudgetDiscount.aliquot_discount
            });
            return;
        }
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-general-discount-authorization',
            data: { aliquot_discount: ModalBudgetDiscount.aliquot_discount },
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'small',
                icon: 'fa-lock',
                id: 'modal-general-discount-authorization',
                class: 'modal-general-discount-authorization',
                title: 'Autorização de Desconto Geral',
                html: html,
                buttons: [{
                    icon: 'fa-times',
                    class: 'pull-left btn-red',
                    title: 'Cancelar'
                },{
                    icon: 'fa-unlock',
                    title: 'Autorizar',
                    class: 'btn-green',
                    unclose: true,
                    action: function(){
                        ModalGeneralDiscountAuthorization.authorize();
                    }
                }],
                shown: function(){
                    $('#modal_user_user').focus();
                }
            });
        });
    },
    discountByAl: function(){
        ModalBudgetDiscount.value_discount = Budget.budget.budget_value * (ModalBudgetDiscount.aliquot_discount/100);
        $('#modal_discount_value').val(global.float2Br(ModalBudgetDiscount.value_discount));
        ModalBudgetDiscount.show();
    },
    discountByVl: function(){
        ModalBudgetDiscount.aliquot_discount = (ModalBudgetDiscount.value_discount*100)/Budget.budget.budget_value;
        $('#modal_discount_aliquot').val(global.float2Br(ModalBudgetDiscount.aliquot_discount));
        ModalBudgetDiscount.show();
    },
    events: function(){
        $('#form-discount').on('submit',function(e){
            e.preventDefault();
            e.stopPropagation();
            ModalBudgetDiscount.discountAuthorization();
        });
        $('#modal_discount_aliquot').on('keyup',function(){
            if( !$(this).val().length ) return;
            ModalBudgetDiscount.aliquot_discount = global.br2Float($(this).val());
            if( ModalBudgetDiscount.aliquot_discount.toString() == 'NaN' ) return;
            ModalBudgetDiscount.discountByAl();
        });
        $('#modal_discount_value').on('keyup',function(){
            if( !$(this).val().length ) return;
            ModalBudgetDiscount.value_discount = global.br2Float($(this).val());
            if( ModalBudgetDiscount.value_discount.toString() == 'NaN' ) return;
            ModalBudgetDiscount.discountByVl();
        });
        global.mask();
    },
    show: function(){
        $('#modal-budget-discount .modal-box').html(
            'Valor Total: <b>R$' + global.float2Br(Budget.budget.budget_value) + '</b><br/>' +
            'Valor com Desconto: <b>R$' + global.float2Br(Budget.budget.budget_value-ModalBudgetDiscount.value_discount) + '</b>'
        );
    }
};
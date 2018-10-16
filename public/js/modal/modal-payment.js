$(document).ready(function(){
    ModalPayment.events();
});

ModalPayment = {
    payment: {
        image: null,
        bank_id: null,
        agency_id: null,
        agency_code: null,
        check_number: null,
        modality_id: null,
        modality_type: null,
        modality_description: '',
        modality_installment: 1,
        nature_id: null,
        payment_value: Payment.payment_remaining,
        payment_entry: 'N',
        payment_installment: 1,
        payment_deadline: global.today()
    },
    check: function(type){
        var $bank = $('#modal_bank_id');
        var $agency = $('#modal_agency_id');
        var $agency2 = $('#modal_agency_code');
        var $check = $('#modal_check_number');
        $bank.prop('disabled',type != 'C');
        $agency.prop('disabled',type != 'C');
        $agency2.prop('disabled',type != 'C');
        $check.prop('disabled',type != 'C');
        if( type != 'C' ){
            $bank.val(null).prop('required',false);
            $agency.val(null).prop('required',false);
            $agency2.val(null).prop('required',false);
            $check.val(null).prop('required',false);
            $('#modal-payment .bank-section').hide();
        } else {
            $bank.val(null).prop('required',true);
            $agency.val(null);
            $agency2.val(null);
            $check.val(null).prop('required',true);
            $('#modal-payment .bank-section').show();
        }
        if( !ModalPayment.payment.agency_code ){
            $('#col-agency').show();
            $('#col-new-agency').hide();
            $agency.prop('required',true);
            $agency2.prop('required',false);
        } else {
            $('#col-new-agency').show();
            $('#col-agency').hide();
            $agency.prop('required',false);
            $agency2.prop('required',true);
        }
        $bank.selectpicker('refresh');
        $agency.selectpicker('refresh');
    },
    data2form: function(){
        ModalPayment.check(ModalPayment.payment.modality_type);
        $('#modal_payment_entry').bootstrapToggle(ModalPayment.payment.payment_entry == 'Y' ? 'on' : 'off');
        $('#modal_payment_deadline').val(global.date2Br(ModalPayment.payment.payment_deadline));
        $('#modal_modality_id').selectpicker('val',ModalPayment.payment.modality_id);
        $('#modal_payment_value').val(global.float2Br(ModalPayment.payment.payment_value));
        $('#modal_bank_id').selectpicker('val',ModalPayment.payment.bank_id);
        $('#modal_agency_code').val(ModalPayment.payment.agency_code);
        $('#modal_check_number').val(ModalPayment.payment.check_number);
        ModalPayment.installments(ModalPayment.payment.modality_installment);
        ModalPayment.showAgencies();
    },
    events: function(){
        $('#modal_payment_deadline').datepicker({
            zIndex: 1091,
            format: 'dd/mm/yyyy',
            minDate: -3
        }).val(global.date2Br(global.today())).blur(function () {
            if ($(this).val().length != 10) {
                $(this).val(global.date2Br(ModalPayment.payment.payment_deadline));
            }
        });
        $('#modal_payment_entry').on('change',function(){
            $('#modal_payment_deadline').val(global.date2Br(global.today())).prop('disabled',$(this).prop('checked'));
        });
        $('#modal_payment_value').val(global.float2Br(ModalPayment.payment.payment_value)).blur(function () {
            ModalPayment.payment.payment_value = $(this).val().length ? global.br2Float($(this).val()) : ModalPayment.payment.payment_value;
            $(this).val(global.float2Br(ModalPayment.payment.payment_value));
            ModalPayment.installments(ModalPayment.payment.payment_installment);
        });
        $('#modal_modality_id').on('changed.bs.select',function(e, clickedIndex){
            var modality = Payment.modalities[clickedIndex-1];
            ModalPayment.payment.image = modality.image;
            ModalPayment.payment.nature_id = modality.nature_id;
            ModalPayment.payment.modality_type = modality.modality_type;
            ModalPayment.payment.modality_installment = modality.modality_installment;
            $('#modal_payment_entry').bootstrapToggle(modality.modality_entry == 'Y' ? 'on' : 'off');
            $('#modal_payment_entry').prop('disabled',modality.modality_type == 'D' || modality.modality_type == 'A');
            if( modality.modality_entry == 'N' ){
                var deadline = global.dateAddDays(global.today(),modality.modality_delay);
                $('#modal_payment_deadline').val(global.date2Br(deadline));
            }
            ModalPayment.check(modality.modality_type);
            ModalPayment.installments(modality.modality_installment);
        });
        $.each(Payment.modalities, function(key, modality){
            $('#modal_modality_id').append($('<option>',{
                value: modality.modality_id,
                text: modality.modality_code + ' - ' + modality.modality_description,
                'data-content': ( modality.image ? '<img src="' + modality.image + '" style="max-width:40px;max-height:18px;"/> ' : '<i class="fa fa-credit-card"></i> ' ) + modality.modality_code + ' - ' + modality.modality_description,
            }));
        });
        $.each(Payment.banks, function(key, bank){
            $('#modal_bank_id').append($('<option>',{
                value: bank.bank_id,
                text: bank.bank_name
            }));
        });
        $('#modal_bank_id').on('changed.bs.select',function(e, clickedIndex){
            ModalPayment.bank = Payment.banks[clickedIndex-1];
            ModalPayment.showAgencies();
        });
        $('#button-new-agency').click(function(){
            $('#modal_agency_id').selectpicker('val','default');
            $('#col-new-agency').show();
            $('#col-agency').hide();
            $('#modal_agency_id').prop('required',false);
            $('#modal_agency_code').prop('required',true);
        });
        $('#button-new-agency-cancel').click(function(){
            $('#modal_agency_code').val('');
            $('#col-agency').show();
            $('#col-new-agency').hide();
            $('#modal_agency_id').prop('required',true);
            $('#modal_agency_code').prop('required',false);
        });
        global.mask();
        global.toggle();
        global.tooltip();
        global.selectpicker();
        ModalPayment.installments(1);
        $('#modal_bank_id, #modal_modality_id').selectpicker('refresh');
    },
    form2data: function(){
        ModalPayment.payment = {
            image: ModalPayment.payment.image,
            bank_id: $('#modal_bank_id').val() || null,
            agency_id: $('#modal_agency_id').val() || null,
            agency_code: $('#modal_agency_code').val() || null,
            check_number: $('#modal_check_number').val() || null,
            modality_id: $('#modal_modality_id').val(),
            modality_type: ModalPayment.payment.modality_type,
            modality_description: $('#modal_modality_id option:selected').text(),
            modality_installment: ModalPayment.payment.modality_installment,
            payment_entry: $('#modal_payment_entry').prop('checked') ? 'Y' : 'N',
            payment_value: global.br2Float($('#modal_payment_value').val()),
            payment_installment: parseInt($('#modal_payment_installment').val()),
            payment_deadline: global.date2Us($('#modal_payment_deadline').val()),
            nature_id: ModalPayment.payment.nature_id
        };
    },
    installments: function(installment){
        var $select = $('#modal_payment_installment');
        $select.find('option').remove();
        for( var i=1; i<=installment; i++ ){
            $select.append($('<option>', {
                value: i,
                text: i + 'x (R$ ' + global.float2Br(ModalPayment.payment.payment_value/i) + ')',
                selected: i == ModalPayment.payment.payment_installment
            }));
        }
        $select.selectpicker('refresh');
        if( !installment ){
            global.validateMessage('A forma de pagamento <b>' + $('#modal_modality_id option:selected').text() + '</b> não possui convênio cadastrado para a empresa selecionada!<br/>Contacte o setor financeiro.')
        }
    },
    showAgencies: function(){
        var bank = Payment.banks[$('#modal_bank_id option:selected').index()-1];
        $('#modal_agency_id option').remove();
        if( bank && bank.agencies ){
            $.each(bank.agencies, function (key, agency) {
                $('#modal_agency_id').append($('<option>', {
                    value: agency.agency_id,
                    text: agency.agency_number + ' ' + agency.agency_name,
                    selected: agency.agency_id == ModalPayment.payment.agency_id
                }));
            });
        }
        $('#modal_agency_id').selectpicker('refresh');
    }
};
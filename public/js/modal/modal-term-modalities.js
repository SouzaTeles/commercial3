$(document).ready(function(){

    ModalTermModalities.setModalities();
    ModalTermModalities.setEntries();
    ModalTermModalities.showModalities();

});

ModalTermModalities = {
    entries: [],
    parcels: [],
    options: [],
    modal: $('#modal-term-modalities'),
    table: global.table({
        selector: '#table-term-modalities',
        scrollY: 186,
        scrollCollapse: 1,
        order: [[2,'asc']]
    }),
    get: function(key){
        var modality = ModalTermModalities.options[key];
        if( !modality.modality_installment ){
            global.validateMessage('A forma de pagamento <b>' + modality.modality_code + ' - ' + modality.modality_description + '</b> não possui convênio cadastrado para a empresa selecionada!<br/>Contate o setor financeiro.')
            return;
        }
        var start = 1;
        var installment = modality.modality_type == 'A' ? 1 : Term.term.term_installment;
        var budget_payment_value = parseInt((100*Budget.budget.budget_value_total-Budget.budget.credit.value)/installment)/100;
        Budget.budget.payments = [];
        if( !!modality.entry && !!modality.parcel ){
            Budget.budget.payments.push({
                bank_id: null,
                agency_id: null,
                agency_code: null,
                check_number: null,
                external_id: null,
                budget_payment_id: null,
                image: modality.image,
                nature_id: modality.nature_id,
                modality_id: modality.entry.modality_id,
                modality_type: modality.entry.modality_type,
                modality_description: modality.entry.modality_description,
                modality_installment: modality.modality_installment,
                budget_payment_value: budget_payment_value,
                budget_payment_installment: (modality.modality_type == 'A' ? Term.term.term_installment : 1),
                budget_payment_credit: 'N',
                budget_payment_entry: modality.modality_entry,
                budget_payment_deadline: global.dateAddDays(global.today(),modality.modality_delay)
            });
            start = 2;
            modality = modality.parcel;
        }
        for( var i=start; i<=installment; i++ ){
            if( i == Term.term.term_installment ){
                budget_payment_value = parseFloat((budget_payment_value + parseFloat((Budget.budget.budget_value_total-Budget.budget.credit.value-(Term.term.term_installment*budget_payment_value)).toFixed(2))).toFixed(2));
            }
            var deadline = global.dateAddDays(global.today(), Term.term.term_delay + ( (i-1) * Term.term.term_interval ));
            Budget.budget.payments.push({
                bank_id: null,
                agency_id: null,
                agency_code: null,
                check_number: null,
                external_id: null,
                budget_payment_id: null,
                image: modality.image,
                nature_id: modality.nature_id,
                modality_id: modality.modality_id,
                modality_type: modality.modality_type,
                modality_description: modality.modality_description,
                modality_installment: modality.modality_installment,
                budget_payment_value: budget_payment_value,
                budget_payment_installment: (modality.modality_type == 'A' ? Term.term.term_installment : 1),
                budget_payment_credit: 'N',
                budget_payment_entry: modality.modality_entry,
                budget_payment_deadline: deadline
            });
        }
        if( Budget.budget.credit.value > 0 ){
            Term.init();
            Term.data2form();
        }
        Payment.total();
        Payment.showList();
        ModalTermModalities.modal.modal('hide');
    },
    setEntries: function(){
        if( ModalTermModalities.entries.length && ModalTermModalities.parcels.length ) {
            $.each(ModalTermModalities.entries, function (k, entry) {
                $.each(ModalTermModalities.parcels, function (j, parcel) {
                    ModalTermModalities.options.push({
                        'modality_description': 'Entrada em ' + entry.modality_description + ' + [' + (Term.term.term_installment - 1) + 'x] ' + parcel.modality_description,
                        'entry': entry,
                        'parcel': parcel
                    });
                });
            });
        } else {
            ModalTermModalities.options = ModalTermModalities.options.concat(ModalTermModalities.entries);
            ModalTermModalities.options = ModalTermModalities.options.concat(ModalTermModalities.parcels);
        }
    },
    setModalities: function(){
        $.each(Term.term.modalities, function(key, modality){
            if( modality.term_modality_type == 'E' ){
                ModalTermModalities.entries.push(modality);
            } else {
                ModalTermModalities.parcels.push(modality);
            }
        });
    },
    showModalities: function(){
        $.each( ModalTermModalities.options, function( key, option ){
            var row = ModalTermModalities.table.row.add([
                ( option.image ? '<img src="' + option.image + '" />' : '<i class="fa fa-credit-card"></i>' ),
                option.modality_code,
                option.modality_description,
                Term.term.term_installment + 'x (R$ ' + global.float2Br(Budget.budget.budget_value_total / Term.term.term_installment) + ')'
            ]).node();
            $(row).click(function(){
                ModalTermModalities.get(key);
            })
        });
        ModalTermModalities.table.draw();
    }
};
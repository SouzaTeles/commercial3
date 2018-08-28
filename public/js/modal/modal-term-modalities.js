$(document).ready(function(){

    var entries = [];
    var parcels = [];
    var options = [];
    var $modal = $('#modal-term-modalities');

    $.each(Term.term.modalities, function(key, modality){
        if( modality.term_modality_type == 'E' ){
            entries.push(modality);
        } else {
            parcels.push(modality);
        }
    });

    if( entries.length && parcels.length ) {
        $.each(entries, function (k, entry) {
            $.each(parcels, function (j, parcel) {
                options.push({
                    'modality_description': 'Entrada em ' + entry.modality_description + ' + [' + (Term.term.term_installment - 1) + 'x] ' + parcel.modality_description,
                    'entry': entry,
                    'parcel': parcel
                });
            });
        });
    } else {
        options = options.concat(entries);
        options = options.concat(parcels);
    }

    var $table = $('#modal-table-payment-options');
    $.each( options, function( key, option ){
        $table.find('tbody').append(
            '<tr data-key="' + key + '">' +
            '<td><i class="fa fa-credit-card"></i></td>' +
            '<td>' + option.modality_code + '</td>' +
            '<td>' + option.modality_description + '</td>' +
            '<td>' + Term.term.term_installment + 'x (R$ ' + global.float2Br(Budget.budget.budget_value_total / Term.term.term_installment) + ')</td>' +
            '</tr>'
        );
    });

    $table.find('tbody tr').click(function(){
        var modality = options[$(this).attr('data-key')];
        if( !modality.modality_installment ){
            global.validateMessage('A forma de pagamento <b>' + modality.modality_code + ' - ' + modality.modality_description + '</b> não possui convênio cadastrado para a empresa selecionada!<br/>Contacte o setor financeiro.')
            return;
        }
        var start = 1;
        var installment = modality.modality_type == 'A' ? 1 : Term.term.term_installment;
        var budget_payment_value = parseInt((100*Budget.budget.budget_value_total)/installment)/100;
        Budget.budget.payments = [];
        if( !!modality.entry && !!modality.parcel ){
            Budget.budget.payments.push({
                bank_id: null,
                agency_id: null,
                agency_code: null,
                check_number: null,
                external_id: null,
                budget_payment_id: null,
                nature_id: modality.nature_id,
                modality_id: modality.entry.modality_id,
                modality_type: modality.entry.modality_type,
                modality_description: modality.entry.modality_description,
                modality_installment: modality.modality_installment,
                budget_payment_value: budget_payment_value,
                budget_payment_installment: (modality.modality_type == 'A' ? Term.term.term_installment : 1),
                budget_payment_credit: 'N',
                budget_payment_entry: 'Y',
                budget_payment_deadline: global.dateAddDays(global.today(),modality.modality_delay)
            });
            start = 2;
            modality = modality.parcel;
        }
        for( var i=start; i<=installment; i++ ){
            if( i == Term.term.term_installment ){
                budget_payment_value = parseFloat((budget_payment_value + parseFloat((Budget.budget.budget_value_total-(Term.term.term_installment*budget_payment_value)).toFixed(2))).toFixed(2));
            }
            var deadline = global.dateAddDays(global.today(), Term.term.term_delay + ( (i-1) * Term.term.term_interval ));
            Budget.budget.payments.push({
                bank_id: null,
                agency_id: null,
                agency_code: null,
                check_number: null,
                external_id: null,
                budget_payment_id: null,
                nature_id: modality.nature_id,
                modality_id: modality.modality_id,
                modality_type: modality.modality_type,
                modality_description: modality.modality_description,
                modality_installment: modality.modality_installment,
                budget_payment_value: budget_payment_value,
                budget_payment_installment: (modality.modality_type == 'A' ? Term.term.term_installment : 1),
                budget_payment_credit: 'N',
                budget_payment_entry: ( modality.term_modality_type == 'E' ? 'Y' : 'N' ),
                budget_payment_deadline: deadline
            });
        }
        Payment.total();
        Payment.showList();
        $modal.modal('hide');
    });
});
$(document).ready(function(){
    ModalCredit.events();
    ModalCredit.getList();
});

ModalCredit = {
    last: [],
    person: {},
    credits: [],
    selected: [],
    instance_id: '',
    table: global.table({
        selector: '#modal-table-credit',
        scrollX: 1,
        scrollY: 320,
        scrollCollapse: 1,
        order: [[2,'asc']],
        noControls: [0]
    }),
    cancel: function(){
        if( ModalCredit.last.length ){
            global.post({
                url: global.uri.uri_public_api + 'credit.php?action=redeem',
                data: {
                    payable: ModalCredit.last,
                    instance_id: ModalCredit.instance_id
                },
                dataType: 'json'
            },function(){
                $('#modal-credit').modal('hide');
            });
        } else {
            $('#modal-credit').modal('hide');
        }
    },
    events: function(){
        $('#button-credit-select').click(function(){
            $('#modal-credit').modal('hide');
            ModalCredit.success(ModalCredit.selected);
        });
        $('#button-credit-cancel').click(function(){
            ModalCredit.cancel();
        });
        ModalCredit.table.on('draw',function(){
            if( ModalCredit.selected.length ){
                $.each(ModalCredit.selected,function(key,credit){
                    $('#modal-credit input[data-id="' + credit.payable_id + '"]').prop('checked',true);
                });
            }
        });
    },
    getList: function(){
        global.post({
            url: global.uri.uri_public_api + 'credit.php?action=getList',
            data: {
                instance_id: ModalCredit.instance_id,
                person_id: ModalCredit.person.person_id
            },
            dataType: 'json'
        },function(credits){
            ModalCredit.credits = credits;
            ModalCredit.showList();
        });
    },
    pawn: function(data){
        global.post({
            url: global.uri.uri_public_api + 'credit.php?action=pawn',
            data: {
                payable_id: data.payable_id,
                instance_id: ModalCredit.instance_id
            },
            dataType: 'json'
        },function(){
            ModalCredit.selected.push(data);
            ModalCredit.last.push(data.payable_id);
            $('#modal-credit input[data-id="' + data.payable_id + '"]').prop('checked',true);
        });
    },
    select: function(key){
        var credit = ModalCredit.credits[key];
        if( credit.pawn ){
            global.validateMessage(
                '<p>Carta de crédito em uso:</p>' +
                '<p>' +
                    'Usuário: ' + credit.pawn.user_name + '<br/>' +
                    'Módulo: ' + credit.pawn.system_name + '<br/>' +
                    'Descrição: ' + credit.pawn.description + '<br/>' +
                '</p>'
            );
            return;
        }
        if( credit.company_id != ModalCredit.company_id ){
            global.validateMessage(
                '<p>' +
                    'A Carta de crédito não poderá ser utilizada em uma empresa diferente de sua origem.<br/>' +
                    'Contate o setor financeiro.' +
                '</p>'
            );
            return;
        }
        var index = -1;
        $.each( ModalCredit.selected, function(k,s){
             if( s.payable_id == credit.payable_id ){
                 index = k;
             }
        });
        if( index == -1 ){
            ModalCredit.pawn({
                payable_id: credit.payable_id,
                payable_value: credit.credit_value_available
            });
        } else {
            ModalCredit.redeem({
                index: index,
                payable_id: credit.payable_id
            });
        }
    },
    showList: function(){
        ModalCredit.table.clear();
        $.each( ModalCredit.credits, function(key, credit){
            var row = ModalCredit.table.row.add([
                '<input data-id="' + credit.payable_id + '" data-key="' + key + '" type="checkbox" />',
                credit.company_id,
                credit.payable_code,
                'R$' + global.float2Br(credit.credit_value),
                'R$' + global.float2Br(credit.credit_value_utilized),
                'R$' + global.float2Br(credit.credit_value_available),
                credit.payable_date,
                credit.pawn ? 'Sim' : 'Não'
            ]).node();
            $(row).addClass(credit.pawn ? 'txt-red-light' : '').attr('data-key',key).on('dblclick',function(){
                ModalCredit.select($(this).attr('data-key'));
            }).on('click',function(){
                $('#modal_credit_note').val(ModalCredit.credits[$(this).attr('data-key')].payable_note);
            }).find('input').change(function(){
                ModalCredit.select($(this).attr('data-key'));
            }).prop('disabled',(!!credit.pawn || credit.company_id != ModalCredit.company_id));
        });
        ModalCredit.table.draw();
    },
    redeem: function(data){
        global.post({
            url: global.uri.uri_public_api + 'credit.php?action=redeem',
            data: {
                payable: [data.payable_id],
                instance_id: ModalCredit.instance_id
            },
            dataType: 'json'
        },function(){
            ModalCredit.selected.splice(data.index,1);
            ModalCredit.last.splice(ModalCredit.last.indexOf(data.payable_id),1);
            $('#modal-credit input[data-id="' + data.payable_id + '"]').prop('checked',false);
        });
    }
};
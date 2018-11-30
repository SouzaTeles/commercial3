$(document).ready(function(){

    DropAudit.events();
    DropAudit.showCompanies();
    DropAudit.getList();

    global.mask();
    global.toggle();
    global.tooltip();
    global.unLoader();

});

DropAudit = {
    chartShowed: false,
    chart: {},
    drops: [],
    sellers: [],
    data: {
        company_id: null,
        seller_id: null,
        only_diff: 'Y',
        process_start_date: global.today(),
        process_end_date: global.today(),
        drop_start_date: moment().startOf('month').format('YYY-MM-DD'),
        drop_end_date: global.today()
    },
    type: {
        'B': {
            icon: 'file',
            title: 'Orçamento',
            color: 'green-light'
        },
        'P': {
            icon: 'file-powerpoint-o',
            title: 'Pedido de Venda',
            color: 'orange'
        },
        'D': {
            icon: 'file-text-o',
            title: 'DAV',
            color: 'blue'
        }
    },
    status: {
        'O': {
            icon: 'cloud',
            title: 'Aberto'
        },
        'L': {
            icon: 'cloud',
            title: 'Liberado'
        },
        'B': {
            icon: 'cloud-download',
            title: 'Faturado'
        },
        'C': {
            icon: 'cloud-download',
            title: 'Cancelado'
        }
    },
    typeahead: {
        items: 10,
        delay: 500,
        last: '',
        timer: 0
    },
    table: global.table({
        selector: '#table-drops',
        searching: 1,
        noControls: [10],
        order: [[1,'desc']],
        buttons: [{
            extend: 'collection',
            buttons: [{extend:'excel',exportOptions:{columns:[0,1,2,3,4,5,6,7,8,9]}}]
        }]
    }),
    actions: function(drop){
        return(
            '<div class="dropdown dropdown-budget dropdown-actions">' +
                '<button class="btn btn-empty-blue" type="button" data-toggle="dropdown">' +
                    '<i class="fa fa-ellipsis-v"></i>' +
                '</button>' +
                '<ul class="dropdown-menu dropdown-custom pull-right">' +
                    '<li><a data-action="edit" data-key="' + drop.key + '" data-id="' + drop.title_id + '" class="dropdown-item" href="#"><i class="fa fa-pencil txt-blue"></i>Editar</a></li>' +
                    '<li class="divider"></li>' +
                    '<li><a data-action="audit" data-key="' + drop.key + '" data-id="' + drop.title_id + '" class="dropdown-item" href="#"><i class="fa fa-shield txt-green"></i>Auditoria</a></li>' +
                    '<li><a data-action="pass" data-key="' + drop.key + '" data-id="' + drop.title_id + '" class="dropdown-item" href="#"><i class="fa fa-lock txt-orange"></i>Alterar Senha</a></li>' +
                '</ul>' +
            '</div>'
        );
    },
    events: function(){
        $('#form-filter').on('submit',function(e){
            e.preventDefault();
            e.stopPropagation();
            DropAudit.getList();
        });
        $('#process_start_date, #process_end_date, #drop_end_date').datepicker({
            format: 'dd/mm/yyyy'
        }).blur(function(){
            if( $(this).val().length != 10 ){
                $(this).val(global.date2Br(global.today()));
            }
        }).val(global.date2Br(global.today()));
        $('#drop_start_date').datepicker({
            format: 'dd/mm/yyyy'
        }).blur(function(){
            if( $(this).val().length != 10 ){
                $(this).val(moment().startOf('month').format('DD/MM/YYYY'));
            }
        }).val(moment().startOf('month').format('DD/MM/YYYY'));
        $('#drop_search').keyup(function(){
            DropAudit.table.search(this.value).draw();
        });
        $('#button-excel').click(function(){
            DropAudit.table.button('0-0').trigger();
        });
    },
    getList: function(){
        DropAudit.data.companies = $('#companies').val();
        DropAudit.data.only_diff = $('#only_diff').prop('checked') ? 'Y' : null;
        DropAudit.data.drop_start_date = global.date2Us($('#drop_start_date').val());
        DropAudit.data.drop_end_date = global.date2Us($('#drop_end_date').val());
        DropAudit.data.process_start_date = global.date2Us($('#process_start_date').val());
        DropAudit.data.process_end_date = global.date2Us($('#process_end_date').val());
        if( DropAudit.data.process_start_date.length != 10 || DropAudit.data.process_end_date.length != 10 ){
            global.validateMessage('<p>Verifique as datas informadas.</p>');
            return;
        }
        if( parseInt(DropAudit.data.process_start_date.split('-').join('')) > parseInt(DropAudit.data.process_end_date.split('-').join('')) ){
            global.validateMessage('<p>A data inicial não pode ser maior que a data final.</p>');
            return;
        }
        var diff = global.dateDiff(DropAudit.data.process_start_date,DropAudit.data.process_end_date);
        if( diff > 31 ){
            global.validateMessage('<p>Verifique o intervalo entre as datas selecionadas.<br/>O período máximo permitido será de 31 dias.</p>')
            return;
        }
        global.post({
            url: global.uri.uri_public_api + 'title.php?action=dropAudit',
            data: DropAudit.data,
            dataType: 'json'
        },function(drops){
            DropAudit.drops = drops;
            DropAudit.showList();
        });
    },
    showCompanies: function(){
        if( !global.login.companies.length ){
            global.validateMessage('Você não possui acesso as empresas. Procure o administrador do sistema.');
        }
        $.each( global.login.companies, function(key,company){
            $('#companies').append($('<option>',{
                'value': company.company_id,
                'data-content': '<i class="fa fa-stop" style="color:' + company.company_color + ';"></i> ' + ('0'+company.company_id).slice(-2) + ' - ' + company.company_name
            }));
        });
        $('#companies').selectpicker('refresh');
    },
    showList: function(){
        DropAudit.table.clear();
        var diff = 0;
        var title_value = 0;
        var title_drop_value = 0;
        var title_drop_value_total = 0;
        $.each( DropAudit.drops, function( key, drop ){
            drop.key = key;
            DropAudit.table.row.add([
                drop.company_id,
                drop.title_code,
                '<span>' + drop.title_value_order + '</span>R$' + global.float2Br(drop.title_value),
                '<span>' + drop.title_drop_value_order + '</span>R$' + global.float2Br(drop.title_drop_value),
                '<span>' + drop.title_drop_value_total_order + '</span>R$' + global.float2Br(drop.title_drop_value_total),
                '<span>' + drop.diff_order + '</span>R$' + global.float2Br(drop.diff),
                '<span>' + drop.title_drop_process + '</span>' + drop.title_drop_process_br,
                '<span>' + drop.title_drop_date + '</span>' + drop.title_drop_date_br,
                drop.user_name,
                drop.modality_description,
                DropAudit.actions(drop)
            ]);
            diff += drop.diff;
            title_value += drop.title_value;
            title_drop_value += drop.title_drop_value;
            title_drop_value_total += drop.title_drop_value_total;
        });
        DropAudit.table.draw();
        $('footer div[data-label="title-count"]').html('<i class="fa fa-files-o"></i> ' + DropAudit.drops.length);
        $('footer div[data-label="title-value"]').html('<i class="fa fa-usd"></i> R$ ' + global.float2Br(title_value));
        //$('footer div[data-label="title-drop-value"]').html('<i class="fa fa-usd"></i> R$ ' + global.float2Br(title_drop_value));
        $('footer div[data-label="title-drop-value-total"]').html('<i class="fa fa-usd"></i> R$ ' + global.float2Br(title_drop_value_total));
        $('footer div[data-label="title-diff"]').html('<i class="fa fa-usd"></i> R$ ' + global.float2Br(diff));
    }
};
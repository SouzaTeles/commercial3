$(document).ready(function(){

    Budget.events();
    Budget.showCompanies(function(){
        if( !!Budget.data.company_id ){
            Budget.getList();
        }
    });

    global.mask();
    global.tooltip();
    global.toggle();
    global.unLoader();

});

Budget = {
    budgets: [],
    selected: [],
    selectedIndex: [],
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
    origin: {
        'D': {
            icon: 'desktop',
            title: 'Desktop',
            color: 'blue-light',
            class: 'desktop'
        },
        'M': {
            icon: 'mobile',
            title: 'Celular',
            color: 'orange-light',
            class: 'mobile'
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
    delivery: {
        'Y': {
            icon: 'truck',
            title: 'Com Entrega',
            color: 'blue-light'
        },
        'N': {
            icon: 'truck',
            title: 'Sem Entrega',
            color: 'gray'
        }
    },
    data: {
        company_id: null,
        no_map: 'Y',
        start_date: global.today(),
        end_date: global.today()
    },
    filters: {
        status: [],
        delivery: []
    },
    table: global.table({
        selector: '#table-budgets',
        searching: 1,
        noControls: [0],
        order: [[2,'asc']],
        scrollY: $(window).height()-306,
        scrollCollapse: 1
    }),
    actions: function(budget){
        return(
            '<div class="dropdown dropdown-budget dropdown-actions">' +
                '<button class="btn btn-empty" type="button" data-toggle="dropdown">' +
                    '<i class="fa fa-ellipsis-v"></i>' +
                '</button>' +
                '<ul class="dropdown-menu pull-right">' +
                    '<li><a data-action="open" disabled="' + ( global.login.access.budget.open.value == 'N' ) + '" data-key="' + budget.key + '" data-id="' + budget.budget.id + '" class="dropdown-item" href="#"><i class="fa fa-folder-open-o"></i>Abrir</a></li>' +
                    '<li><a data-action="clone" disabled="' + ( global.login.access.budget.clone.value == 'N' ) + '" data-key="' + budget.key + '" data-id="' + budget.budget.id + '" class="dropdown-item" href="#"><i class="fa fa-clone txt-orange"></i>Duplicar</a></li>' +
                    '<li class="divider"></li>' +
                    '<li><a data-action="beforePrint" disabled="' + ( global.login.access.budget.print.value == 'N' ) + '" data-key="' + budget.key + '" data-id="' + budget.budget.id + '" class="dropdown-item" href="#"><i class="fa fa-print txt-green"></i>Imprimir</a></li>' +
                    '<li><a data-action="beforeDelivery" disabled="' + ( budget.budget.status == 'B' ) + '" data-key="' + budget.key + '" data-id="' + budget.budget.id + '" class="dropdown-item" href="#"><i class="fa fa-truck txt-blue"></i>Entrega</a></li>' +
                    '<li><a data-action="mail" disabled="' + ( global.login.access.budget.mail.value == 'N' ) + '" data-key="' + budget.key + '" data-id="' + budget.budget.id + '" class="dropdown-item" href="#"><i class="fa fa-envelope-o txt-blue"></i>E-mail</a></li>' +
                    '<li><a data-action="audit" disabled="' + ( global.login.access.budget.audit.value == 'N' ) + '" data-key="' + budget.key + '" data-id="' + budget.budget.id + '" class="dropdown-item" href="#"><i class="fa fa-shield txt-green"></i>Auditoria</a></li>' +
                    '<li class="divider"></li>' +
                    '<li><a data-action="beforeDel" disabled="' + ( budget.budget.status != 'O' || global.login.access.budget.del.value == 'N' ) + '" data-key="' + budget.key + '" data-id="' + budget.budget.id + '" class="dropdown-item" href="#"><i class="fa fa-trash-o txt-red"></i>Apagar</a></li>' +
                '</ul>' +
            '</div>'
        );
    },
    events: function(){
        $('#form-budget-filter').on('submit',function(e){
            e.preventDefault();
            e.stopPropagation();
            Budget.getList();
        });
        $('#budget_company_id').on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
            Budget.company = global.login.companies[clickedIndex-1];
            Budget.data.company_id = Budget.company.company_id;
            Budget.getList();
        });
        $('#budget_start_date, #budget_end_date').datepicker({
            format: 'dd/mm/yyyy'
        }).blur(function(){
            if( $(this).val().length != 10 ){
                $(this).val(global.date2Br(global.today()));
            }
        }).val(global.date2Br(global.today()));
        $('#budget_search').keyup(function(){
            Budget.table.search(this.value).draw();
        });
        $('#button-budget-new').click(function(){
            if( !Budget.data.company_id ){
                global.validateMessage('Selecione a Empresa.');
                return;
            }
            Budget.new();
        });
        $('button[data-action="status"]').click(function(){
            var value = $(this).attr('data-value');
            $(this).toggleClass('selected');
            var index = Budget.filters.status.indexOf(value);
            if( index > -1 ){
                Budget.filters.status.splice(index,1);
            } else {
                Budget.filters.status.push(value);
            }
            Budget.showList();
        });
        $('button[data-action="delivery"]').click(function(){
            var value = $(this).attr('data-value');
            $(this).toggleClass('selected');
            var index = Budget.filters.delivery.indexOf(value);
            if( index > -1 ){
                Budget.filters.delivery.splice(index,1);
            } else {
                Budget.filters.delivery.push(value);
            }
            Budget.showList();
        });
        $('#button-new-shipment').click(function(){
            Budget.newShipment();
        });
        $('#button-show-selected').click(function(){
            $('#selected-box').toggleClass('visible');
        });
        Budget.table.on('draw',function(){
            var table = $('#table-budgets');
            $(table).find('button[data-toggle="dropdown"]').unbind('click').click(function(){
                var top = $(this).parent().position().top;
                var documentHeight = $(document).innerHeight();
                var menuHeight = 236;
                if( top + menuHeight > documentHeight ){
                    $(this).next().css({
                        'top': 'auto',
                        'bottom': '100%'
                    });
                }
            });
            $(table).find('a[disabled="false"]').unbind('click').click(function(e){
                e.preventDefault();
                e.stopPropagation();
                $('.dropdown-budget').removeClass('open');
                Budget[$(this).attr('data-action')]($(this).attr('data-key'),$(this).attr('data-id'));
            });
            $(table).find('[data-toggle="tooltip"]').tooltip({container:'body'});
        });
        $('#table-budgets-selected tbody').sortable({update:function(){
            var selected = [];
            var selectedIndex = [];
            $('#table-budgets-selected tbody tr').each(function(key,tr){
                selected.push(Budget.selected[$(tr).attr('data-key')]);
                selectedIndex.push(Budget.selected[$(tr).attr('data-key')].budget_id);
                $(tr).attr('data-key',key);
            });
            Budget.selected = selected;
            Budget.selectedIndex = selectedIndex;
            Budget.distanceMatrix();
        }}).disableSelection();
        global.mask();
    },
    getList: function(){
        Budget.data.company_id = $('#budget_company_id').val();
        if( !Budget.data.company_id ){
            global.validateMessage('A empresa deverá ser selecionada.');
        }
        Budget.data.start_date = global.date2Us($('#budget_start_date').val());
        Budget.data.end_date = global.date2Us($('#budget_end_date').val());
        Budget.data.no_map = $('#no_map').prop('checked') ? 'Y' : null;
        if( Budget.data.start_date.length != 10 || Budget.data.end_date.length != 10 ){
            global.validateMessage('<p>Verifique as datas informadas.</p>');
            return;
        }
        if( parseInt(Budget.data.start_date.split('-').join('')) > parseInt(Budget.data.end_date.split('-').join('')) ){
            global.validateMessage('<p>A data inicial não pode ser maior que a data final.</p>');
            return;
        }
        var diff = global.dateDiff(Budget.data.start_date,Budget.data.end_date);
        if( diff > 31 ){
            global.validateMessage('<p>Verifique o intervalo entre as datas selecionadas.<br/>O período máximo permitido será de 31 dias.</p>')
            return;
        }
        global.post({
            url: global.uri.uri_public_api + 'budget.php?action=getMaps',
            data: Budget.data,
            dataType: 'json'
        },function(budgets){
            Budget.budgets = budgets;
            Budget.showList();
            Budget.selected = [];
            Budget.selectedIndex = [];
            Budget.showSelected();
        });
    },
    newShipment: function(){
        global.post({
            url: global.uri.uri_public + 'api/modal.php?modal=modal-new-shipment',
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'big',
                id: 'modal-new-shipment',
                class: 'modal-new-shipment',
                icon: 'fa-truck',
                title: 'Novo Mapa',
                html: html,
                buttons: [{
                    icon: 'fa-times',
                    title: 'Cancelar',
                    class: 'pull-left'
                },{
                    icon: 'fa-plus',
                    title: 'Adicionar Mapa',
                    unclose: true,
                    action: function(){
                        ModalNewShipment.add();
                    }
                }],
                shown: function(){
                    ModalNewShipment.table.columns.adjust().draw();
                }
            });
        });
    },
    distanceMatrix: function(){
        if( !Budget.selected.length ){
            Budget.showSelected();
            return;
        }
        var points = [];
        points.push(Budget.company.company_latitude + ',' + Budget.company.company_longitude);
        $.each(Budget.selected,function(key,budget){
            points.push(budget.city_name + '-' + budget.uf_id + ',' + budget.address_public_place + ',' + budget.district_name)
        });
        global.post({
            url: global.uri.uri_public_api + 'shipment.php?action=distanceMatrix',
            data: {
                points: points
            },
            dataType: 'json'
        },function(data){
            $.each(data,function(key,element){
                Budget.selected[key].distance = element.distance;
                Budget.selected[key].duration = element.duration;
            });
            Budget.showSelected();
        },function(){
            global.validateMessage('Não foi possível calcular a distância e tempos das entregas.');
            Budget.showSelected();
        });
    },
    showCompanies: function(success){
        if( !global.login.companies.length ){
            global.validateMessage('Você não possui acesso as empresas. Procure o administrador do sistema.');
        }
        $.each( global.login.companies, function(key,company){
            if( !company.parent_id ){
                $('#budget_company_id').append($('<option>',{
                    'value': company.company_id,
                    'selected': company.user_company_main == 'Y',
                    'data-content': '<i class="fa fa-stop" style="color:' + company.company_color + ';"></i> ' + ('0'+company.company_id).slice(-2) + ' - ' + company.company_name
                }));
                if( company.user_company_main == 'Y' ){
                    Budget.company = company;
                    Budget.data.company_id = company.company_id;
                }
            }
        });
        $('#budget_company_id').selectpicker('refresh');
        if( success ) success();
    },
    showList: function(){
        Budget.table.clear();
        $.each( Budget.budgets, function(key, budget){
            budget.key = key;
            var row = Budget.table.row.add([
                '<input data-budget-id="' + budget.budget_id + '" ' + ( !!budget.map_code ? 'disabled ' : '' )+ 'type="checkbox" value="" />',
                '<label>' + budget.person_code + '</label><div class="client">' + budget.person_name + '</div>',
                ( budget.external_code|| '--' ),
                ( budget.document_code || '--' ),
                ( budget.map_code || '--' ),
                budget.district_name,
                '<span>' + budget.gross_weight_order + '</span>' + global.float2Br(budget.gross_weight,0,3) + 'Kg',
            ]).node();
            if( !budget.map_code ){
                $(row).click(function(){
                    $(this).toggleClass('selected');
                    $(this).find('input').prop('checked',Budget.selectedIndex.indexOf(budget.budget_id) == -1);
                    var key = Budget.selectedIndex.indexOf(budget.budget_id);
                    if( key == -1 ){
                        Budget.selectedIndex.push(budget.budget_id);
                        Budget.selected.push(budget);
                    } else {
                        Budget.selectedIndex.splice(key,1);
                        Budget.selected.splice(key,1);
                    }
                    Budget.distanceMatrix();
                });
            }
        });
        Budget.table.draw();
    },
    showSelected: function(){
        var table = $('#table-budgets-selected');
        $(table).find('tbody tr').remove();
        var weight = 0;
        var distance = 0;
        var duration = 0;
        $.each( Budget.selected, function(key, budget){
            $(table).find('tbody').append(
                '<tr data-key="' + key + '">' +
                    '<td>' + budget.document_code + '</td>' +
                    '<td>' + budget.district_name + '</td>' +
                    '<td>' + global.float2Br(budget.gross_weight,3,3) + ' Kg</td>' +
                    '<td>' + global.formatDistance(budget.distance) + '</td>' +
                    '<td>' + global.formatDuration(budget.duration) + '</td>' +
                    '<td><button data-key="' + key + '" class="btn btn-empty-red-light"><i class="fa fa-trash-o"></i></button></td>' +
                '</tr>'
            );
            weight += budget.gross_weight;
            distance += budget.distance;
            duration += budget.duration;
        });
        $('#selected-weight').html('<span>Peso:</span> ' + global.float2Br(weight,3,3) + 'kg');
        $('#selected-distance').html('<span>Distância:</span> ' + global.formatDistance(distance));
        $('#selected-duration').html('<span>Tempo:</span> ' + global.formatDuration(duration));
        $(table).find('button').click(function(){
            var key = $(this).attr('data-key');
            var budget_id = Budget.selected[key].budget_id;
            Budget.selectedIndex.splice(key,1);
            Budget.selected.splice(key,1);
            $('#table-budgets').find('input[data-budget-id="' + budget_id + '"]').prop('checked',false).parents('tr').toggleClass('selected');
            Budget.showSelected();
        });
        $('#button-new-shipment').prop('disabled',!Budget.selected.length);
    },
    total: function(){
        var count = 0, total = 0;
        $.each( Budget.table.rows({filter: 'applied'})[0], function(k,key){
            count ++;
            total += Budget.budgets[key].budget.value_total;
        });
        $('footer div[data-label="budgets-count"]').html('<i class="fa fa-files-o"></i> ' + count);
        $('footer div[data-label="budgets-total"]').html('<i class="fa fa-money"></i> R$ ' + global.float2Br(total));
        $('footer div[data-label="budgets-average"]').html('<i class="fa fa-bar-chart"></i> R$ ' + global.float2Br(total/(count||1)));
    }
};
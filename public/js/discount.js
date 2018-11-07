$(document).ready(function(){
    Budget.events();
    Budget.getSellers();
    Budget.showCompanies(function(){
        if( !!Budget.data.company_id ){
            Budget.getList();
        }
    });
    global.mask();
    global.toggle();
    global.tooltip();
    global.unLoader();
});

Budget = {
    chartShowed: false,
    chart: {},
    budgets: [],
    sellers: [],
    data: {
        company_id: null,
        seller_id: null,
        only_discount: 'Y',
        start_date: global.today(),
        end_date: global.today()
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
        selector: '#table-budgets',
        searching: 1,
        noControls: [0,10],
        order: [[8,'desc']],
        buttons: [{
            extend: 'collection',
            buttons: [{extend:'excel',exportOptions:{columns:[1,2,3,4,5,6,7,8,9]}}]
        }]
    }),
    audit: function(key){
        global.post({
            url: global.uri.uri_public + 'api/modal.php?modal=modal-audit',
            data: {
                log_script: 'budget',
                log_parent_id: Budget.budgets[key].budget_id
            },
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'big',
                id: 'modal-audit',
                class: 'modal-audit',
                icon: 'fa-shield',
                title: 'Auditoria Orçamento ' + ('00000'+Budget.budgets[key].budget_id).slice(-6),
                html: html,
                buttons: [{
                    title: 'Fechar'
                }]
            });
        });
    },
    initChart: function(){
        Budget.chart = {
            keys: [],
            categories: [],
            series: [{
                name: 'Pedido',
                data: []
            },{
                name: 'Desconto',
                data: []
            }]
        };
    },
    showChart: function(){
        Highcharts.chart('chart', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'Gráfico de Descontos'
            },
            xAxis: {
                categories: Budget.chart.categories
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Valor total dos pedidos'
                },
                stackLabels: {
                    enabled: true,
                    style: {
                        fontWeight: 'bold',
                        color: 'gray'
                    }
                }
            },
            legend: {
                align: 'right',
                x: -30,
                verticalAlign: 'top',
                y: 25,
                floating: true,
                backgroundColor: 'white',
                borderColor: '#CCC',
                borderWidth: 1,
                shadow: false
            },
            tooltip: {
                headerFormat: '<b>{point.x}</b><br/>',
                pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}'
            },
            plotOptions: {
                column: {
                    stacking: 'normal',
                    dataLabels: {
                        enabled: true,
                        color: 'white'
                    }
                }
            },
            series: Budget.chart.series
        });
    },
    events: function(){
        $('#form-filter').on('submit',function(e){
            e.preventDefault();
            e.stopPropagation();
            Budget.getList();
        });
        $('#start_date, #end_date').datepicker({
            format: 'dd/mm/yyyy'
        }).blur(function(){
            if( $(this).val().length != 10 ){
                $(this).val(global.date2Br(global.today()));
            }
        }).val(global.date2Br(global.today()));
        $('#budget_search').keyup(function(){
            Budget.table.search(this.value).draw();
        });
        $('#button-chart').click(function(){
            if( Budget.chartShowed ){
                $('#chart').fadeOut(function(){
                    $('#chart').html('');
                });
            } else{
                $('#chart').fadeIn(function(){
                    Budget.showChart();
                });
            }
            Budget.chartShowed = !Budget.chartShowed;
        });
        $('#button-excel').click(function(){
            Budget.table.button('0-0').trigger();
        });
    },
    getList: function(){
        Budget.data.company_id = $('#company_id').val();
        Budget.data.seller_id = $('#seller_id').val();
        Budget.data.only_billed = $('#only_billed').prop('checked') ? 'Y' : 'N';
        Budget.data.only_discount = $('#only_discount').prop('checked') ? 'Y' : 'N';
        Budget.data.start_date = global.date2Us($('#start_date').val());
        Budget.data.end_date = global.date2Us($('#end_date').val());
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
            url: global.uri.uri_public_api + 'budget.php?action=getDiscounts',
            data: Budget.data,
            dataType: 'json'
        },function(budgets){
            Budget.budgets = budgets;
            Budget.showList();
        });
    },
    getSellers: function(){
        global.post({
            url: global.uri.uri_public_api + 'person.php?action=getList',
            data: {
                limit: 500,
                person_active: 'Y',
                person_category_id: global.config.person.seller_category_id
            },
            dataType: 'json'
        },function(data){
            Budget.sellers = data;
            Budget.showSellers();
        });
    },
    showCompanies: function(success){
        if( !global.login.companies.length ){
            global.validateMessage('Você não possui acesso as empresas. Procure o administrador do sistema.');
        }
        $.each( global.login.companies, function(key,company){
            $('#company_id').append($('<option>',{
                'value': company.company_id,
                'selected': company.user_company_main == 'Y',
                'data-content': '<i class="fa fa-stop" style="color:' + company.company_color + ';"></i> ' + ('0'+company.company_id).slice(-2) + ' - ' + company.company_name
            }));
            if( company.user_company_main == 'Y' ){
                Budget.company = company;
                Budget.data.company_id = company.company_id;
            }
        });
        $('#company_id').selectpicker('refresh');
        if( success ) success();
    },
    showList: function(){
        Budget.table.clear();
        var total_value = 0;
        var total_value_discount = 0;
        Budget.initChart();
        $.each( Budget.budgets, function( key, budget ){
            total_value += budget.budget_value;
            total_value_discount += budget.budget_value_discount;
            var type = Budget.type[budget.budget_status == 'O' ? 'B' : budget.budget_type];
            var status = Budget.status[budget.budget_status];
            if( Budget.chart.categories.indexOf(budget.seller_name) == -1 ){
                Budget.chart.keys.push(budget.seller_id);
                Budget.chart.categories.push(budget.seller_name);
                Budget.chart.series[0].data.push(0);
                Budget.chart.series[1].data.push(0);
            }
            Budget.chart.series[0].data[Budget.chart.keys.indexOf(budget.seller_id)] += budget.budget_value;
            Budget.chart.series[1].data[Budget.chart.keys.indexOf(budget.seller_id)] += budget.budget_value_discount;
            Budget.table.row.add([
                '<i data-toggle="tooltip" title="' + type.title + '" class="fa fa-' + status.icon + ' txt-' + (budget.budget_status != 'C' ? type.color : 'gray') + '"></i>',
                budget.company_id,
                (budget.external_code || '--'),
                (budget.document_code || '--'),
                budget.seller_name,
                budget.person_name,
                '<span>' + budget.budget_value_order + '</span>R$' + global.float2Br(budget.budget_value),
                '<span>' + budget.budget_value_discount_order + '</span>R$' + global.float2Br(budget.budget_value_discount),
                '<span>' + budget.budget_value_percent + '</span>' + global.float2Br(budget.budget_value_percent) + '%',
                '<span>' + budget.budget_date + '</span>' + budget.budget_date_br,
                '<button data-key="' + key + '" class="btn btn-empty-green"><i class="fa fa-shield"></i></button>'
            ]);
        });
        $.each(Budget.chart.series,function(k,serie){
            $.each(serie.data,function(j,value){
                Budget.chart.series[k].data[j] = parseFloat(value.toFixed(2));
            });
        });
        Budget.table.draw();
        global.tooltip();
        $('#table-budgets').find('button').click(function(){
            console.log($(this).attr('data-key'));
            Budget.audit($(this).attr('data-key'));
        });
        $('footer div[data-label="budgets-count"]').html('<i class="fa fa-files-o"></i> ' + Budget.budgets.length);
        $('footer div[data-label="budgets-value"]').html('<i class="fa fa-usd"></i> R$ ' + global.float2Br(total_value));
        $('footer div[data-label="budgets-value-discount"]').html('<i class="fa fa-usd"></i> R$ ' + global.float2Br(total_value_discount));
    },
    showSellers: function(){
        $.each( Budget.sellers, function(key,person){
            $('#seller_id').append($('<option>',{
                'value': person.person_id,
                'text': person.person_code + ' - ' + person.person_name
            }));
        });
        $('#seller_id').selectpicker('refresh');
    }
};
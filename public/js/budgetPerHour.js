$(document).ready(function(){
    BudgetPerHour.events();
    BudgetPerHour.getSellers();
    BudgetPerHour.showCompanies();
    BudgetPerHour.getList();
    global.mask();
    global.tooltip();
    global.unLoader();
});

BudgetPerHour = {
    field: 'count',
    pie1: [],
    pie2: [],
    pie3: [],
    stacked: {},
    budgets: [],
    sellers: [],
    data: {
        company_id: null,
        seller_id: null,
        only_discount: 'Y',
        start_date: global.today(),
        end_date: global.today()
    },
    initStacked: function(){
        BudgetPerHour.stacked = {
            keys: [],
            categories: [],
            series: [{
                name: 'Cupom',
                data: [],
                stack: 'document'
            },{
                name: 'Ordem de Entrega',
                data: [],
                stack: 'document'
            },{
                name: 'Nota Fiscal',
                data: [],
                stack: 'document'
            },{
                name: 'Orçamento',
                data: [],
                stack: 'budget'
            }]
        };
    },
    showChart: function(){
        BudgetPerHour.pie1 = [{
            name: 'Manhã',
            y: 0
        },{
            name: 'Almoço',
            y: 0
        },{
            name: 'Tarde',
            y: 0
        }];
        BudgetPerHour.pie2 = [{
            name: 'NFCe',
            y: 0
        },{
            name: 'OE',
            y: 0
        },{
            name: 'NFe',
            y: 0
        }];
        BudgetPerHour.pie3 = [{
            name: 'Faturado',
            y: 0
        },{
            name: 'Orçamento',
            y: 0
        }];
        BudgetPerHour.initStacked();
        $.each(BudgetPerHour.budgets,function(key,item){
            if( BudgetPerHour.stacked.categories.indexOf(item.hour) == -1 ){
                BudgetPerHour.stacked.categories.push(item.hour);
                BudgetPerHour.stacked.series[0].data.push(0);
                BudgetPerHour.stacked.series[1].data.push(0);
                BudgetPerHour.stacked.series[2].data.push(0);
                BudgetPerHour.stacked.series[3].data.push(0);
            }

            if( parseInt(item.hour) <= 11 ){
                BudgetPerHour.pie1[0].y += parseFloat(item[BudgetPerHour.field]);
            } else if( parseInt(item.hour) <= 14 ){
                BudgetPerHour.pie1[1].y += parseFloat(item[BudgetPerHour.field]);
            } else {
                BudgetPerHour.pie1[2].y += parseFloat(item[BudgetPerHour.field]);
            }

            switch( item.type ){
                case 'ECF':
                case 'NCF-e':
                case 'NFCe':
                    BudgetPerHour.pie2[0].y += parseFloat(item[BudgetPerHour.field]);
                    BudgetPerHour.pie3[0].y += parseFloat(item[BudgetPerHour.field]);
                    BudgetPerHour.stacked.series[0].data[BudgetPerHour.stacked.categories.indexOf(item.hour)] = parseFloat(item[BudgetPerHour.field]);
                break;
                case 'NF':
                case 'NFE':
                case 'NFF':
                    BudgetPerHour.pie2[1].y += parseFloat(item[BudgetPerHour.field]);
                    BudgetPerHour.pie3[0].y += parseFloat(item[BudgetPerHour.field]);
                    BudgetPerHour.stacked.series[1].data[BudgetPerHour.stacked.categories.indexOf(item.hour)] = parseFloat(item[BudgetPerHour.field]);
                break;
                case 'OE':
                    BudgetPerHour.pie2[2].y += parseFloat(item[BudgetPerHour.field]);
                    BudgetPerHour.pie3[0].y += parseFloat(item[BudgetPerHour.field]);
                    BudgetPerHour.stacked.series[2].data[BudgetPerHour.stacked.categories.indexOf(item.hour)] = parseFloat(item[BudgetPerHour.field]);
                break;
                default:
                    BudgetPerHour.pie3[1].y += parseFloat(item[BudgetPerHour.field]);
                    BudgetPerHour.stacked.series[3].data[BudgetPerHour.stacked.categories.indexOf(item.hour)] = parseFloat(item[BudgetPerHour.field]);
                break;
            }
        });

        BudgetPerHour.pie1[0].y = parseFloat(BudgetPerHour.pie1[0].y.toFixed(2));
        BudgetPerHour.pie1[1].y = parseFloat(BudgetPerHour.pie1[1].y.toFixed(2));
        BudgetPerHour.pie1[2].y = parseFloat(BudgetPerHour.pie1[2].y.toFixed(2));
        BudgetPerHour.pie2[0].y = parseFloat(BudgetPerHour.pie2[0].y.toFixed(2));
        BudgetPerHour.pie2[1].y = parseFloat(BudgetPerHour.pie2[1].y.toFixed(2));
        BudgetPerHour.pie2[2].y = parseFloat(BudgetPerHour.pie2[2].y.toFixed(2));
        BudgetPerHour.pie3[0].y = parseFloat(BudgetPerHour.pie3[0].y.toFixed(2));
        BudgetPerHour.pie3[1].y = parseFloat(BudgetPerHour.pie3[1].y.toFixed(2));

        BudgetPerHour.showStacked();
        BudgetPerHour.showPie1();
        BudgetPerHour.showPie2();
        BudgetPerHour.showPie3();
    },
    events: function(){
        $('#form-filter').on('submit',function(e){
            e.preventDefault();
            e.stopPropagation();
            BudgetPerHour.getList();
        });
        $('#start_date, #end_date').datepicker({
            format: 'dd/mm/yyyy'
        }).blur(function(){
            if( $(this).val().length != 10 ){
                $(this).val(global.date2Br(global.today()));
            }
        }).val(global.date2Br(global.today()));
        $('#budget_search').keyup(function(){
            BudgetPerHour.table.search(this.value).draw();
        });
        $('#button-chart').click(function(){
            if( BudgetPerHour.chartShowed ){
                $('#chart').fadeOut(function(){
                    $('#chart').html('');
                });
            } else{
                $('#chart').fadeIn(function(){
                    BudgetPerHour.showChart();
                });
            }
            BudgetPerHour.chartShowed = !BudgetPerHour.chartShowed;
        });
        $('#button-excel').click(function(){
            BudgetPerHour.table.button('0-0').trigger();
        });
        $('#show_value').bootstrapToggle({
            width: '140px',
            on: '<i class="fa fa-files-o"></i> Quantidade',
            off: '<i class="fa fa-usd"></i> Valor',
            onstyle: 'blue',
            offstyle: 'blue'
        }).on('change',function(){
            BudgetPerHour.field = this.checked ? 'count' : 'value';
            BudgetPerHour.showChart();
        });
    },
    getList: function(){
        BudgetPerHour.data.company_id = $('#company_id').val();
        BudgetPerHour.data.seller_id = $('#seller_id').val();
        BudgetPerHour.data.start_date = global.date2Us($('#start_date').val());
        BudgetPerHour.data.end_date = global.date2Us($('#end_date').val());
        if( BudgetPerHour.data.start_date.length != 10 || BudgetPerHour.data.end_date.length != 10 ){
            global.validateMessage('<p>Verifique as datas informadas.</p>');
            return;
        }
        if( parseInt(BudgetPerHour.data.start_date.split('-').join('')) > parseInt(BudgetPerHour.data.end_date.split('-').join('')) ){
            global.validateMessage('<p>A data inicial não pode ser maior que a data final.</p>');
            return;
        }
        global.post({
            url: global.uri.uri_public_api + 'budget.php?action=getPerHour',
            data: BudgetPerHour.data,
            dataType: 'json'
        },function(budgets){
            BudgetPerHour.budgets = budgets;
            BudgetPerHour.showChart();

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
            BudgetPerHour.sellers = data;
            BudgetPerHour.showSellers();
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
                'data-content': '<i class="fa fa-stop" style="color:' + company.company_color + ';"></i> ' + ('0'+company.company_id).slice(-2) + ' - ' + (company.company_short_name || company.company_name)
            }));
            if( company.user_company_main == 'Y' ){
                BudgetPerHour.company = company;
                BudgetPerHour.data.company_id = company.company_id;
            }
        });
        $('#company_id').selectpicker('refresh');
        if( success ) success();
    },
    showSellers: function(){
        $.each( BudgetPerHour.sellers, function(key,person){
            $('#seller_id').append($('<option>',{
                'value': person.person_id,
                'text': person.person_code + ' - ' + person.person_name
            }));
        });
        $('#seller_id').selectpicker('refresh');
    },
    showPie1: function(){
        Highcharts.chart('pie1-chart', {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: 'Hora do Orçamento'
            },
            tooltip: {
                pointFormat: '{series.name} <b>{point.y} / {point.percentage:.1f}%</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: false
                    },
                    showInLegend: true
                }
            },
            series: [{
                name: 'Orçamentos',
                colorByPoint: true,
                data: BudgetPerHour.pie1
            }]
        });
    },
    showPie2: function(){
        Highcharts.chart('pie2-chart', {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: 'Tipos de Documento'
            },
            tooltip: {
                pointFormat: '{series.name} <b>{point.y} / {point.percentage:.1f}%</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: false
                    },
                    showInLegend: true
                }
            },
            series: [{
                name: 'Orçamentos',
                colorByPoint: true,
                data: BudgetPerHour.pie2
            }]
        });
    },
    showPie3: function(){
        Highcharts.chart('pie3-chart', {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: 'Aproveitamento'
            },
            tooltip: {
                pointFormat: '{series.name} <b>{point.y} / {point.percentage:.1f}%</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: false
                    },
                    showInLegend: true
                }
            },
            series: [{
                name: 'Orçamentos',
                colorByPoint: true,
                data: BudgetPerHour.pie3
            }]
        });
    },
    showStacked: function(){
        Highcharts.chart('stacked-chart', {
            chart: {
                type: 'column'
            },
            title: {
                text: 'Gráfico por Hora'
            },
            xAxis: {
                categories: BudgetPerHour.stacked.categories
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Pedidos x Hora'
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
            series: BudgetPerHour.stacked.series
        });
    }
};
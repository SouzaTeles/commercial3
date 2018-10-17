
$(document).ready(function(){
    Shipment.events();
    global.unLoader();

});

Shipment = {

    //Informações do Formulario
    data: {
        company_id: null,
        driver_name: null,
        start_date: global.today(),
        end_date: global.today(),
        shipment_code: null,
        shipment_driver_name: null
    },

    //Array de Status do mapa.
    status: {

        'A': {
            icon: 'clock-o',
            title: 'Aberto',
            color: 'gray'
        },

        'L': {
            icon: 'cloud',
            title: 'Liberado',
            color: 'blue'
        }

    },

    table: global.table({
        selector: '#table-shipment-docs',
        searching: 1,
        // scrollY: $(window).innerHeight()-372,
        // scrollCollapse: 1,
        noControls: [0,6],
        order: [[1,'desc']]
    }),

    //Busca as empresas às quais o usuario possui acesso.
    showCompanies: function(success){
        if( !global.login.companies.length ){
            global.validateMessage('Você não possui acesso as empresas. Contacte o administrador do sistema.');
        }
        $.each( global.login.companies, function(key,company){
            $('#shipment_company_id').append($('<option>',{
                'value': company.company_id,
                'selected': company.user_company_main == 'Y',
                'data-content': '<i class="fa fa-stop" style="color:' + company.company_color + ';"></i> ' + ('0'+company.company_id).slice(-2) + ' - ' + company.company_name
            }));
            if( company.user_company_main == 'Y' ){
                //Budget.data.company_id = company.company_id;
            }
        });
        $('#shipment_company_id').selectpicker('refresh');
        if( success ) success();
    },

    //Busca os mapas de carregamento de acordo com o filtro informado
    getList: function(){
        Shipment.data.company_id = $('#shipment_company_id').val();
        Shipment.data.start_date = global.date2Us($('#shipment_start_date').val());
        Shipment.data.end_date = global.date2Us($('#shipment_end_date').val());
        Shipment.data.shipment_code = $('#shipment_code').val();
        Shipment.data.shipment_driver_name = $('#shipment_driver_name').val();
        global.post({
            url: global.uri.uri_public_api + 'shipment.php?action=getList',
            data: Shipment.data,
            dataType: 'json'
        },function(documents){
            Shipment.documents = documents;
            console.log(documents);
            Shipment.showList();
        });
    },

    get: function () {
        global.post({
            url: global.uri.uri_public_api + 'shipment.php?action=get',
            data:{
                shipment_code:$("#shipment_code").val()
            },
            dataType: 'json'
        },function(documents){
            console.log("Ta entrando na função...");
            Shipment.documents = documents;
            console.log(documents);
            Shipment.showList();
        });
    },

    //Monta a tabela e exibe as informações
    showList: function(){

        //Limpa a tabela
        Shipment.table.clear();

        //Entra no loop de exibição para cada item do array de mapas
        $.each(Shipment.documents, function (key, document) {

            console.log(document.shipment_status)
            //Adiciona uma nova linha na lista de exibição
            var status = Shipment.status[document.shipment_status]

            //console.log(status)
            Shipment.table.row.add([
                '<i data-toggle="tooltip" title="' + status.title + '" class="fa fa-' + status.icon + ' text-' + status.color + '"></i>',
                document.shipment_code,
                document.shipment_name,
                document.shipment_date,
                //'<div class="person-cover"' + ( document.driver_image? 'style="background-image:url(' + document.driver_image+ ')"' : '' ) + '></div><label>' + document.driver_image + '</label><div class="seller">' + <!--( budget.seller.short_name || budget.seller.name )--> + '</div>',
                '<div class="person-cover"' + ( document.driver_image? 'style="background-image:url(' + document.driver_image+ ')"' : '' ) + '></div><label>' + '</label><div class="seller">' + <!--( budget.seller.short_name || budget.seller.name )--!>  '</div>',
                document.shipment_driver,
                '',
                ''
            ])
        })
        Shipment.table.draw();
    },

    events: function () {
        //Seletor de data e validações
        $('#shipment_start_date, #shipment_end_date').datepicker({
            format: 'dd/mm/yyyy'
        }).blur(function(){
            if( $(this).val().length != 10 ){
                $(this).val(global.date2Br(global.today()));
            }
        }).val(global.date2Br(global.today()));

        //Evento do botão de Atualizar
        $('#btn-filter').click(function(e){
            Shipment.getList();
            //Trata a tentativa de submissão repetitiva
            e.preventDefault();
            e.stopPropagation();
        });

        $('#button-shipment-new').click(function(){
            global.window({url: "http://www.commercial3.net/commercial3/window.php?module=shipment&action=new"});
        });

        //Evento da busca rapida pelo codigo do mapa
        $('#shipment_code').on('keyup', function(event){
            var key = event.keyCode || event.wich;
            if(key == 13)
                Shipment.get();
        });
    }
}
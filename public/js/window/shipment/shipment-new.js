$(document).ready(function(){

    //Verifica se o usuario tem acesso a criar um novo mapa.
    if(global.login.access.shipment.add.value == 'Y'){
        global.mask();
        New.events();
        New.showCompanies(function(){
           // New.getList();
        });

    } else {
        window.close();
    }
    global.unLoader();
});

New = {
    showList: function(){

        //Limpa a tabela
        New.table.clear();

        //Entra no loop de exibição para cada item do array de mapas
        $.each(New.documents, function (key, document) {

            console.log(document.shipment_status)
            //Adiciona uma nova linha na lista de exibição
            var status = New.status[document.shipment_status]

            //console.log(status)
            New.table.row.add([
                '<i data-toggle="tooltip" title="' + status.title + '" class="fa fa-' + status.icon + ' text-' + status.color + '"></i>',
                document.shipment_code,
                document.shipment_name,
                document.shipment_date,
                '<div class="person-cover"' + ( document.driver_image? 'style="background-image:url(' + document.driver_image+ ')"' : '' ) + '></div><label>' + document.driver_image + '</label><div class="seller">' + <!--( budget.seller.short_name || budget.seller.name )--> + '</div>',
                document.shipment_driver,
                '',
                ''
            ])
        })
        New.table.draw();
    },

    events: function(){
        $('#document_key').on('keyup', function(event){
            console.log("Entrou no evento do input...")
            var key = event.keyCode || event.wich;
            if(key == 13)
                New.get();
        });
    },

    get: function () {
        global.post({
            url: global.uri.uri_public_api + 'shipment_new.php?action=get',
            data:{
                document_key:$("#document_key").val()
            },
            dataType: 'json'
        },function(documents){
            New.documents = documents;
            console.log(documents);
            //Shipment.showList();
        });
    },

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

}
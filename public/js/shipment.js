$(document).ready(function(){

    Shipment.events();
    Shipment.getDrivers();
    Shipment.showCompanies(function(){
        Shipment.getList();
    });

    global.mask();
    global.unLoader();

});

Shipment = {
    data: {
        driver_id: null,
        company_id: null,
        shipment_code: null,
        start_date: global.today(),
        end_date: global.today()
    },
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
        },
        'C': {
            icon: 'cloud',
            title: 'Cancelado',
            color: 'red'
        }
    },
    drivers: [],
    shipments: [],
    table: global.table({
        selector: '#table-shipments',
        searching: 1,
        noControls: [0,7],
        order: [[1,'desc']]
    }),
    actions: function(shipment){
        return(
            '<div class="dropdown dropdown-budget dropdown-actions">' +
                '<button class="btn btn-empty" type="button" data-toggle="dropdown">' +
                    '<i class="fa fa-ellipsis-v"></i>' +
                '</button>' +
                '<ul class="dropdown-menu pull-right">' +
                    '<li><a data-action="open" disabled="' + ( global.login.access.shipment.open.value == 'N' ) + '" data-key="' + shipment.key + '" data-id="' + shipment.shipment_id + '" class="dropdown-item" href="#"><i class="fa fa-folder-open-o txt-yellow"></i>Abrir</a></li>' +
                    '<li><a data-action="map" disabled="' + ( global.login.access.shipment.map.value == 'N' ) + '" data-key="' + shipment.key + '" data-id="' + shipment.shipment_id + '" class="dropdown-item" href="#"><i class="fa fa-map-marker txt-red"></i>Mapa</a></li>' +
                    '<li class="divider"></li>' +
                    '<li><a data-action="print" disabled="' + ( global.login.access.shipment.print.value == 'N' ) + '" data-key="' + shipment.key + '" data-id="' + shipment.shipment_id + '" class="dropdown-item" href="#"><i class="fa fa-print txt-orange"></i>Imprimir</a></li>' +
                    '<li><a data-action="audit" disabled="' + ( global.login.access.shipment.audit.value == 'N' ) + '" data-key="' + shipment.key + '" data-id="' + shipment.shipment_id + '" class="dropdown-item" href="#"><i class="fa fa-shield txt-red"></i>Auditoria</a></li>' +
                    '<li><a data-action="beforeDel" disabled="' + ( shipment.shipment_status != 'A' || global.login.access.shipment.del.value == 'N' ) + '" data-key="' + shipment.key + '" data-id="' + shipment.shipment_id + '" class="dropdown-item" href="#"><i class="fa fa-trash-o txt-red"></i>Apagar</a></li>' +
                '</ul>' +
            '</div>'
        );
    },
    events: function(){
        $('#start_date, #end_date').datepicker({
            format: 'dd/mm/yyyy'
        }).blur(function(){
            if( $(this).val().length != 10 ){
                $(this).val(global.date2Br(global.today()));
            }
        }).val(global.date2Br(global.today()));
        $('#btn-filter').click(function(e){
            Shipment.getList();
            e.preventDefault();
            e.stopPropagation();
        });
        $('#button-shipment-new').click(function(){
            Shipment.new();
        });
        $('#shipment_code').on('keyup', function(event){
            var key = event.keyCode || event.wich;
            if(key == 13)
                Shipment.get();
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
    getDrivers: function(){
        global.post({
            url: global.uri.uri_public_api + 'person.php?action=getInternalList',
            data: {
                categories: [2],
                functions: [2]
            },
            dataType: 'json'
        },function(data){
            Shipment.drivers = data;
            Shipment.showDrivers();
        });
    },
    getList: function(){
        Shipment.data.company_id = $('#company_id').val();
        Shipment.data.start_date = global.date2Us($('#start_date').val());
        Shipment.data.end_date = global.date2Us($('#end_date').val());
        Shipment.data.shipment_code = $('#shipment_code').val();
        Shipment.data.driver_id = $('#driver_id').val();
        global.post({
            url: global.uri.uri_public_api + 'shipment.php?action=getList',
            data: Shipment.data,
            dataType: 'json'
        },function(data){
            Shipment.shipments = data;
            Shipment.showList();
        });
    },
    map: function(key,shipment_id){
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-shipment-map',
            data: { shipment_id: shipment_id },
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'big',
                icon: 'fa-map-marker',
                id: 'modal-shipment-map',
                class: 'modal-shipment-map',
                title: 'Mapa ' + Shipment.shipments[key].external_code,
                html: html,
                buttons: [{
                    icon: 'fa-check',
                    title: 'Ok'
                }],
                hidden: function(){
                    map.destroy();
                }
            });
        });
    },
    new: function(){
        global.window({
            url: global.uri.uri_public + 'window.php?module=shipment&action=new&company_id=' + $('#company_id').val()
        });
    },
    showDrivers: function(){
        $.each( Shipment.drivers, function(key,driver){
            $('#driver_id').append($('<option>',{
                'value': driver.person_id,
                'text': driver.external_code + ' - ' + driver.person_name
            }));
        });
        $('#driver_id').selectpicker('refresh');
    },
    showList: function(){
        Shipment.table.clear();
        $.each(Shipment.shipments, function (key, shipment) {
            var status = Shipment.status[shipment.shipment_status];
            shipment.key = key;
            Shipment.table.row.add([
                '<i data-toggle="tooltip" title="' + status.title + '" class="fa fa-' + status.icon + ' text-' + status.color + '"></i>',
                shipment.external_code,
                shipment.driver_name,
                '<span>' + shipment.shipment_reference + '</span>' + shipment.shipment_reference_br,
                '<span>' + shipment.weight_order + '</span>' + global.float2Br(shipment.weight,3,3) + 'Kg',
                '<span>' + shipment.distance_order + '</span>' + global.formatDistance(shipment.distance),
                '<span>' + shipment.duration_order + '</span>' + global.formatDuration(shipment.duration),
                Shipment.actions(shipment)
            ]);
        });
        Shipment.table.draw();
        var $table = $('#table-shipments');
        $table.find('a[disabled="false"]').click(function(e){
            e.preventDefault();
            e.stopPropagation();
            $('.dropdown-budget').removeClass('open');
            Shipment[$(this).attr('data-action')]($(this).attr('data-key'),$(this).attr('data-id'));
        });
        $table.find('[data-toggle="tooltip"]').tooltip({container:'body'});
    },
    showCompanies: function(success){
        if( !global.login.companies.length ){
            global.validateMessage('Você não possui acesso as empresas. Contacte o administrador do sistema.');
        }
        $.each( global.login.companies, function(key,company){
            if( !company.parent_id ){
                $('#company_id').append($('<option>',{
                    'value': company.company_id,
                    'selected': company.user_company_main == 'Y',
                    'data-content': '<i class="fa fa-stop" style="color:' + company.company_color + ';"></i> ' + ('0'+company.company_id).slice(-2) + ' - ' + company.company_name
                }));
                if( company.user_company_main == 'Y' ){
                    Shipment.data.company_id = company.company_id;
                }
            }
        });
        $('#company_id').selectpicker('refresh');
        if( success ) success();
    }
};
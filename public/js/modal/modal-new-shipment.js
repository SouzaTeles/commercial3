$(document).ready(function(){

    ModalNewShipment.getVehicles();
    ModalNewShipment.getRoutes();
    ModalNewShipment.getDrivers();
    ModalNewShipment.getHelpers();
    ModalNewShipment.showSelected();

    $('#modal_shipment_reference').datepicker({
        format: 'dd/mm/yyyy',
        zIndex: 1051
    }).blur(function(){
        if( $(this).val().length != 10 ){
            $(this).val(global.date2Br(global.today()));
        }
    }).val(global.date2Br(global.today()));

    global.mask();
    global.selectpicker();

});

ModalNewShipment = {
    vehicles: [],
    drivers: [],
    routes: [],
    helpers: [],
    table: global.table({
        selector: '#modal-table-budgets',
        scrollX: 1,
        scrollY: 320,
        scrollCollapse: 1,
        order: [[0,'asc']]
    }),
    add: function(){
        var shipment = {
            company_id: $('#budget_company_id').val(),
            description: $('#modal_shipment_description').val(),
            vehicle: ModalNewShipment.vehicles[$('#modal_vehicle_id option:selected').index()-1],
            driver: ModalNewShipment.drivers[$('#modal_vehicle_id option:selected').index()-1],
            routes: ModalNewShipment.getSelectedRouters(),
            helpers: ModalNewShipment.getSelectedHelpers(),
            reference: global.date2Us($('#modal_shipment_reference').val()),
            documents: []
        };
        $.each( Budget.selected, function(key, budget){
            shipment.documents.push({
                document_id: budget.document_id,
                weight: budget.gross_weight,
                distance: budget.distance,
                duration: budget.duration
            });
        });
        if( !shipment.vehicle ){
            global.validateMessage('O Veículo deverá ser selecionado.');
            return;
        }
        if( !shipment.driver ){
            global.validateMessage('O Motorista deverá ser selecionado.');
            return;
        }
        if( !shipment.routes.length ){
            global.validateMessage('Pelo menos uma rota deverá ser selecionada.');
            return;
        }
        if( !shipment.helpers.length ){
            global.validateMessage('Pelo menos um ajudante deverá ser selecionado.');
            return;
        }
        global.post({
            url: global.uri.uri_public_api + 'shipment.php?action=addSingle',
            data: shipment,
            dataType: 'json'
        },function(data){
            $('#modal-new-shipment').modal('hide');
            $('#selected-box').toggleClass('visible');
            Budget.getList();
            global.modal({
                size: 'small',
                icon: 'fa-info',
                title: 'Informação',
                html: '<p>O mapa <b>' + data.external_code + '</b> foi criado com sucesso!',
                buttons: [{
                    title: 'Fechar'
                }]
            });
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
            ModalNewShipment.drivers = data;
            ModalNewShipment.showDrivers();
        });
    },
    getHelpers: function(){
        global.post({
            url: global.uri.uri_public_api + 'person.php?action=getInternalList',
            data: {
                categories: [2],
                functions: [3]
            },
            dataType: 'json'
        },function(data){
            ModalNewShipment.helpers = data;
            ModalNewShipment.showHelpers();
        });
    },
    getRoutes: function(){
        global.post({
            url: global.uri.uri_public_api + 'route.php?action=getList',
            dataType: 'json'
        },function(data){
            ModalNewShipment.routes = data;
            ModalNewShipment.showRoutes();
        });
    },
    getSelectedRouters: function(){
        var data = [];
        var selected = $('#modal_routes').val();
        $.each( ModalNewShipment.routes, function(key,route){
            if( selected.indexOf(route.route_id) != -1 ){
                data.push(route);
            }
        });
        return data;
    },
    getSelectedHelpers: function(){
        var data = [];
        var selected = $('#modal_helpers').val();
        $.each( ModalNewShipment.helpers, function(key,helper){
            if( selected.indexOf(helper.person_id) != -1 ){
                data.push(helper);
            }
        });
        return data;
    },
    getVehicles: function(){
        global.post({
            url: global.uri.uri_public_api + 'vehicle.php?action=getList',
            dataType: 'json'
        },function(data){
            ModalNewShipment.vehicles = data;
            ModalNewShipment.showVehicles();
        });
    },
    showDrivers: function(){
        $.each( ModalNewShipment.drivers, function(key,driver){
            $('#modal_driver_id').append($('<option>',{
                'value': driver.person_id,
                'text': driver.external_code + ' - ' + driver.person_name
            }));
        });
        $('#modal_driver_id').selectpicker('refresh');
    },
    showHelpers: function(){
        $.each( ModalNewShipment.helpers, function(key,helper){
            $('#modal_helpers').append($('<option>',{
                'value': helper.person_id,
                'text': helper.external_code + ' - ' + helper.person_name
            }));
        });
        $('#modal_helpers').selectpicker('refresh');
    },
    showVehicles: function(){
        $.each( ModalNewShipment.vehicles, function(key,vehicle){
            $('#modal_vehicle_id').append($('<option>',{
                'value': vehicle.vehicle_id,
                'text': vehicle.vehicle_plate + ' - ' + vehicle.vehicle_model
            }));
        });
        $('#modal_vehicle_id').selectpicker('refresh');
    },
    showRoutes: function(){
        $.each( ModalNewShipment.routes, function(key,route){
            $('#modal_routes').append($('<option>',{
                'value': route.route_id,
                'text': route.route_name
            }));
        });
        $('#modal_routes').selectpicker('refresh');
    },
    showSelected: function(){
        ModalNewShipment.table.clear();
        $.each( Budget.selected, function(key, budget){
            ModalNewShipment.table.row.add([
                budget.document_code,
                budget.person_name,
                budget.district_name,
                global.float2Br(budget.gross_weight,3,3) + 'Kg',
                global.formatDistance(budget.distance),
                global.formatDuration(budget.duration)
            ]);
        });
        ModalNewShipment.table.draw();
    }
};
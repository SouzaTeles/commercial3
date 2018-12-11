$(document).ready(function () {
    global.unLoader();
    Vehicle.events();
    Vehicle.getList();
});

Vehicle = {
    //vehicles: null,
    table: global.table({
        autoWidth: true,
        selector: '#table-vehicles',
        //searching: 1,
        scrollY: $(window).innerHeight() - 372,
        scrollCollapse: 1,
        // noControls: [0, 3],
        order: [
            [0, 'asc']
        ]
    }),
    events: function () {

        $('#vehicle_year').mask('9999');
        $('#vehicle_plate').mask('AAAAAAA');
        $('#btn-filter').click(function () {
            global.post({
                url: global.uri.uri_public_api + 'vehicle.php?action=getList',
                data: $('#form-vehicle-filter').serialize(),
                dataType: "json"
            }, function(data){
                console.log(data);
                if(data.length == 0){
                    global.modal({
                        icon: 'fa-warning',
                        title: 'Atenção',
                        html: '<p>' + 'Nenhum veículo localizado, verifique os filtros.' + '</p>',
                        buttons: [{
                            icon: 'fa-check',
                            title: 'Ok',
                            action: function () {
                            }
                        }],
                    })
                    Vehicle.table.clear();
                    Vehicle.table.draw();
                }
                else {
                    Vehicle.showList(data);
                }
            }, 
            )
        });
        $('#button-vehicle-new').click(function () {
            global.window({ url: "http://localhost/commercial3/window.php?module=vehicle&action=new" });
        });
        $('#vehicle_id').on('keyup', function () {
            var key = event.keyCode || event.wich;
            if (key == 13 && $('#vehicle_id').val()) {
                global.post({
                    url: global.uri.uri_public_api + 'vehicle.php?action=get',
                    dataType: "json",
                    data: {
                        vehicle_id: ($('#vehicle_id').val()),

                    }
                }, function (data) {
                    if (data.vehicle_id) {
                        Vehicle.showList(data);
                    }
                    else
                        global.modal({
                            icon: 'fa-warning',
                            title: 'Atenção',
                            html: '<p>' + data + '</p>',
                            buttons: [{
                                icon: 'fa-check',
                                title: 'Ok',
                                action: function () {
                                }
                            }],
                        })
                })
            }
        });
        $('#vehicle_plate').on('keyup', function () {
            var key = event.keyCode || event.wich;
            if (key == 13 && $('#vehicle_plate').val()) {
                global.post({
                    url: global.uri.uri_public_api + 'vehicle.php?action=getPlate',
                    dataType: "json",
                    data: {
                        vehicle_plate: ($('#vehicle_plate').val()),
                        query_type: 'V' //Veiculo
                    }
                }, function (data) {
                    if (data.vehicle_id) {
                        Vehicle.showList(data);
                    }
                    else
                        global.modal({
                            icon: 'fa-warning',
                            title: 'Atenção',
                            html: '<p>' + data + '</p>',
                            buttons: [{
                                icon: 'fa-check',
                                title: 'Ok',
                                action: function () {
                                }
                            }],
                        })
                });
            }
        });

    },
    getList: function () {
        global.post({
            url: global.uri.uri_public_api + 'vehicle.php?action=getList',
            dataType: "json"
        }, function (data) {
            Vehicle.showList(data);

        })
    },
    showList: function (vehicles) {
        Vehicle.table.clear();
        if (vehicles.length) {
            $.each(vehicles, function (key, vehicles) {
                vehicles.key = key;
                Vehicle.table.row.add([
                    vehicles.vehicle_id,
                    vehicles.vehicle_plate,
                    vehicles.maker_name,
                    vehicles.vehicle_model,
                    vehicles.vehicle_year,
                    vehicles.vehicle_capacity_kg ? vehicles.vehicle_capacity_kg + ' KG' : '-',
                ]);
            });
        } else {
            Vehicle.table.row.add([
                vehicles.vehicle_id,
                vehicles.vehicle_plate,
                vehicles.maker_name,
                vehicles.vehicle_model,
                vehicles.vehicle_year,
                vehicles.vehicle_capacity_kg ? vehicles.vehicle_capacity_kg + ' KG' : '-',
            ]);
        }
        Vehicle.table.draw();
    }
}
$(document).ready(function() {
    global.unLoader();
    Vehicle_new.events();
});

Vehicle_new = {
    events: function(){
        $('#vehicle_year').mask('9999');
        $('#vehicle_capacity_kg').mask('999999');
        $('#vehicle_capacity_m3').mask('999999');
        $('#vehicle_axis').mask('99');
        $('#vehicle_renavam').mask('99999999999');
        $('#vehicle_tare').mask('999999');
        $('#vehicle_crlv').mask('999999999999');
        $('#vehicle_plate').mask('AAAAAAA');
        $('#vehicle_plate').blur(function(){
            if($('#vehicle_plate').val().length < 7 || $('#vehicle_plate').val() == null){
                return;
            } else {
                global.post({
                    url: global.uri.uri_public_api + 'vehicle.php?action=getPlate',
                    data: {
                        vehicle_plate: ($('#vehicle_plate').val()),
                        query_type: 'P' //placa
                    },
                    dataType: 'json'
                }, function(data){
                    console.log(data);
                    if(data == "Ok")
                        return 0;
                    else
                        // alert("- Criar modal avisando da placa");
                        global.modal({
                            icon: 'fa-warning',
                            title: 'Atenção',
                            html: '<p>' + "Placa " + data.vehicle_plate + " já cadastrada para o veículo "+ data.vehicle_id + ", " + data.maker_name + " " + data.vehicle_model + ". Verifique!" + '</p>',
                            buttons: [{
                                icon: 'fa-check',
                                title: 'Ok',
                                action: function() {
                                }
                            }],
                        })
                        $('#vehicle_plate').val("");
                })
                    
                
            }
        });  
        $('#vehicle_form').on('submit', function(event){
            event.preventDefault();
            event.stopPropagation();   
            global.post({
                url: global.uri.uri_public_api + 'vehicle.php?action=add',
                data: $('#vehicle_form').serialize(),
                dataType: "json"
            }, function(ret) {
                //Lançar o modal
                global.modal({
                    icon: 'fa-warning',
                    title: 'Atenção',
                    html: '<p>' + ret + '</p>',
                    buttons: [{
                        icon: 'fa-check',
                        title: 'Ok',
                        unclose: true,
                        action: function() {
                            window.close();
                        }
                    }],
                    shown: function(){
                        $('#modal-1-button-1').focus();
                    }
                });
            });
        })
    }
}
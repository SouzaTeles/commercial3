$(document).ready(function(){
    ModalShipmentMap.get();
});

ModalShipmentMap = {
    data: {},
    labels: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
    get: function(){
        global.post({
            url: global.uri.uri_public_api + 'shipment.php?action=dataMap',
            data: {
                company_id: Shipment.data.company_id,
                shipment_id: ModalShipmentMap.shipment_id
            },
            dataType: 'json'
        },function(data){
            ModalShipmentMap.data = data;
            ModalShipmentMap.showCompany();
            ModalShipmentMap.showPoints();
            ModalShipmentMap.showVehicle();
        });
    },
    showCompany: function(){
        map.init({
            zoom: 4,
            point: {
                lat: parseFloat(ModalShipmentMap.data.company.latitude),
                lng: parseFloat(ModalShipmentMap.data.company.longitude)
            },
            style: [{
                featureType: 'all',
                elementType: 'labels',
                stylers: [{
                    visibility: 'on'
                }]
            }],
            infowindow: true,
            selector: 'modal-map'
        });
        map.addMarker({
            title: 'Partida: DAFEL - ' + ModalShipmentMap.data.company.company_short_name,
            point: {
                lat: parseFloat(ModalShipmentMap.data.company.latitude),
                lng: parseFloat(ModalShipmentMap.data.company.longitude)
            },
            html: (
                '<b>DAFEL - ' + ModalShipmentMap.data.company.company_short_name + '</b><br/>' +
                '<b>Endereço:</b> ' + ModalShipmentMap.data.company.address_public_place + ', ' + ModalShipmentMap.data.company.address_number + '<br/>' +
                '<b>Bairro:</b> ' + ModalShipmentMap.data.company.district_name + '<br/>' +
                '<b>Cidade:</b> ' + ModalShipmentMap.data.company.city_name + ' - ' + ModalShipmentMap.data.company.uf_id + '<br>' +
                '<b>CEP:</b> ' + (ModalShipmentMap.data.company.address_cep || '--') + '<br>'
            ),
            icon: 'images/marker-home.png',
            infowindow: true,
            animation: google.maps.Animation.DROP
        });
    },
    showPoints: function(){
        $.each(ModalShipmentMap.data.points,function(key,point){
            map.addMarker({
                title: 'Documento: ' + point.document_code,
                point: {
                    lat: parseFloat(point.latitude),
                    lng: parseFloat(point.longitude)
                },
                html: (
                    '<b>Documento:</b> ' + point.document_code + '<br>' +
                    '<b>Pessoa:</b> ' + point.person_code + ' - ' + point.person_name + '<br>' +
                    '<b>Endereço:</b> ' + point.address_public_place + ', ' + point.address_number + '<br/>' +
                    '<b>Bairro:</b> ' + point.district_name + '<br/>' +
                    '<b>Cidade:</b> ' + point.city_name + ' - ' + point.uf_id + '<br>' +
                    '<b>CEP:</b> ' + (point.address_cep || '--') + '<br>' +
                    '<b>URL:</b> <a href="' + point.url + '" target="_blank">Visualizar</a><br>' +
                    '<b>Latitude:</b> ' + point.latitude + '<br>' +
                    '<b>Longitude:</b> ' + point.longitude + '<br>'
                ),
                label: {
                    text: ModalShipmentMap.labels[key % ModalShipmentMap.labels.length],
                    color: '#fff'
                },
                infowindow: true,
                animation: google.maps.Animation.DROP
            });
        });
    },
    showVehicle: function(){
        map.addMarker({
            title: ModalShipmentMap.data.vehicle.vehicle_model,
            point: {
                lat: parseFloat(ModalShipmentMap.data.vehicle.latitude),
                lng: parseFloat(ModalShipmentMap.data.vehicle.longitude)
            },
            html: (
                '<b>' + ModalShipmentMap.data.vehicle.vehicle_model + '</b><br/>' +
                '<b>Motorista:</b> ' + ModalShipmentMap.data.vehicle.person_name + '<br>' +
                '<b>Placa:</b> ' + ModalShipmentMap.data.vehicle.vehicle_plate + '<br>'
            ),
            icon: 'images/marker-truck.png',
            infowindow: true,
            animation: google.maps.Animation.DROP
        });
        setTimeout(function(){
            map.fitBounds();
            ModalShipmentMap.showRoute();
        },1000);
    },
    showRoute: function(){
        var directionsService = new google.maps.DirectionsService;
        var directionsDisplay = new google.maps.DirectionsRenderer({
            map: map.map,
            suppressMarkers: true
        });
        var waypoints = [];
        var start = ModalShipmentMap.data.company;
        var end = ModalShipmentMap.data.points[ModalShipmentMap.data.points.length-1];
        for(var i=0; i<ModalShipmentMap.data.points.length-1; i++){
            waypoints.push({
                location: new google.maps.LatLng(ModalShipmentMap.data.points[i].latitude, ModalShipmentMap.data.points[i].longitude),
                stopover: true
            });
        }
        directionsService.route({
            origin: new google.maps.LatLng(start.latitude, start.longitude),
            destination: new google.maps.LatLng(end.latitude, end.longitude),
            waypoints: waypoints,
            avoidTolls: true,
            avoidHighways: false,
            travelMode: google.maps.TravelMode.DRIVING
        }, function(response, status){
            if (status == google.maps.DirectionsStatus.OK){
                directionsDisplay.setDirections(response);
            } else {
                global.validateMessage('O Google Maps não conseguiu traçar a rota entre os endereços informados. Verifique!')
            }
        });
    }
};
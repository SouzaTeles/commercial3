$(document).ready(function(){
    ModalMapSingle.show();
});

ModalMapSingle = {
    address: {},
    get: function(){
        global.post({
            url: global.uri.uri_public_api + 'address.php?action=geocode',
            data: ModalMapSingle.address,
            dataType: 'json'
        },function(point){
            ModalMapSingle.address.address_lat = point.lat;
            ModalMapSingle.address.address_lng = point.lng;
            ModalMapSingle.show();
        });
    },
    show: function(){
        if( !ModalMapSingle.address.address_lat || !ModalMapSingle.address.address_lng ){
            ModalMapSingle.get();
            return;
        }
        map.init({
            mapTypeControl: false,
            zoom: 16,
            point: {
                lat: parseFloat(ModalMapSingle.address.address_lat),
                lng: parseFloat(ModalMapSingle.address.address_lng)
            },
            infowindow: (
                '<b>ENDEREÇO ' + (ModalMapSingle.address.address_code) + '</b><br/>' +
                ModalMapSingle.address.address_type + ' ' +
                ModalMapSingle.address.address_public_place + ', ' +
                ModalMapSingle.address.address_number + '<br/>' +
                ModalMapSingle.address.district_name + ' - ' +
                ModalMapSingle.address.city_name + ' - ' +
                ModalMapSingle.address.uf_id
            ),
            selector: 'modal-map'
        });
        map.addMarker({
            title: 'Endereço ' + (ModalMapSingle.address.address_code),
            point: {
                lat: parseFloat(ModalMapSingle.address.address_lat),
                lng: parseFloat(ModalMapSingle.address.address_lng)
            },
            infowindow: true,
            animation: google.maps.Animation.DROP
        });
        setTimeout(function(){
            new google.maps.event.trigger(map.markers[0], 'click');
        },1000);
    }
};
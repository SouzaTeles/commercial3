$(document).ready(function(){
    ModalMapSingle.show();
    global.onLoader();
});

ModalMapSingle = {
    address: {},
    get: function(){
        global.post({
            url: global.uri.uri_public_api + 'address.php?action=geocode',
            data: ModalMapSingle.address,
            dataType: 'json'
        },function(point){
            ModalMapSingle.address.lat = point.lat;
            ModalMapSingle.address.lng = point.lng;
            ModalMapSingle.show();
        });
    },
    show: function(){
        if( !ModalMapSingle.address.lat || !ModalMapSingle.address.lng ){
            ModalMapSingle.get();
            return;
        }
        map.init({
            mapTypeControl: false,
            zoom: 16,
            point: {
                lat: parseFloat(ModalMapSingle.address.lat),
                lng: parseFloat(ModalMapSingle.address.lng)
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
                lat: parseFloat(ModalMapSingle.address.lat),
                lng: parseFloat(ModalMapSingle.address.lng)
            },
            infowindow: true
        });
        setTimeout(function(){
            new google.maps.event.trigger(map.markers[0], 'click');
        },1000);
        global.unLoader();
    }
};
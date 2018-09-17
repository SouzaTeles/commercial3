$(document).ready(function(){
    ModalMapGeolocation.events();
    ModalMapGeolocation.show();
});

ModalMapGeolocation = {
    events: function(){
        $('#button-zip-code').click(function(){
            ModalMapGeolocation.get();
        }).prop('disabled',!ModalMapGeolocation.data.cep);
    },
    get: function(){
        global.post({
            url: global.uri.uri_public_api + 'address.php?action=geocode',
            data: { address_cep: ModalMapGeolocation.data.cep },
            dataType: 'json'
        },function(point){
            ModalMapGeolocation.data.address_lat = point.lat;
            ModalMapGeolocation.data.address_lng = point.lng;
            map.markers[0].setPosition(point);
            map.map.setCenter(point);
        });
    },
    show: function(){
        ModalMapGeolocation.data.lat = ModalMapGeolocation.data.lat || -15.818142465877486;
        ModalMapGeolocation.data.lng = ModalMapGeolocation.data.lng || -47.78571496917914;
        map.init({
            selector: ModalMapGeolocation.selector,
            mapTypeControl: true,
            zoom: parseInt(ModalMapGeolocation.data.zoom),
            point: {
                lat: parseFloat(ModalMapGeolocation.data.lat),
                lng: parseFloat(ModalMapGeolocation.data.lng)
            },
            onClick: function(event){
                map.markers[0].setPosition({
                    lat: event.latLng.lat(),
                    lng: event.latLng.lng()
                });
            }
        });
        map.addMarker({
            draggable: true,
            title: 'Geolocalização',
            point: {
                lat: parseFloat(ModalMapGeolocation.data.lat),
                lng: parseFloat(ModalMapGeolocation.data.lng)
            }
        });
        setTimeout(function(){
            new google.maps.event.trigger(map, "resize");
        },1000);
        global.unLoader();
    }
};
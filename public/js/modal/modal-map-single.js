$(document).ready(function(){
    global.onLoader();
});

ModalMapSingle = {
    point: {},
    get: function(address){
        global.post({
            url: global.uri.uri_public_api + 'address.php?action=geocode',
            data: address,
            dataType: 'json'
        },function(point){
            ModalMapSingle.point = point;
            ModalMapSingle.show();
        });
    },
    show: function(){
        map.init({
            mapTypeControl: false,
            zoom: 16,
            point: {
                lat: parseFloat(ModalMapSingle.point.lat),
                lng: parseFloat(ModalMapSingle.point.lng)
            },
            selector: 'modal-map'
        });
        map.addMarker({
            title: '@@@',
            point: {
                lat: parseFloat(ModalMapSingle.point.lat),
                lng: parseFloat(ModalMapSingle.point.lng)
            }
        });
        // map.map.setZoom(16);
        // map.map.setCenter(data.results[0].geometry.location);
        // map.markers[0].setPosition(data.results[0].geometry.location);
        global.unLoader();
    }
};
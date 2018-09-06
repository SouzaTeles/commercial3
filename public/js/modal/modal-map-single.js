$(document).ready(function(){
    global.onLoader();
});

ModalMapSingle = {
    point: {},
    get: function(address){
        console.log(address);
        global.post({
            url: 'https://maps.googleapis.com/maps/api/geocode/json?key=AIzaSyA4we-5aCbXOqPvzcbUJW49x46LXnhwdbY&address=' + address.address_cep,
            dataType: 'json'
        },function(data){
            if( data.results.length ){
                ModalMapSingle.point = data.results[0].geometry.location;
                ModalMapSingle.show();
            } else {
                global.unLoader();
                global.validateMessage('O google maps não encontrou o CEP <b>' + address.address_cep + '</b>.');
            }
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
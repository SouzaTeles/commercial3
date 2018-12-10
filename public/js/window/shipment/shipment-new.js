var shipment_id = global.url.searchParams.get('shipment_id');

$(document).ready(function(){
    if( !!shipment_id ){
        Shipment.get(shipment_id);
    } else {
        Company.get();
        Route.getList();
        Route.showMap();
        Vehicle.getList();
        People.getDrivers();
        People.getHelpers();
    }
    global.mask();
    global.unLoader();

});

Company = {
    company: {
        company_id: null,
        company_name: '',
        company_short_name: ''
    },
    afterGet: function(){
        Shipment.events();
        Document.events();
        Vehicle.events();
        People.events();
        Route.events();
    },
    get: function(){
        var company_id = global.url.searchParams.get('company_id');
        if( !!company_id ){
            $.each(global.login.companies, function (key, company) {
                if (company.company_id == company_id) {
                    Company.company = company;
                    Company.company.delivery_days = parseInt(Company.company.delivery_days);
                    Shipment.init();
                    Company.show();
                    Shipment.tools();
                    if( Shipment.shipment.shipment_status == 'L' ){
                        Shipment.released();
                    } else if( Shipment.shipment.shipment_status == 'C' ){
                        Shipment.canceled();
                    } else {
                        Company.afterGet();
                    }
                }
            });
        }
        if( !Company.company.company_id ){
            global.modal({
                icon: 'fa-warning',
                title: 'Aviso',
                html: '<p>Você não possui acesso a empresa informada.</p>',
                buttons: [{
                    icon: 'fa-check',
                    title: 'Fechar',
                    action: function(){
                        window.close();
                    }
                }],
                hidden: function(){
                    window.close();
                }
            });
        }
    },
    show: function(){
        $('footer .logo').css({'background-image': 'url(' + Company.company.image + ')'});
        $('footer .info').text(('0'+Company.company.company_id).slice(-2) + ' - ' + Company.company.company_name + ' | Autor: ' + global.login.user_name);
    }
};

Shipment = {
    shipment: {
        shipment_id: null,
        external_id: '',
        external_code: '',
        company_id: Company.company.company_id,
        driver_id: null,
        vehicle_id: null,
        shipment_status: 'A',
        shipment_description: '',
        shipment_trash: 'N',
        shipment_reference: global.today(),
        documents: []
    },
    canceled: function(){

    },
    close: function(){
        global.modal({
            size: 'small',
            icon: 'fa-question-circle-o',
            title: 'Confirmação',
            html: '<p>Deseja realmente cancelar a ' + ( Shipment.shipment.shipment_id ? 'edição' : 'inclusão' ) + ' do mapa?</p>',
            buttons: [{
                icon: 'fa-times',
                title: 'Não',
                class: 'pull-left'
            },{
                icon: 'fa-check',
                title: 'Sim',
                action: function(){
                    if( !!window.opener ){
                        window.close();
                    } else {
                        location.reload();
                    }
                }
            }]
        });
    },
    events: function(){
        $('#button-shipment-cancel').click(function(){
            Shipment.close();
        }).prop('disabled',false);
        $('#button-shipment-save').click(function(){
            if( Shipment.validate() ){
                if( !Shipment.shipment.shipment_id ){
                    Shipment.add();
                } else {
                    Shipment.submit = function(){
                        Shipment.beforeSave(function(){
                            Shipment.edit();
                        });
                    }
                }
            }
        }).prop('disabled',Shipment.shipment.shipment_status != 'A');
    },
    get: function(){

    },
    init: function(){
        Shipment.shipment = {
            shipment_id: null,
            external_id: '',
            external_code: '',
            company_id: Company.company.company_id,
            driver_id: null,
            vehicle_id: null,
            shipment_status: 'A',
            shipment_description: '',
            shipment_trash: 'N',
            shipment_reference: global.today(),
            documents: []
        }
    },
    released: function(){

    },
    tools: function(){
        var $panel = $('.panel-tools');
        $panel.find('button[data-action="new"]').click(function(){
            Shipment.new();
        });
        $panel.find('button[data-action="save"]').click(function(){
            $('#button-shipment-save').click();
        }).prop('disabled',Shipment.shipment.shipment_status != 'A');
        $panel.find('button[data-action="close"]').click(function(){
            Shipment.close();
        });
        $panel.find('button[data-action="print"]').click(function(){
            if( !!window.opener ){
                window.opener.Shipment.print({
                    shipment_id: Shipment.shipment.shipment_id,
                    action: 'print'
                });
            } else{
                global.validateMessage('A janela é órfã e não sera possível realizar esta operação.')
            }
        }).prop('disabled',!Shipment.shipment.shipment_id);
        $panel.find('button[data-action="pdf"]').click(function(){
            if( !!window.opener ){
                window.opener.Shipment.print({
                    shipment_id: Shipment.shipment.shipment_id,
                    action: 'print'
                });
            } else{
                global.validateMessage('A janela é órfã e não sera possível realizar esta operação.')
            }
        }).prop('disabled',!Shipment.shipment.shipment_id);
        $panel.find('button[data-action="mail"]').click(function(){
            if( !!window.opener ){
                window.opener.Shipment.print({
                    shipment_id: Shipment.shipment.shipment_id,
                    action: 'mail'
                });
            } else{
                global.validateMessage('A janela é órfã e não sera possível realizar esta operação.')
            }
        }).prop('disabled',!Shipment.shipment.shipment_id);
        $panel.find('button[data-action="pdf"]').click(function(){
            if( !!window.opener ){
                window.opener.Shipment.print({
                    shipment_id: Shipment.shipment.shipment_id,
                    action: 'pdf'
                });
            } else{
                global.validateMessage('A janela é órfã e não sera possível realizar esta operação.')
            }
        }).prop('disabled',!Shipment.shipment.shipment_id);
        $panel.find('[data-toggle="tooltip"]').tooltip();
    },
    validate: function(){
        if( !Shipment.shipment.documents.length ){
            global.validateMessage('Pelo menos um documento deverá ser adicionado ao Mapa.');
            global.scrollTo({
                delay: 500,
                addition: 200,
                selector: '.panel-documents'
            });
            return false;
        }
        return true;
    }
};

Document = {
    weight: 0,
    distance: 0,
    duration: 0,
    document: {
        document_id: null,
        document_code: '',
        budget_code: '',
        person_name: '',
        district_name: '',
        document_weight: 0,
        document_distance: 0,
        document_duration: 0,
        document_date: '',
        document_date_br: '',
        document_type: ''
    },
    add: function(){
        Shipment.shipment.documents.push(Document.document);
        Document.init();
        Document.data2form();
        Document.distanceMatrix();
        Route.showDocuments();
    },
    beforeDel: function(key){
        Document.del(key)
    },
    data2form: function(){
        $('#document_code').val(Document.document.document_code).attr('data-value',Document.document.document_code);
        $('#budget_code').val(Document.document.budget_code);
        $('#person_name').val(Document.document.person_name);
        $('#district_name').val(Document.document.district_name);
        $('#document_weight').val(global.float2Br(Document.document.document_weight));
        $('#document_distance').val(global.formatDistance(Document.document.document_distance));
        $('#document_duration').val(global.formatDuration(Document.document.document_duration));
        $('#document_date').val(Document.document.document_date_br);
        $('#document_type').val(Document.document.document_type);
        if( Document.document.document_id ){
            $('#button-document-add').focus();
        }
    },
    del: function(key){
        Shipment.shipment.documents.splice(key,1);
        Document.distanceMatrix();
    },
    distanceMatrix: function(){
        var points = [];
        points.push(Company.company.company_latitude + ',' + Company.company.company_longitude);
        $.each(Shipment.shipment.documents,function(key,document){
            points.push(document.city_name + '-' + document.uf_id + ',' + document.address_public_place + ',' + document.district_name)
        });
        global.post({
            url: global.uri.uri_public_api + 'shipment.php?action=distanceMatrix',
            data: {
                points: points
            },
            dataType: 'json'
        },function(data){
            $.each(data,function(key,element){
                Shipment.shipment.documents[key].document_distance = element.distance;
                Shipment.shipment.documents[key].document_duration = element.duration;
            });
            Document.showList();
        },function(){
            global.validateMessage('Não foi possível calcular a distância e o tempo das entregas.');
            Document.showList();
        });
    },
    events: function(){
        $('#document_code').on('keyup',function (e) {
            var keycode = e.keyCode || e.which;
            if( (keycode == '13' && $(this).val().length > 0) || $(this).val().length == 44 ){
                e.preventDefault();
                e.stopPropagation();
                if (global.posts < 1){
                    Document.get({
                        document_id: null,
                        document_code: $(this).val()
                    });
                }
            }
        }).focus().on('blur',function(){
            $(this).val(Document.document.document_code)
        });
        $('#button-document-search').click(function(){
            Document.search();
        });
        $('#button-document-add').click(function(){
            if( !!Document.document.document_id ){
                Document.add();
            }
        });
        $('#table-documents tbody').sortable({update:function(){
            var documents = [];
            $('#table-documents tbody tr').each(function(key,tr){
                documents.push(Shipment.shipment.documents[$(tr).find('button').attr('data-key')]);
            });
            Shipment.shipment.documents = documents;
            Document.distanceMatrix();
            for(var i=map.markers.length-1; i>0; i--){
                map.markers[i].setMap(null);
                map.markers.splice(i,1);
            }
            Route.showDocuments();
            Route.showRoute();
        }}).disableSelection();
    },
    get: function(data){
        var deny = false;
        $.each( Shipment.shipment.documents, function(key,document){
            if( document.document_id == data.document_id || parseInt(document.document_code) == parseInt(data.document_code) ){
                deny = true;
            }
        });
        if( deny ){
            global.modal({
                icon: 'fa-exclamation-triangle',
                title: 'Aviso',
                html: '<p>O documento já foi adicionado ao mapa de carregamento.</p>',
                buttons: [{
                    icon: 'fa-check',
                    title: 'Ok'
                }]
            });
            Document.data2form();
            return;
        }
        data.company_id = Company.company.company_id;
        data.company_latitude = Company.company.company_latitude;
        data.company_longitude = Company.company.company_longitude;
        global.post({
            url: global.uri.uri_public_api + 'shipment.php?action=getDocument',
            data: data,
            dataType: 'json'
        },function(data){
            if( data.length == 1 ){
                Document.document = data[0];
                Document.data2form();
            } else{
                Document.showDocuments(data);
            }
        });
    },
    init: function(){
        Document.document = {
            document_id: null,
            document_code: '',
            budget_code: '',
            person_name: '',
            district_name: '',
            document_weight: 0,
            document_distance: 0,
            document_duration: 0,
            document_date: '',
            document_date_br: '',
            document_type: ''
        }
    },
    search: function(){
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-document-search',
            data: {
                document_type: ['NF','OE']
            },
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'big',
                id: 'modal-document-search',
                class: 'modal-document-search',
                icon: 'fa-search',
                title: 'Localizar Cliente',
                html: html,
                buttons: [{
                    icon: 'fa-times',
                    title: 'Cancelar',
                    class: 'pull-left'
                }],
                shown: function(){
                    setTimeout(function(){
                        $('#document_code').focus();
                    },300);
                    ModalDocumentSearch.success = function(document){
                        console.log(document);
                    };
                    ModalDocumentSearch.table.columns.adjust().draw();
                }
            });
        });
    },
    showDocuments: function(data){
        global.post({
            url: global.uri.uri_public + 'api/modal.php?modal=modal-shipment-documents',
            data: {documents: data},
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'big',
                id: 'modal-shipment-documents',
                class: 'modal-shipment-documents',
                icon: 'fa-file-text-o',
                title: 'Selecione o Documento',
                html: html,
                buttons: [{
                    icon: 'fa-times',
                    title: 'Cancelar',
                    class: 'pull-left'
                }],
                shown: function(){
                    ModalShipmentDocuments.table.columns.adjust().draw();
                    ModalShipmentDocuments.success = function(document){
                        Document.document = document;
                        Document.data2form();
                    };
                }
            });
        });
    },
    showList: function(){
        var table = $('#table-documents');
        $(table).find('tbody tr').remove();
        Document.weight = 0;
        Document.distance = 0;
        Document.duration = 0;
        $.each( Shipment.shipment.documents, function(key, document){
            document.document_weight = parseFloat(document.document_weight);
            $(table).find('tbody').append(
                '<tr data-key="' + key + '">' +
                    '<td>' + document.document_code + '</td>' +
                    '<td>' + document.person_name + '</td>' +
                    '<td>' + document.district_name + '</td>' +
                    '<td>' + global.float2Br(document.document_weight,3,3) + ' Kg</td>' +
                    '<td>' + global.formatDistance(document.document_distance) + '</td>' +
                    '<td>' + global.formatDuration(document.document_duration) + '</td>' +
                    '<td><button data-key="' + key + '" class="btn btn-empty-red-light"><i class="fa fa-trash-o"></i></button></td>' +
                '</tr>'
            );
            Document.weight += document.document_weight;
            Document.distance += document.document_distance;
            Document.duration += document.document_duration;
        });
        $(table).find('button').click(function(){
            Document.beforeDel($(this).attr('data-key'));
        });
        $('#total-documents').html('<span>Documents:</span> ' + Shipment.shipment.documents.length);
        $('#total-weight').html('<span>Peso:</span> ' + global.float2Br(Document.weight,3,3) + 'kg');
        $('#total-distance').html('<span>Distância:</span> ' + global.formatDistance(Document.distance));
        $('#total-duration').html('<span>Tempo:</span> ' + global.formatDuration(Document.duration));
        Vehicle.data2form();
    }
};

People = {
    driver: {
        person_id: '',
        person_name: '',
        person_code: ''
    },
    drivers: [],
    helpers: [],
    data2form: function(){
        if( !!People.driver.person_id ){
            $('#driver-image').css('background-image','url(' + (People.driver.image || '../../../../commercial3/images/empty-image.png') + ')');
            $('#driver-name').text(People.driver.person_name);
            $('#driver-code').text('Código: ' + People.driver.external_code);
        }
        $('#grid-helpers .row').html('');
        $('#helpers option:selected').each(function(key,option){
            var helper = People.helpers[$(option).index()];
            $('#grid-helpers .row').append(
                '<div class="col-xs-12 col-sm-6">' +
                    '<div class="card-helper box-shadow">' +
                        '<div class="image" style="background-image:url(' + (helper.image || '../../../../commercial3/images/empty-image.png') + ')"></div>' +
                        '<div class="info">' +
                            '<div class="name">' + helper.person_name + '</div>' +
                            '<div class="item"><span>Código</span>' + helper.external_code + '</div>' +
                            '<div class="item"><span>Nascimento</span>' + global.date2Br(helper.person_birthday) + '</div>' +
                        '</div>' +
                        '<button class="btn btn-empty-red-light">' +
                            '<i class="fa fa-trash-o"></i>' +
                        '</button>' +
                    '</div>' +
                '</div>'
            )
        });
    },
    events: function(){
        $('#driver_id').on('change',function(){
            People.driver = People.drivers[$(this).find('option:selected').index()-1];
            People.data2form();
        });
        $('#helpers').on('change',function(){
            People.data2form();
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
            People.drivers = data;
            People.showDrivers();
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
            People.helpers = data;
            People.showHelpers();
        });
    },
    showDrivers: function(){
        $.each( People.drivers, function(key,driver){
            $('#driver_id').append($('<option>',{
                'value': driver.person_id,
                'text': driver.external_code + ' - ' + driver.person_name
            }));
        });
        $('#driver_id').selectpicker('refresh');
    },
    showHelpers: function(){
        $.each( People.helpers, function(key,helper){
            $('#helpers').append($('<option>',{
                'value': helper.person_id,
                'text': helper.external_code + ' - ' + helper.person_name
            }));
        });
        $('#helpers').selectpicker('refresh');
    }
};

Route = {
    routes: [],
    labels: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
    directionsDisplay: null,
    directionsService: null,
    events: function(){
        $('#routes').on('change',function(){
            $('#grid-route .row').html('');
            $('#routes option:selected').each(function(key,option){
                var route = Route.routes[$(option).index()];
                $('#grid-route .row').append(
                    '<div class="col-xs-12 col-sm-6">' +
                        '<div class="card-route box-shadow">' +
                            '<p>' + route.route_name + '</p>' +
                            '<button disabled class="btn btn-empty-red-light">' +
                                '<i class="fa fa-trash-o"></i>' +
                            '</button>' +
                        '</div>' +
                    '</div>'
                )
            });
        });
    },
    getList: function(){
        global.post({
            url: global.uri.uri_public_api + 'route.php?action=getList',
            dataType: 'json'
        },function(data){
            Route.routes = data;
            Route.showList();
        });
    },
    showList: function(){
        $.each( Route.routes, function(key,route){
            $('#routes').append($('<option>',{
                'value': route.route_id,
                'text': route.route_name
            }));
        });
        $('#routes').selectpicker('refresh');
    },
    showDocuments: function(){
        $.each(Shipment.shipment.documents,function(key,document){
            map.addMarker({
                title: 'Documento: ' + document.document_code,
                point: {
                    lat: parseFloat(document.address_latitude),
                    lng: parseFloat(document.address_longitude)
                },
                html: (
                    '<b>Documento:</b> ' + document.document_code + '<br>' +
                    '<b>Pessoa:</b> ' + document.person_code + ' - ' + document.person_name + '<br>' +
                    '<b>Endereço:</b> ' + document.address_public_place + ', ' + document.address_number + '<br/>' +
                    '<b>Bairro:</b> ' + document.district_name + '<br/>' +
                    '<b>Cidade:</b> ' + document.city_name + ' - ' + document.uf_id + '<br>'
                ),
                label: {
                    text: Route.labels[key % Route.labels.length],
                    color: '#fff'
                },
                infowindow: true,
                animation: google.maps.Animation.DROP
            });
        });
        Route.showRoute();
    },
    showMap: function(){
        map.init({
            zoom: 12,
            point: {
                lat: parseFloat(Company.company.company_latitude),
                lng: parseFloat(Company.company.company_longitude)
            },
            style: [{
                featureType: 'all',
                elementType: 'labels',
                stylers: [{
                    visibility: 'on'
                }]
            }],
            infowindow: true,
            selector: 'google-map'
        });
        map.addMarker({
            title: 'Partida: ' + Company.company.company_short_name,
            point: {
                lat: parseFloat(Company.company.company_latitude),
                lng: parseFloat(Company.company.company_longitude)
            },
            html: (
                '<b>' + Company.company.company_short_name + '</b><br/>'
                //'<b>Endereço:</b> ' + Company.company.address_public_place + ', ' + Company.company.address_number + '<br/>' +
                //'<b>Bairro:</b> ' + Company.company.district_name + '<br/>' +
                //'<b>Cidade:</b> ' + Company.company.city_name + ' - ' + Company.company.uf_id + '<br>' +
                //'<b>CEP:</b> ' + (Company.company.address_cep || '--') + '<br>'
            ),
            icon: 'images/marker-home.png',
            infowindow: true,
            animation: google.maps.Animation.DROP
        });
        setTimeout(function(){
            //map.fitBounds();
            //ModalShipmentMap.showRoute();
        },1000);
    },
    showRoute: function(){
        if(Route.directionsDisplay != null) {
            Route.directionsDisplay.setMap(null);
            Route.directionsDisplay = null;
        }
        Route.directionsService = new google.maps.DirectionsService;
        Route.directionsDisplay = new google.maps.DirectionsRenderer({
            map: map.map,
            suppressMarkers: true
        });
        var waypoints = [];
        var start = Company.company;
        var end = Shipment.shipment.documents[Shipment.shipment.documents.length-1];
        for(var i=0; i<Shipment.shipment.documents.length-1; i++){
            waypoints.push({
                location: new google.maps.LatLng(Shipment.shipment.documents[i].address_latitude, Shipment.shipment.documents[i].address_longitude),
                stopover: true
            });
        }
        Route.directionsService.route({
            origin: new google.maps.LatLng(parseFloat(start.company_latitude), parseFloat(start.company_longitude)),
            destination: new google.maps.LatLng(end.address_latitude, end.address_longitude),
            waypoints: waypoints,
            avoidTolls: true,
            avoidHighways: false,
            travelMode: google.maps.TravelMode.DRIVING
        }, function(response, status){
            if (status == google.maps.DirectionsStatus.OK){
                Route.directionsDisplay.setDirections(response);
            } else {
                global.validateMessage('O Google Maps não conseguiu traçar a rota entre os endereços informados. Verifique!')
            }
            setTimeout(function(){
                map.fitBounds();
            },1000);
        });
    }
};

Vehicle = {
    vehicle: {
        vehicle_id: null,
        maker_name: '',
        vehicle_capacity_kg: 0,
        vehicle_model: '',
        vehicle_plate: '',
        vehicle_type: '',
        vehicle_uf: '',
        vehicle_year: ''
    },
    vehicles: [],
    data2form: function(){
        $('#vehicle_code').val(Vehicle.vehicle.vehicle_id);
        $('#vehicle_plate').val(Vehicle.vehicle.vehicle_plate);
        $('#vehicle_year').val(Vehicle.vehicle.vehicle_year);
        $('#vehicle_uf').val(Vehicle.vehicle.vehicle_uf);
        $('#vehicle_image').css('background-image','url(' + (Vehicle.vehicle.image || '../../../../commercial3/images/empty-image.png') + ')');
        $('#bar-load-weight').css('height',parseInt(Document.weight/parseInt(Vehicle.vehicle.vehicle_capacity_kg)*164));
        $('#bar-load-label').text(parseInt(Document.weight) + 'Kg de ' + global.float2Br(Vehicle.vehicle.vehicle_capacity_kg).split(',')[0] + 'Kg');
    },
    events: function(){
        $('#vehicle_id').on('change',function(){
            Vehicle.vehicle = Vehicle.vehicles[$(this).find('option:selected').index()-1];
            Vehicle.data2form();
        });
    },
    getList: function(){
        global.post({
            url: global.uri.uri_public_api + 'vehicle.php?action=getList',
            dataType: 'json'
        },function(data){
            Vehicle.vehicles = data;
            Vehicle.showList();
        });
    },
    showList: function(){
        $.each( Vehicle.vehicles, function(key,vehicle){
            $('#vehicle_id').append($('<option>',{
                'value': vehicle.vehicle_id,
                'text': vehicle.vehicle_plate + ' - ' + vehicle.vehicle_model
            }));
        });
        $('#vehicle_id').selectpicker('refresh');
    }
};
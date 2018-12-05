$(document).ready(function(){

    Ticket.events();
    Ticket.getPeople();

    global.unLoader();

});

Ticket = {
    data: {
        company_id: '',
        company_name: '',
        ticket_type_id: '',
        ticket_type_name: '',
        urgency_id: '',
        urgency_name: '',
        message: ''
    },
    person: {
        person_id: '',
        person_code: '',
        person_name: ''
    },
    people: [],
    images: [],
    events: function(){
        $('#person_code').on('keyup', function(e){
            var code = $(this).val();
            if( (e.keyCode || e.which) == 13 && code.length > 0 ){
                Ticket.searchPerson(code);
            }
        }).on('blur',function(){
            $(this).val($(this).attr('data-value'));
        });
        $('#person_id').on('changed.bs.select', function (e, clickedTicket, isSelected, previousValue) {
            Ticket.person = Ticket.people[clickedTicket-1];
            $('#person_code').val(Ticket.person.person_code).attr('data-value',Ticket.person.person_code);
        });
        $('#file').change(function(){
            Ticket.upImages();
        });
        $('#submit').on('click',function(){
            Ticket.form2data();
            if( Ticket.validate() ){
                Ticket.submit()
            }
        });
    },
    form2data: function(){
        Ticket.data = {
            company_id: $('#company_id').val(),
            company_name: $('#company_id option:selected').text(),
            ticket_type_id: $('#ticket_type_id').val(),
            ticket_type_name: $('#ticket_type_id option:selected').text(),
            urgency_id: $('#urgency_id').val(),
            urgency_name: $('#urgency_id option:selected').text(),
            message: $('#message').val()
        };
    },
    getPeople: function(){
        global.post({
            url: global.uri.uri_public_api + 'person.php?action=getList',
            data: {
                limit: 500,
                person_active: 'Y',
                categories: [
                    global.config.person.employ_category_id,
                    global.config.person.seller_category_id
                ]
            },
            dataType: 'json'
        },function(data){
            Ticket.people = data;
            Ticket.showPeople();
        });
    },
    searchPerson: function(code){
        var found = null;
        $.each( Ticket.people,function(key,person){
            if( parseInt(person.person_code) == code){
                found = person;
            }
        });
        if( !found ){

        } else {
            Ticket.person = found;
            $('#person_id').selectpicker('val',found.person_id);
            $('#person_code').val(found.person_code).attr('data-value',found.person_code);
        }
    },
    showImages: function(){
        $('#images').html('');
        $.each(Ticket.images,function(key,image){
            $('#images').append(
                '<div class="col-xs-6 col-sm-4 col-md-3">' +
                '<div class="image" style="background-image:url(' + image + ')">' +
                '<i data-key="' + key + '" data-toggle="tooltip" title="Remover" class="fa fa-trash-o"></i>' +
                '</div>' +
                '</div>'
            );
        });
        $('#images i').click(function(){
            Ticket.images.splice($(this).attr('data-key'),1);
            Ticket.showImages();
        });
        global.tooltip();
    },
    showPeople: function(){
        $.each( Ticket.people, function(key,person){
            $('#person_id').append($('<option>',{
                value: person.person_id,
                text: person.person_code + ' - ' + person.person_name
            }));
        });
        $('#person_id').selectpicker('refresh');
    },
    submit: function(){
        global.post({
            url: global.uri.uri_public_api + 'ticket.php?action=new',
            data: {
                data: Ticket.data,
                person: Ticket.person,
                images: Ticket.images
            },
            dataType: 'json'
        },function(data){
            global.modal({
                icon: 'fa-info-circle',
                title: 'Informação',
                html: '<p>O seu chamado foi aberto com sucesso.<br>Código: <b>' + data.ticket_code + '</b>.</p>',
                buttons: [{
                    icon: 'fa-check',
                    title: 'Ok'
                }],
                hidden: function(){
                    if( !!window.opener ){
                        window.close();
                    } else {
                        location.reload();
                    }
                }
            });
        });
    },
    upImages: function(){
        var size = $('#file')[0].files.length;
        $.each( $('#file')[0].files, function( i, file ){
            var reader = new FileReader();
            reader.onload = function(e){
                var key = Ticket.images.length;
                var image = e.target.result;
                Ticket.images.push(image);
                if( i+1 == size ){
                    Ticket.showImages();
                }
            };
            reader.readAsDataURL(file);
        });
        $('#file').filestyle('clear');
    },
    validate: function(){
        if( !Ticket.person.person_id.length ){
            global.validateMessage('Selecione a pessoa do chamado.');
            return false;
        }
        if( !Ticket.data.company_id.length ){
            global.validateMessage('Selecione a unidade.');
            return false;
        }
        if( !Ticket.data.ticket_type_id.length ){
            global.validateMessage('Selecione o tipo do chamado.');
            return false;
        }
        if( !Ticket.data.urgency_id.length ){
            global.validateMessage('Selecione a categoria do chamado.');
            return false;
        }
        if( !Ticket.data.message.length ){
            global.validateMessage('Informe a descrição do problema.');
            return false;
        }
        return true;
    }
};
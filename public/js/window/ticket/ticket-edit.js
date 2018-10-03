$(document).ready(function(){
    Ticket.get();
    global.unLoader();
});

Ticket = {
    note: {
        images: [],
        note_text: '',
        owner_id: null,
        urgency_id: null,
        ticket_status: null,
        ticket_id: global.url.searchParams.get('ticket_id')
    },
    users: [],
    ticket: null,
    company: null,
    ticket_id: global.url.searchParams.get('ticket_id'),
    company_id: global.url.searchParams.get('company_id'),
    status: {
        'O': {
            icon: 'cloud',
            title: 'Aberto',
            color: 'gray'
        },
        'A': {
            icon: 'cloud',
            title: 'Em atendimento',
            color: 'orange'
        },
        'F': {
            icon: 'cloud-download',
            title: 'Concluido',
            color: 'green'
        },
        'C': {
            icon: 'cloud',
            title: 'Cancelado',
            color: 'red-light'
        }
    },
    type:[
        {'name': 'ERP'},
        {'name': 'Commercial'},
        {'name': 'Impressora'},
        {'name': 'Informatica Hardware'},
        {'name': 'Telefonia'},
        {'name': 'Outros'}
    ],
    urgency: [
        {'name': 'Pouco Urgente', 'color': 'blue'},
        {'name': 'Mediano', 'color': 'orange'},
        {'name': 'Urgente!', 'color': 'red'}
    ],
    add: function(){
        global.post({
            url: global.uri.uri_public_api + 'ticket.php?action=addNote',
            data: Ticket.note,
            dataType: 'json'
        },function(){
            global.modal({
                icon: 'fa-info-circle',
                title: 'Informação',
                html: '<p>O seu parecer foi adicionado com sucesso.</p>',
                buttons: [{
                    icon: 'fa-check',
                    title: 'Ok'
                }],
                hidden: function(){
                    location.reload();
                }
            });
        });
    },
    attachment: function(files){
          var html = '';
          $.each( files,function(key,file){
              html += '<i class="fa fa-file-image-o"></i><a data-gallery="note" href="' + file.url + '" data-title="' + file.ticket_file_name + '" data-toggle="lightbox">' + file.ticket_file_name + '</a>';
          });
          return html;
    },
    events: function(){
        $('#form-ticket-note').on('submit',function(e){
            e.preventDefault();
            e.stopPropagation();
            Ticket.form2data();
            if( Ticket.validate() ){
                Ticket.add();
            }
        });
        $('#file').change(function(){
            Ticket.upImages();
        });
    },
    form2data: function(){
        Ticket.note.note_text = $('#ticket_note').val();
        Ticket.note.owner_id = $('#owner').val();
        Ticket.note.urgency_id = $('#urgency').val();
        Ticket.note.ticket_status = $('#status').val();
    },
    get: function(){
        if( !Ticket.ticket_id ){
            global.validateMessage('O Id do ticket não foi informado. Contate o setor de TI.');
            return;
        }
        if( !Ticket.company_id ){
            global.validateMessage('A empresa não foi informada. Contate o setor de TI.');
            return;
        }
        var allowed = false;
        $.each(global.login.companies,function(key,company){
             if( company.company_id == Ticket.company_id ){
                 allowed = true;
                 Ticket.company = company;
             }
        });
        if( !allowed ){
            global.validateMessage('você não possui acesso a empresa do chamado.');
            return;
        }
        global.post({
            url: global.uri.uri_public_api + 'ticket.php?action=get',
            data: {
                get_ticket_user: 1,
                get_ticket_notes: 1,
                get_ticket_owner: 1,
                ticket_id: Ticket.ticket_id
            },
            dataType: 'json'
        },function(ticket){
            Ticket.ticket = ticket;
            Ticket.ticket.type = Ticket.type[ticket.ticket_type_id];
            Ticket.ticket.status = Ticket.status[ticket.ticket_status];
            Ticket.ticket.urgency = Ticket.urgency[ticket.urgency_id-1];
            Ticket.show();
            Ticket.events();
            Ticket.getUsers();
            Ticket.showNotes();
            Ticket.showStatus();
            Ticket.showUrgency();
        });
    },
    getUsers: function(){
        global.post({
            url: global.uri.uri_public_api + 'user.php?action=getList',
            dataType: 'json'
        },function(data){
            Ticket.users = data;
            Ticket.showUsers();
        });
    },
    show: function(){
        var header = $('header');
        $(header).css({'border-left': '10px solid ' + Ticket.ticket.urgency.color});
        $(header).find('.urgency').html('<span>Urgência</span><br/>' + Ticket.ticket.urgency.name);
        $(header).find('.ticket').html('<span>Chamado</span><br/>' + Ticket.ticket.ticket_code);
        $(header).find('.status').html('<span>Status</span><br/><i data-toggle="tooltip" data-title="' + Ticket.ticket.status.title + '" class="fa fa-' + Ticket.ticket.status.icon + ' txt-' + Ticket.ticket.status.color + '"></i>');
        $(header).find('.owner').html(
            '<span>Responsável</span><br/>' +
            '<div class="cover box-shadow"' + ( Ticket.ticket.owner.image ? ' style="background-image:url(' + Ticket.ticket.owner.image + ')"' : '' ) + '></div>' +
            Ticket.ticket.owner.user_name
        );

        var footer = $('footer');
        $(footer).find('.logo').css({'background-image': 'url(' + Ticket.company.image + ')'});
        $(footer).find('.info').html(('0'+Ticket.company.company_id).slice(-2) + ' - ' + Ticket.company.company_name);
    },
    showImages: function(){
        $('#images').html('');
        $.each(Ticket.note.images,function(key,image){
            $('#images').append(
                '<div class="col-xs-6 col-sm-4 col-md-3">' +
                '<div class="image" style="background-image:url(' + image + ')">' +
                '<i data-key="' + key + '" data-toggle="tooltip" title="Remover" class="fa fa-trash-o"></i>' +
                '</div>' +
                '</div>'
            );
        });
        $('#images i').click(function(){
            Ticket.note.images.splice($(this).attr('data-key'),1);
            Ticket.showImages();
        });
        global.tooltip();
    },
    showNotes: function(){
        var notes = $('#notes');
        var platforms = {
            'win32': {'title': 'Windows', 'icon': 'windows'}
        };
        $.each(Ticket.ticket.notes,function(key,note){
            var status = Ticket.status[note.ticket_status];
            var platform = note.ticket_note_host_platform ? platforms[note.ticket_note_host_platform] : {'title': 'Desconhecido','icon': 'cog'};
            $(notes).append(
                '<div class="note">' +
                    '<div class="user">' +
                        '<div class="cover box-shadow"' + ( note.user_image ? ' style="background-image:url(' + note.user_image + ')"' : '' ) + '></div>' +
                        '<div class="name">' + note.user_name + '</div>' +
                        '<div class="date">' + note.ticket_note_date + '</div>' +
                    '</div>' +
                    '<div class="info">' +
                        '<div><span>Status</span><br/><i data-toggle="tooltip" data-title="' + status.title + '" class="fa fa-' + status.icon + ' txt-' + status.color + '"></i></div>' +
                        '<div><span>Host</span><br/><i data-toggle="tooltip" data-title="' + (note.ticket_note_host_name || 'Desconhecido') + '" class="fa fa-desktop"></i></div>' +
                        '<div><span>IP</span><br/><i data-toggle="tooltip" data-title="' + (note.ticket_note_host_ip || 'Desconhecido') + '" class="fa fa-globe"></i></div>' +
                        '<div><span>SO</span><br/><i data-toggle="tooltip" data-title="' + platform.title + '" class="fa fa-' + (platform.icon) + '"></i></div>' +
                        '<div><span>Responsável</span><br/><span data-toggle="tooltip" data-title="' + note.owner_name + '" class="cover"' + ( note.owner_image ? 'style="background-image:url(' + note.owner_image + ')"' : '') + '></span></div>' +
                    '</div>' +
                    '<div class="text">&#8220;' + note.ticket_note_text + '&#8221;</div>' +
                    '<div class="attachment"><b><i class="fa fa-paperclip"></i> Anexos</b>: ' + Ticket.attachment(note.files) + '</div>' +
                '</div>'
            );
            global.tooltip();
        });
        $(notes).find('a[data-toggle="lightbox"]').click(function(e){
            e.preventDefault();
            $(this).ekkoLightbox();
        });
    },
    showStatus: function(){
        $.each( Ticket.status, function(key,status){
            $('#status').append($('<option>',{
                'value': key,
                'data-content': '<i class="fa fa-' + status.icon + ' txt-' + status.color + '"></i> ' + status.title,
                'selected': key == Ticket.ticket.ticket_status
            }));
        });
        $('#status').selectpicker('refresh');
    },
    showUrgency: function(){
        $.each( Ticket.urgency, function(key,urgency){
            $('#urgency').append($('<option>',{
                'value': key+1,
                'data-content': '<i class="fa fa-stop txt-' + urgency.color + '"></i> ' + urgency.name,
                'selected': key+1 == Ticket.ticket.urgency_id
            }));
        });
        $('#urgency').selectpicker('refresh');
    },
    showUsers: function(){
        $.each( Ticket.users, function(key,user){
            $('#owner').append($('<option>',{
                'value': user.user_id,
                'text': user.user_name,
                'selected': user.user_id == Ticket.ticket.owner_id
            }));
        });
        $('#owner').selectpicker('refresh');
    },
    upImages: function(){
        var size = $('#file')[0].files.length;
        $.each( $('#file')[0].files, function( i, file ){
            var reader = new FileReader();
            reader.onload = function(e){
                var key = Ticket.note.images.length;
                var image = e.target.result;
                Ticket.note.images.push(image);
                if( i+1 == size ){
                    Ticket.showImages();
                }
            };
            reader.readAsDataURL(file);
        });
        $('#file').filestyle('clear');
    },
    validate: function(){
        if( !Ticket.note.note_text.length ){
            global.validateMessage('Você não poderá adicionar um novo parecer sem adicionar o texto.');
            return false;
        }
        return true;
    }
};
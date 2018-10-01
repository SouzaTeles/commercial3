$(document).ready(function(){

    Ticket.events();
    Ticket.getList();
    Ticket.getUsers();
    Ticket.showCompanies();

    global.mask();
    global.unLoader();

});

Ticket = {
    data: {
        ticket_code: '',
        company_id: null,
        user_id: null,
        owner_id: null,
        type_id: null,
        status_id: null,
        start_date: global.today(),
        end_date: global.today()
    },
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
    table: global.table({
        selector: '#table-tickets',
        noControls: [0,8],
        order: [[0,'desc']]
    }),
    type:[
        {'name': 'ERP'},
        {'name': 'Commercial'},
        {'name': 'Impressora'},
        {'name': 'Informatica Hardware'},
        {'name': 'Telefonia'},
        {'name': 'Outros'}
    ],
    urgency: [
        {'name': 'Pouco Urgente'},
        {'name': 'Mediano'},
        {'name': 'Urgente!'}
    ],
    actions: function(ticket){
        return(
            '<div class="dropdown dropdown-budget dropdown-actions">' +
                '<button class="btn btn-empty" type="button" data-toggle="dropdown">' +
                    '<i class="fa fa-ellipsis-v"></i>' +
                '</button>' +
                '<ul class="dropdown-menu pull-right">' +
                    '<li><a data-action="open" disabled="' + ( global.login.access.ticket.open.value == 'N' ) + '" data-key="' + ticket.key + '" data-id="' + ticket.ticket_id + '" class="dropdown-item" href="#"><i class="fa fa-folder-open-o"></i>Abrir</a></li>' +
                '</ul>' +
            '</div>'
        );
    },
    events: function(){
        $('#form-filter').on('submit',function(e){
            e.preventDefault();
            e.stopPropagation();
            Ticket.getList();
        });
        $('#company_id').on('changed.bs.select', function (e, clickedIndex) {
            Ticket.company = global.login.companies[clickedIndex-1];
            Ticket.data.company_id = Ticket.company.company_id;
            Ticket.getList();
        });
        $('#button-company-remove').click(function(){
            if( !!Ticket.company ){
                Ticket.company = null;
                $('#company_id').selectpicker('val','default');
                Ticket.getList();
            }
        });
        $('#start_date, #end_date').datepicker({
            format: 'dd/mm/yyyy'
        }).blur(function(){
            if( $(this).val().length != 10 ){
                $(this).val(global.date2Br(global.today()));
            }
        }).val(global.date2Br(global.today()));
    },
    getList: function(){
        Ticket.data.ticket_code = $('#ticket_code').val();
        Ticket.data.company_id = $('#company_id').val();
        Ticket.data.user_id = $('#user_id').val();
        Ticket.data.owner_id = $('#owner_id').val();
        Ticket.data.type_id = $('#type_id').val();
        Ticket.data.status_id = $('#status_id').val();
        Ticket.data.start_date = global.date2Us($('#start_date').val());
        Ticket.data.end_date = global.date2Us($('#end_date').val());
        if( parseInt(Ticket.data.start_date.split('-').join('')) > parseInt(Ticket.data.end_date.split('-').join('')) ){
            global.validateMessage('<p>A data inicial não pode ser maior que a data final.</p>');
            return;
        }
        var diff = global.dateDiff(Ticket.data.start_date,Ticket.data.end_date);
        if( diff > 31 ){
            global.validateMessage('<p>Verifique o intervalo entre as datas selecionadas.<br/>O período máximo permitido será de 31 dias.</p>')
            return;
        }
        global.post({
            url: global.uri.uri_public_api + 'ticket.php?action=getList',
            data: Ticket.data,
            dataType: 'json'
        },function(tickets){
            Ticket.tickets = tickets;
            Ticket.showList();
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
    showCompanies: function(){
        if( !global.login.companies.length ){
            global.validateMessage('Você não possui acesso as empresas. Procure o administrador do sistema.');
        }
        $.each( global.login.companies, function(key,company){
            $('#company_id').append($('<option>',{
                'value': company.company_id,
                'data-content': '<i class="fa fa-stop" style="color:' + company.company_color + ';"></i> ' + ('0'+company.company_id).slice(-2) + ' - ' + company.company_short_name
            }));
        });
        $('#company_id').selectpicker('refresh');
    },
    showList: function(){
        Ticket.table.clear();
        $.each( Ticket.tickets, function(key, ticket){
            var type = Ticket.type[ticket.ticket_type_id];
            var urgency = Ticket.type[ticket.urgency_id];
            var status = Ticket.status[ticket.ticket_status];
            var row = Ticket.table.row.add([
                '<i data-toggle="tooltip" data-title="' + status.title + '" class="fa fa-' + status.icon + ' txt-' + status.color + '"></i>',
                ('00000'+ticket.ticket_id).slice(-6),
                ('0'+ticket.company_id).slice(-2),
                urgency.name,
                type.name,
                ticket.user_name || '--',
                ticket.owner_name || '--',
                ticket.ticket_update,
                Ticket.actions(ticket)
            ]).node();
            $(row).on('dblclick', function () {
                Ticket.open(key, ticket.ticket_id);
            });
        });
        Ticket.table.draw();
        global.tooltip();
    },
    showUsers: function(){
        $.each( Ticket.users, function(key,user){
            $('#user_id, #owner_id').append($('<option>',{
                'value': user.user_id,
                'text': user.user_name
            }));
        });
        $('#user_id, #owner_id').selectpicker('refresh');
    },
    open: function(key,id){
        global.window({
            url: global.uri.uri_public + 'window.php?module=ticket&action=edit&ticket_id=' + id
        });
    }
};
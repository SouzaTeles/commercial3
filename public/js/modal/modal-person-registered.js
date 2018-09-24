$(document).ready(function(){
    ModalPersonRegistered.showList();
});

ModalPersonRegistered = {
    data: [],
    person: {},
    table: global.table({
        selector: '#modal-table-person-registered',
        scrollY: 136,
        scrollCollapse: 1,
        order: [[2,'asc']],
        noControls: [0]
    }),
    active: function(){
        global.post({
            url: global.uri.uri_public_api + 'person.php?action=active',
            data: {
                person_id: ModalPersonRegistered.person.person_id,
                person_category_id: global.config.person.client_category_id
            },
            dataType: 'json'
        },function(){
            $('#modal-person-registered').modal('hide');
            ModalPersonRegistered.success(ModalPersonRegistered.person);
        });
    },
    beforeActive: function(){
        global.modal({
            size: 'small',
            icon: 'fa-question-circle-o',
            title: 'Confirmação',
            html: '<p>O cliente <b>' + ModalPersonRegistered.person.person_name + '</b> está inativo. Deseja ativa-lo?</p>',
            buttons: [{
                icon: 'fa-times',
                title: 'Não',
                class: 'pull-left'
            },{
                icon: 'fa-check',
                title: 'Sim',
                action: function(){
                    ModalPersonRegistered.active();
                }
            }]
        });
    },
    showList: function(){
        ModalPersonRegistered.table.clear();
        $.each( ModalPersonRegistered.data, function(key, person){
            var row = ModalPersonRegistered.table.row.add([
                person.person_type,
                person.person_code,
                person.person_name
            ]).node();
            $(row).addClass(person.person_active == 'N' ? 'txt-gray' : '').on('dblclick',function(){
                ModalPersonRegistered.person = ModalPersonRegistered.data[key];
                if( ModalPersonRegistered.person.person_active == 'N' ){
                    ModalPersonRegistered.beforeActive(ModalPersonRegistered.person);
                } else {
                    $('#modal-person-registered').modal('hide');
                    ModalPersonRegistered.success(ModalPersonRegistered.person);
                }
            });
        });
        ModalPersonRegistered.table.draw();
    }
};
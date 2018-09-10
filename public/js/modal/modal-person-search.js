$(document).ready(function(){
    ModalPersonSearch.events();
});

ModalPersonSearch = {
    person: {},
    people: [],
    data: {
        person_name: '',
        person_doc: '',
        person_contact: '',
        person_address: 'N',
        person_active: 'Y',
        person_category_id: global.config.person.client_category_id
    },
    table: global.table({
        selector: '#modal-table-people',
        scrollX: 1,
        scrollY: 320,
        scrollCollapse: 1,
        order: [[2,'asc']],
        noControls: [0]
    }),
    active: function(){
        global.post({
            url: global.uri.uri_public_api + 'person.php?action=active',
            data: {
                person_id: ModalPersonSearch.person.person_id,
                person_category_id: global.config.person.client_category_id
            },
            dataType: 'json'
        },function(){
            ModalPersonSearch.success(ModalPersonSearch.person.person_id);
        });
    },
    beforeActive: function(){
        global.modal({
            icon: 'fa-question-circle-o',
            title: 'Confirmação',
            html: '<p>O cliente <b>' + ModalPersonSearch.person.person_name + '</b> está inativo. Deseja ativa-lo?</p>',
            buttons: [{
                icon: 'fa-times',
                title: 'Não',
                class: 'pull-left'
            },{
                icon: 'fa-check',
                title: 'Sim',
                action: function(){
                    ModalPersonSearch.active();
                }
            }]
        });
    },
    events: function(){
        var $modal = $('#modal-person-search');
        $modal.find('form').on('submit',function(e){
            e.preventDefault();
            e.stopPropagation();
            ModalPersonSearch.form2data();
            if( !ModalPersonSearch.data.person_name.length && !ModalPersonSearch.data.person_doc.length && !ModalPersonSearch.data.person_contact.length ){
                global.validateMessage('Informe pelo menos um dos campos para realizar a pesquisa.');
                return;
            }
            ModalPersonSearch.getList();
        });
        $('#modal_show_address').on('change',function(){
            ModalPersonSearch.table.column(5).visible(this.checked);
        });
        $('#modal_person_active').bootstrapToggle({
            width: '100%',
            on: 'Somente ativos',
            off: 'Ativos e inativos',
            onstyle: 'blue',
            offstyle: 'gray-dark'
        });
        $('#modal_person_address').bootstrapToggle({
            width: '100%',
            on: 'Exibir endereço',
            off: 'Ocultar endereço',
            onstyle: 'blue',
            offstyle: 'gray-dark'
        }).on('change',function(){
            ModalPersonSearch.table.column(5).visible(this.checked);
            ModalPersonSearch.table.columns.adjust().draw();
        });
        $('#modal_person_name').focus();
        ModalPersonSearch.table.column(5).visible(false);
    },
    form2data: function(){
        ModalPersonSearch.data.person_name = $('#modal_person_name').val();
        ModalPersonSearch.data.person_doc = $('#modal_person_doc').val();
        ModalPersonSearch.data.person_contact = $('#modal_person_contact').val();
        ModalPersonSearch.data.person_address = $('#modal_person_address').prop('checked') ? 'Y' : 'N';
        ModalPersonSearch.data.person_active = $('#modal_person_active').prop('checked') ? 'Y' : 'N';
    },
    getList: function(){
        global.post({
            url: global.uri.uri_public_api + 'person.php?action=getList',
            data: ModalPersonSearch.data,
            dataType: 'json'
        },function(data){
            ModalPersonSearch.people = data;
            ModalPersonSearch.showList();
            ModalPersonSearch.legend();
        });
    },
    legend: function(){
        var $modal = $('#modal-person-search');
        var found = ModalPersonSearch.people.length;
        $modal.find('.found').html('<i class="fa fa-users"></i> ' + found + ' Pessoa' + ( found != 1 ? 's' : '' ) + ' localizada' + ( found != 1 ? 's' : '' ))
    },
    showList: function(){
        ModalPersonSearch.table.clear();
        $.each( ModalPersonSearch.people, function(key, person){
            var row = ModalPersonSearch.table.row.add([
                '<div class="cover"' + ( person.image ? ' style="background-image:url(' + person.image + ')"' : '' ) + '></div>',
                person.person_code,
                person.person_name,
                person.person_type,
                (person.person_document ? person.person_document : '--'),
                ( $('#modal_person_address').prop('checked') ? (person.address_public_place + ', ' + person.address_number + ' - ' + person.district_name + ' - ' + person.city_name + ' - ' + person.uf_code + ' - ' + 'CEP ' + ( !!person.address_cep ? person.address_cep : '<i>não informado</i>' )) : '<i>refaça a consulta</i>')
            ]).node();
            $(row).addClass(person.person_active == 'N' ? 'txt-gray' : '').attr('data-key',key).on('dblclick',function(){
                ModalPersonSearch.person = ModalPersonSearch.people[$(this).attr('data-key')];
                if( person.person_active == 'N' ){
                    ModalPersonSearch.beforeActive();
                } else {
                    ModalPersonSearch.success(ModalPersonSearch.person.person_id);
                }
            });
        });
        ModalPersonSearch.table.draw();
    },
    success: function(person_id){
        Person.get({person_id: person_id});
        $('#modal-person-search').modal('hide');
        Budget.goTo(2);
    }
};
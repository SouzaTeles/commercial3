$(document).ready(function(){
    ModalCepSearch.events();

    global.mask();
    global.selectpicker();
});

ModalCepSearch = {
    cep: [],
    data: {
        uf_code: '',
        cep_code: '',
        city_name: '',
        public_place: '',
        district_name: '',
        public_place_type: ''
    },
    table: global.table({
        selector: '#modal-table-cep',
        scrollY: 140,
        scrollCollapse: 1,
        order: [[4,'asc']]
    }),
    events: function(){
        $('#form-cep-search').on('submit',function(e){
            e.preventDefault();
            e.stopPropagation();
            ModalCepSearch.form2data();
            if( ModalCepSearch.validate() ) {
                ModalCepSearch.getList();
            }
        });
        $('#button-search-uf-remove').click(function(){
            $('#modal_search_uf_id').selectpicker('val','default');
        });
    },
    form2data: function(){
        ModalCepSearch.data = {
            uf_id: $('#modal_search_uf_id').val(),
            cep_code: $('#modal_search_cep_code').val(),
            city_name: $('#modal_search_city_name').val(),
            public_place: $('#modal_search_public_place').val(),
            district_name: $('#modal_search_district_name').val()
        };
    },
    getList: function(){
        global.post({
            url: global.uri.uri_public_api + 'cep.php?action=getList',
            data: ModalCepSearch.data,
            dataType: 'json'
        },function(cep){
            ModalCepSearch.cep = cep;
            ModalCepSearch.showList();
        });
    },
    showList: function(){
        ModalCepSearch.table.clear();
        $.each( ModalCepSearch.cep, function(key, cep){
            var row = ModalCepSearch.table.row.add([
                cep.cep_code,
                cep.uf_id,
                cep.city_name,
                cep.district_name,
                cep.public_place
            ]).node();
            $(row).attr('data-key',key).on('dblclick',function(){
                ModalCepSearch.success(ModalCepSearch.cep[$(this).attr('data-key')]);
            });
        });
        ModalCepSearch.table.draw();
    },
    validate: function(){
        if(
            !ModalCepSearch.data.cep_code.length &&
            !ModalCepSearch.data.uf_id.length &&
            !ModalCepSearch.data.city_name.length &&
            !ModalCepSearch.data.district_name.length &&
            !ModalCepSearch.data.public_place.length
        ){
            global.validateMessage('Pelo menos um dos campos deverá ser informado.');
            return false;
        }
        if( ModalCepSearch.data.cep_code.length && ModalCepSearch.data.cep_code.length != 9 ){
            global.validateMessage('Informe um CEP válido.');
            return false;
        }
        return true;
    }
};
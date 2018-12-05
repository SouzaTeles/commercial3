$(document).ready(function() {

    Company.events();
    Company.getList();
    global.unLoader();

});

Company = {
    companies: [],
    table: global.table({
        searching: 1,
        noControls: [0,1,5],
        order: [[3, "asc"]],
        selector: '#table-companies'
    }),
    actions: function(key,company_id){
        return(
            '<div class="dropdown dropdown-budget dropdown-actions">' +
                '<button class="btn btn-empty-blue" type="button" data-toggle="dropdown">' +
                    '<i class="fa fa-ellipsis-v"></i>' +
                '</button>' +
                '<ul class="dropdown-menu dropdown-custom pull-right">' +
                    '<li><a disabled="' + ( global.login.access.company.edit.value == 'N' ) + '" data-action="edit" data-key="' + key + '" data-id="' + company_id + '" class="dropdown-item" href="#"><i class="fa fa-pencil txt-blue"></i>Editar</a></li>' +
                    '<li><a disabled="' + ( global.login.access.company.audit.value == 'N' ) + '" data-action="audit" data-key="' + key + '" data-id="' + company_id + '" class="dropdown-item" href="#"><i class="fa fa-shield txt-green"></i>Auditoria</a></li>' +
                '</ul>' +
            '</div>'
        );
    },
    audit: function(key,company_id){
        global.post({
            url: global.uri.uri_public + 'api/modal.php?modal=modal-audit',
            data: {
                log_script: 'company',
                log_parent_id: company_id
            },
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'big',
                id: 'modal-audit',
                class: 'modal-audit',
                icon: 'fa-shield',
                title: Company.companies[key].company_name,
                html: html,
                buttons: [{
                    title: 'Fechar'
                }]
            });
        });
    },
    edit: function(key,company_id){
        if( global.login.access.company.edit.value == 'N' ) return;
        global.window({
            url: global.uri.uri_public + 'window.php?module=company&action=new&company_id=' + company_id
        });
    },
    events: function(){
        $('#company_search').keyup(function(){
            Company.table.search(this.value).draw();
        });
        $('#button-refresh').click(function(){
            Company.getList();
        });
        $('#button-new').click(function(){
            Company.new();
        });
    },
    getList: function(){
        global.post({
            url: global.uri.uri_public_api + 'company.php?action=getList',
            dataType: 'json'
        }, function(companies){
            Company.companies = companies;
            Company.showList();
        });
    },
    new: function(){
        global.window({
            url: global.uri.uri_public + 'window.php?module=company&action=new'
        });
    },
    showList: function(){
        Company.table.clear();
        $.each( Company.companies, function( key, company ){
            var row = Company.table.row.add([
                '<div class="cover box-shadow"' + ( company.image ? 'style="background-image:url(' + company.image + '")' : '' ) + '></div>',
                '<i class="fa fa-stop" style="color:' + company.company_color + ';"></i>',
                '<span>'+company.company_active+'</span><i title="' + ( company.company_active == 'Y' ? 'Ativo' : 'Inativo' ) + '" data-toggle="tooltip" class="fa fa-toggle-' + ( company.company_active == 'Y' ? 'on' : 'off' ) + '"></i>',
                company.company_code,
                company.company_name,
                Company.actions(key,company.company_id)
            ]).node();
            $(row).on('dblclick',function(){
                Company.edit(key,company.company_id);
            });
        });
        Company.table.draw();
        var table = $('#table-companies');
        $(table).find('a[data-action="edit"][disabled="false"]').click(function(e){
            e.preventDefault();
            e.stopPropagation();
            Company.edit($(this).attr('data-key'),$(this).attr('data-id'));
        });
        $(table).find('a[data-action="audit"][disabled="false"]').click(function(e){
            e.preventDefault();
            e.stopPropagation();
            Company.audit($(this).attr('data-key'),$(this).attr('data-id'));
        });
        $('footer div').html('<i class="fa fa-building-o"></i> ' + Company.companies.length + ' Empresas');
        global.tooltip();
    }
};
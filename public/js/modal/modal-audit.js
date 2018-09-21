$(document).ready(function(){
    ModalAudit.events();
    ModalAudit.getList();
});

ModalAudit = {
    data: {},
    audit: [],
    actions: {
        'insert': { title: 'Cadastro', icon: 'fa-plus', color: 'green' },
        'creditAuthorization': { title: 'Liberação de Crédito', icon: 'fa-usd', color: 'brown' },
        'itemDiscountAuthorization': { title: 'Desconto Individual', icon: 'fa-usd', color: 'brown' },
        'generalDiscountAuthorization': { title: 'Desconto Geral', icon: 'fa-usd', color: 'brown' }
    },
    table: global.table({
        selector: '#modal-table-audit',
        scrollX: 1,
        scrollY: 320,
        scrollCollapse: 1,
        order: [[3,'desc']],
        noControls: [0,4]
    }),
    events: function(){
        ModalAudit.table.on('draw',function(){
            $('#modal-table-audit').find('button').click(function(){
                ModalAudit.authorization($(this).attr('data-key'));
            });
        });
    },
    getList: function(){
        global.post({
            url: global.uri.uri_public_api + 'audit.php?action=getList',
            data: ModalAudit.data,
            dataType: 'json'
        },function(data){
            ModalAudit.audit = data;
            ModalAudit.showList();
        });
    },
    showList: function(){
        ModalAudit.table.clear();
        $.each( ModalAudit.audit, function(key, audit){
            var action  = ModalAudit.actions[audit.log_action] || { title:audit.log_action, icon:'fa-question-circle', color:'blue-dark' };
            ModalAudit.table.row.add([
                '<i class="fa ' + action.icon + ' txt-' + action.color + '"></i>',
                action.title,
                audit.user_name,
                '<span>' + audit.log_date + '</span>' + audit.log_date_br,
                '<button data-key="' + key + '" class="btn btn-empty-red"><i class="fa fa-file-code-o"></i></button>'
            ]);
        });
        ModalAudit.table.draw();
    },
    authorization: function(key){
        global.post({
            url: global.uri.uri_public + 'api/modal.php?modal=modal-audit-authorization',
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'small',
                id: 'modal-audit-authorization',
                class: 'modal-audit-authorization',
                icon: 'fa-lock',
                title: 'Autorização',
                html: html,
                buttons: [{
                    icon: 'fa-times',
                    class: 'btn-red pull-left',
                    title: 'Cancelar'
                },{
                    icon: 'fa-unlock',
                    class: 'btn-green-dark',
                    title: 'Autorizar',
                    unclose: true,
                    id: 'button-submit'
                }],
                shown: function(){
                    $('#modal_user_user').focus();
                    ModalAuditAuthorization.success = function(){
                        console.log(key);
                        ModalAudit.showMore(key);
                    }
                }
            });
        });
    },
    showMore: function(key){
        global.post({
            url: global.uri.uri_public + 'api/modal.php?modal=modal-audit-info',
            data: ModalAudit.audit[key],
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'big',
                id: 'modal-audit-info',
                class: 'modal-audit-info',
                icon: 'fa-file-code-o',
                title: 'Data Post',
                html: html,
                buttons: [{
                    title: 'Fechar'
                }]
            });
        });
    }
};
var budget_id = global.url.searchParams.get('budget_id');

$(document).ready(function(){
    if( !!budget_id ){
        Budget.get(budget_id);
    } else {
        Company.get();
        Term.getList();
    }
    global.mask();
    global.unLoader();
});

Keyboard = {
    events: function(){
        global.listener.simple_combo("shift enter", function () {
            Budget.section++;
            if( Budget.section > 4 ){
                Budget.section = 1;
            }
            Budget.goTo(Budget.section);
        });
        global.listener.simple_combo("ctrl enter", function () {
            Budget.section--;
            if( Budget.section < 1 ){
                Budget.section = 1;
            }
            Budget.goTo(Budget.section);
        });
        global.listener.simple_combo("ctrl 1", function () {
            Budget.goTo(1);
        });
        global.listener.simple_combo("ctrl 2", function () {
            Budget.goTo(2);
        });
        global.listener.simple_combo("ctrl 3", function () {
            Budget.goTo(3);
        });
        global.listener.simple_combo("ctrl 4", function () {
            Budget.goTo(4);
        });
        global.listener.simple_combo("f2", function () {
            if( $('#product_code').is(':focus') || $('#product_name').is(':focus') ){
                Item.search();
                return;
            }
            if( $('#person_code').is(':focus') || $('#person_name').is(':focus') ){
                Person.search();
                return;
            }
        });
    }
};

Company = {
    company: {
        company_id: null,
        company_name: '',
        company_short_name: '',
        company_consumer_id: null
    },
    afterGet: function(){
        Budget.events();
        Item.events();
        Person.events();
        Address.events();
        Term.events();
        Payment.events();
        Keyboard.events();
        Item.table.draw();
        Payment.table.draw();
    },
    get: function(){
        var company_id = global.url.searchParams.get('company_id');
        if( !!company_id ){
            $.each(global.login.companies, function (key, company) {
                if (company.company_id == company_id) {
                    Company.company = company;
                    Budget.init();
                    Company.show();
                    if( !!global.url.searchParams.get('clone') ){
                        Budget.cloned();
                    } else {
                        Budget.tools();
                        if( Budget.budget.budget_status == 'B' ){
                            Budget.billed();
                        } else if( Budget.budget.budget_status == 'L' ){
                            Budget.blocked();
                        } else if( Budget.budget.budget_status == 'C' ){
                            Budget.canceled();
                        } else{
                            Company.afterGet();
                        }
                    }
                }
            });
        }
        if( !Company.company.company_id ){
            global.modal({
                icon: 'fa-warning',
                title: 'Aviso',
                html: '<p>Voc não possui acesso a empresa informada.</p>',
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

Budget = {
    section: 1,
    budget: {},
    budgets: [],
    type: {
        'B': {
            icon: 'file-o',
            title: 'Orçamento',
            color: 'gray'
        },
        'P': {
            icon: 'file-powerpoint-o',
            title: 'Pedido de Venda',
            color: 'red'
        },
        'D': {
            icon: 'file-text-o',
            title: 'DAV',
            color: 'orange'
        }
    },
    origin: {
        'D': {
            icon: 'desktop',
            title: 'Desktop',
            color: 'blue-light'
        },
        'M': {
            icon: 'mobile',
            title: 'Celular',
            color: 'blue-light'
        }
    },
    status: {
        'O': {
            icon: 'clock-o',
            title: 'Aberto',
            color: 'gray'
        },
        'L': {
            icon: 'cloud',
            title: 'Liberado',
            color: 'blue'
        },
        'T': {
            icon: 'cloud-download',
            title: 'Faturado',
            color: 'green'
        }
    },
    delivery: {
        'Y': {
            icon: 'truck',
            title: 'Entrega',
            color: 'blue-light'
        },
        'N': {
            icon: 'truck',
            title: 'Entrega',
            color: 'gray'
        }
    },
    table: global.table({
        selector: '#table-budgets',
        searching: 1,
        noControls: [0,7],
        order: [[2,'desc']]
    }),
    typeahead: {
        items: 10,
        delay: 500,
        last: '',
        timer: 0
    },
    add: function(){
        global.post({
            url: global.uri.uri_public_api + 'budget.php?action=insert',
            data: Budget.budget,
            dataType: 'json'
        }, function(data){
            Budget.saved(data);
            if( !!window.opener ){
                try{
                    window.opener.Budget.getList();
                } catch(e){
                    console.log(e);
                }
            }
        });
    },
    afterRecover: function(){
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-budget-recovered',
            dataType: 'html'
        },function(html){
            global.modal({
                icon: 'fa-info-circle',
                id: 'modal-budget-recovered',
                class: 'modal-budget-recovered',
                title: 'Informação',
                html: html,
                buttons: [{
                    icon: 'fa-check',
                    title: 'Ok'
                }],
                hidden: function(){
                    global.onLoader();
                    location.reload();
                }
            })
        });
    },
    beforeRecover: function(){
        if( Budget.budget.external_type == 'D' ){
            Budget.unrecover();
        } else {
            Budget.recover();
        }
    },
    beforeSave: function(success){
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-budget-confirm',
            data: {
                type: Budget.budget.export ? ( Budget.budget.export == 'dav' ? 'Dav' : 'Pedido' ) : 'orçamento',
                stock: Item.check()
            },
            dataType: 'html'
        },function(html){
            global.modal({
                icon: 'fa-question-circle',
                title: 'Confirmação',
                html: html,
                buttons: [{
                    icon: 'fa-check',
                    title: 'Confirmar',
                    id: 'button-before-save-confirm',
                    action: function(){
                        success();
                    }
                }],
                shown: function(){
                    $('#button-before-save-confirm').focus();
                }
            });
        });
    },
    billed: function(){
        $('input').prop('disabled',true);
        $('.panel-items button').not('[data-action="info"]').prop('disabled',true);
        $('.panel-person button').prop('disabled',true);
        $('#file-image-person').filestyle('disabled',true);
        $('.panel-payment button').prop('disabled',true);
        global.modal({
            icon: 'fa-info-circle',
            id: 'modal-budget-recover',
            class: 'modal-budget-recover',
            title: 'Aviso',
            html: '<p>O pedido encontra-se exportado e faturado.</p>',
            buttons: [{
                icon: 'fa-check',
                title: 'Fechar'
            }]
        });
    },
    blocked: function(){
        $('input').prop('disabled',true);
        $('.panel-items button').not('[data-action="info"]').prop('disabled',true);
        $('.panel-person button').prop('disabled',true);
        $('#file-image-person').filestyle('disabled',true);
        $('.panel-payment button').prop('disabled',true);
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-budget-blocked',
            dataType: 'html'
        },function(html){
            global.modal({
                icon: 'fa-info-circle',
                id: 'modal-budget-recover',
                class: 'modal-budget-recover',
                title: 'Aviso',
                html: html,
                buttons: [{
                    icon: 'fa-check',
                    title: 'Fechar'
                }]
            })
        });
    },
    canceled: function(){
        $('input').prop('disabled',true);
        $('.panel-items button').not('[data-action="info"]').prop('disabled',true);
        $('.panel-person button').prop('disabled',true);
        $('#file-image-person').filestyle('disabled',true);
        $('.panel-payment button').prop('disabled',true);
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-budget-canceled',
            dataType: 'html'
        },function(html){
            global.modal({
                icon: 'fa-info-circle',
                id: 'modal-budget-recover',
                class: 'modal-budget-recover',
                title: 'Aviso',
                html: html,
                buttons: [{
                    icon: 'fa-check',
                    title: 'Fechar'
                }]
            })
        });
    },
    clone: function(){
        global.modal({
            icon: 'fa-question-circle',
            title: 'Confirmação',
            html: '<p>Deseja realmente duplicar o orçamento?</p><p>Observação: Os descontos e a carta de crédito serão removidos do orçamento original.</p>',
            buttons: [{
                icon: 'fa-times',
                class: 'pull-left',
                title: 'Cancelar'
            },{
                icon: 'fa-check',
                title: 'Confirmar',
                action: function(){
                    global.onLoader();
                    location.href = global.uri.uri_public + 'window.php?module=budget&action=new&clone=1&budget_id=' + Budget.budget.budget_id + '&company_id=' + Company.company.company_id
                }
            }]
        });
    },
    cloned: function(){
        Budget.budget.clone_id = Budget.budget.budget_id;
        Budget.budget.budget_id = null;
        Budget.budget.budget_credit = 'N';
        Budget.budget.budget_status = 'O';
        Budget.budget.budget_delivery = 'N';
        Budget.budget.budget_delivery_date = global.dateAddDays(global.today(),3);

        Budget.budget.budget_value_st = 0;
        Budget.budget.budget_value_icms = 0;
        Budget.budget.budget_value_addition = 0;
        Budget.budget.budget_value_discount = 0;
        Budget.budget.budget_aliquot_discount = 0;
        Budget.budget.budget_value_total = Budget.budget.budget_value;

        Budget.budget.external_id = null;
        Budget.budget.external_code = null;
        Budget.budget.external_type = null;

        Budget.budget.document_id = null;
        Budget.budget.document_type = null;
        Budget.budget.document_code = null;
        Budget.budget.document_canceled = null;

        $.each(Budget.budget.items,function(key,item){
            item.budget_item_value_st = 0;
            item.budget_item_value_icms = 0;
            item.budget_item_value_discount = 0;
            item.budget_item_aliquot_discount = 0;
            item.budget_item_value_total = item.budget_item_quantity * item.budget_item_value_unitary;
        });

        $.each(Budget.budget.payments,function(key,payment){
            if( !!payment && payment.budget_payment_credit == 'Y'){
                Budget.budget.payments.splice(key,1);
            }
        });

        Budget.tools();
        Item.total();
        Item.showList();
        Company.afterGet();
        Payment.showList();
        Payment.recalculate();
    },
    close: function(){
        global.modal({
            icon: 'fa-question-circle-o',
            title: 'Confirmação',
            html: '<p>Deseja realmente cancelar a ' + ( Budget.budget.budget_id ? 'edição' : 'inclusão' ) + ' do pedido?</p>',
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
    discount: function(){
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-budget-discount',
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'small',
                icon: 'fa-usd',
                id: 'modal-budget-discount',
                class: 'modal-budget-discount',
                title: 'Desconto Geral',
                html: html,
                buttons: [{
                    icon: 'fa-times',
                    title: 'Cancelar'
                },{
                    icon: 'fa-check',
                    title: 'Aplicar',
                    unclose: true,
                    action: function(){
                        ModalBudgetDiscount.discountAuthorization();
                    }
                }],
                show: function(){
                    $('#modal_discount_aliquot').focus();
                }
            });
        });
    },
    discountAliquot: function(data){
        Budget.budget.budget_value_discount = 0;
        Budget.budget.budget_aliquot_discount = data.aliquot_discount;
        $.each(Budget.budget.items,function(key,item){
            item.budget_item_aliquot_discount = data.aliquot_discount.toFixed(2);
            item.budget_item_value_discount = parseFloat(((data.aliquot_discount / 100) * item.budget_item_value).toFixed(2));
            item.budget_item_value_total = item.budget_item_value - item.budget_item_value_discount;
            Budget.budget.budget_value_discount += item.budget_item_value_discount;
        });
        Budget.budget.budget_value_total = Budget.budget.budget_value - Budget.budget.budget_value_discount + Budget.budget.budget_value_addition;
        Item.total();
        Item.showList();
        Payment.total();
    },
    edit: function(){
        console.log('editando :)');
        global.post({
            url: global.uri.uri_public_api + 'budget.php?action=edit',
            data: Budget.budget,
            dataType: 'json'
        }, function(data){
            Budget.saved(data);
            if( !!window.opener ){
                window.opener.Budget.getList();
            }
        });
    },
    events: function(){
        $('#button-budget-cancel').click(function(){
            Budget.close();
        }).prop('disabled',false);
        $('#button-budget-save').click(function(){
            Budget.budget.export = null;
            if( Budget.validate() ){
                if( !Budget.budget.budget_id ){
                    Budget.submit = function(){
                        Budget.beforeSave(function(){
                            Budget.add();
                        });
                    }
                } else {
                    Budget.submit = function(){
                        Budget.beforeSave(function(){
                            Budget.edit();
                        });
                    }
                }
                Seller.search(function(){
                    Payment.check();
                });
            }
        }).prop('disabled',Budget.budget.budget_status != 'O');
        $('#button-budget-save-dav').click(function(){
            Budget.budget.export = 'dav';
            if( Budget.validate() ){
                if( !Budget.budget.budget_id ){
                    Budget.submit = function(){
                        Budget.beforeSave(function(){
                            Budget.add();
                        });
                    }
                } else {
                    Budget.submit = function(){
                        Budget.beforeSave(function(){
                            if( Budget.budget.external_type == 'P' ){
                                Budget.budget.external_id = null;
                                Budget.budget.external_type = null;
                                Budget.budget.external_code = null;
                            }
                            Budget.edit();
                        });
                    }
                }
                Seller.search(function(){
                    Payment.check();
                });
            }
        }).prop('disabled',Budget.budget.budget_status != 'O');
        $('#button-budget-save-order').click(function(){
            Budget.budget.export = 'order';
            if( Budget.validate() ){
                if( !Budget.budget.budget_id ){
                    Budget.submit = function(){
                        Budget.beforeSave(function(){
                            Budget.add();
                        });
                    }
                } else {
                    Budget.submit = function(){
                        Budget.beforeSave(function(){
                            Budget.edit();
                        });
                    }
                }
                Seller.search(function(){
                    Payment.check();
                });
            }
        }).prop('disabled',Budget.budget.budget_status != 'O');
    },
    get: function(budget_id){
        global.post({
            url: global.uri.uri_public_api + 'budget.php?action=get',
            data: {
                budget_id: budget_id,
                get_budget_items: 1,
                get_product_stock: 1,
                get_budget_person: 1,
                get_person_credit: 1,
                get_person_credit_limit: 1,
                get_budget_address: 1,
                get_person_attribute: 1,
                get_budget_seller: 1,
                get_budget_payments: 1
            },
            dataType: 'json'
        },function(budget){
            Seller.seller = budget.seller;
            Person.person = budget.person;
            Address.delivery = budget.address;
            delete budget.seller;
            delete budget.person;
            delete budget.address;
            Budget.budget = budget;
            Budget.budget.instance_id = Budget.instance();
            Budget.budget.address_uf_id = Address.delivery.uf_id;
            Company.get();
            Term.getList();
        });
    },
    goTo: function(index){
        Budget.section = index;
        var panels = [
            { selector: '.panel-items', focus: '#product_code' },
            { selector: '.panel-person', focus: '#person_code' },
            { selector: '.panel-payment', focus: '#term_code' },
            { selector: '.panel-buttons', focus: '#button-budget-save' }
        ];
        global.scrollTo({
            delay: 500,
            addition: 200,
            selector: panels[index-1].selector
        });
        if( !!panels[index-1].focus ){
            setTimeout(function(){
                $(panels[index-1].focus).focus().select();
            },500);
        }
    },
    init: function(){
        if( !budget_id ) {
            Budget.budget = {
                budget_id: null,
                company_id: Company.company.company_id,
                person_id: null,
                term_id: null,
                address_code: null,
                address_uf_id: null,
                external_id: null,
                external_type: null,
                external_code: null,
                budget_code: '',
                budget_value: 0,
                budget_aliquot_discount: 0,
                budget_value_discount: 0,
                budget_value_addition: 0,
                budget_value_icms: 0,
                budget_value_st: 0,
                budget_cost: 0,
                budget_value_total: 0,
                budget_note: '',
                budget_note_document: '',
                budget_credit: 'N',
                budget_status: 'O',
                budget_delivery: 'N',
                budget_delivery_date: global.dateAddDays(global.today(),3),
                items: [],
                payments: [],
                credit: {
                    value: 0,
                    payable: []
                },
                export: null,
                authorization: [],
                instance_id: Budget.instance(),
            };
            Item.init();
            Person.init();
            Term.init();
        }
        Person.data2form();
        Item.showList();
        Item.total();
        Address.showList();
        Address.showDelivery();
        Term.data2form();
        Payment.showList();
        Payment.total();
    },
    instance: function(){
        return ('00000'+btoa(global.random(1000000,9999999)).toUpperCase().substr(0,10)).slice(-10);
    },
    item: function(){
        Item.search();
    },
    new: function(){
        global.modal({
            icon: 'fa-question-circle',
            title: 'Confirmação',
            html: '<p>Deseja realmente criar um novo orçamento?</p><p>As informações atuais poderão ser perdidas.</p>',
            buttons: [{
                icon: 'fa-times',
                class: 'pull-left',
                title: 'Cancelar'
            },{
                icon: 'fa-check',
                title: 'Confirmar',
                action: function(){
                    global.onLoader();
                    location.href = global.uri.uri_public + 'window.php?module=budget&action=new&company_id=' + Company.company.company_id;
                }
            }]
        });
    },
    note: function(){
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-budget-note',
            dataType: 'html'
        },function(html){
            global.modal({
                icon: 'fa-file-text-o',
                id: 'modal-budget-note',
                class: 'modal-budget-note',
                title: 'Observações do Pedido',
                html: html,
                buttons: [{
                    icon: 'fa-floppy-o',
                    title: 'Atualizar',
                    action: function(){
                        Budget.budget.budget_note = $('#modal_budget_note').val();
                        Budget.budget.budget_note_document = $('#modal_budget_note_document').val();
                    }
                }],
                shown: function(){
                    $('#modal_budget_note').val(Budget.budget.budget_note);
                    $('#modal_budget_note_document').val(Budget.budget.budget_note_document);
                }
            })
        });
    },
    print: function(params){
        location.href = global.uri.uri_public + 'window.php?module=budget&action=' + params.action + '&budget_id=' + params.budget_id;
        // global.window({
        //     url: global.uri.uri_public + 'window.php?module=budget&action=' + params.action + '&budget_id=' + params.budget_id,
        //     width: params.width || 860,
        //     height: params.height || 620
        // });
    },
    recover: function(){
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-budget-recover',
            data: { budget_id: Budget.budget.budget_id },
            dataType: 'html'
        },function(html){
            global.modal({
                icon: 'fa-question-circle',
                id: 'modal-budget-recover',
                class: 'modal-budget-recover',
                title: 'Recuperar Pedido?',
                html: html,
                buttons: [{
                    icon: 'fa-eye',
                    title: 'Apenas Visualizar'
                },{
                    icon: 'fa-refresh',
                    title: 'Recuperar Pedido',
                    action: function(){
                        global.post({
                            url: global.uri.uri_public_api + 'budget.php?action=recover',
                            data: { budget_id: Budget.budget.budget_id },
                            dataType: 'json'
                        },function(){
                            Budget.afterRecover();
                        });
                    }
                }]
            })
        });
    },
    saved: function(data){
        var budget = data;
        global.post({
            url: global.uri.uri_public + 'api/modal.php?modal=modal-budget-saved',
            data: budget,
            dataType: 'html'
        },function(html){
            global.modal({
                id: 'modal-saved',
                class: 'modal-saved',
                size: 'small',
                icon: 'fa-check',
                title: budget.budget_title + ( budget.budget_status == 'L' ? ' exportado!' : ' salvo!' ),
                html: html,
                buttons: [{
                    icon: 'fa-check',
                    title: 'Fechar (10)'
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
    seller: function(){
        Seller.search();
    },
    setDelivery: function(success){
        global.post({
            url: global.uri.uri_public + 'api/modal.php?modal=modal-budget-delivery',
            dataType: 'html'
        },function(html){
            global.modal({
                id: 'modal-budget-delivery',
                class: 'modal-budget-delivery',
                icon: 'fa-truck',
                title: 'Informação de Entrega',
                html: html,
                buttons: [{
                    icon: 'fa-times',
                    title: 'Cancelar',
                    class: 'pull-left'
                },{
                    icon: 'fa-check',
                    title: 'Confirmar',
                    action: function(){
                        ModalDelivery.form2data();
                        Budget.budget.budget_delivery = ModalDelivery.delivery.budget_delivery;
                        Budget.budget.budget_delivery_date = ModalDelivery.delivery.budget_delivery_date;
                        Budget.budget.budget_note_document = ModalDelivery.delivery.budget_note_document;
                        if( !!success ) success();
                    }
                }],
                shown: function(){
                    ModalDelivery.delivery = {
                        budget_delivery: Budget.budget.budget_delivery,
                        budget_delivery_date: Budget.budget.budget_delivery_date,
                        budget_note_document: Budget.budget.budget_note_document
                    };
                    ModalDelivery.data2form();
                }
            });
        });
    },
    submit: function(){},
    ticket: function(budget_id){
        global.window({
            url: global.uri.uri_public + 'window.php?module=commercial&action=ticket&budget_id=' + budget_id,
            width: 300,
            height: 420
        },function(){
            console.log('fechou');
        });
    },
    tools: function(){
        var $panel = $('.panel-tools');
        $panel.find('button[data-action="new"]').click(function(){
            Budget.new();
        });
        $panel.find('button[data-action="clone"]').click(function(){
            Budget.clone();
        }).prop('disabled',!Budget.budget.budget_id);
        $panel.find('button[data-action="recover"]').click(function(){
            Budget.beforeRecover();
        }).prop('disabled',Budget.budget.budget_status == 'O');
        $panel.find('button[data-action="save"]').click(function(){
            $('#button-budget-save').click();
        }).prop('disabled',Budget.budget.budget_status != 'O');
        $panel.find('button[data-action="close"]').click(function(){
            Budget.close();
        });
        $panel.find('button[data-action="print"]').click(function(){
            if( !!window.opener ){
                window.opener.Budget.print({
                    budget_id: Budget.budget.budget_id,
                    action: 'print'
                });
            } else{
               global.validateMessage('A janela é órfã e não sera possível realizar esta operação.')
            }
        }).prop('disabled',!Budget.budget.budget_id);
        $panel.find('button[data-action="pdf"]').click(function(){
            if( !!window.opener ){
                window.opener.Budget.print({
                    budget_id: Budget.budget.budget_id,
                    action: 'print'
                });
            } else{
                global.validateMessage('A janela é órfã e não sera possível realizar esta operação.')
            }
        }).prop('disabled',!Budget.budget.budget_id);
        $panel.find('button[data-action="mail"]').click(function(){
            if( !!window.opener ){
                window.opener.Budget.print({
                    budget_id: Budget.budget.budget_id,
                    action: 'mail'
                });
            } else{
                global.validateMessage('A janela é órfã e não sera possível realizar esta operação.')
            }
        }).prop('disabled',!Budget.budget.budget_id);
        $panel.find('button[data-action="pdf"]').click(function(){
            if( !!window.opener ){
                window.opener.Budget.print({
                    budget_id: Budget.budget.budget_id,
                    action: 'pdf'
                });
            } else{
                global.validateMessage('A janela é órfã e não sera possível realizar esta operação.')
            }
        }).prop('disabled',!Budget.budget.budget_id);
        $panel.find('button[data-action="seller"]').click(function(){
            Seller.search();
        }).prop('disabled',Budget.budget.budget_status != 'O');
        $panel.find('button[data-action="item"]').click(function(){
            Item.search();
        }).prop('disabled',Budget.budget.budget_status != 'O');
        $panel.find('button[data-action="client"]').click(function(){
            Person.search();
        }).prop('disabled',Budget.budget.budget_status != 'O');
        $panel.find('button[data-action="clientInfo"]').click(function(){
            Person.info();
        }).prop('disabled',Budget.budget.budget_status != 'O');
        $panel.find('button[data-action="note"]').click(function(){
            Budget.note();
        }).prop('disabled',Budget.budget.budget_status != 'O');
        $panel.find('button[data-action="discount"]').click(function(){
            Budget.discount();
        }).prop('disabled',Budget.budget.budget_status != 'O');
        $panel.find('button[data-action="delivery"]').click(function(){
            Budget.setDelivery();
        }).prop('disabled',Budget.budget.budget_status != 'O');
        $panel.find('[data-toggle="tooltip"]').tooltip();
    },
    validate: function(){
        if( !!Item.item && !!Item.item.product_id ){
            global.validateMessage('Existe um produto em edição. Verifique.',function(){
                setTimeout(function(){
                    $('#product_code').focus();
                },200);
            });
            global.scrollTo({
                delay: 500,
                addition: 200,
                selector: '.panel-budget'
            });
            return false;
        }
        if( !Budget.budget.items.length ){
            global.validateMessage('Ao menos um produto deverá ser adicionado ao pedido.',function(){
                setTimeout(function(){
                    $('#product_code').focus();
                },200);
            });
            global.scrollTo({
                delay: 500,
                addition: 200,
                selector: '.panel-budget'
            });
            return false;
        }
        if( !Budget.budget.client_id ){
            global.validateMessage('O cliente deverá ser informado.',function(){
                setTimeout(function(){
                    $('#person_code').focus();
                },200);
            });
            global.scrollTo({
                delay: 500,
                addition: 200,
                selector: '.panel-person'
            });
            return false;
        }
        if( !Budget.budget.address_code ){
            global.validateMessage('Informe o endereço de entrega do pedido.');
            global.scrollTo({
                delay: 500,
                addition: 200,
                selector: '.panel-person'
            });
            return false;
        }
        if( !!Budget.budget.export ){
            if(!Budget.budget.payments.length && Budget.budget.credit.value == 0){
                global.validateMessage('As informações de pagamentos deverão ser adicionadas.');
                global.scrollTo({
                    delay: 500,
                    addition: 200,
                    selector: '.panel-payment'
                });
                return false;
            }
            if(Budget.budget.export == 'dav' && Budget.budget.credit.value > 0){
                global.validateMessage('Não será possível exportar o DAV utilizando uma carta de crédito.');
                global.scrollTo({
                    delay: 500,
                    addition: 200,
                    selector: '.panel-payment'
                });
                return false;
            }
        } else if(Budget.budget.credit.value > 0){
            global.validateMessage('Este orçamento está utilizando uma Carta de Crédito como forma de pagamento e por esse motivo não será permitido salvá-lo, você deverá exporta-lo.');
            global.scrollTo({
                delay: 500,
                addition: 200,
                selector: '.panel-payment'
            });
            return false;
        }
        if( (!!Budget.budget.payments.length || Budget.budget.credit.value > 0) && parseFloat(Payment.payment_remaining.toFixed(2)) != 0 ){
            global.validateMessage('A soma das parcelas é diferente do valor total do pedido.');
            global.scrollTo({
                delay: 500,
                addition: 200,
                selector: '.panel-payment'
            });
            return false;
        }
        return true;
    },
    total: function(){
        var count = 0, total = 0;
        $.each( Budget.table.rows({filter: 'applied'})[0], function(k,key){
            count ++;
            total += Budget.budgets[key].budget.value_total;
        });
        $('#footer-budgets-count span').text(count);
        $('#footer-budgets-total span').text('R$ ' + global.float2Br(total));
        $('#footer-budgets-average span').text('R$ ' + global.float2Br(total/(count||1)));
    },
    unrecover: function(){
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-budget-unrecover',
            dataType: 'html'
        },function(html){
            global.modal({
                icon: 'fa-lock',
                id: 'modal-budget-unrecover',
                class: 'modal-budget-unrecover',
                title: 'Orçamento Bloqueado',
                html: html,
                buttons: [{
                    icon: 'fa-eye',
                    title: 'Apenas Visualizar'
                },{
                    icon: 'fa-files-o',
                    title: 'Duplicar',
                    action: function(){
                        Budget.clone();
                    }
                }]
            })
        });
    }
};

Seller = {
    seller: {
        seller_id: null,
        seller_code: '',
        seller_name: '',
        seller_image: null
    },
    search: function(success){
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-seller-search',
            data: Seller.seller,
            dataType: 'html'
        },function(html){
            global.modal({
                icon: 'fa-search',
                id: 'modal-seller-search',
                class: 'modal-seller-search',
                title: 'Pesquisar Vendedor',
                html: html,
                buttons: [{
                    icon: 'fa-check',
                    title: 'Selecionar',
                    id: 'button-seller-select',
                    unclose: true
                }],
                shown: function(){
                    $('#modal_seller_code').focus();
                    ModalSeller.success = function(seller){
                        Seller.seller = seller;
                        Budget.budget.seller_id = seller.seller_id;
                        if( !!success ) success();
                    }
                }
            })
        });
    }
};

Item = {
    typeahead: {
        items: 10,
        delay: 500,
        last: '',
        timer: 0
    },
    table: global.table({
        selector: '#table-budget-items',
        noControls: [0,8],
        order: [[1,'asc']],
        scrollY: 186,
        scrollCollapse: 1
    }),
    actions: function(key){
        return(
            '<div class="dropdown dropdown-budget dropdown-actions">' +
                '<button class="btn btn-empty" type="button" data-toggle="dropdown">' +
                    '<i class="fa fa-ellipsis-v"></i>' +
                '</button>' +
                '<ul class="dropdown-menu pull-right">' +
                    '<li><a data-action="info" data-key="' + key + '" class="dropdown-item" href="#"><i class="fa fa-info txt-orange"></i>Informações</a></li>' +
                    '<li><a data-action="edit" data-key="' + key + '" class="dropdown-item" href="#"><i class="fa fa-pencil txt-blue"></i>Editar</a></li>' +
                    '<li><a data-action="del" data-key="' + key + '" class="dropdown-item" href="#"><i class="fa fa-trash-o txt-red-light"></i>Remover</a></li>' +
                    '<li><a data-action="up" data-key="' + key + '" class="dropdown-item" href="#"><i class="fa fa-chevron-up txt-gray"></i>Subir</a></li>' +
                    '<li><a data-action="down" data-key="' + key + '" class="dropdown-item" href="#"><i class="fa fa-chevron-down txt-gray"></i>Descer</a></li>' +
                '</ul>' +
            '</div>'
        );
    },
    add: function(){
        Budget.budget.items.push(Item.item);
        Budget.budget.budget_value += Item.item.budget_item_value;
        Budget.budget.budget_value_total += Item.item.budget_item_value_total;
        Budget.budget.budget_value_discount += Item.item.budget_item_value_discount;
        Budget.budget.budget_cost += Item.item.budget_item_quantity * Item.item.budget_item_cost;
        Budget.budget.budget_aliquot_discount = parseFloat((Budget.budget.budget_value_discount/Budget.budget.budget_value*100).toFixed(2));
        Budget.budget.budget_value = parseFloat(Budget.budget.budget_value.toFixed(2));
        Budget.budget.budget_value_total = parseFloat(Budget.budget.budget_value_total.toFixed(2));
        Budget.budget.budget_value_discount = parseFloat(Budget.budget.budget_value_discount.toFixed(2));
        Item.init();
        Item.total();
        Item.data2form();
        Item.showList();
        setTimeout(function(){
            $('#product_code').focus();
        },200);
    },
    beforeAdd: function(){
        if( Item.item.budget_item_quantity == 0 ){
            global.modal({
                icon: 'fa-warning-triangle',
                title: 'Informação',
                html: '<p>A quantidade do produto não poderá ficar zerada.</p>',
                buttons: [{
                    icon: 'fa-check',
                    title: 'Ok'
                }],
                hidden: function(){
                    $('budget_item_quantity').focus().select();
                }
            });
            return;
        }
        Item.add();
    },
    beforeRemove: function(){
        global.modal({
            icon: 'fa-question-circle',
            title: 'Confirmação',
            html: '<p>Deseja remover o item em edição <b>' + Item.item.product_code + ' - ' + Item.item.product_name + '</b>?.</p>',
            buttons: [{
                icon: 'fa-times',
                title: 'Não',
                class: 'pull-left'
            },{
                icon: 'fa-check',
                title: 'Sim',
                action: function(){
                    Item.init();
                    Item.data2form();
                }
            }],
            hidden: function(){
                $('budget_code').focus().select();
            }
        });
    },
    complement: function(key){
        var item = Budget.budget.items[key];
        global.post({
            url: global.uri.uri_public_api + 'product.php?action=complement',
            data: {
                company_id: Company.company.company_id,
                product_id: item.product_id
            },
            dataType: 'json'
        }, function(data){
            Budget.budget.items[key].prices = data.prices;
            Item.edit(key);
        });
    },
    beforeEdit: function(key){
        if( !!Item.item && !!Item.item.product_id ){
            global.modal({
                icon: 'fa-question-circle',
                title: 'Confirmação',
                html: '<p>O produto <b>' + Item.item.product_code + ' - ' + Item.item.product_name + '</b> está em edição. Deseja descartá-lo?</p>',
                buttons: [{
                    icon: 'fa-times',
                    title: 'Não',
                    class: 'pull-left'
                },{
                    icon: 'fa-check',
                    title: 'Sim',
                    action: function(){
                        var item = Budget.budget.items[key];
                        if (!item.prices) {
                            Item.complement(key);
                            return;
                        }
                        Item.edit(key);
                    }
                }],
                hidden: function(){
                    $('budget_item_quantity').focus().select();
                }
            });
        } else {
            var item = Budget.budget.items[key];
            if (!item.prices) {
                Item.complement(key);
                return;
            }
            Item.edit(key);
        }
    },
    check: function(){
        var noStock = 0;
        $.each(Budget.budget.items,function(key,item){
            if( item.stock_value <= 0 ){
                noStock++;
            }
        });
        return noStock;
    },
    data2form: function(){
        $('#cover-product').css({'background-image':'url(' + (Item.item.image || 'images/empty-image.png') + ')'});
        $('#product_code').val(Item.item.product_code).attr('data-value',Item.item.product_code);
        $('#product_name').val(Item.item.product_name).attr('data-value',Item.item.product_name);
        $('#stock_value, #budget_item_quantity').unmask();
        if( Item.item.unit_type == 'F' ){
            $('#product_stock').val(global.float2Br(Item.item.stock_value,0,4).replace(',0000','').replace(',000','').replace(',00','').replace(',0',''));
            $('#budget_item_quantity').val(global.float2Br(Item.item.budget_item_quantity,0,4)).prop({
                'readonly': !Item.item.product_id
            }).attr({
                'data-value': Item.item.budget_item_quantity
            }).unmask().mask("999999,9999");
        } else {
            $('#product_stock').val(parseInt(Item.item.stock_value));
            $('#budget_item_quantity').val(Item.item.budget_item_quantity).prop({
                'readonly': !Item.item.product_id
            }).attr({
                'data-value': Item.item.budget_item_quantity
            }).unmask().mask("999999");
        }
        $('#price_id').prop('disabled',!Item.item.product_id);
        $('#price_id option').remove();
        $.each( Item.item.prices, function(key,price){
            $('#price_id').append($('<option>',{
                'value': price.price_id,
                'selected': Item.item.price_id == price.price_id,
                'data-subtext': price.price_code + ' ' + price.price_name,
                'text': 'R$ ' + global.float2Br(price.price_value)
                //'text': price.price_code + ' ' + price.price_name + ' (R$ ' + global.float2Br(price.price_value) + ')'
            }));
        });
        $('#price_id').selectpicker('refresh');
        $('.budget-item-unit-code').text(Item.item.product_id ? Item.item.unit_code : 'UN');
        $('#budget_item_aliquot_discount').val(global.float2Br(Item.item.budget_item_aliquot_discount,2,4)).prop({
            'readonly': !Item.item.product_id
        }).attr({
            'data-value': Item.item.budget_item_aliquot_discount
        });
        $('#budget_item_value_discount').val(global.float2Br(Item.item.budget_item_value_discount)).prop({
            'readonly': !Item.item.product_id
        }).attr({
            'data-value': Item.item.budget_item_value_discount
        });
        $('#budget_item_value_total').val('R$ ' + global.float2Br(Item.item.budget_item_value_total));
        $('#button-budget-item-add').prop('disabled',!Item.item.product_id);
        $('#button-budget-item-remove').prop('disabled',!Item.item.product_id);
    },
    del: function(key){
        global.modal({
            icon: 'fa-question-circle-o',
            title: 'Confirmação',
            html: '<p>Deseja realmente remover o produto <b>' + Budget.budget.items[key].product_name + '</b> do pedido?</p>',
            buttons: [{
                icon: 'fa-times',
                title: 'Não',
                class: 'pull-left'
            }, {
                icon: 'fa-check',
                title: 'Sim',
                action: function(){
                    var item = Budget.budget.items[key];
                    Budget.budget.budget_value -= item.budget_item_value;
                    Budget.budget.budget_value_total -= item.budget_item_value_total;
                    Budget.budget.budget_value_discount -= item.budget_item_value_discount;
                    Budget.budget.budget_value_st -= item.budget_item_value_st;
                    Budget.budget.budget_value_icms -= item.budget_item_value_icms;
                    Budget.budget.budget_cost -= item.budget_item_quantity*item.budget_item_cost;
                    Budget.budget.items.splice(key,1);
                    Item.showList();
                    Item.total();
                    Payment.total();
                }
            }]
        });
    },
    discountAuthorization: function(params){
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-discount-authorization',
            data: {
                product_id: Item.item.product_id,
                product_name: Item.item.product_name,
                product_max_discount: Item.item.product_discount,
                item_quantity: Item.item.budget_item_quantity,
                item_value_total: Item.item.budget_item_value - parseFloat(params.value),
                item_value_discount: parseFloat(params.value),
                item_aliquot_discount: parseFloat(params.aliquot)
            },
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'small',
                icon: 'fa-lock',
                id: 'modal-discount-authorization',
                class: 'modal-discount-authorization',
                title: 'Autorização de Desconto',
                html: html,
                buttons: [{
                    icon: 'fa-times',
                    class: 'pull-left btn-red',
                    title: 'Cancelar'
                },{
                    icon: 'fa-unlock',
                    title: 'Autorizar',
                    class: 'btn-green',
                    unclose: true,
                    action: function(){
                        ModalDiscountAuthorization.authorize();
                    }
                }],
                shown: function(){
                    $('#modal_user_user').focus();
                },
                hidden: function(){
                    if( Item.item.budget_item_value_discount > 0 ){
                        $('#button-budget-item-add').focus();
                    } else {
                        $('#budget_item_aliquot_discount').focus().select();
                    }
                }
            });
        });
    },
    discountAliquot: function(budget_item_aliquot_discount,focus){
        Item.item.budget_item_aliquot_discount = parseFloat(budget_item_aliquot_discount);
        Item.item.budget_item_value_discount = parseFloat(((budget_item_aliquot_discount / 100) * Item.item.budget_item_value).toFixed(2));
        Item.item.budget_item_value_total = Item.item.budget_item_value - Item.item.budget_item_value_discount;
        $(focus || '#button-budget-item-add').focus().select();
        Item.data2form();
    },
    edit: function(key){
        var item = Budget.budget.items[key];
        Budget.budget.budget_value -= item.budget_item_value;
        Budget.budget.budget_value_total -= item.budget_item_value_total;
        Budget.budget.budget_value_discount -= item.budget_item_value_discount;
        Budget.budget.budget_cost -= item.budget_item_quantity*item.budget_item_cost;
        Budget.budget.budget_value_st -= item.budget_item_value_st;
        Budget.budget.budget_value_icms -= item.budget_item_value_icms;
        Budget.budget.items.splice(key,1);
        Item.item = item;
        Item.data2form();
        Item.showList();
        Item.total();
        $('#budget_item_quantity').focus().select();
    },
    events: function(){
        $('#button-item-info').click(function(){
            if( !!Item.item.product_id ){
                Item.info({
                    image: Item.item.image,
                    product_id: Item.item.product_id,
                    product_code: Item.item.product_code,
                    product_name: Item.item.product_name,
                    unit_code: Item.item.unit_code
                });
            }
        });
        $('#product_code, #product_name').on('focus',function(){
            Budget.section = 1;
        });
        $('#product_code').on('keyup',function (e) {
            var keycode = e.keyCode || e.which;
            if (keycode == '13' && $(this).val().length) {
                e.preventDefault();
                e.stopPropagation();
                if (global.posts < 1) {
                    Item.get({
                        product_id: null,
                        product_code: $(this).val()
                    });
                }
            }
        }).focus();
        $('#button-budget-product-code-search').click(function(){
            if( $('#product_code').val().length ){
                Item.get({
                    product_id: null,
                    product_code: $('#product_code').val()
                });
            }
        });
        $('#product_name').on('keyup',function(){
            if( $(this).val().length >= 3 && $(this).val() != Item.typeahead.last ){
                clearTimeout(Item.typeahead.timer);
                Item.typeahead.last = $(this).val();
                Item.typeahead.timer = setTimeout(function(){
                    global.autocomplete({
                        items: 'all',
                        selector: '#product_name',
                        data: {
                            limit: Item.typeahead.items,
                            company_id: Budget.budget.company_id,
                            product_name: $('#product_name').val()
                        },
                        url: global.uri.uri_public_api + 'product.php?action=typeahead',
                        callBack: function(item){
                            Item.get({
                                product_id: item.item_id,
                                product_code: null
                            });
                        }
                    });
                },Item.typeahead.delay);
            }
        });
        $('#button-budget-product-name-search').click(function(){
            Item.search();
        });
        $('#price_id').on('changed.bs.select',function(e,clickedIndex){
            Item.item.price_id = Item.item.prices[clickedIndex-1].price_id;
            Item.item.budget_item_value_discount = 0;
            Item.item.budget_item_aliquot_discount = 0;
            Item.item.budget_item_value_unitary = Item.item.prices[clickedIndex-1].price_value;
            Item.item.budget_item_value = Item.item.budget_item_quantity * Item.item.budget_item_value_unitary;
            Item.item.budget_item_value_total = Item.item.budget_item_value - Item.item.budget_item_value_discount;
            $('#budget_item_aliquot_discount').val(global.float2Br(Item.item.budget_item_aliquot_discount,2,4)).prop({
                'readonly': !Item.item.product_id
            }).attr({
                'data-value': Item.item.budget_item_aliquot_discount
            });
            $('#budget_item_value_discount').val(global.float2Br(Item.item.budget_item_value_discount)).prop({
                'readonly': !Item.item.product_id
            }).attr({
                'data-value': Item.item.budget_item_value_discount
            });
            $('#budget_item_value_total').val('R$ ' + global.float2Br(Item.item.budget_item_value_total));
            $('#budget_item_quantity').focus().select();
        });
        $('#budget_item_quantity').on('keyup',function(e){
            var keycode = e.keyCode || e.which;
            if( keycode == '13' && $(this).val().length ){
                var budget_item_quantity = $(this).val().length ? ( Item.item.unit_type == 'F' ? parseFloat(global.br2Float($(this).val())) : parseInt($(this).val()) ) : $(this).attr('data-value');
                Item.quantity(budget_item_quantity);
            }
        }).blur(function(){
            var budget_item_quantity = $(this).val().length ? ( Item.item.unit_type == 'F' ? parseFloat(global.br2Float($(this).val())) : parseInt($(this).val()) ) : $(this).attr('data-value');
            $(this).val(budget_item_quantity);
            if( budget_item_quantity != Item.item.budget_item_quantity ){
                Item.quantity(budget_item_quantity);
            }
        });
        $('#budget_item_aliquot_discount').on('keyup',function(e) {
            var keycode = e.keyCode || e.which;
            if (keycode == '13') {
                if( $(this).val().length ){
                    var budget_item_aliquot_discount = global.br2Float($(this).val());
                    if( budget_item_aliquot_discount <= Item.item.product_discount || budget_item_aliquot_discount == Item.item.budget_item_aliquot_discount ){
                        $(this).attr('data-value',budget_item_aliquot_discount);
                        Item.item.authorization_id = null;
                        Item.discountAliquot(budget_item_aliquot_discount,'#budget_item_value_discount');
                    } else {
                        Item.discountAuthorization({
                            aliquot: budget_item_aliquot_discount,
                            value: parseFloat(((budget_item_aliquot_discount / 100) * Item.item.budget_item_value).toFixed(2))
                        });
                    }
                } else {
                    Item.discountAliquot(0);
                    $('#budget_item_value_discount').focus();
                }
            }
        }).blur(function(){
            $(this).val(global.float2Br($(this).attr('data-value')));
        });
        $('#budget_item_value_discount').on('keyup',function(e){
            var keycode = e.keyCode || e.which;
            if (keycode == '13'){
                if( $(this).val().length ){
                    var budget_item_value_discount = global.br2Float($(this).val());
                    if( budget_item_value_discount == Item.item.budget_item_value_discount ){
                        $('#button-budget-item-add').focus();
                        return;
                    }
                    var budget_item_aliquot_discount = parseFloat(((budget_item_value_discount/(Item.item.budget_item_quantity*Item.item.budget_item_value_unitary))*100).toFixed(4));
                    if( budget_item_aliquot_discount <= Item.item.product_discount || budget_item_aliquot_discount == Item.item.budget_item_aliquot_discount ){
                        $(this).attr('data-value',budget_item_value_discount);
                        Item.item.authorization_id = null;
                        Item.discountAliquot(budget_item_aliquot_discount);
                    } else {
                        Item.discountAuthorization({
                            aliquot: budget_item_aliquot_discount,
                            value: parseFloat(((budget_item_aliquot_discount / 100) * Item.item.budget_item_value).toFixed(2))
                        });
                    }
                } else {
                    Item.discountAliquot(0);
                    $('#button-budget-item-add').focus();
                }
            }
        }).blur(function(){
            $(this).val(global.float2Br($(this).attr('data-value')));
        });
        $('#button-budget-item-add').click(function(){
            Item.beforeAdd();
        });
        $('#button-budget-item-remove').click(function(){
            Item.beforeRemove();
        });
    },
    get: function(data){
        var deny = false;
        $.each( Budget.budget.items, function(key,item){
            if( item.product_id == data.product_id || item.product_code == ('00000' + data.product_code ).slice(-6) ){
                deny = true;
            }
        });
        if( deny ){
            global.modal({
                icon: 'fa-exclamation-triangle',
                title: 'Aviso',
                html: '<p>O produto já foi adicionado ao pedido.</p>',
                buttons: [{
                    icon: 'fa-check',
                    title: 'Ok'
                }]
            });
            $('#product_code').focus().select();
            $('#product_name').val('').attr('data-value','');
            return;
        }
        global.post({
            url: global.uri.uri_public_api + 'product.php?action=get',
            data: {
                get_unit: 1,
                get_product_cost: 1,
                get_product_stock: 1,
                get_product_prices: 1,
                company_id: Company.company.company_id,
                product_id: data.product_id,
                product_code: data.product_code
            },
            dataType: 'json'
        }, function(product){
            if( !product.prices.length ) {
                global.validateMessage('O produto <b>' + product.product_code + ' - ' + product.product_name + '</b> não possui preço vinculado para venda na empresa selecionada. Verifique com o setor responsável.');
                return;
            }
            if( product.product_active == 'N' ) {
                global.validateMessage('O produto <b>' + product.product_code + ' - ' + product.product_name + '</b> não está ativo para venda na empresa selecionada.. Verifique com o setor responsável.');
                return;
            }
            Item.item = {
                budget_item_id: null,
                external_id: null,
                image: product.image,
                ncm_id: product.ncm_id,
                icms_id: product.icms_id,
                price_id: product.prices[0].price_id,
                product_id: product.product_id,
                product_code: product.product_code,
                product_name: product.product_name,
                product_discount: product.product_discount,
                product_commission: product.product_commission,
                product_weight_net: product.product_weight_net,
                product_weight_gross: product.product_weight_gross,
                product_cfop: product.product_cfop,
                product_cfop_extra: product.product_cfop_extra,
                budget_item_quantity: 1,
                budget_item_value: product.prices[0].price_value,
                budget_item_value_unitary: product.prices[0].price_value,
                budget_item_aliquot_discount: 0,
                budget_item_value_discount: 0,
                budget_item_value_st: 0,
                budget_item_value_icms: 0,
                budget_item_cost: product.cost ? product.cost.cost_value : 0,
                budget_item_value_total: product.prices[0].price_value,
                stock_value: product.stock ? product.stock.stock_value : 0,
                stock_date: product.stock ? product.stock.stock_date : null,
                unit_code: product.unit.unit_code,
                unit_type: product.unit.unit_type,
                prices: product.prices
            };
            Item.data2form();
            Payment.total();
            $('#budget_item_quantity').focus().select();
        });
    },
    info: function(product){
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-product-info',
            data: product,
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'big',
                icon: 'fa-info-circle',
                id: 'modal-product-info',
                class: 'modal-product-info',
                title: 'Informações do Produto',
                html: html,
                buttons: [{
                    icon: 'fa-check',
                    title: 'Ok'
                }]
            })
        });
    },
    init: function(){
        Item.item = {
            image: null,
            budget_item_id: null,
            external_id: null,
            price_id: null,
            product_id: null,
            product_code: '',
            product_name: '',
            product_discount: null,
            product_commission: null,
            stock_value: 0,
            unit_code: 'UN',
            budget_item_quantity: 0,
            budget_item_value: 0,
            budget_item_value_unitary: 0,
            budget_item_aliquot_discount: 0,
            budget_item_value_discount: 0,
            budget_item_cost: 0,
            budget_item_value_st: 0,
            budget_item_value_icms: 0,
            budget_item_value_total: 0,
            prices: [],
            budget_item_key: Budget.budget.items.length
        };
    },
    quantity: function(budget_item_quantity){
        Item.item.budget_item_quantity = budget_item_quantity;
        Item.item.budget_item_value = Item.item.budget_item_quantity * Item.item.budget_item_value_unitary;
        Item.item.budget_item_value_discount = parseFloat(((Item.item.budget_item_aliquot_discount / 100) * Item.item.budget_item_value).toFixed(2));
        Item.item.budget_item_value_total = Item.item.budget_item_value - Item.item.budget_item_value_discount;
        $('#budget_item_aliquot_discount').focus().select();
        Item.data2form();
    },
    search: function(){
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-product-search',
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'big',
                icon: 'fa-search',
                id: 'modal-product-search',
                class: 'modal-product-search',
                title: 'Localização de Produto',
                html: html,
                buttons: [{
                    icon: 'fa-times',
                    class: 'pull-left btn-red',
                    title: 'Cancelar'
                },{
                    icon: 'fa-plus',
                    title: 'Adicionar Produtos',
                    class: 'btn-green',
                    unclose: true,
                    action: function(){
                        $.each(ModalProductSearch.selected,function(key,item){
                            var allowed = true;
                            $.each(Budget.budget.items,function(key,item2){
                                if(item2.product_id == item.product_id) allowed = false;
                            });
                            if( allowed ) {
                                item.budget_item_key = Budget.budget.items.length;
                                Budget.budget.items.push(item);
                                Budget.budget.budget_value += item.budget_item_value;
                                Budget.budget.budget_value_total += item.budget_item_value_total;
                                Budget.budget.budget_cost += item.budget_item_quantity * item.budget_item_cost;
                            }
                        });
                        Item.init();
                        Item.total();
                        Item.data2form();
                        Item.showList();
                        Payment.total();
                        $('#modal-product-search').modal('hide');
                    }
                }],
                shown: function(){
                    $('#modal_product_name').focus();
                },
                hidden: function(){
                    if( !!Item.item.product_id ){
                        $('#budget_item_quantity').focus().select();
                    } else {
                        $('#product_code').focus().select();
                    }
                }
            });
        });
    },
    showList: function(){
        Item.table.clear();
        $.each( Budget.budget.items, function (key, item) {
            item.margin = 0;
            item.profit = 0;
            item.idne = 'idne0';
            item.budget_item_key = item.budget_item_key ? item.budget_item_key : key+1;
            if( !!item.budget_item_cost ){
                item.margin = parseFloat((100 * (item.budget_item_quantity * item.budget_item_cost) / item.budget_item_value_total).toFixed(2));
                item.profit = parseFloat((((item.budget_item_value_total/(item.budget_item_quantity * item.budget_item_cost))*100)-100).toFixed(2));
                if (item.profit < 25) item.idne = 'idne1';
                else if (item.profit < 50) item.idne = 'idne2';
                else if (item.profit < 75) item.idne = 'idne3';
                else if (item.profit < 100) item.idne = 'idne4';
                else item.idne = 'idne5';
            }
            var row = Item.table.row.add([
                '<div class="budget-product-cover"' + ( !!item.image ? (' data-action="lightbox" data-gallery="gallery" href="' + item.image + '" data-toggle="lightbox" data-footer="' + (item.product_code + ' - ' + item.product_name) + '" data-footer="Léo" style="background-image:url(' + item.image + ')"') : '' ) + '></div>',
                item.budget_item_key,
                item.product_code + ' - ' + item.product_name,
                ( item.unit_type == 'F' ? global.float2Br(item.budget_item_quantity,0,4) : item.budget_item_quantity ) + item.unit_code,
                global.float2Br(item.budget_item_value_unitary),
                global.float2Br(item.budget_item_aliquot_discount,2,4),
                global.float2Br(item.budget_item_value_discount),
                global.float2Br(item.budget_item_value_total),
                Item.actions(key)
            ]).node();
            if( item.budget_item_quantity > item.stock_value ){
                $(row).addClass('txt-red-light');
            }
            $(row).addClass(item.idne).on('dblclick',function(){
                if( Budget.budget.budget_status == 'O' )
                    Item.beforeEdit(key);
            });
        });
        Item.table.draw();
        var $table = $('#table-budget-items');
        $table.find('button[data-toggle="dropdown"]').click(function(){
            var top = $(this)[0].getBoundingClientRect().top;
            $(this).next().css({
                'top': top + 18
            });
        });
        $table.find('a[data-action="info"]').click(function(e){
            e.preventDefault();
            Item.info(Budget.budget.items[$(this).attr('data-key')]);
        });
        $table.find('a[data-action="edit"]').click(function(e){
            e.preventDefault();
            Item.beforeEdit($(this).attr('data-key'));
        });
        $table.find('a[data-action="del"]').click(function(e){
            e.preventDefault();
            Item.del($(this).attr('data-key'));
        });
        $table.find('a[data-action="up"]').click(function(e){
            e.preventDefault();
            var key2 = -1;
            var key1 = parseInt($(this).attr('data-key'));
            var target = Budget.budget.items[key1];
            if( target.budget_item_key > 1 ){
                $.each(Budget.budget.items,function(key,item){
                     if( item.budget_item_key == target.budget_item_key-1 ){
                         key2 = key;
                     }
                });
                if( key2 > -1 ){
                    var old = Budget.budget.items[key1].budget_item_key;
                    Budget.budget.items[key1].budget_item_key = Budget.budget.items[key2].budget_item_key;
                    Budget.budget.items[key2].budget_item_key = old;
                    Item.showList();
                }
            }
        });
        $table.find('a[data-action="down"]').click(function(e){
            e.preventDefault();
            var key2 = -1;
            var key1 = parseInt($(this).attr('data-key'));
            var target = Budget.budget.items[key1];
            if( target.budget_item_key < Budget.budget.items.length ){
                $.each(Budget.budget.items,function(key,item){
                    if( item.budget_item_key == target.budget_item_key+1 ){
                        key2 = key;
                    }
                });
                if( key2 > -1 ){
                    var old = Budget.budget.items[key1].budget_item_key;
                    Budget.budget.items[key1].budget_item_key = Budget.budget.items[key2].budget_item_key;
                    Budget.budget.items[key2].budget_item_key = old;
                    Item.showList();
                }
            }
        });
        $table.find('[data-action="lightbox"]').click(function(){
            $(this).ekkoLightbox();
        });
        $('.panel-tools button[data-action="discount"]').prop('disabled',!Budget.budget.items.length);
    },
    total: function(){
        $('.panel-items .items').html('Itens: <b>' + Budget.budget.items.length + '</b>');
        $('.panel-items .cost').html('Custo: <b>R$ ' + global.float2Br(Budget.budget.budget_cost) + '</b>');
        $('.panel-items .margin').html('Markup: <b>' + global.float2Br((Budget.budget.budget_cost/(Budget.budget.budget_value_total||1))*100) + '%</b>');
        $('.panel-items .profit').html('Lucro Bruto: <b>R$ ' + global.float2Br(Budget.budget.budget_value_total-Budget.budget.budget_cost) + '</b>');
        $('.panel-items .profit2').html('Margem: <b>' + global.float2Br(Budget.budget.budget_cost ? (((Budget.budget.budget_value_total/Budget.budget.budget_cost)*100)-100).toFixed(2) : 0) + '%</b>');
        $('.panel-items .total').html('Valor Total: <b>R$ ' + global.float2Br(Budget.budget.budget_value_total) + '</b>');
        Budget.budget.budget_cost_idne = 'idne0';
        if( !!Budget.budget.budget_cost ) {
            Budget.budget.budget_cost_margin = parseFloat(((100 * Budget.budget.budget_cost) / Budget.budget.budget_value_total).toFixed(2));
            Budget.budget.budget_cost_profit = parseFloat(Budget.budget.budget_cost ? (((Budget.budget.budget_value_total/Budget.budget.budget_cost)*100)-100).toFixed(2) : 0);
            if (Budget.budget.budget_cost_profit < 25) Budget.budget.budget_cost_idne = 'idne1';
            else if (Budget.budget.budget_cost_profit < 50) Budget.budget.budget_cost_idne = 'idne2';
            else if (Budget.budget.budget_cost_profit < 75) Budget.budget.budget_cost_idne = 'idne3';
            else if (Budget.budget.budget_cost_profit < 100) Budget.budget.budget_cost_idne = 'idne4';
            else Budget.budget.budget_cost_idne = 'idne5';
        }
        $('footer .idne').html('<i class="fa fa-stop ' + Budget.budget.budget_cost_idne + '"></i>');
        Payment.recalculate();
    }
};

Person = {
    person: null,
    typeahead: {
        items: 10,
        delay: 500,
        last: '',
        timer: 0
    },
    active: function(){
        global.post({
            url: global.uri.uri_public_api + 'person.php?action=active',
            data: {
                person_id: Person.person.person_id,
                person_category_id: global.config.person.client_category_id
            },
            dataType: 'json'
        },function(){
            Person.afterGet();
        });
    },
    afterGet: function(){
        Address.getAddress = true;
        Budget.budget.client_id = Person.person.person_id;
        Address.delivery = Person.person.address ? Person.person.address[0] : null;
        Budget.budget.address_code = Address.delivery ? Address.delivery.address_code : null;
        Budget.budget.address_uf_id = Address.delivery ? Address.delivery.uf_id : null;
        if( !!Budget.budget.budget_note_document ){
            Budget.budget.budget_note_document = Budget.budget.budget_note_document.split('\n\nObs de Entrega: ')[0];
        }
        if( !!Person.person.address[0] && !!Person.person.address[0].address_note ){
            Budget.budget.budget_note_document +=  '\n\nObs de Entrega: ' + Person.person.address[0].address_note;
        }
        Person.data2form();
        Address.showDelivery();
    },
    beforeActive: function(){
        global.modal({
            icon: 'fa-question-circle-o',
            title: 'Confirmação',
            html: '<p>O cliente <b>' + Person.person.person_name + '</b> está inativo. Deseja ativa-lo?</p>',
            buttons: [{
                icon: 'fa-times',
                title: 'Não',
                class: 'pull-left'
            },{
                icon: 'fa-check',
                title: 'Sim',
                action: function(){
                    Person.active();
                }
            }],
            hidden: function(){
                $('#person_code').focus().select();
            }
        });
    },
    data2form: function(){
        $('#person_code').val(Person.person.person_code).attr('data-value',Person.person.person_code);
        $('#person_name').val(Person.person.person_name).attr('data-value',Person.person.person_name);
        $('#person_type').val(Person.person.person_type == 'F' ? 'Física' : 'Jurídica');
        $('#person_cpf').val(Person.person.person_cpf);
        $('#person_cnpj').val(Person.person.person_cnpj);
        $('#person_rg').val(Person.person.person_rg);
        $('#person_gender').val(Person.person.person_gender ? ( Person.person.person_gender == 'F' ? 'Feminino' : 'Masculino' ) : '--');
        $('#person_birth').val(global.date2Br(Person.person.person_birth));
        $('#file-image-person').filestyle('disabled',!Person.person.person_id);
        $('#button-image-person-remove').prop('disabled',!Person.person.image);
        $('#button-image-person-web-cam').prop('disabled',!Person.person.person_id);
        $('#button-budget-person-address').prop('disabled',!Person.person.person_id);
        $('#button-budget-payment-credit').prop('disabled',!Person.person.person_id);
        Person.showAttributes();
        PersonImage.show();
        if( Budget.budget.credit.value > 0){
            Payment.removeCredit();
        }
    },
    events: function(){
        $('#person_code, #person_name').on('focus',function(){
            Budget.section = 2;
        });
        $('#person_code').keypress(function(e){
            var keycode = e.keyCode || e.which;
            if( keycode == '13' ){
                e.preventDefault();
                e.stopPropagation();
                var person_id = !$(this).val().length ? Company.company.company_consumer_id : null;
                var person_code = $(this).val().length ? $(this).val() : null;
                if( !!person_code && !!Person.person && person_code == Person.person.person_code ){
                    Budget.goTo(3);
                    return;
                }
                if( (!!person_id || !!person_code) && global.posts < 1 ){
                    Person.get({
                        person_id: person_id,
                        person_code: person_code
                    });
                }
            }
        });
        $('#button-budget-person-code-search').click(function () {
            if( $('#person_code').val().length ){
                Person.get({
                    person_id: null,
                    person_code: $('#person_code').val()
                });
            }
        });
        $('#person_name').on('keyup',function(){
            if( $(this).val().length >= 3 && $(this).val() != Person.typeahead.last ){
                clearTimeout(Person.typeahead.timer);
                Person.typeahead.last = $(this).val();
                Person.typeahead.timer = setTimeout(function(){
                    global.autocomplete({
                        items: 'all',
                        selector: '#person_name',
                        data: {
                            limit: Person.typeahead.items,
                            person_name: $('#person_name').val(),
                            person_category_id: global.config.person.client_category_id
                        },
                        url: global.uri.uri_public_api + 'person.php?action=typeahead',
                        callBack: function(item){
                            Person.get({
                                person_id: item.item_id,
                                product_code: null
                            });
                        }
                    });
                },Item.typeahead.delay);
            }
        });
        $('#button-budget-person-name-search').click(function(){
            Person.search();
        });
        $('#button-budget-person-remove').click(function () {
            if( !Person.person.person_id ) return;
            global.modal({
                icon: 'fa-question-circle-o',
                title: 'Confirmação',
                html: '<p>Deseja realmente remover a pessoa do pedido?</p>',
                buttons: [{
                    title: 'Não',
                    icon: 'fa-times',
                    class: 'pull-left'
                }, {
                    title: 'Sim',
                    icon: 'fa-check',
                    action: function(){
                        Person.init();
                        Person.data2form();
                        Person.person_id = null;
                    }
                }]
            });
        });
        $('#button-budget-person-new').click(function(){
            Person.new();
        });
        PersonImage.events();
    },
    get: function(data){
        if( Person.person.person_id && Person.person.person_id == data.person_id ) return;
        global.post({
            url: global.uri.uri_public_api + 'person.php?action=get',
            data: {
                get_person_address: 1,
                get_person_attribute: 1,
                get_person_credit_limit: 1,
                person_id: data.person_id,
                person_code: data.person_code,
                person_category_id: global.config.person.client_category_id
            },
            dataType: 'json'
        }, function(person){
            Person.person = person;
            if( Person.person.person_active == 'N' ){
                Person.beforeActive();
            } else {
                Person.afterGet();
            }
        });
    },
    info: function(){
        if( !Person.person.person_id ){
            global.validateMessage('A pessoa deverá ser informada.',function(){
                Budget.goTo(2);
            });
            return;
        }
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-person-info',
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'big',
                icon: 'fa-info',
                id: 'modal-person-info',
                class: 'modal-person-info',
                title: 'Informações da Pessoa',
                html: html,
                buttons: [{
                    icon: 'fa-check',
                    title: 'Fechar'
                }],
                show: function(){
                    ModalPersonInfo.person = Person.person;
                    ModalPersonInfo.show();
                }
            });
        });
    },
    init: function(){
        Person.person = {
            image: null,
            person_id: null,
            person_code: '',
            person_name: '',
            person_type: '',
            person_cpf: '',
            person_cnpj: '',
            person_rg: '',
            person_gender: '',
            person_birth: '',
            address: [],
            credits: [],
            attributes: []
        };
    },
    new: function(){
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-person-new',
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'big',
                icon: 'fa-plus',
                id: 'modal-person-new',
                class: 'modal-person-new',
                title: 'Novo Cliente',
                html: html,
                buttons: [{
                    icon: 'fa-times',
                    title: 'Cancelar',
                    class: 'pull-left btn-red'
                },{
                    icon: 'fa-plus',
                    title: 'Cadastrar',
                    class: 'btn-green',
                    unclose: true,
                    action: function(){
                        ModalPersonNew.submit();
                    }
                }],
                shown: function(){
                    setTimeout(function(){
                        ModalPersonNew.success = function(person_id){
                            Person.get({
                                person_id: person_id
                            });
                        }
                    },1000);
                }
            });
        });
    },
    search: function(){
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-person-search',
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'big',
                icon: 'fa-search',
                id: 'modal-person-search',
                class: 'modal-person-search',
                title: 'Localização de Pessoa',
                html: html,
                buttons: [{
                    icon: 'fa-times',
                    title: 'Fechar'
                }],
                shown: function(){
                    $('#modal_person_name').focus();
                },
                hidden: function(){
                    $('#person_code').focus();
                }
            });
        });
    },
    showAttributes: function(){
        $('#person-attributes').html('');
        $.each(Person.person.attributes,function(e,attribute){
            $('#person-attributes').append(
                '<div style="background-image:url(' + ( attribute.image || '' ) + ')" data-toggle="tooltip" title="' + attribute.attribute_name + '" class="attribute">' +
                ( !attribute.image ? attribute.attribute_name.substring(0,1) : '' ) +
                '</div>'
            );
        });
        $('#person-attributes div').tooltip();
    }
};

PersonImage = {
    del: function(){
        global.modal({
            icon: 'fa-question-circle-o',
            title: 'Confirmação',
            html: '<p>Deseja realmente remover a imagem da pessoa?</p>',
            buttons: [{
                icon: 'fa-times',
                title: 'Não',
                class: 'pull-left'
            },{
                icon: 'fa-check',
                title: 'Sim',
                action: function(){
                    global.post({
                        url: global.uri.uri_public_api + 'image.php?action=del',
                        data: {
                            image_id: Person.person.person_id,
                            image_dir: 'person'
                        }
                    },function(){
                        $('#button-image-person-remove').prop('disabled',true);
                        Person.person.image = null;
                        PersonImage.show();
                    });
                }
            }]
        });
    },
    events: function(){
        $('#button-image-person-web-cam').click(function(){
            if( !Electron ){
                global.validateMessage('Esse recurso só está disponível através do aplicativo.');
                return;
            }
            PersonImage.webcam();
        });
        $('#file-image-person').change(function(){
            PersonImage.up();
        });
        $('#button-image-person-remove').click(function(){
            PersonImage.del();
        });
        if( typeof(Electron) == 'object' ){
            ipcRenderer.on('taken-photo',(event, response) => {
                global.unLoader();
                if( !!response.fileImage ){
                    PersonImage.electronWebcam(response.fileImage);
                }
            });
        }
    },
    show: function(){
        if( !!Person.person.image ) {
            $('#person-image-cover .text').hide();
        } else {
            $('#person-image-cover .text').show();
        }
        $('#person-image-cover').css({
            'background-image': !!Person.person.image ? 'url(' + Person.person.image + ')' : ''
        });
    },
    up: function(){
        var data = new FormData();
        data.append('image_id',Person.person.person_id);
        data.append('image_dir','person');
        data.append('file[]',$('#file-image-person')[0].files[0]);
        global.post({
            url: global.uri.uri_public_api + 'image.php?action=up',
            data: data,
            cache: false,
            dataType: 'json',
            contentType: false,
            processData: false
        },function(data){
            Person.person.image = data.images[0].image;
            $('#button-image-person-remove').prop('disabled',false);
            PersonImage.show();
        });
        $('#file-image-person').filestyle('clear');
    },
    webcam: function(){
        global.onLoader();
        ipcRenderer.send('take-photo',{
            open: false
        });
    },
    electronWebcam: function(fileImage){
        global.post({
            url: global.uri.uri_public_api + 'person.php?action=electronWebcam',
            data: {
                person_id: Person.person.person_id,
                image: fileImage
            },
            dataType: 'json'
        },function(data){
            Person.person.image = data.image;
            PersonImage.show();
        })
    }
};

Address = {
    address: {},
    delivery: null,
    getAddress: true,
    contact: function(key){
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-address-contact',
            data: { contacts: Person.person.address[key].contacts },
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'small',
                icon: 'fa-phone',
                id: 'modal-address-contact',
                class: 'modal-address-contact',
                title: 'Contatos',
                html: html,
                buttons: [{
                    icon: 'fa-times',
                    title: 'Fechar'
                }]
            });
        });
    },
    events: function(){
        $('#button-budget-person-address').click(function(){
            if( Address.getAddress ){
                Address.getList();
            } else {
                $(this).hide();
                $('#button-budget-person-new').hide();
                $('#button-budget-person-address-new').show();
                $('#button-budget-person-address-back').show();
                $('a[href="#tab-person-2"]').click();
            }
        });
        $('#button-budget-person-address-new').click(function(){
            Address.new();
        });
        $('#button-budget-person-address-back').click(function(){
            $(this).hide();
            $('#button-budget-person-new').show();
            $('#button-budget-person-address').show();
            $('#button-budget-person-address-new').hide();
            $('a[href="#tab-person-1"]').click();
        });
        $('#button-address-new').click(function(){
            Address.new();
        });
    },
    getList: function(){
        global.post({
            url: global.uri.uri_public_api + 'address.php?action=getList',
            data: {
                get_address_contact: 1,
                person_id: Person.person.person_id
            },
            dataType: 'json'
        }, function(address){
            Address.getAddress = false;
            Person.person.address = address;
            $('a[href="#tab-person-2"]').click();
            $('#button-budget-person-new').hide();
            $('#button-budget-person-address').hide();
            $('#button-budget-person-address-new').show();
            $('#button-budget-person-address-back').show();
            Address.showList();
        });
    },
    main: function(key){
        global.post({
            url: global.uri.uri_public_api + 'address.php?action=main',
            data: {
                person_id: Person.person.person_id,
                address_code: Person.person.address[key].address_code
            },
            dataType: 'json'
        }, function (data) {
            global.modal({
                icon: 'fa-info',
                title: 'Informação',
                html: '<p>' + data.message + '</p>',
                buttons: [{
                    icon: 'fa-check',
                    title: 'Ok'
                }]
            });
            Person.person.address[key].address_main = 'Y';
            $.each(Person.person.address, function (k) {
                if (k != key) {
                    Person.person.address[k].address_main = 'N';
                }
            });
            Address.showList();
        });
    },
    map: function(key){
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-map-single',
            data: Person.person.address[key],
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'big',
                icon: 'fa-map-marker',
                id: 'modal-map-single',
                class: 'modal-map-single',
                title: 'Mapa',
                html: html,
                buttons: [{
                    icon: 'fa-check',
                    title: 'Ok'
                }],
                hidden: function(){
                    map.destroy();
                }
            });
        });
    },
    showDelivery: function(){
        var text = '<span>--</span>Nenhum endereço informado';
        if( !!Address.delivery ){
            text = (
                '<span>Código: ' + Address.delivery.address_code + '</span>' +
                Address.delivery.address_type + ' ' + Address.delivery.address_public_place + ' ' + Address.delivery.address_number + ' - ' +
                Address.delivery.district_name + ', ' + Address.delivery.city_name + ' - ' + Address.delivery.uf_id + ' - CEP ' + Address.delivery.address_cep
            );
        }
        $('#address-delivery').html(text);
    },
    showList: function(){
        var $panel = $('#tab-person-2');
        $panel.find('.address-card').parent().remove();
        $.each( Person.person.address, function(key,address){
            address.key = key;
            var main = address.address_main == 'Y';
            var selected = Budget.budget && Budget.budget.address_code == address.address_code;
            $panel.append(
                '<div class="col-xs-12 col-sm-4">' +
                    '<div data-key="' + key +'" class="box-shadow no-select address-card address-card-' + ( selected ? 'selected' : 'un-selected' ) + '">' +
                        '<div class="address-header">' +
                            'Endereço ' + address.address_code +
                            '<label>selecionado</label>' +
                            '<button ' + ( main ? 'disabled' : '' ) + ' class="btn btn-empty-gold pull-right" data-action="main" data-toggle="tooltip" title="Principal" data-key="' + key +'"><i class="fa fa-star' + ( main ? '' : '-o' ) + '"></i></button>' +
                            '<button class="btn btn-empty-red pull-right" data-action="map" data-toggle="tooltip" title="Ver no Mapa" data-key="' + key +'"><i class="fa fa-map-marker"></i></button>' +
                        '</div>' +
                        '<div class="address-body">' +
                            address.address_public_place + ', ' + address.address_number + '<br/>' +
                            address.district_name + ' - ' + address.city_name + ' - ' + address.uf_id + '<br/>' +
                            'CEP ' + ( !!address.address_cep ? address.address_cep : '<i>não informado</i>' ) + '<br/>' +
                        '</div>' +
                        '<div class="address-footer">' +
                            '<button data-toggle="tooltip" data-title="Editar" data-key="' + key +'" data-action="edit" class="btn btn-empty-blue pull-right"><i class="fa fa-pencil"></i></button>' +
                            '<button disabled data-toggle="tooltip" data-title="Excluir" data-key="' + key +'" data-action="del" class="btn btn-empty-red pull-right"><i class="fa fa-trash-o"></i></button>' +
                            '<button data-toggle="tooltip" data-title="Contatos" data-key="' + key +'" data-action="contact" class="btn btn-empty-blue pull-left"><i class="fa fa-phone"></i></button>' +
                        '</div>' +
                    '</div>' +
                '</div>'
            );
        });
        $panel.find('.address-card').click(function(){
            Address.delivery = Person.person.address[$(this).attr('data-key')];
            Budget.budget.address_code = Address.delivery.address_code;
            Budget.budget.address_uf_id = Address.delivery.uf_id;
            Budget.budget.budget_note_document = Budget.budget.budget_note_document.split('\n\nObs de Entrega: ')[0];
            if( !!Address.delivery.address_note ){
                Budget.budget.budget_note_document +=  '\n\nObs de Entrega: ' + Address.delivery.address_note;
            }
            Address.showDelivery();
            Address.showList();
        });
        $panel.find('button[data-action="map"]').click(function(e){
            e.preventDefault();
            e.stopPropagation();
            Address.map($(this).attr('data-key'));

        });
        $panel.find('button[data-action="main"]').click(function(e){
            e.preventDefault();
            e.stopPropagation();
            Address.main($(this).attr('data-key'));
        });
        $panel.find('button[data-action="contact"]').click(function(e){
            e.preventDefault();
            e.stopPropagation();
            Address.contact($(this).attr('data-key'));
        });
        $panel.find('button[data-action="edit"]').click(function(e){
            e.preventDefault();
            e.stopPropagation();
            Address.new($(this).attr('data-key'));
        });
        $panel.find('[data-toggle="tooltip"]').tooltip();
    },
    new: function(key){
        var address = (key && Person.person.address[key] ? Person.person.address[key] : null);
        if( address ) address.key = key;
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-address-new',
            data: {
                person_id: Person.person.person_id,
                address: address
            },
            dataType: 'html'
        },function(html){
            global.modal({
                icon: 'fa-plus',
                size: 'big',
                id: 'modal-address-new',
                class: 'modal-address-new',
                title: 'Novo Endereço',
                html: html,
                buttons: [{
                    icon: 'fa-times',
                    title: 'Cancelar',
                    class: 'pull-left btn-red'
                },{
                    unclose: true,
                    icon: (key && Person.person.address[key] ? 'fa-floppy-o' : 'fa-plus'),
                    title: (key && Person.person.address[key] ? 'Atualizar' : 'Cadastrar'),
                    class: (key && Person.person.address[key] ? 'btn-blue' : 'btn-green'),
                    action: function(){
                        ModalAddressNew.submit();
                    }
                }]
            });
        });
    }
};

Term = {
    term: {},
    terms: [],
    modal: null,
    typeahead: {
        items: 10,
        delay: 500,
        last: '',
        timer: 0,
        min: 2
    },
    data2form: function(){
        $('#term_id').selectpicker('val',Term.term.term_id);
        $('#term_code').val(Term.term.term_code).attr('data-value',Term.term.term_code);
    },
    events: function(){
        $('#term_code, #term_name').on('focus',function(){
            Budget.section = 3;
        });
        $('#term_id').on('changed.bs.select', function (e, clickedIndex) {
            Term.init();
            Term.data2form();
            Term.get({
                term_id: Term.terms[clickedIndex-1].term_id,
                term_code: null
            });
        });
        $('#term_code').keypress(function (e) {
            var keycode = e.keyCode || e.which;
            if (keycode == '13' && $(this).val().length) {
                e.preventDefault();
                e.stopPropagation();
                if( global.posts < 1 && ( !Term.term.term_id || parseInt(Term.term.term_code) != parseInt($(this).val()) )){
                    if( Budget.budget.payments.length ){
                        global.modal({
                            icon: 'fa-question-circle-o',
                            title: 'Confirmação',
                            html: '<p>Ao editar o prazo as parcelas serão removidas. Deseja continuar?</p>',
                            buttons: [{
                                icon: 'fa-times',
                                title: 'Não',
                                class: 'pull-left'
                            },{
                                icon: 'fa-check',
                                title: 'Sim',
                                action: function(){
                                    //Term.init();
                                    //Term.data2form();
                                    Term.get({
                                        term_id: null,
                                        term_code: $('#term_code').val()
                                    });
                                }
                            }]
                        });
                    } else {
                        Term.get({
                            term_id: null,
                            term_code: $('#term_code').val()
                        });
                    }
                }
            }
        });
        $('#button-budget-term-remove').click(function(){
            if( !Term.term.term_id ) return;
            global.modal({
                icon: 'fa-question-circle-o',
                title: 'Confirmação',
                html: '<p>Deseja realmente remover o prazo?</p>',
                buttons: [{
                    icon: 'fa-times',
                    title: 'Não',
                    class: 'pull-left'
                }, {
                    icon: 'fa-check',
                    title: 'Sim',
                    action: function(){
                        Term.init();
                        Term.data2form();
                    }
                }]
            });
        });
    },
    get: function(data){
        global.post({
            url: global.uri.uri_public_api + 'term.php?action=get',
            data: {
                get_term_modalities: 1,
                term_id: data.term_id,
                term_code: data.term_code,
                company_id: Company.company.company_id
            },
            dataType: 'json'
        }, function(term){
            Term.term = term;
            Term.data2form();
            Term.getModality();
            Budget.budget.term_id = term.term_id;
        });
    },
    getList: function(){
        global.post({
            url: global.uri.uri_public_api + 'term.php?action=getList',
            dataType: 'json'
        }, function(data){
            Term.terms = data;
            Term.showList();
        });
    },
    getModality: function(){
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-term-modalities',
            dataType: 'html'
        },function(html){
            global.modal({
                icon: 'fa-credit-card',
                id: 'modal-term-modalities',
                class: 'modal-term-modalities',
                title: 'Selecione a Modalidade',
                html: html,
                buttons: [{
                    icon: 'fa-times',
                    title: 'Cancelar',
                    class: 'pull-left',
                    action: function(){
                        Term.init();
                        Term.data2form();
                        $('#term_code').focus();
                    }
                }]
            });
        });
    },
    init: function(){
        Term.term = {
            term_id: null,
            term_code: '',
            term_description: '',
            modalities: []
        };
        Budget.budget.term_id = null;
    },
    showList: function(){
        $.each( Term.terms, function(key,term){
            $('#term_id').append($('<option>',{
                'value': term.term_id,
                'data-content': term.term_description
            }));
            if( Budget.budget.term_id == term.term_id ){
                Term.term = term;
                Term.data2form();
            }
        });
        $('#term_id').selectpicker('refresh');
    }
};

Payment = {
    modal: null,
    payment: {},
    banks: [],
    modalities: [],
    payment_value: 0,
    payment_aliquot: 0,
    payment_remaining: 0,
    table: global.table({
        selector: '#table-budget-payments',
        noControls: [0,4],
        order: [[2,'asc']],
        scrollY: 186,
        scrollCollapse: 1
    }),
    beforeEdit: function(key){
        if( !Term.term.term_id ) {
            if( !!Payment.modalities.length ){
                Payment.edit(key);
            } else {
                Payment.getModalities(function(){
                    Payment.edit(key);
                })
            }
            return;
        }
        global.modal({
            icon: 'fa-question-circle-o',
            title: 'Confirmação',
            html: '<p>Ao editar a parcela o prazo será removido. Deseja continuar?</p>',
            buttons: [{
                icon: 'fa-times',
                title: 'Não',
                class: 'pull-left'
            }, {
                icon: 'fa-check',
                title: 'Sim',
                action: function () {
                    Term.init();
                    Term.data2form();
                    if( !!Payment.modalities.length ){
                        Payment.edit(key);
                    } else {
                        Payment.getModalities(function(){
                            Payment.edit(key);
                        })
                    }
                }
            }]
        });
    },
    beforeRemoveCredit: function(){
        global.modal({
            icon: 'fa-question-circle-o',
            title: 'Confirmação',
            html: '<p>Deseja remover a carta de crédito do orçamento?</p>',
            buttons: [{
                icon: 'fa-times',
                title: 'Não',
                class: 'pull-left'
            }, {
                icon: 'fa-check',
                title: 'Sim',
                action: function () {
                    Payment.removeCredit();
                }
            }]
        });
    },
    credit: function(){
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-credit',
            data: {
                instance_id: Budget.budget.instance_id,
                company_id: Company.company.company_id,
                person: {
                    person_id: Person.person.person_id,
                    person_code: Person.person.person_code,
                    person_name: Person.person.person_name
                },
                selected: Budget.budget.credit.payable
            },
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'big',
                hideClose: 1,
                icon: 'fa-money',
                id: 'modal-credit',
                class: 'modal-credit',
                title: 'Cartas de crédito',
                html: html,
                buttons: [{
                    icon: 'fa-times',
                    title: 'Cancelar',
                    class: 'pull-left btn-red',
                    id: 'button-credit-cancel',
                    unclose: true
                },{
                    icon: 'fa-check',
                    title: 'Selecionar',
                    class: 'btn-blue',
                    id: 'button-credit-select',
                    unclose: true
                }],
                shown: function(){
                    ModalCredit.success = function(credits){
                        Payment.payment_value -= Budget.budget.credit.value;
                        Budget.budget.credit.value = 0;
                        Budget.budget.credit.payable = [];
                        Budget.budget.budget_credit = 'N';
                        $.each(credits,function(key,credit){
                            if( Budget.budget.credit.value < ( Budget.budget.budget_value_total - Payment.payment_value ) ) {
                                if( (Budget.budget.credit.value + credit.payable_value) > ( Budget.budget.budget_value_total - Payment.payment_value )) {
                                    credit.payable_value = Budget.budget.budget_value_total - Payment.payment_value - Budget.budget.credit.value;
                                }
                                Budget.budget.credit.value += parseFloat(credit.payable_value);
                                Budget.budget.credit.payable.push(credit);
                                Budget.budget.budget_credit = 'Y';
                            }
                        });
                        Payment.total();
                        Payment.showList();
                    }
                }
            })
        });
    },
    creditAuthorization: function(params){
        params.image = Person.person.image;
        params.person_id = Person.person.person_id;
        params.person_code = Person.person.person_code;
        params.person_name = Person.person.person_name;
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-credit-authorization',
            data: params,
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'small',
                icon: 'fa-lock',
                id: 'modal-credit-authorization',
                class: 'modal-credit-authorization',
                title: 'Autorização de Crédito',
                html: html,
                buttons: [{
                    icon: 'fa-times',
                    class: 'pull-left btn-red',
                    title: 'Cancelar'
                },{
                    icon: 'fa-unlock',
                    title: 'Autorizar',
                    class: 'btn-green',
                    id: 'button-credit-authorize',
                    unclose: true
                }],
                shown: function(){
                    $('#modal_user_user').focus();
                }
            });
        });
    },
    check: function(){
        if( !Budget.budget.export ){
            Budget.submit();
            return;
        }
        var deferred = 0;
        $.each(Budget.budget.payments,function(key,payment){
            if( global.config.credit.authorized_modality_id.indexOf(payment.modality_id) == -1 ){
                deferred += payment.budget_payment_value;
            }
        });
        if( deferred > 0 ){
            var debit_day_limit = parseInt(global.config.credit.debit_day_limit);
            if( Person.person.credit_limit.delay > debit_day_limit ){
                Payment.creditAuthorization({
                    reason: 1,
                    message: 'O cliente possui títulos vencidos em aberto por mais de ' + debit_day_limit + ' dias.'
                });
            } else if( deferred > Person.person.credit_limit.balance ){
                Payment.creditAuthorization({
                    reason: 2,
                    message: 'O valor da compra ultrapassa o limite de crédito do cliente.'
                });
            } else {
                Budget.submit();
            }
        } else {
            Budget.submit();
        }
    },
    del: function(key){
        global.modal({
            icon: 'fa-question-circle-o',
            title: 'Confirmação',
            html: '<p>Deseja realmente remover a parcela? O Prazo também será removido.</p>',
            buttons: [{
                icon: 'fa-times',
                title: 'Não',
                class: 'pull-left'
            },{
                icon: 'fa-check',
                title: 'Sim',
                action: function(){
                    Term.init();
                    Term.data2form();
                    Budget.budget.payments.splice(key,1);
                    Payment.showList();
                    Payment.total();
                }
            }]
        });
    },
    edit: function(key){
        Term.init();
        Term.data2form();
        Payment.payment = Budget.budget.payments[key];
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-payment',
            dataType: 'html'
        },function(htm) {
            global.modal({
                icon: 'fa-pencil',
                id: 'modal-payment',
                class: 'modal-payment',
                title: 'Editar Pagamento',
                html: htm,
                buttons: [{
                    icon: 'fa-times',
                    title: 'Cancelar',
                    class: 'pull-left'
                },{
                    icon: 'fa-floppy-o',
                    title: 'Atualizar',
                    unclose: true,
                    id: 'button-payment-edit',
                    action: function () {
                        $('#modal-payment').find('form button').click();
                    }
                }],
                shown: function(){
                    ModalPayment.payment = {
                        image: Payment.payment.image,
                        nature_id: Payment.payment.nature_id,
                        modality_id: Payment.payment.modality_id,
                        modality_type: Payment.payment.modality_type,
                        modality_description: Payment.payment.modality_description,
                        modality_installment: Payment.payment.modality_installment,
                        payment_entry: Payment.payment.budget_payment_entry,
                        payment_value: Payment.payment.budget_payment_value,
                        payment_installment: Payment.payment.budget_payment_installment,
                        payment_deadline: Payment.payment.budget_payment_deadline,
                        bank_id: Payment.payment.bank_id,
                        agency_id: Payment.payment.agency_id,
                        agency_code: Payment.payment.agency_code,
                        check_number: Payment.payment.check_number
                    };
                    ModalPayment.data2form();
                    $('#modal-payment').find('form').on('submit',function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        ModalPayment.form2data();
                        Budget.budget.payments[key] = {
                            budget_payment_id: Payment.payment.budget_payment_id,
                            external_id: Payment.payment.external_id,
                            budget_payment_credit: 'N',
                            image: ModalPayment.payment.image,
                            nature_id: ModalPayment.payment.nature_id,
                            modality_id: ModalPayment.payment.modality_id,
                            modality_type: ModalPayment.payment.modality_type,
                            modality_description: ModalPayment.payment.modality_description,
                            modality_installment: ModalPayment.payment.modality_installment,
                            budget_payment_entry: ModalPayment.payment.payment_entry,
                            budget_payment_value: ModalPayment.payment.payment_value,
                            budget_payment_installment: ModalPayment.payment.payment_installment,
                            budget_payment_deadline: ModalPayment.payment.payment_deadline,
                            bank_id: ModalPayment.payment.bank_id,
                            agency_id: ModalPayment.payment.agency_id,
                            agency_code: ModalPayment.payment.agency_code,
                            check_number: ModalPayment.payment.check_number
                        };
                        Payment.sort();
                        Payment.total();
                        $('#modal-payment').modal('hide');
                    });
                }
            });
        });
    },
    events: function(){
        $('#button-budget-payment-new').click(function(){
            if( !!Term.term.term_id ){
                global.modal({
                    icon: 'fa-question-circle-o',
                    title: 'Confirmação',
                    html: '<p>Se uma nova parcela for adicionada o prazo será removido.<br/>Deseja continuar?</p>',
                    buttons: [{
                        icon: 'fa-times',
                        title: 'Não',
                        class: 'pull-left'
                    },{
                        icon: 'fa-check',
                        title: 'Sim',
                        action: function(){
                            Term.init();
                            Term.data2form();
                            if( !!Payment.modalities.length ){
                                Payment.new();
                            } else {
                                Payment.getModalities(function(){
                                    Payment.new();
                                })
                            }
                        }
                    }]
                });
            } else {
                if( !!Payment.modalities.length ){
                    Payment.new();
                } else {
                    Payment.getModalities(function(){
                        Payment.new();
                    })
                }
            }
        });
        $('#button-budget-payment-remove').click(function(){
            global.modal({
                icon: 'fa-question-circle-o',
                title: 'Confirmação',
                html: '<p>Deseja realmente remover todas as parcelas?</p>',
                buttons: [{
                    icon: 'fa-times',
                    title: 'Não',
                    class: 'pull-left'
                },{
                    icon: 'fa-check',
                    title: 'Sim',
                    action: function(){
                        Term.init();
                        Term.data2form();
                        Budget.budget.payments = [];
                        if( Budget.budget.credit.value > 0){
                            Payment.removeCredit();
                        } else {
                            Payment.showList();
                            Payment.total();
                        }
                    }
                }]
            });
        });
        $('#button-budget-payment-recalculate').click(function(){
            global.modal({
                icon: 'fa-question-circle-o',
                title: 'Confirmação',
                html: '<p>O valor do pedido será rateado entre as parcelas. Deseja continuar?</p>',
                buttons: [{
                    icon: 'fa-times',
                    title: 'Não',
                    class: 'pull-left'
                }, {
                    icon: 'fa-check',
                    title: 'Sim',
                    action: function(){
                        Payment.recalculate();
                    }
                }]
            });
        });
        $('#button-budget-notes').click(function(){
            Budget.note();
        });
        $('#button-budget-payment-credit').click(function(){
            if( Budget.budget.budget_value_total == 0 ){
                global.validateMessage('O valor do pedido está zerado. Verifique.');
                return;
            }
            Payment.credit();
        });
        Payment.table.on('draw',function(){
            var table = $('#table-budget-payments');
            $(table).find('tr').on('dblclick',function(){
                if( $(this).attr('data-key') == -1 ){
                    Payment.credit();
                } else {
                    Payment.beforeEdit($(this).attr('data-key'));
                }
            }).find('button').click(function(){
                Payment[$(this).attr('data-action')]($(this).attr('data-key'));
            }).tooltip({
                container: 'body'
            });
        });
    },
    new: function(){
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-payment',
            data: { modalities: Payment.modalities },
            dataType: 'html'
        },function(htm){
            global.modal({
                icon: 'fa-plus',
                title: 'Novo Pagamento',
                id: 'modal-payment',
                class: 'modal-payment',
                html: htm,
                buttons: [{
                    icon: 'fa-times',
                    title: 'Cancelar',
                    class: 'pull-left'
                },{
                    icon: 'fa-plus',
                    title: 'Adicionar',
                    unclose: true,
                    action: function(){
                        $('#modal-payment').find('form button').click();
                    }
                }],
                load: function(){
                    $('#modal-payment').find('form').on('submit',function(e){
                        e.preventDefault();
                        e.stopPropagation();
                        ModalPayment.form2data();
                        Budget.budget.payments.push({
                            budget_payment_id: null,
                            external_id: null,
                            budget_payment_credit: 'N',
                            image: ModalPayment.payment.image,
                            modality_id: ModalPayment.payment.modality_id,
                            modality_type: ModalPayment.payment.modality_type,
                            modality_description: ModalPayment.payment.modality_description,
                            modality_installment: ModalPayment.payment.modality_installment,
                            budget_payment_entry: ModalPayment.payment.payment_entry,
                            budget_payment_value: ModalPayment.payment.payment_value,
                            budget_payment_installment: ModalPayment.payment.payment_installment,
                            budget_payment_deadline: ModalPayment.payment.payment_deadline,
                            bank_id: ModalPayment.payment.bank_id,
                            agency_id: ModalPayment.payment.agency_id,
                            agency_code: ModalPayment.payment.agency_code,
                            check_number: ModalPayment.payment.check_number,
                            nature_id: ModalPayment.payment.nature_id
                        });
                        Payment.sort();
                        Payment.total();
                        $('#modal-payment').modal('hide');
                    });
                }
            });
        });
    },
    recalculate: function(){
        var installment = Budget.budget.payments.length;
        var value_total = Budget.budget.budget_value_total - Budget.budget.credit.value;
        var budget_payment_value = parseInt((100*value_total)/installment)/100;
        for( var i=0; i<installment; i++ ){
            if( (i+1) == installment ){
                budget_payment_value = parseFloat((budget_payment_value + parseFloat((value_total-(installment*budget_payment_value)).toFixed(2))).toFixed(2));
            }
            Budget.budget.payments[i].budget_payment_value = budget_payment_value;
        }
        Payment.total();
        Payment.showList();
    },
    removeCredit: function(){
        var payable = [];
        $.each(Budget.budget.credit.payable,function(key,credit){
             payable.push(credit.payable_id);
        });
        global.post({
            url: global.uri.uri_public_api + 'credit.php?action=redeem',
            data: {
                payable: payable,
                instance_id: Budget.budget.instance_id
            },
            dataType: 'json'
        },function(){
            Budget.budget.credit.value = 0;
            Budget.budget.credit.payable = [];
            Budget.budget.budget_credit = 'Y';
            $('#payment-credit-value').text('R$ 0,00');
            Payment.total();
            Payment.showList();
        });
    },
    showList: function(){
        Payment.table.clear();
        if( Budget.budget.credit.value > 0 ){
            var row = Payment.table.row.add([
                '1x',
                '<img src="' + global.uri.uri_public + 'files/modality/00A000000P.png"/>Carta de Crédito',
                '<span>' + global.today() + '</span>' + global.date2Br(global.today()),
                global.float2Br(Budget.budget.credit.value),
                '<button data-toggle="tooltip" data-title="Editar Parcela" data-action="credit" class="btn-empty"><i class="fa fa-pencil txt-blue"></i></button>' +
                '<button data-toggle="tooltip" data-title="Remover Parcela" data-action="beforeRemoveCredit" class="btn-empty"><i class="fa fa-trash-o txt-red"></i></button>'
            ]).node();
            $(row).attr('data-key',-1);
        }
        $.each( Budget.budget.payments, function(key, payment){
            var row = Payment.table.row.add([
                ( payment.budget_payment_entry == 'Y' ? '<i class="fa fa-check-circle"></i> ' : '' ) + payment.budget_payment_installment + 'x',
                ( payment.image ? '<img src="' + payment.image + '"/> ' : '<i class="fa fa-credit-card"></i> ' ) + payment.modality_description,
                '<span>' + payment.budget_payment_deadline + '</span>' + global.date2Br(payment.budget_payment_deadline),
                global.float2Br(payment.budget_payment_value),
                '<button data-toggle="tooltip" data-title="Editar Parcela" data-action="beforeEdit" data-key="' + key + '" class="btn-empty"><i class="fa fa-pencil txt-blue"></i></button>' +
                '<button data-toggle="tooltip" data-title="Remover Parcela" data-action="del" data-key="' + key + '" class="btn-empty"><i class="fa fa-trash-o txt-red"></i></button>'
            ]).node();
            $(row).attr('data-key',key);
        });
        $('#button-budget-payment-remove').prop('disabled',!Budget.budget.payments.length);
        $('#button-budget-payment-recalculate').prop('disabled',!Budget.budget.payments.length);
        Payment.table.draw();
    },
    sort: function(){
        Budget.budget.payments.sort(function( a, b ){
            if( a.budget_payment_deadline < b.budget_payment_deadline )
                return -1;
            if( a.budget_payment_deadline > b.budget_payment_deadline )
                return 1;
            return 0;
        });
        Payment.showList();
    },
    total: function(){
        Payment.payment_value = 0;
        $.each(Budget.budget.payments,function(e,payment){
            Payment.payment_value += payment.budget_payment_value;
        });
        Payment.payment_value = parseFloat(Payment.payment_value.toFixed(2)) + Budget.budget.credit.value;
        Payment.payment_aliquot = (Payment.payment_value/(Budget.budget.budget_value_total ? Budget.budget.budget_value_total : 1))*100;
        Payment.payment_remaining = Budget.budget.budget_value_total - Payment.payment_value;
        $('#budget_payment_value').val('R$ '+global.float2Br(Payment.payment_value));
        $('#budget_payment_aliquot').val(global.float2Br(Payment.payment_aliquot)+'%');
        $('#budget_payment_remaining').val('R$ '+global.float2Br(Payment.payment_remaining));
        $('#resume-st').text('R$ ' + global.float2Br(Budget.budget.budget_value_st));
        $('#resume-icms').text('R$ ' + global.float2Br(Budget.budget.budget_value_icms));
        $('#resume-total').text('R$ ' + global.float2Br(Budget.budget.budget_value));
        $('#resume-addition').text('R$ ' + global.float2Br(Budget.budget.budget_value_addition));
        $('#resume-total-liquid').text('R$ ' + global.float2Br(Budget.budget.budget_value_total));
        var value_discount = 0;
        $.each(Budget.budget.items,function(key,item){
            value_discount += item.budget_item_value_discount;
        });
        var aliquot_discount = Budget.budget.budget_value > 0 ? parseFloat(((value_discount/Budget.budget.budget_value) * 100).toFixed(2)) : 0;
        $('#resume-discount').text(global.float2Br(aliquot_discount) + '% / R$ ' + global.float2Br(value_discount));
        $('#resume-items').text(Budget.budget.items.length + ' Ite' + (Budget.budget.items.length == 1 ? 'm' : 'ns'));
        $('#payment-credit-value').text('R$ '+global.float2Br(Budget.budget.credit.value));
    },
    getModalities: function(success){
        global.post({
            url: global.uri.uri_public_api + 'modality.php?action=getList',
            data: { company_id: Company.company.company_id },
            dataType: 'json'
        },function(modalities){
            Payment.modalities = modalities;
            global.post({
                url: global.uri.uri_public_api + 'bank.php?action=getList',
                dataType: 'json'
            },function(banks){
                Payment.banks = banks;
                success()
            });
        });
    }
};
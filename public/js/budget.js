$(document).ready(function(){

    Keyboard.events();
    Budget.events();
    Budget.showCompanies(function(){
        if( !!Budget.data.company_id ){
            Budget.getList();
        }
    });

    global.mask();
    global.tooltip();
    global.unLoader();

});

Keyboard = {
    events: function(){
        global.listener.simple_combo("ctrl n", function(e){
            e.preventDefault();
            e.stopPropagation();
            Budget.new();
        });
    }
};

Budget = {
    budgets: [],
    type: {
        'B': {
            icon: 'file',
            title: 'Orçamento',
            color: 'green-light'
        },
        'P': {
            icon: 'file-powerpoint-o',
            title: 'Pedido de Venda',
            color: 'orange'
        },
        'D': {
            icon: 'file-text-o',
            title: 'DAV',
            color: 'blue'
        }
    },
    origin: {
        'D': {
            icon: 'desktop',
            title: 'Desktop',
            color: 'blue-light',
            class: 'desktop'
        },
        'M': {
            icon: 'mobile',
            title: 'Celular',
            color: 'orange-light',
            class: 'mobile'
        }
    },
    status: {
        'O': {
            icon: 'cloud',
            title: 'Aberto'
        },
        'L': {
            icon: 'cloud',
            title: 'Liberado'
        },
        'B': {
            icon: 'cloud-download',
            title: 'Faturado'
        },
        'C': {
            icon: 'cloud-download',
            title: 'Cancelado'
        }
    },
    delivery: {
        'Y': {
            icon: 'truck',
            title: 'Com Entrega',
            color: 'blue-light'
        },
        'N': {
            icon: 'truck',
            title: 'Sem Entrega',
            color: 'gray'
        }
    },
    data: {
        company_id: null,
        seller_id: null,
        start_date: global.today(),
        end_date: global.today()
    },
    filters: {
        status: [],
        delivery: []
    },
    table: global.table({
        selector: '#table-budgets',
        searching: 1,
        noControls: [7,8,9],
        order: [[0,'desc']]
    }),
    typeahead: {
        items: 10,
        delay: 500,
        last: '',
        timer: 0
    },
    actions: function(budget){
        return(
            '<div class="dropdown dropdown-budget dropdown-actions">' +
                '<button class="btn btn-empty" type="button" data-toggle="dropdown">' +
                    '<i class="fa fa-ellipsis-v"></i>' +
                '</button>' +
                '<ul class="dropdown-menu pull-right">' +
                    '<li><a data-action="open" disabled="' + ( global.login.access.budget.open.value == 'N' ) + '" data-key="' + budget.key + '" data-id="' + budget.budget.id + '" class="dropdown-item" href="#"><i class="fa fa-folder-open-o"></i>Abrir</a></li>' +
                    '<li><a data-action="clone" disabled="' + ( global.login.access.budget.clone.value == 'N' ) + '" data-key="' + budget.key + '" data-id="' + budget.budget.id + '" class="dropdown-item" href="#"><i class="fa fa-clone txt-orange"></i>Duplicar</a></li>' +
                    '<li class="divider"></li>' +
                    '<li><a data-action="beforePrint" disabled="' + ( global.login.access.budget.print.value == 'N' ) + '" data-key="' + budget.key + '" data-id="' + budget.budget.id + '" class="dropdown-item" href="#"><i class="fa fa-print txt-green"></i>Imprimir</a></li>' +
                    '<li><a data-action="beforeDelivery" disabled="' + ( budget.budget.status == 'B' ) + '" data-key="' + budget.key + '" data-id="' + budget.budget.id + '" class="dropdown-item" href="#"><i class="fa fa-truck txt-blue"></i>Entrega</a></li>' +
                    '<li><a data-action="mail" disabled="' + ( global.login.access.budget.mail.value == 'N' ) + '" data-key="' + budget.key + '" data-id="' + budget.budget.id + '" class="dropdown-item" href="#"><i class="fa fa-envelope-o txt-blue"></i>E-mail</a></li>' +
                    // '<li><a data-action="recover" disabled="' + ( true || global.login.access.budget.recover.value == 'N' ) + '" data-key="' + budget.key + '" data-id="' + budget.budget.id + '" class="dropdown-item" href="#"><i class="fa fa-unlock txt-red"></i>Recuperar Pedido</a></li>' +
                    // '<li><a data-action="order" disabled="' + ( true || global.login.access.budget.order.value == 'N' ) + '" data-key="' + budget.key + '" data-id="' + budget.budget.id + '" class="dropdown-item" href="#"><i class="fa fa-file-powerpoint-o txt-orange"></i>Exportar Pedido</a></li>' +
                    // '<li><a data-action="dav" disabled="' + ( true || global.login.access.budget.dav.value == 'N' ) + '" data-key="' + budget.key + '" data-id="' + budget.budget.id + '" class="dropdown-item" href="#"><i class="fa fa-file-text-o txt-blue"></i>Exportar Dav</a></li>' +
                    // '<li><a data-action="info" disabled="' + ( true || global.login.access.budget.info.value == 'N' ) + '" data-key="' + budget.key + '" data-id="' + budget.budget.id + '" class="dropdown-item" href="#"><i class="fa fa-info txt-gray"></i>Informações</a></li>' +
                    '<li><a data-action="audit" disabled="' + ( global.login.access.budget.audit.value == 'N' ) + '" data-key="' + budget.key + '" data-id="' + budget.budget.id + '" class="dropdown-item" href="#"><i class="fa fa-shield txt-green"></i>Auditoria</a></li>' +
                    '<li class="divider"></li>' +
                    '<li><a data-action="beforeDel" disabled="' + ( budget.budget.status != 'O' || global.login.access.budget.del.value == 'N' ) + '" data-key="' + budget.key + '" data-id="' + budget.budget.id + '" class="dropdown-item" href="#"><i class="fa fa-trash-o txt-red"></i>Apagar</a></li>' +
                '</ul>' +
            '</div>'
        );
    },
    audit: function(key,budget_id){
        global.post({
            url: global.uri.uri_public + 'api/modal.php?modal=modal-audit',
            data: {
                log_script: 'budget',
                log_parent_id: budget_id
            },
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'big',
                id: 'modal-audit',
                class: 'modal-audit',
                icon: 'fa-shield',
                title: 'Auditoria Orçamento ' + ('00000'+budget_id).slice(-6),
                html: html,
                buttons: [{
                    title: 'Fechar'
                }]
            });
        });
    },
    beforeDel: function(key,id){
        global.modal({
            size: 'small',
            icon: 'fa-question-circle-o',
            title: 'Confirmação',
            html: '<p>Deseja realmente remover o orçamento?</p>',
            buttons: [{
                icon: 'fa-times',
                title: 'Não',
                class: 'pull-left'
            },{
                icon: 'fa-check',
                title: 'Sim',
                action: function(){
                    Budget.del(id)
                }
            }]
        });
    },
    beforeDelivery: function(key){
        global.post({
            url: global.uri.uri_public_api + 'budget.php?action=getDelivery',
            data: { budget_id: Budget.budgets[key].budget.id },
            dataType: 'json'
        }, function(data){
            Budget.budgets[key].budget.delivery = data.budget_delivery;
            Budget.budgets[key].delivery = {
                delivery_date: data.budget_delivery_date,
                note_document: data.budget_note_document
            };
            Budget.budgets[key].getDelivery = 1;
            Budget.setDelivery(key);
        });
    },
    beforePrint: function(key){
        var budget = Budget.budgets[key];
        global.post({
            url: global.uri.uri_public + 'api/modal.php?modal=modal-budget-print',
            data: budget,
            dataType: 'html'
        },function(html){
            global.modal({
                id: 'modal-budget-print',
                class: 'modal-budget-print',
                size: 'small',
                icon: 'fa-print',
                title: 'Imprimir Orçamento',
                html: html,
                buttons: [{
                    icon: 'fa-check',
                    title: 'Fechar'
                }]
            });
        });
    },
    clone: function(key,id){
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
                    global.window({
                        url: global.uri.uri_public + 'window.php?module=budget&action=new&clone=1&budget_id=' + id + '&company_id=' + Budget.company.company_id
                    });
                }
            }]
        });
    },
    del: function(id){
        global.post({
            url: global.uri.uri_public_api + 'budget.php?action=del',
            data: { budget_id: id }
        },function(){
            Budget.getList();
        });
    },
    events: function(){
        $('#form-budget-filter').on('submit',function(e){
            e.preventDefault();
            e.stopPropagation();
            Budget.getList();
        });
        $('#budget_company_id').on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
            Budget.company = global.login.companies[clickedIndex-1];
            Budget.data.company_id = Budget.company.company_id;
            Budget.getList();
        });
        $('#budget_seller_name').on('keyup',function(){
            if( $(this).val().length >= 3 && $(this).val() != Budget.typeahead.last ){
                clearTimeout(Budget.typeahead.timer);
                Budget.typeahead.last = $(this).val();
                Budget.typeahead.timer = setTimeout(function(){
                    global.autocomplete({
                        items: 'all',
                        selector: '#budget_seller_name',
                        data: {
                            limit: Budget.typeahead.items,
                            person_name: $('#budget_seller_name').val(),
                            categories: [global.config.person.seller_category_id]
                        },
                        url: global.uri.uri_public_api + 'person.php?action=typeahead',
                        callBack: function(item){
                            Budget.data.seller_id = item.item_id;
                            $('#budget_seller_code').val(item.item_code).attr('data-value',item.item_code);
                            Budget.getList();
                        }
                    });
                },Budget.typeahead.delay);
            }
        });
        $('#button-budget-seller-search').click(function(){
            Seller.search(function(){
                Budget.getList();
            });
        });
        $('#budget_seller_code').keypress(function (e){
            var keycode = e.keyCode || e.which;
            if( keycode == '13' && $(this).val().length ){
                e.preventDefault();
                e.stopPropagation();
                Seller.get({
                    person_code: $(this).val(),
                    category_id: global.config.person.seller_category_id
                });
            }
        }).on('blur',function(){
            if( $(this).attr('data-value').length ){
                $(this).val($(this).attr('data-value'));
            }
        });
        $('#button-budget-seller-remove').click(function(){
            if( !!Budget.data.seller_id ){
                Budget.data.seller_id = null;
                $('#budget_seller_code').val('').attr('data-value','');
                $('#budget_seller_name').val('').attr('data-value','');
            }
        });
        if( !!global.login.person ){
            Budget.data.seller_id = global.login.person_id;
            $('#budget_seller_code').val(global.login.person.person_code).attr('data-value',global.login.person.person_code);
            $('#budget_seller_name').val(global.login.person.person_name).attr('data-value',global.login.person.person_name);
        }
        $('#budget_start_date, #budget_end_date').datepicker({
            format: 'dd/mm/yyyy'
        }).blur(function(){
            if( $(this).val().length != 10 ){
                $(this).val(global.date2Br(global.today()));
            }
        }).val(global.date2Br(global.today()));
        $('#budget_search').keyup(function(){
            Budget.table.search(this.value).draw();
        });
        $('#button-budget-new').click(function(){
            if( !Budget.data.company_id ){
                global.validateMessage('Selecione a Empresa.');
                return;
            }
            Budget.new();
        });
        $('button[data-action="status"]').click(function(){
            var value = $(this).attr('data-value');
            $(this).toggleClass('selected');
            var index = Budget.filters.status.indexOf(value);
            if( index > -1 ){
                Budget.filters.status.splice(index,1);
            } else {
                Budget.filters.status.push(value);
            }
            Budget.showList();
        });
        $('button[data-action="delivery"]').click(function(){
            var value = $(this).attr('data-value');
            $(this).toggleClass('selected');
            var index = Budget.filters.delivery.indexOf(value);
            if( index > -1 ){
                Budget.filters.delivery.splice(index,1);
            } else {
                Budget.filters.delivery.push(value);
            }
            Budget.showList();
        });
        Budget.table.on('draw',function(){
            var $table = $('#table-budgets');
            $table.find('button[data-toggle="dropdown"]').unbind('click').click(function(){
                var top = $(this).parent().position().top;
                var documentHeight = $(document).innerHeight();
                var menuHeight = 236;
                if( top + menuHeight > documentHeight ){
                    $(this).next().css({
                        'top': 'auto',
                        'bottom': '100%'
                    });
                }
            });
            $table.find('a[disabled="false"]').unbind('click').click(function(e){
                e.preventDefault();
                e.stopPropagation();
                $('.dropdown-budget').removeClass('open');
                Budget[$(this).attr('data-action')]($(this).attr('data-key'),$(this).attr('data-id'));
            });
            $table.find('[data-toggle="tooltip"]').tooltip({container:'body'});
        });
        global.mask();
    },
    getList: function(){
        Budget.data.company_id = $('#budget_company_id').val();
        if( !Budget.data.company_id ){
            global.validateMessage('A empresa deverá ser selecionada.');
        }
        Budget.data.start_date = global.date2Us($('#budget_start_date').val());
        Budget.data.end_date = global.date2Us($('#budget_end_date').val());
        if( Budget.data.start_date.length != 10 || Budget.data.end_date.length != 10 ){
            global.validateMessage('<p>Verifique as datas informadas.</p>');
            return;
        }
        if( parseInt(Budget.data.start_date.split('-').join('')) > parseInt(Budget.data.end_date.split('-').join('')) ){
            global.validateMessage('<p>A data inicial não pode ser maior que a data final.</p>');
            return;
        }
        var diff = global.dateDiff(Budget.data.start_date,Budget.data.end_date);
        if( diff > 31 ){
            global.validateMessage('<p>Verifique o intervalo entre as datas selecionadas.<br/>O período máximo permitido será de 31 dias.</p>')
            return;
        }
        global.post({
            url: global.uri.uri_public_api + 'budget.php?action=getList',
            data: Budget.data,
            dataType: 'json'
        },function(budgets){
            Budget.budgets = budgets;
            Budget.showList();
        });
    },
    new: function(){
        global.window({
            url: global.uri.uri_public + 'window.php?module=budget&action=new&company_id=' + Budget.company.company_id
        });
    },
    mail: function(key,id){
        global.window({
            url: global.uri.uri_public + 'window.php?module=budget&action=mail&budget_id=' + id +'&company_id=' + Budget.company.company_id
        });
    },
    open: function(key,id){
        if( global.login.access.budget.open.value == 'N' ) return;
        global.window({
            url: global.uri.uri_public + 'window.php?module=budget&action=new&budget_id=' + id +'&company_id=' + Budget.company.company_id
        });
    },
    print: function(params){
        global.window({
            url: global.uri.uri_public + 'window.php?module=budget&action=' + params.action + '&budget_id=' + params.budget_id,
            width: params.width || 860,
            height: params.height || 620
        });
    },
    setDelivery: function(key){
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
                    title: 'Salvar',
                    action: function(){
                        ModalDelivery.form2data();
                        global.post({
                            url: global.uri.uri_public_api + 'budget.php?action=delivery',
                            data: {
                                budget_id: Budget.budgets[key].budget.id,
                                budget_delivery: ModalDelivery.delivery.budget_delivery,
                                budget_delivery_date: ModalDelivery.delivery.budget_delivery_date,
                                budget_note_document: ModalDelivery.delivery.budget_note_document
                            },
                            dataType: 'json'
                        },function(){
                            Budget.budgets[key].budget.delivery = ModalDelivery.delivery.budget_delivery;
                            Budget.budgets[key].delivery.delivery_date = ModalDelivery.delivery.budget_delivery_date;
                            Budget.budgets[key].delivery.note_document = ModalDelivery.delivery.budget_note_document;
                            Budget.showList();
                        });
                    }
                }],
                shown: function(){
                    ModalDelivery.delivery = {
                        budget_delivery: Budget.budgets[key].budget.delivery,
                        budget_delivery_date: Budget.budgets[key].delivery.delivery_date,
                        budget_note_document: Budget.budgets[key].delivery.note_document
                    };
                    ModalDelivery.data2form();
                }
            });
        });
    },
    showCompanies: function(success){
        if( !global.login.companies.length ){
            global.validateMessage('Você não possui acesso as empresas. Procure o administrador do sistema.');
        }
        if( !global.login.prices.length ){
            global.validateMessage('Você não possui acesso as tabelas de preço. Procure o administrador do sistema.');
        }
        $.each( global.login.companies, function(key,company){
            $('#budget_company_id').append($('<option>',{
                'value': company.company_id,
                'selected': company.user_company_main == 'Y',
                'data-content': '<i class="fa fa-stop" style="color:' + company.company_color + ';"></i> ' + ('0'+company.company_id).slice(-2) + ' - ' + company.company_name
            }));
            if( company.user_company_main == 'Y' ){
                Budget.company = company;
                Budget.data.company_id = company.company_id;
            }
        });
        $('#budget_company_id').selectpicker('refresh');
        if( success ) success();
    },
    showList: function(){
        Budget.table.clear();
        var count = 0, total = 0;
        $.each( Budget.budgets, function(key, budget){
            budget.status = (budget.budget.status == 'C' ? 'C' : (budget.budget.status == 'O' ? 'B' : (budget.budget.type + (budget.budget.status == 'B' ? 'B' : ''))));
            if(
                (!Budget.filters.status.length || Budget.filters.status.indexOf(budget.status) != -1) &&
                (!Budget.filters.delivery.length || Budget.filters.delivery.indexOf(budget.budget.delivery) != -1)
            ){
                count ++;
                budget.key = key;
                total += Budget.budgets[key].budget.value_total;
                var type = Budget.type[budget.budget.status == 'O' ? 'B' : budget.budget.type];
                var status = Budget.status[budget.budget.status];
                var delivery = Budget.delivery[budget.budget.delivery];

                if( !!budget.cost.value ) {
                    budget.cost.margin = parseFloat(((100 * budget.cost.value) / budget.budget.value_total).toFixed(2));
                    budget.cost.profit = parseFloat(budget.cost.value ? (((budget.budget.value_total/budget.cost.value)*100)-100).toFixed(2) : 0);
                    if (budget.cost.profit < 25) budget.cost.idne = 'idne1';
                    else if (budget.cost.profit < 50) budget.cost.idne = 'idne2';
                    else if (budget.cost.profit < 75) budget.cost.idne = 'idne3';
                    else if (budget.cost.profit < 100) budget.cost.idne = 'idne4';
                    else budget.cost.idne = 'idne5';
                }

                var row = Budget.table.row.add([
                    '<i data-toggle="tooltip" title="' + type.title + '" class="fa fa-' + status.icon + ' txt-' + (budget.status != 'C' ? type.color : 'gray') + '"></i><br/>' + budget.budget.code,
                    '<label>' + budget.seller.code + '</label><div class="seller">' + ( budget.seller.short_name || budget.seller.name ) + '</div>',
                    ( budget.budget.status != 'O' && budget.external.code ? budget.external.code : '--' ),
                    ( budget.budget.status == 'B' || budget.budget.status == 'C' ? budget.document.code : '--' ),
                    '<div class="person-cover"' + ( budget.person.image ? 'style="background-image:url(' + budget.person.image + ')"' : '' ) + '></div><label>' + budget.person.code + '</label><div class="client">' + budget.person.name + '</div>',
                    '<span>' + budget.budget.value_total_order + '</span>R$ ' + global.float2Br(budget.budget.value_total),
                    '<span>' + budget.budget.date + '</span>' + budget.budget.date_formatted,
                    budget.budget.icon ? '<img src="' + budget.budget.icon + '" />' : '--',
                    '<i data-toggle="tooltip" title="' + delivery.title + '" class="fa fa-' + delivery.icon + ' txt-' + delivery.color + '"></i>',
                    Budget.actions(budget)
                ]).node();
                $(row).addClass(budget.cost.idne).on('dblclick', function () {
                    Budget.open(key, budget.budget.id);
                });
            }
        });
        Budget.table.draw();
        $('footer div[data-label="budgets-count"]').html('<i class="fa fa-files-o"></i> ' + count);
        $('footer div[data-label="budgets-total"]').html('<i class="fa fa-money"></i> R$ ' + global.float2Br(total));
        $('footer div[data-label="budgets-average"]').html('<i class="fa fa-bar-chart"></i> R$ ' + global.float2Br(total/(count||1)));
    },
    total: function(){
        var count = 0, total = 0;
        $.each( Budget.table.rows({filter: 'applied'})[0], function(k,key){
            count ++;
            total += Budget.budgets[key].budget.value_total;
        });
        $('footer div[data-label="budgets-count"]').html('<i class="fa fa-files-o"></i> ' + count);
        $('footer div[data-label="budgets-total"]').html('<i class="fa fa-money"></i> R$ ' + global.float2Br(total));
        $('footer div[data-label="budgets-average"]').html('<i class="fa fa-bar-chart"></i> R$ ' + global.float2Br(total/(count||1)));
    }
};

Seller = {
    seller: {},
    get: function(data){
        global.post({
            url: global.uri.uri_public_api + 'person.php?action=get',
            data: data,
            dataType: 'json'
        },function(person){
            Seller.seller = {
                seller_id: person.person_id,
                seller_code: person.person_code,
                seller_name: person.person_name,
                image: person.image
            };
            Budget.data.seller_id = Seller.seller.seller_id;
            $('#budget_seller_code').val(Seller.seller.seller_code).attr('data-value',Seller.seller.seller_code);
            $('#budget_seller_name').val(Seller.seller.seller_name).attr('data-value',Seller.seller.seller_name);
            Budget.getList();
        });
    },
    search: function(success){
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-seller-search',
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
                    unclose: true,
                    action: function(){
                        if( !ModalSeller.seller.seller_id ){
                            global.validateMessage('Nenhum vendedor foi informado.',function(){
                                $('#modal_seller_code').focus().select();
                            });
                            return;
                        }
                        Seller.seller = ModalSeller.seller;
                        Budget.data.seller_id = Seller.seller.seller_id;
                        $('#modal-seller-search').modal('hide');
                        $('#budget_seller_code').val(Seller.seller.seller_code).attr('data-value',Seller.seller.seller_code);
                        $('#budget_seller_name').val(Seller.seller.seller_name).attr('data-value',Seller.seller.seller_name);
                        if( !!success ) success();
                    }
                }],
                shown: function(){
                    if( !!Seller.seller.seller_id ){
                        ModalSeller.seller = Seller.seller;
                        ModalSeller.show();
                    }
                }
            })
        });
    }
};
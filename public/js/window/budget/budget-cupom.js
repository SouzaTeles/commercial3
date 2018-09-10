$(document).ready(function(){

    Cupom.getBudget();
    global.unLoader();

});

Cupom = {
    budget: null,
    company: null,
    budget_id: global.url.searchParams.get('budget_id'),
    events: function(){
        $(document).ready(function(){
            window.print();
        });
        $('button').click(function(){
            window.print();
        });
    },
    getBudget: function(){
        global.post({
            url: global.uri.uri_public_api + 'budget.php?action=get',
            data: {
                budget_id: Cupom.budget_id,
                get_budget_items: 1,
                get_budget_person: 1,
                get_person_address: 1,
                get_person_address_contact: 1,
                get_budget_address: 1,
                get_budget_seller: 1,
                get_budget_payments: 1,
                get_budget_company: 1,
                get_budget_term: 1
            },
            dataType: 'json'
        },function(budget){
            Cupom.budget = budget;
            Cupom.getCompany();
        });
    },
    getCompany: function(){
        $.each(global.login.companies, function (key, company) {
            if (company.company_id == Cupom.budget.company_id) {
                Cupom.company = company;
                Cupom.showBudget();
            }
        });
        if( !Cupom.company ){
            global.modal({
                icon: 'fa-warning',
                title: 'Aviso',
                html: '<p>Você não possui acesso a empresa do pedido.</p>',
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
    showBudget: function(){

        $('#company-name').text(Cupom.budget.company.company_short_name);
        $('#company-address').html(
            Cupom.budget.company.address.address_type + ' ' +
            Cupom.budget.company.address.address_public_place + '<br/>' +
            Cupom.budget.company.address.district_name + ' - ' +
            Cupom.budget.company.address.city_name + ' - ' +
            Cupom.budget.company.address.uf_id
        );
        $('#company-phone').text(Cupom.budget.company.company_phone);

        $('#client-name').text('Cliente: ' + Cupom.budget.person.person_code + ' - ' + Cupom.budget.person.person_name);
        $('#client-contact').text(
            'Contato: ' +
            ( Cupom.budget.person.address[0] && Cupom.budget.person.address[0].contacts[0] ? Cupom.budget.person.address[0].contacts[0].address_contact_value : '--' )
        );
        $('#client-address').text(
            'Endereço: ' +
            Cupom.budget.person.address[0].address_type + ' ' +
            Cupom.budget.person.address[0].address_public_place + ' ' +
            Cupom.budget.person.address[0].address_number + ' - ' +
            Cupom.budget.person.address[0].district_name + ', ' +
            Cupom.budget.person.address[0].city_name + ' - ' +
            Cupom.budget.person.address[0].uf_id
        );

        $('#seller-name').html('Vendedor: ' + (Cupom.budget.seller.seller_short_name ? Cupom.budget.seller.seller_short_name : Cupom.budget.seller.seller_name));

        if( Cupom.budget.budget_status != 'O' ){
            $('#external-code').text(( Cupom.budget.external_type == "D" ? 'DAV' : 'Pedido' ) + ': ' + Cupom.budget.external_code);
        } else {
            $('#external-code').remove();
        }
        $('#budget-code').text('Orçamento: ' + Cupom.budget.budget_code);

        if( Cupom.budget.items.length ){
            var $table = $('#table-items');
            $table.find('tbody tr').remove();
            $.each( Cupom.budget.items, function(key, item){
                $table.append(
                    '<tr>' +
                        '<td>' + ('00'+(key+1)).slice('-3') + '</td>' +
                        '<td>' + item.product_code + '</td>' +
                        '<td colspan="3">' + item.product_name.substr(0,38) + '</td>' +
                    '</tr>' +
                    '<tr>' +
                        '<td></td>' +
                        '<td class="ng-binding">' + item.budget_item_quantity + item.unit_code + ' X</td>' +
                        '<td>R$' + global.float2Br(item.budget_item_value) + '</td>' +
                        '<td>R$' + global.float2Br(item.budget_item_value_discount) + ' [' + item.budget_item_aliquot_discount + '%]</td>' +
                        '<td class="text-right">' + global.float2Br(item.budget_item_value_total) + '</td>' +
                    '</tr>' +
                    '<tr><td colspan="5"></td></tr>'
                );
            });
        }

        $('#budget-value-total').text('R$' + global.float2Br(Cupom.budget.budget_value_total));
        if( Cupom.budget.payments.length ){
            var $table = $('#table-payments');
            $table.find('tbody tr').remove();
            $.each( Cupom.budget.payments, function(key, payment){
                $table.append(
                    '<tr>' +
                    '<td class="text-center">' + payment.budget_payment_installment + 'x</td>' +
                    '<td>' + payment.modality_description + '</td>' +
                    '<td class="text-center">R$' + global.float2Br(payment.budget_payment_value) + '</td>' +
                    '<td class="text-center">' + global.date2Br(payment.budget_payment_deadline) + '</td>' +
                    '</tr>'
                );
            });
        }

        $('#address-delivery').html(
            '<b>Endereço de entrega</b><br/>' +
            Cupom.budget.address.address_type + ' ' +
            Cupom.budget.address.address_public_place + ' ' +
            Cupom.budget.address.address_number + '<br/>' +
            Cupom.budget.address.district_name + ' - ' +
            Cupom.budget.address.city_name + ' - ' +
            Cupom.budget.address.uf_id
        );

        setTimeout(function(){
            Cupom.events();
        },1000);
    }
};
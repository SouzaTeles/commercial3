$(document).ready(function(){

    Print.getBudget();
    global.unLoader();

});

Print = {
    budget: null,
    company: null,
    budget_id: global.url.searchParams.get('budget_id'),
    events: function(){
        $('button').click(function(){
            window.print();
        });
    },
    getBudget: function(){
        global.post({
            url: global.uri.uri_public_api + 'budget.php?action=get',
            data: {
                budget_id: Print.budget_id,
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
            Print.budget = budget;
            Print.getCompany();
        });
    },
    getCompany: function(){
        $.each(global.login.companies, function (key, company) {
            if (company.company_id == Print.budget.company_id) {
                Print.company = company;
                Print.showBudget();            }
        });
        if( !Print.company ){
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

        $('#company-logo').attr('src',Print.company.image);
        $('#company-name').text(Print.budget.company.company_short_name);
        $('#company-address').html(
            Print.budget.company.address.address_type + ' ' +
            Print.budget.company.address.address_public_place + '<br/>' +
            Print.budget.company.address.district_name + ' - ' +
            Print.budget.company.address.city_name + ' - ' +
            Print.budget.company.address.uf_id
        );
        $('#company-phone').text(Print.budget.company.company_phone);

        if( Print.budget.budget_status != 'O' ){
            $('#external-code').text((Print.budget.external_type == "D" ? 'DAV' : 'Pedido') + ': ' + Print.budget.external_code);
        } else {
            $('#external-code').remove();
        }
        $('#budget-date').text('Data: ' + global.date2Br(Print.budget.budget_date.substring(0,10)));
        $('#budget-code').text('Orçamento n°: ' + Print.budget.budget_code);
        $('#seller-name').html('Vendedor: ' + (Print.budget.seller.seller_short_name ? Print.budget.seller.seller_short_name : Print.budget.seller.seller_name));

        $('#budget-message').text(Print.company.company_budget_message);

        if( Print.budget.client_id != Print.company.company_consumer_id ){
            $('#client-name').text('Cliente: ' + Print.budget.person.person_code + ' - ' + Print.budget.person.person_name);
            $('#client-contact').text(
                'Contato: ' +
                ( Print.budget.person.address[0] && Print.budget.person.address[0].contacts ? Print.budget.person.address[0].contacts[0].address_contact_value : '--' )
            );
            $('#client-address').text(
                'Endereço: ' +
                Print.budget.person.address[0].address_type + ' ' +
                Print.budget.person.address[0].address_public_place + ' ' +
                Print.budget.person.address[0].address_number + ' - ' +
                Print.budget.person.address[0].district_name + ', ' +
                Print.budget.person.address[0].city_name + ' - ' +
                Print.budget.person.address[0].uf_id
            )
        } else {
            $('#client-info').remove();
        }

        var $table = $('#table-products');
        $table.find('tbody tr').remove();
        $.each( Print.budget.items, function(key,item){
            $table.append(
                '<tr>' +
                    '<td>' + item.product_code + ' - ' + item.product_name + '</td>' +
                    '<td>' + item.unit_code + '</td>' +
                    '<td>' + item.budget_item_quantity + '</td>' +
                    '<td>R$' + global.float2Br(item.budget_item_value) + '</td>' +
                    '<td>' + global.float2Br(item.budget_item_aliquot_discount) + '%</td>' +
                    '<td>R$' + global.float2Br(item.budget_item_value_discount) + '</td>' +
                    ( Print.company.company_st == 'Y' ? '<td>R$' + global.float2Br(item.budget_item_value_st) + '</td>' : '' ) +
                    '<td>R$' + global.float2Br(item.budget_item_value_total/item.budget_item_quantity) + '</td>' +
                    '<td>R$' + global.float2Br(item.budget_item_value_total) + '</td>' +
                '</tr>'
            );
        });

        if( Print.company.company_st == 'N' ){
            $table.find('thead th').eq(5).hide();
        }

        $('#budget-address').text(
            'Endereço: ' +
            Print.budget.address.address_type + ' ' +
            Print.budget.address.address_public_place + ' ' +
            Print.budget.address.address_number + ' - ' +
            Print.budget.address.district_name + ', ' +
            Print.budget.address.city_name + ' - ' +
            Print.budget.address.uf_id
        );

        $('#budget-value').text('R$' + global.float2Br(Print.budget.budget_value));
        $('#budget-value-discount').text('R$' + global.float2Br(Print.budget.budget_value_discount));
        $('#budget-value-st').text('R$' + global.float2Br(Print.budget.budget_value_st));
        $('#budget-value-total').text('R$' + global.float2Br(Print.budget.budget_value_total));
        $('#budget-value-total-st').text('R$' + global.float2Br(Print.budget.budget_value_total+Print.budget.budget_value_st));

        if( Print.company.company_st == 'N' ){
            $('#table-budget-values tbody tr').eq(2).hide();
            $('#table-budget-values tbody tr').eq(4).hide();
        }

        if( !!Print.budget.term ){
            $('#payment-title').text('Pagamento ' + Print.budget.term.term_description);
        }

        if( Print.budget.payments.length ){
            var $table = $('#table-payments');
            $table.find('tbody tr').remove();
            $.each( Print.budget.payments, function(key, payment){
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

        $('#budget-note').html(Print.budget.budget_note||'--');
        $('#budget-note-document').html(Print.budget.budget_note_document||'--');

        setTimeout(function(){
            Print.events();
            window.print();
        },3000);
    }
};
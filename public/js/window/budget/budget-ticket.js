$(document).ready(function(){

    Ticket.getBudget();
    global.unLoader();

});

Ticket = {
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
                budget_id: Ticket.budget_id,
                get_budget_person: 1,
                get_budget_seller: 1,
                get_budget_payments: 1,
                get_budget_company: 1
            },
            dataType: 'json'
        },function(budget){
            Ticket.budget = budget;
            Ticket.getCompany();
        });
    },
    getCompany: function(){
        $.each(global.login.companies, function (key, company) {
            if (company.company_id == Ticket.budget.company_id) {
                Ticket.company = company;
                Ticket.showBudget();
            }
        });
        if( !Ticket.company ){
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

        $('#company-name').text(Ticket.budget.company.company_short_name);
        $('#client-name').text('Cliente: ' + Ticket.budget.person.person_code + ' - ' + Ticket.budget.person.person_name);
        $('#seller-name').html('Vendedor: ' + (Ticket.budget.seller.seller_short_name ? Ticket.budget.seller.seller_short_name : Ticket.budget.seller.seller_name));

        if( Ticket.budget.budget_status != 'O' ){
            $('#external-code').text(( Ticket.budget.external_type == "D" ? 'DAV' : 'Pedido' ) + ': ' + Ticket.budget.external_code);
        } else {
            $('#external-code').remove();
        }
        $('#budget-code').text('Orçamento: ' + Ticket.budget.budget_code);

        if( Ticket.budget.payments.length ){
            var $table = $('#table-payments');
            $table.find('tbody tr').remove();
            $.each( Ticket.budget.payments, function(key, payment){
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

        setTimeout(function(){
            Ticket.events();
        },1000);
    }
};
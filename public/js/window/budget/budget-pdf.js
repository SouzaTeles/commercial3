$(document).ready(function(){

    Pdf.getBudget();
    setTimeout(function(){
        global.unLoader();
    },1000);

});

Pdf = {
    budget: null,
    company: null,
    budget_id: global.url.searchParams.get('budget_id'),
    events: function(){
        $(document).ready(function(){
            Pdf.pdf();
        });
        $('button').click(function(){
            Pdf.pdf();
        });
        if( typeof(Electron) == 'object' ){
            ipcRenderer.on('wrote-pdf',(event, response) => {
                console.log(response);
                global.unLoader();
            });
        }
    },
    getBudget: function(){
        global.post({
            url: global.uri.uri_public_api + 'budget.php?action=get',
            data: {
                budget_id: Pdf.budget_id,
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
            Pdf.budget = budget;
            Pdf.getCompany();
        });
    },
    getCompany: function(){
        $.each(global.login.companies, function (key, company) {
            if (company.company_id == Pdf.budget.company_id) {
                Pdf.company = company;
                Pdf.showBudget();            }
        });
        if( !Pdf.company ){
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
    pdf: function(){
        global.onLoader();
        if( typeof(Electron) == 'object' ){
            ipcRenderer.send('print-to-pdf',{
                open: true
            });
        } else{
            var pdf = new jsPDF('p', 'in', 'a4');
            pdf.internal.scaleFactor = 30;
            pdf.addHTML($('.print-order')[0], function(){
                pdf.save(parseInt(Math.random().toString().replace('0.','')) + '.pdf');
            });
            global.unLoader();
        }
    },
    showBudget: function(){

        $('#company-logo').attr('src',Pdf.company.image);
        $('#company-name').text(Pdf.budget.company.company_short_name);
        $('#company-address').html(
            Pdf.budget.company.address.address_type + ' ' +
            Pdf.budget.company.address.address_public_place + '<br/>' +
            Pdf.budget.company.address.district_name + ' - ' +
            Pdf.budget.company.address.city_name + ' - ' +
            Pdf.budget.company.address.uf_id
        );
        $('#company-phone').text(Pdf.budget.company.company_phone);

        if( Pdf.budget.budget_status != 'O' ){
            $('#external-code').text(Pdf.budget.external_type == "D" ? 'DAV' : 'Pedido' + ': ' + Pdf.budget.external_code);
        } else {
            $('#external-code').remove();
        }
        $('#budget-date').text('Data: ' + global.date2Br(Pdf.budget.budget_date.substring(0,10)));
        $('#budget-code').text('Orçamento n°: ' + Pdf.budget.budget_code);
        $('#seller-name').html('Vendedor: ' + (Pdf.budget.seller.seller_short_name ? Pdf.budget.seller.seller_short_name : Pdf.budget.seller.seller_name));

        $('#budget-message').text(Pdf.company.company_budget_message);

        if( Pdf.budget.client_id != Pdf.company.company_consumer_id ){
            $('#client-name').text('Cliente: ' + Pdf.budget.person.person_code + ' - ' + Pdf.budget.person.person_name);
            $('#client-contact').text(
                'Contato: ' +
                ( Pdf.budget.person.address[0] && Pdf.budget.person.address[0].contacts ? Pdf.budget.person.address[0].contacts[0].address_contact_value : '--' )
            );
            $('#client-address').text(
                'Endereço: ' +
                Pdf.budget.person.address[0].address_type + ' ' +
                Pdf.budget.person.address[0].address_public_place + ' ' +
                Pdf.budget.person.address[0].address_number + ' - ' +
                Pdf.budget.person.address[0].district_name + ', ' +
                Pdf.budget.person.address[0].city_name + ' - ' +
                Pdf.budget.person.address[0].uf_id
            )
        } else {
            $('#client-info').remove();
        }

        var $table = $('#table-products');
        $table.find('tbody tr').remove();
        $.each( Pdf.budget.items, function(key,item){
            $table.append(
                '<tr>' +
                '<td>' + item.product_code + ' - ' + item.product_name + '</td>' +
                '<td>' + item.unit_code + '</td>' +
                '<td>' + item.budget_item_quantity + '</td>' +
                '<td>R$' + global.float2Br(item.budget_item_value) + '</td>' +
                '<td>' + global.float2Br(item.budget_item_aliquot_discount) + '%</td>' +
                '<td>R$' + global.float2Br(item.budget_item_value_discount) + '</td>' +
                ( Pdf.company.company_st == 'Y' ? '<td>R$' + global.float2Br(item.budget_item_value_st) + '</td>' : '' ) +
                '<td>R$' + global.float2Br(item.budget_item_value_total/item.budget_item_quantity) + '</td>' +
                '<td>R$' + global.float2Br(item.budget_item_value_total) + '</td>' +
                '</tr>'
            );
        });

        if( Pdf.company.company_st == 'N' ){
            $table.find('thead th').eq(5).hide();
        }

        $('#budget-address').text(
            'Endereço: ' +
            Pdf.budget.address.address_type + ' ' +
            Pdf.budget.address.address_public_place + ' ' +
            Pdf.budget.address.address_number + ' - ' +
            Pdf.budget.address.district_name + ', ' +
            Pdf.budget.address.city_name + ' - ' +
            Pdf.budget.address.uf_id
        );

        $('#budget-value').text('R$' + global.float2Br(Pdf.budget.budget_value));
        $('#budget-value-discount').text('R$' + global.float2Br(Pdf.budget.budget_value_discount));
        $('#budget-value-st').text('R$' + global.float2Br(Pdf.budget.budget_value_st));
        $('#budget-value-total').text('R$' + global.float2Br(Pdf.budget.budget_value_total));
        $('#budget-value-total-st').text('R$' + global.float2Br(Pdf.budget.budget_value_total+Pdf.budget.budget_value_st));

        if( Pdf.company.company_st == 'N' ){
            $('#table-budget-values tbody tr').eq(2).hide();
            $('#table-budget-values tbody tr').eq(4).hide();
        }

        if( !!Pdf.budget.term ){
            $('#payment-title').text('Pagamento ' + Pdf.budget.term.term_description);
        }

        if( Pdf.budget.payments.length ){
            var $table = $('#table-payments');
            $table.find('tbody tr').remove();
            $.each( Pdf.budget.payments, function(key, payment){
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

        $('#budget-note').html(Pdf.budget.budget_note||'--');
        $('#budget-note-document').html(Pdf.budget.budget_note_document||'--');

        setTimeout(function(){
            Pdf.events();
        },1000);
    }
};
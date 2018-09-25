$(document).ready(function(){

    Mail.getBudget();
    global.unLoader();

});

Mail = {
    data: {
        to: [],
        subject: '',
        message: '',
        pdfFileData: '',
        budget_id: global.url.searchParams.get('budget_id'),
        pdfFileName: parseInt(Math.random().toString().replace('0.','')) + '.pdf'
    },
    budget: null,
    company: null,
    budget_id: global.url.searchParams.get('budget_id'),
    events: function(){
        $(document).ready(function(){
            Mail.pdfGenerator();
        });
        $('#to').tagsinput();
        $('button').click(function(){
            if( typeof(Electron) != 'object' ){
                global.validateMessage('Este recurso só está disponível através do aplicativo.');
                return;
            }
            Mail.form2data();
            if( Mail.validate() ){
                Mail.mail();
            }
        });
        if( typeof(Electron) == 'object' ){
            ipcRenderer.on('wrote-pdf',(event, response) => {
                Mail.data.pdfFileData = response.pdf;
                global.unLoader();
            });
        }
    },
    form2data: function(){
        Mail.data.to = $('#to').tagsinput('items');
        Mail.data.subject = $('#subject').val();
        Mail.data.message = $('#message').val();
    },
    getBudget: function(){
        global.post({
            url: global.uri.uri_public_api + 'budget.php?action=get',
            data: {
                budget_id: Mail.budget_id,
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
            Mail.budget = budget;
            Mail.getCompany();
        });
    },
    getCompany: function(){
        $.each(global.login.companies, function (key, company) {
            if (company.company_id == Mail.budget.company_id) {
                Mail.company = company;
                Mail.showBudget();            }
        });
        if( !Mail.company ){
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
    mail: function(){
        global.post({
            url: global.uri.uri_public_api + 'budget.php?action=mail',
            data: Mail.data,
            dataType: 'json'
        },function(){
            global.modal({
                size: 'small',
                icon: 'fa-info-circle',
                title: 'Informação',
                html: '<p>O e-mail será enviado em breve.</p>',
                buttons: [{
                    title: 'Ok'
                }],
                hidden: function(){
                    window.close();
                }
            })
        });
    },
    pdfGenerator: function(){
        global.onLoader();
        if( typeof(Electron) == 'object' ){
            ipcRenderer.send('print-to-pdf',{
                open: false
            });
        } else {
            // var pdf = new jsPDF('p', 'in', 'a4');
            // pdf.internal.scaleFactor = 30;
            // pdf.addHTML($('.print-order')[0], function(){
            //     pdf.save(parseInt(Math.random().toString().replace('0.','')) + '.pdf');
            // });
            $('.files-files').html('<i class="fa fa-file-pdf-o txt-red"></i> ' + Mail.data.pdfFileName );
            global.unLoader();
        }
    },
    showBudget: function(){

        $('#company-logo').attr('src',Mail.company.image);
        $('#company-name').text(Mail.budget.company.company_short_name);
        $('#company-address').html(
            Mail.budget.company.address.address_type + ' ' +
            Mail.budget.company.address.address_public_place + '<br/>' +
            Mail.budget.company.address.district_name + ' - ' +
            Mail.budget.company.address.city_name + ' - ' +
            Mail.budget.company.address.uf_id
        );
        $('#company-phone').text(Mail.budget.company.company_phone);

        if( Mail.budget.budget_status != 'O' ){
            $('#external-code').text(Mail.budget.external_type == "D" ? 'DAV' : 'Pedido' + ': ' + Mail.budget.external_code);
        } else {
            $('#external-code').remove();
        }
        $('#budget-date').text('Data: ' + global.date2Br(Mail.budget.budget_date.substring(0,10)));
        $('#budget-code').text('Orçamento n°: ' + Mail.budget.budget_code);
        $('#seller-name').html('Vendedor: ' + (Mail.budget.seller.seller_short_name ? Mail.budget.seller.seller_short_name : Mail.budget.seller.seller_name));

        $('#budget-message').text(Mail.company.company_budget_message);

        if( Mail.budget.client_id != Mail.company.company_consumer_id ){
            $('#client-name').text('Cliente: ' + Mail.budget.person.person_code + ' - ' + Mail.budget.person.person_name);
            $('#client-contact').text(
                'Contato: ' +
                ( Mail.budget.person.address[0] && Mail.budget.person.address[0].contacts ? Mail.budget.person.address[0].contacts[0].address_contact_value : '--' )
            );
            $('#client-address').text(
                'Endereço: ' +
                Mail.budget.person.address[0].address_type + ' ' +
                Mail.budget.person.address[0].address_public_place + ' ' +
                Mail.budget.person.address[0].address_number + ' - ' +
                Mail.budget.person.address[0].district_name + ', ' +
                Mail.budget.person.address[0].city_name + ' - ' +
                Mail.budget.person.address[0].uf_id
            )
        } else {
            $('#client-info').remove();
        }

        var $table = $('#table-products');
        $table.find('tbody tr').remove();
        $.each( Mail.budget.items, function(key,item){
            $table.append(
                '<tr>' +
                    '<td>' + item.product_code + ' - ' + item.product_name + '</td>' +
                    '<td>' + item.unit_code + '</td>' +
                    '<td>' + item.budget_item_quantity + '</td>' +
                    '<td>R$' + global.float2Br(item.budget_item_value) + '</td>' +
                    '<td>' + global.float2Br(item.budget_item_aliquot_discount) + '%</td>' +
                    '<td>R$' + global.float2Br(item.budget_item_value_discount) + '</td>' +
                    ( Mail.company.company_st == 'Y' ? '<td>R$' + global.float2Br(item.budget_item_value_st) + '</td>' : '' ) +
                    '<td>R$' + global.float2Br(item.budget_item_value_total/item.budget_item_quantity) + '</td>' +
                    '<td>R$' + global.float2Br(item.budget_item_value_total) + '</td>' +
                '</tr>'
            );
        });

        if( Mail.company.company_st == 'N' ){
            $table.find('thead th').eq(5).hide();
        }

        $('#budget-address').text(
            'Endereço: ' +
            Mail.budget.address.address_type + ' ' +
            Mail.budget.address.address_public_place + ' ' +
            Mail.budget.address.address_number + ' - ' +
            Mail.budget.address.district_name + ', ' +
            Mail.budget.address.city_name + ' - ' +
            Mail.budget.address.uf_id
        );

        $('#budget-value').text('R$' + global.float2Br(Mail.budget.budget_value));
        $('#budget-value-discount').text('R$' + global.float2Br(Mail.budget.budget_value_discount));
        $('#budget-value-st').text('R$' + global.float2Br(Mail.budget.budget_value_st));
        $('#budget-value-total').text('R$' + global.float2Br(Mail.budget.budget_value_total));
        $('#budget-value-total-st').text('R$' + global.float2Br(Mail.budget.budget_value_total+Mail.budget.budget_value_st));

        if( Mail.company.company_st == 'N' ){
            $('#table-budget-values tbody tr').eq(2).hide();
            $('#table-budget-values tbody tr').eq(4).hide();
        }

        if( !!Mail.budget.term ){
            $('#payment-title').text('Pagamento ' + Mail.budget.term.term_description);
        }

        if( Mail.budget.payments.length ){
            var $table = $('#table-payments');
            $table.find('tbody tr').remove();
            $.each( Mail.budget.payments, function(key, payment){
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

        $('#budget-note').html(Mail.budget.budget_note||'--');
        $('#budget-note-document').html(Mail.budget.budget_note_document||'--');

        setTimeout(function(){
            Mail.events();
        },1000);
    },
    validate: function(){
        if( !Mail.data.to.length ) {
            global.validateMessage('Ao menos um destinatário deverá ser informado.', function () {
                setTimeout(function () {
                    $('#to').focus();
                }, 200);
            });
            return false;
        }
        if( !Mail.data.subject.length ) {
            global.validateMessage('O assunto do e-mail deverá ser informado.', function () {
                setTimeout(function () {
                    $('#to').focus();
                }, 200);
            });
            return false;
        }
        return true;
    }
};
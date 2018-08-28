$(document).ready(function() {

    Home.events();
    Home.getGroups();
    Home.getCompanies();
    Home.getSellers();

    Company.events();

    Billing.getCompanies();

    $('ul[data-toggle="ul-to-show"] li').first().addClass('active');
    $('div[data-toggle="panel-to-show"] div').first().addClass('in active');

    global.tooltip();
    global.unLoader();

});

Billing = {
    companies: [],
    getCompanies: function(){
        global.post({
            url: global.uri.uri_public_api + 'company.php?action=getList',
            data: {
                parent: 1,
                company_active: 'Y'
            },
            dataType: 'json'
        }, function(companies){
            Billing.companies = companies;
            Billing.showCompanies();
        }, function(){

        });
    },
    showCompanies: function(){
        $.each( Billing.companies, function(key,company){
            if( !company.parent_id ) {
                $('#billing_company_id').append($('<option>', {
                    'value': company.company_id,
                    'text': company.company_code + ' - ' + company.company_short_name
                }));
            }
        });
        $('#billing_company_id').selectpicker('refresh');
    }
};
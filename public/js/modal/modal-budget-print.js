$(document).ready(function(){

    var $modal = $('#modal-budget-print');

    var title = 'Orçamento: ' + data.budget.code;
    if( data.budget.status == 'L' ){
        title = (data.external.type == 'D' ? 'Dav' : 'Pedido') + ': ' + data.external.code;
    }
    $modal.find('.budget-code').text(title);
    $modal.find('.modal-body button').click(function(){
        $modal.modal('hide');
        Budget.print({
            action: $(this).attr('data-action'),
            budget_id: data.budget.id
        });
    }).tooltip({
        container: 'body'
    });

});
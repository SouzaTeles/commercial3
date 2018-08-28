$(document).ready(function(){
    ModalSaved.events();
});

ModalSaved = {
    timer: 0,
    interval: 0,
    modal: $('#modal-saved'),
    events: function(){
        var $body = $('#modal-budget-saved');
        $body.find('.budget-code').text('Cód: ' + ( budget.external_code || budget.budget_code ));
        $body.find('.budget-message').text(budget.budget_title + ( budget.external_id ? ' exportado!' : ' salvo!' ));
        $body.find('button').click(function(){
            ModalSaved.modal.modal('hide');
            if( !!window.opener ){
                window.opener.Budget.print({
                    budget_id: budget.budget_id,
                    action: $(this).attr('data-action')
                });
            } else {
                window.location.reload();
            }
        }).tooltip({
            container: 'body'
        });
        ModalSaved.timeout();
        ModalSaved.modal.find('.modal-dialog').on('mouseover',function(){
            ModalSaved.timeoutCancel();
        });
        ModalSaved.modal.find('.modal-dialog').on('mouseout',function(){
            ModalSaved.timeout();
        });
    },
    timeout: function(){
        ModalSaved.timer = 10;
        var $button = ModalSaved.modal.find('.modal-footer button');
        $button.html('<i class="fa fa-check"></i> Fechar (10)');
        clearInterval(Budget.interval);
        ModalSaved.interval = setInterval(function(){
            ModalSaved.timer--;
            if( !!ModalSaved.timer ){
                $button.html('<i class="fa fa-check"></i> Fechar (' + ('0'+ModalSaved.timer).slice(-2) + ')');
            } else {
                ModalSaved.modal.modal('hide');
            }
        },1000);
    },
    timeoutCancel: function(){
        ModalSaved.timer = 10;
        var $button = ModalSaved.modal.find('.modal-footer button');
        $button.html('<i class="fa fa-check"></i> Fechar (10)');
        clearInterval(ModalSaved.interval);
    }
};
$(document).ready(function(){
    ModalAddressContact.show();
});

ModalAddressContact = {
    contacts: [],
    show: function(){
        $.each(ModalAddressContact.contacts,function(key,contact){
            $('#modal-address-contact .contacts').append(
                '<p>' + contact.address_contact_label + ': ' + contact.address_contact_value + '</p>'
            );
        });
        if( !ModalAddressContact.contacts.length ){
            $('#modal-address-contact .contacts').html('<p>Nenhum contato para exibir.</p>');
        }
    }
};
$(document).ready(function(){
    ModalImage.events();
});

ModalImage = {
    image: {},
    edit: function(){
        global.post({
            url: global.uri.uri_public_api + 'image.php?action=edit',
            data: ModalImage.image,
            dataType: 'json'
        },function(){
            $('#modal-image').modal('hide');
            ModalImage.success();
        },function(data){
            global.alert({
                class: 'alert-warning',
                message: data.status.description
            });
        });
    },
    events: function(){
        $('#modal_image_preview').css({
            'background-image': 'url(' + ModalImage.image.image_small + ')'
        });
        $('#modal_image_link').val(ModalImage.image.image_link);
        $('#modal_image_name').val(ModalImage.image.image_name);
        $('#modal_image_description').val(ModalImage.image.image_description);
        $('#modal-image form').on('submit',function(e){
            e.preventDefault();
            e.stopPropagation();
            ModalImage.image.image_link = $('#modal_image_link').val();
            ModalImage.image.image_name = $('#modal_image_name').val();
            ModalImage.image.image_description = $('#modal_image_description').val();
            ModalImage.edit();
        });
    }
};
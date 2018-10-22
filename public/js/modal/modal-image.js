$(document).ready(function(){
    ModalImage.events();
    ModalImage.showPosts();
    ModalImage.showPeople();
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
        global.mask();
        global.toggle();
        global.selectpicker();
        $('#modal_image_preview').css({
            'background-image': 'url(' + ModalImage.image.image_large + ')'
        });
        $('#modal-image form').on('submit',function(e){
            e.preventDefault();
            e.stopPropagation();
            ModalImage.image.image_active = $('#modal_image_active').prop('checked') ? 'Y' : 'N';
            ModalImage.image.post_id = $('#modal_post_id').val();
            ModalImage.image.person_id = $('#modal_person_id').val();
            ModalImage.image.image_link = $('#modal_image_link').val();
            ModalImage.image.image_name = $('#modal_image_name').val();
            ModalImage.image.image_description = $('#modal_image_description').val();
            ModalImage.image.image_start_date = global.date2Us($('#modal_image_start_date').val());
            ModalImage.image.image_end_date = global.date2Us($('#modal_image_end_date').val());
            ModalImage.edit();
        });
        $('#modal_image_active').bootstrapToggle(ModalImage.image.image_active == 'Y' ? 'on' : 'off');
        $('#modal_image_link').val(ModalImage.image.image_link);
        $('#modal_post_id').selectpicker('val',ModalImage.image.post_id);
        $('#modal_image_name').val(ModalImage.image.image_name);
        $('#modal_person_id').selectpicker('val',ModalImage.image.person_id);
        $('#modal_image_description').val(ModalImage.image.image_description);
        $('#modal_image_start_date').datepicker({
            format: 'dd/mm/yyyy',
            zIndex: 1091
        }).blur(function(){
            if( $(this).val().length != 10 ){
                $(this).val('');
            }
        }).val(global.date2Br(ModalImage.image.image_start_date));
        $('#modal_image_end_date').datepicker({
            format: 'dd/mm/yyyy',
            zIndex: 1091
        }).blur(function(){
            if( $(this).val().length != 10 ){
                $(this).val('');
            }
        }).val(global.date2Br(ModalImage.image.image_end_date));
    },
    showPeople: function(){
        $.each( Slide.people, function(key,person){
            $('#modal_person_id').append($('<option>',{
                value: person.person_id,
                text: person.person_code + ' - ' + person.person_name,
                selected: person.person_id == ModalImage.image.person_id
            }));
        });
        $('#modal_person_id').selectpicker('refresh');
    },
    showPosts: function(){
        $.each( Slide.posts, function(key,post){
            $('#modal_post_id').append($('<option>',{
                value: post.ID,
                text: post.post_title.substr(0,60) + (post.post_title.length > 60 ? '...' : ''),
                selected: post.ID == ModalImage.image.post_id
            }));
        });
        $('#modal_post_id').selectpicker('refresh');
    }
};
$(document).ready(function(){
    Slide.events();
    Slide.getList();
    Slide.getPosts();
    Slide.getPeople();
    global.unLoader();
});

Slide = {
    posts: [],
    images: [],
    people: [],
    add: function(){
        var data = new FormData();
        data.append('image_section', 'slide');
        data.append('image_in', 1);
        $.each( $('#file')[0].files, function(i,file){
            data.append('file[]', file);
        });
        global.post({
            url: global.uri.uri_public_api + 'image.php?action=add',
            data: data,
            cache: false,
            dataType: 'json',
            contentType: false,
            processData: false
        },function(data){
            Slide.images = Slide.images.concat(data.images);
            Slide.showList();
        });
        $('#file').filestyle('clear');
    },
    del: function(key){
        var image = Slide.images[key];
        global.modal({
            icon: 'fa-check',
            title: 'Aviso',
            html: '<p>Deseja realmente excluir a imagem?</p>',
            buttons: [{
                icon: 'fa-times',
                title: 'Não',
                dismiss: true
            }, {
                icon: 'fa-check',
                title: 'Sim',
                dismiss: true,
                action: function () {
                    global.post({
                        url: global.uri.uri_public_api + 'image.php?action=del',
                        data: {
                            image_dir: 'slide',
                            image_id: image.image_id
                        },
                        dataType: 'json'
                    },function(){
                        Slide.images.splice(key,1);
                        Slide.showList();
                    });
                }
            }]
        });
    },
    events: function(){
        $('#file').change(function(){
            Slide.add();
        });
        $('#button-refresh').click(function(){
            Slide.getList();
        });
    },
    get: function(key){
        var image = Slide.images[key];
        global.post({
            url: global.uri.uri_public_api + 'modal.php?modal=modal-image',
            data: Slide.images[key],
            dataType: 'html'
        },function(html){
            global.modal({
                size: 'big',
                id: 'modal-image',
                icon: 'fa-picture-o',
                class: 'modal-image',
                title: 'Editar imagem',
                html: html,
                buttons: [{
                    icon: 'fa-times',
                    title: 'Cancelar',
                    class: 'pull-left'
                },{
                    icon: 'fa-pencil',
                    title: 'Atualizar',
                    unclose: true,
                    action: function(){
                        $('#modal-image form button').click();
                    }
                }],
                shown: function(){
                    ModalImage.success = function(){
                        Slide.getList();
                    }
                }
            });
        });
    },
    getList: function(){
        global.post({
            url: global.uri.uri_public_api + 'image.php?action=getList',
            data: { image_section: 'slide' },
            dataType: 'json'
        }, function(data) {
            Slide.images = data;
            Slide.showList();
        });
    },
    getPeople: function(){
        global.post({
            url: global.uri.uri_public_api + 'person.php?action=getList',
            data: {
                limit: 500,
                person_active: 'Y',
                person_category_id: global.config.person.employ_category_id
            },
            dataType: 'json'
        },function(data){
            Slide.people = data;
        });
    },
    getPosts: function(){
        global.post({
            url: global.uri.uri_public_api + 'blog.php?action=getList',
            dataType: 'json'
        },function(data){
            Slide.posts = data;
        });
    },
    showList: function(){
        var slides = $('#slides');
        $(slides).find('.col-xs-6').remove();
        $.each(Slide.images,function(key,image){
            $(slides).append(
                '<div class="col-xs-6 col-sm-4 col-lg-3">' +
                    '<div class="image box-shadow" data-key="' + key + '" style="background-image:url(' + image.image_small + ')">' +
                        '<button data-action="del" data-key="' + key + '" class="btn btn-empty-white" data-toggle="tooltip" data-title="Remover">' +
                            '<i class="fa fa-trash-o"></i>' +
                        '</button>' +
                        '<button data-action="edit" data-key="' + key + '" class="btn btn-empty-white" data-toggle="tooltip" data-title="Editar">' +
                            '<i class="fa fa-pencil"></i>' +
                        '</button>' +
                        '<button data-action="lightbox" data-key="' + key + '" class="btn btn-empty-white" href="' + image.image_large + '" data-toggle="lightbox" data-title="' + ( image.image_name ? image.image_name : '' ) + '<br/><label>Imagem ' + (key+1) + ' de ' + Slide.images.length + '</label>" data-gallery="gallery" data-footer="' + ( image.image_description ? image.image_description : '' ) + '">' +
                            '<i data-toggle="tooltip" title="Visualizar" class="fa fa-search"></i>' +
                        '</button>' +
                        ( image.image_name ? '<span>' + image.image_name + '</span>' : '' ) +
                    '</div>' +
                '</div>'
            );
        });
        $(slides).find('button[data-action="del"]').click(function(){
            Slide.del($(this).attr('data-key'));
        });
        $(slides).find('button[data-action="edit"]').click(function(){
            Slide.get($(this).attr('data-key'));
        });
        $(slides).find('button[data-action="lightbox"]').click(function(){
            $(this).ekkoLightbox();
        });
        $(slides).sortable({update:function(){
            Slide.sortable();
        }}).disableSelection();
        $('footer div').html('<i class="fa fa-picture-o"></i> ' + Slide.images.length + ' Imagens');
        global.tooltip();
    },
    sortable: function(){
        var image = {};
        var images = [];
        var sortable = [];
        $('.image').each(function(key,img){
            image = Slide.images[$(img).attr('data-key')];
            images.push(image);
            sortable.push({
                image_id: image.image_id,
                image_order: key
            });
            $(img).attr('data-key',key);
            $(img).find('button').attr('data-key',key);
        });
        Slide.images = images;
        global.post({
            url: global.uri.uri_public_api + 'image.php?action=sortable',
            data: {
                image_section: 'slide',
                images: sortable
            },
            dataType: 'json'
        },function(){});
    }
};
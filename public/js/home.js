$(document).ready(function() {

    Slide.getSlide();
    global.unLoader();

});

Slide = {
    images: [],
    getSlide: function(){
        global.post({
            url: global.uri.uri_public_api + 'image.php?action=getList',
            data: { image_section: 'slide' },
            dataType: 'json'
        }, function(data) {
            Slide.images = data;
            Slide.showList();
        });
    },
    showList: function(){
        var slide = $('#slide');
        var container = $(slide).find('.carousel-inner');
        var indicators = $(slide).find('.carousel-indicators');
        $.each(Slide.images,function(key,image){
            $(container).append(
                '<div class="item' + ( !key ? ' active' : '' ) + '">' +
                    '<div class="image" style="background-image:url(' + image.image_large + ')"></div>' +
                    '<div class="carousel-caption">' +
                        ( image.image_name ? '<h3>' + image.image_name + '</h3>' : '' ) +
                        ( image.image_description ? '<p>' + image.image_description + '</p>' : '' ) +
                        ( image.image_link ?
                            '<button data-key="' + key + '"class="btn btn-orange">' +
                                '<i class="fa fa-plus"></i> Veja mais' +
                            '</button>' : ''
                        ) +
                    '</div>' +
                '</div>'
            );
            $(indicators).append('<li data-target="#slide" data-slide-to="' + key + '" class="' + ( !key ? ' active' : '' ) + '"></li>');
        });
        $(slide).carousel();
        $(slide).find('button').click(function(){
            global.window({
                url: Slide.images[$(this).attr('data-key')].image_link
            });
        });
    }
};

BirthDays = {
    people: [],
    getList: function(){
        global.post({
            url: global.uri.uri_public_api + 'person.php?action=birthdays',
            dataType: 'json'
        }, function(data) {
            BirthDays.people = data;
            BirthDays.showList();
        });
    },
    showList: function(){

    }
};
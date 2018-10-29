$(document).ready(function() {

    Slide.getSlide();
    BirthDays.getList();
    Blog.getList();

    Suggestion.events();
    Suggestion.showCompanies();

    global.unLoader();

});

Slide = {
    images: [],
    getSlide: function(){
        global.post({
            url: global.uri.uri_public_api + 'image.php?action=getList',
            data: {
                image_active: 'Y',
                image_section: 'slide',
                image_start_date: 1,
                image_end_date: 1
            },
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
                        ( image.post_id || image.image_link ?
                            '<button data-key="' + key + '"class="btn btn-orange">' +
                                '<i class="fa fa-plus"></i> Veja mais' +
                            '</button>' : ''
                        ) +
                    '</div>' +
                    (image.person_id ? (
                        '<div class="author">' +
                            '<div class="cover" style="background-image:url(' + (image.person_image || 'images/empty-image.png') + ')"></div>' +
                            '<div class="name">' + (image.person_short_name || image.person_name) + '</div>' +
                        '</div>'
                    ) : '') +
                '</div>'
            );
            $(indicators).append('<li data-target="#slide" data-slide-to="' + key + '" class="' + ( !key ? ' active' : '' ) + '"></li>');
        });
        $(slide).carousel();
        $(slide).find('button').click(function(){
            var image = Slide.images[$(this).attr('data-key')];
            global.window({
                url: image.post_id ? (global.uri.uri_public + 'window.php?module=blog&action=show&post_id=' + image.post_id) : image.image_link
            });
        });
    }
};

Blog = {
    posts: [],
    getList: function(){
        global.post({
            url: global.uri.uri_public_api + 'blog.php?action=getList',
            unLoad: 1,
            data: { limit: 4 },
            noLoader: 1,
            dataType: 'json'
        }, function(data) {
            Blog.posts = data;
            Blog.showList();
        });
    },
    showList: function(){
        var blog = $('#blog');
        $.each(Blog.posts, function(key,post){
            var item = $(blog).find('.item').eq(key);
            $(item).removeClass('loading');
            $(item).find('.post-cover').css({
                'background-image': 'url(' + (post.image || 'images/empty-image.png') + ')'
            });
            $(item).find('.post-category').text(post.category);
            $(item).find('.post-title').text(post.post_title.substr(0,40) +(post.post_title.length > 40 ? '...' : ''));
            $(item).find('.post-preview').text(post.post_content.substr(0,60) + '...');
            $(item).find('.post-date').html(post.post_date_br + ' <i class="fa fa-clock-o"></i>');
            $(item).click(function(){
                global.window({
                    url: global.uri.uri_public + 'window.php?module=blog&action=show&post_id=' + post.ID,
                    width: 920,
                    height: 620
                });
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
        var birthdays = $('#birthdays .carousel-inner');
        $(birthdays).find('.card').remove();
        $.each(BirthDays.people,function(key,person){
            var text = '';
            var color = 'txt-gray';
            if( person.days < 0 ){
                text = 'O seu aniversário já passou, mas ainda da tempo de enviar os parabéns!';
            } else if( person.days > 0 ){
                text = 'O seu aniversário está proximo, não esqueça de dar os parabéns!';
            } else{
                text = 'Hoje é o seu aniversário, aproveite para dar os parabéns!';
            }
            $(birthdays).append(
                '<div class="card item' + person.person_active + '">' +
                    '<div class="image box-shadow"' + ( person.image ? 'style="background-image:url(' + person.image + ')"' : '') + '></div>' +
                    '<div class="name txt-blue">' + person.person_name + '</div>' +
                    '<div class="date txt-red">' + person.person_birthday + '</div>' +
                    '<div class="text">' +text + '</div>' +
                '</div>'
            );
        });
        $(birthdays).carousel({
            interval: false
        });
    }
};

Suggestion = {
    events: function(){
        $('#form-suggestion').on('submit',function(e){
            e.preventDefault();
            e.stopPropagation();
            Suggestion.submit();
        });
    },
    showCompanies: function(){
        if( !global.login.companies.length ){
            global.validateMessage('Você não possui acesso as empresas. Procure o administrador do sistema.');
        }
        $.each( global.login.companies, function(key,company){
            $('#form-suggestion select').append($('<option>',{
                'value': company.company_id,
                'text': company.company_short_name,
                'data-content': '<i class="fa fa-stop" style="color:' + company.company_color + ';"></i> ' + ('0'+company.company_id).slice(-2) + ' - ' + company.company_short_name
            }));
        });
        $('#form-suggestion select').selectpicker('refresh');
    },
    submit: function(){
        var form = $('#form-suggestion');
        global.post({
            url: global.uri.uri_public_api + 'suggestion.php?action=insert',
            data: {
                person_name: $(form).find('[name="person_name"]').val(),
                company_id: $(form).find('[name="company_id"]').val(),
                company_name: $(form).find('[name="company_id"] option:selected').text(),
                suggestion_message: $(form).find('[name="suggestion_message"]').val()
            },
            dataType: 'json'
        },function(){
            global.modal({
                size: 'small',
                icon: 'fa-info-circle',
                title: 'Informação',
                html: '<p>Sua sugestão foi enviada com sucesso. Ela será analisada em breve.</p>',
                buttons:[{
                    icon: 'fa-check',
                    title: 'Fechar'
                }]
            });
            $('#form-suggestion').trigger('reset').find('select').selectpicker('val','default');
        });
    }
};
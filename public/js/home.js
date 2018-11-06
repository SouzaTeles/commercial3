$(document).ready(function() {

    Slide.getSlide();
    BirthDays.getList();
    Blog.getList();
    Target.get();

    Suggestion.events();
    Suggestion.showCompanies();

    global.unLoader();

});

Target = {
    data: {},
    get: function(){
        global.post({
            url: global.uri.uri_public_api + 'target.php?action=dashboard',
            noLoader: 1,
            dataType: 'json'
        },function(data){
            Target.data = data;
            Target.show();
        });
    },
    show: function(){
        var resumeBarDaily = $('#resume-bar-daily');
        $(resumeBarDaily).find('.bar-result').css({
            'width': Target.data.resume.daily_target_broken_percent + '%'
        });
        $(resumeBarDaily).find('.bar-info').html(Target.data.resume.daily_target_broken_value + '/' + Target.data.days);
        var resumeBarConversion = $('#resume-bar-conversion');
        $(resumeBarConversion).find('.bar-result').css({
            'width': Target.data.resume.conversion + '%'
        });
        $(resumeBarConversion).find('.bar-info').html(global.float2Br(Target.data.resume.conversion) + '%');
        var resumeBarExploitation = $('#resume-bar-exploitation');
        $(resumeBarExploitation).find('.bar-result').css({
            'width': Target.data.resume.exploitation + '%'
        });
        $(resumeBarExploitation).find('.bar-info').html(global.float2Br(Target.data.resume.exploitation) + '%');
        var resumeBarDiscount = $('#resume-bar-discount');
        $(resumeBarDiscount).find('.bar-result').css({
            'width': Target.data.resume.discount_percent + '%'
        });
        $(resumeBarDiscount).find('.bar-info').html(global.float2Br(Target.data.resume.discount_average) + '%');
        //////
        $('#month-value').html('Mensal<br/>R$ '+global.float2Br(Target.data.month_value));
        $('#month-percent').html(global.float2Br(Target.data.month_percent)+'%');
        $('#month-result').html('R$ '+global.float2Br(Target.data.month_result)+'<br/>Faturado');
        $('#daily-value').html('Diário<br/>R$ '+global.float2Br(Target.data.daily_value));
        $('#daily-percent').html(global.float2Br(Target.data.daily_percent)+'%');
        $('#daily-result').html('R$ '+global.float2Br(Target.data.daily_result)+'<br/>Faturado');
        $('#month-donut').find('.one').css({
            'transform': 'rotate(' + (Target.data.month_percent <= 50 ? (-90 + Target.data.month_percent * 1.8) : '90') + 'deg)',
            'background-color': '#46c048'
        })
        $('#month-donut').find('.two').css({
            'transform': 'rotate(' + (Target.data.month_percent >= 100 ? '0' : ( Target.data.month_percent > 50 ? (Target.data.month_percent * 1.8) : '0')) + 'deg)',
            'background-color': (Target.data.month_percent > 50 ? '#46c048' : '#666')
        });
        $('#daily-donut').find('.one').css({
            'transform': 'rotate(' + (Target.data.daily_percent <= 50 ? (-90 + Target.data.daily_percent * 1.8) : '90') + 'deg)',
            'background-color': '#f57c00'
        })
        $('#daily-donut').find('.two').css({
            'transform': 'rotate(' + (Target.data.daily_percent >= 100 ? '0' : ( Target.data.daily_percent > 50 ? (Target.data.daily_percent * 1.8) : '0')) + 'deg)',
            'background-color': (Target.data.daily_percent > 50 ? '#f57c00' : '#666')
        });
    }
};

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
            $(item).find('.post-preview').text(post.post_content.substr(0,$(window).width() > 1024 ? 60 : 30) + '...');
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
                text = 'completou mais um ano de vida! Não deixe de parabeniza-lo(a) por esse dia tão especial!';
            } else if( person.days > 0 ){
                text = 'vai completar mais um ano de vida em breve! Não deixe de parabeniza-lo(a) por esse dia tão especial!';
            } else{
                text = 'completa hoje mais um ano de vida. Não deixe de parabeniza-lo(a) por esse dia tão especial!';
            }
            $(birthdays).append(
                '<div class="card item' + person.person_active + '">' +
                    '<div class="image box-shadow"' + ( person.image ? 'style="background-image:url(' + person.image + ')"' : '') + '></div>' +
                    '<div class="date txt-red">' + person.person_birthday + '</div>' +
                    '<div class="name txt-blue">' + person.person_name + '</div>' +
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
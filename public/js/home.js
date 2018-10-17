$(document).ready(function() {

    Slide.getSlide();
    BirthDays.getList();

    Suggestion.events();
    Suggestion.showCompanies();

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
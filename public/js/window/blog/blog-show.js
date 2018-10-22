$(document).ready(function(){

    Post.get();
    global.unLoader();

});

Post = {
    post: null,
    get: function(){
        global.post({
            url: global.uri.uri_public_api + 'blog.php?action=get',
            data: { post_id: global.url.searchParams.get('post_id') },
            dataType: 'json'
        }, function(data){
            Post.post = data;
            Post.show();
        });
    },
    show: function(){
        if( !!Post.post.image ){
            $('#post-image').append(
                '<img class="img-responsive" src="' + Post.post.image + '"/>'
            ).show();
        }
        $('#post-title').text(Post.post.post_title);
        $('#post-date').html(Post.post.post_date_br + ' <i class="fa fa-clock-o"></i>');
        $('#post-content').html(Post.post.post_content.replace(/(?:\r\n|\r|\n)/g,'<br/>'));
        $('#post-author').html('Autor(a): <i class="fa fa-user"></i> ' + Post.post.display_name);
    }
};
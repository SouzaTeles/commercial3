$(document).ready(function() {
    global.unLoader();
    Registration.events();
});

Item = {
        typeahead: {
            items: 10,
            delay: 500,
            last: '',
            timer: 0
        }
    },
Product = {},
ProductImage = {
        // events: function() {
        //     $('#file-image-product').change(function() {
        //         Product.form2data();
        //         ProductImage.up();
        //     });
        //     // $('#button-image-product-remove').click(function() {
        //     //     Product.form2data();
        //     //     ProductImage.del();
        //     // });
        // },
        show: function() {
            if (!!Product.image) {
                $('#product-image-cover .text').hide();
            } else {
                $('#product-image-cover .text').show();
            }
            $('#product-image-cover').css({
                "background-image": "url(" + (Registration.product.product_image ||  global.uri.uri_public + "images/empty-image.png") + ")"
            });
        },
        up: function(image) {
            switch (Registration.type) {
                case 'P':
                    console.log(Product);
                    console.log("LOG 2");
                    global.post({
                        url: global.uri.uri_public_api + 'product_group.php?action=up',
                        data: {
                            product_id: Product.product_id,
                            product_EAN: Product.product_EAN,
                            product_image64: image,
                            registration_type: Registration.type,
                            product_img_act: Registration.img_act
                        },
                        dataType: 'json'
                    }, function(data) {
                        if (data.code == 200) {
                            Registration.modification = false;
                            Registration.img_act = 'N';
                        }
                        console.log(data);
                        global.modal({
                            icon: 'fa-warning',
                            title: 'Atenção',
                            html: '<p>' + data.message + '</p>',
                            buttons: [{
                                icon: 'fa-check',
                                title: 'Ok',
                                action: function() {
                                }
                            }]
                        });

                    });
                    $('#file-image-product').filestyle('clear');
                    break;

                case 'G':
                    product_image64 = image;
                    global.post({
                        url: global.uri.uri_public_api + 'product_group.php?action=up',
                        data: {
                            product_list: Registration.list,
                            product_image64: product_image64,
                            registration_type: Registration.type,
                            product_img_act: 'I'
                        },
                        dataType: 'json'
                    }, function(data) {
                      global.modal({
                          icon: 'fa-warning',
                          title: 'Atenção',
                          html: '<p>' + data.message + '</p>',
                          buttons: [{
                              icon: 'fa-check',
                              title: 'Ok',
                              action: function() {
                              }
                          }]
                      });
                    });
                    break;
            }
        }
    };
Registration = {
    //Flag de alteração de Imagem (N)othink, (R)emove, (I)nsert
    img_act: 'N',
    //Utiliza-se false para não houve alterações e true quando algo for alterado
    modification: false,
    //
    modGroup: false,
    //Define a aba das alterações que estão sendo feitas, (P)roduto ou (G)rupo.
    type: 'P',
    //Contatador de produtos no checklist
    numChecked: 0,
    product: {},
    table: global.table({
        selector: '#table-products',
        //searching: 1,
        scrollY: $(window).innerHeight() - 372,
        scrollCollapse: 1,
        noControls: [0, 3],
        order: [
            [2, 'asc']
        ]
    }),
    imagem: {},
    productList: {},
    list: [],

    imagePreview: function() {
        var reader = new FileReader();
        reader.onload = function(e) {
            var image = e.target.result;
            Registration.imagem = e.target.result;
            $('#product-image-cover').css({
                "background-image": "url(" + image + ")"
            });
        };
        reader.readAsDataURL($('#file-image-product')[0].files[0]);
        $('#file-image-product').filestyle('clear');
        $('#button-image-product-remove').prop("disabled", false);
    },

    //Função de remoção da imagem no preview e preparação antes de salvar
    imageRemove: function() {
        $('#product-image-cover').css({
            "background-image": "url(" + ( global.uri.uri_public + "images/empty-image.png") + ")"
        });
        Registration.enableImage();
        Product.product_image = null;
        Registration.img_act = 'R';
    },

    events: function() {
        $('#registration_product_EAN').mask('99999999999999');

        $('#registration_product_group_code').mask('999999');
        //Campo: Codigo do Produto
        $('#registration_product_code').on('keyup', function() {
            var key = event.keyCode || event.wich;
            if (key == 13 && $('#registration_product_code').val()) {
                if (Registration.modification) {
                    global.modal({
                        icon: 'fa-warning',
                        title: 'Atenção',
                        html: '<p>Algumas alterações ainda não foram salvas, deseja continuar?</p>',
                        buttons: [{
                            icon: 'fa-check',
                            title: 'Sim',
                            action: function() {
                                Registration.beforePost();
                            }
                        }, {
                            icon: 'fa-times',
                            title: 'Não',
                            action: function() {
                            }
                        }, ],
                        hidden: function() {
                        }
                    });
                } else {
                    Registration.beforePost();
                }
            }
        });
        //Campo: Codigo do Grupo de Produto
        $('#registration_product_group_code').on('keyup', function() {
            var key = event.keyCode || event.wich;
            if (key == 13 && $('#registration_product_group_code').val()) {
                Registration.beforePost($('#registration_product_group_code').val());
            }
        });
        //Campo: Nome do Grupo de Produto
        $('#registration_product_group').on('keyup', function() {
            if ($(this).val().length >= 3 && $(this).val() != Item.typeahead.last) {
                clearTimeout(Item.typeahead.timer);
                Item.typeahead.last = $(this).val();
                Item.typeahead.timer = setTimeout(function() {
                    global.autocomplete({
                        items: 'all',
                        selector: '#registration_product_group',
                        data: {
                            type: 'G',
                            limit: Item.typeahead.items,
                            item_name: $('#registration_product_group').val()
                        },
                        url: global.uri.uri_public_api + 'product_group.php?action=typeahead',
                        callBack: function(item) {
                            console.log(item);
                            $('#registration_product_group').val(item.item_name);
                            $('#registration_product_group_code').val(item.item_code);
                            Registration.beforePost(item.item_code);
                        }
                    });
                }, Item.typeahead.delay);
            }
        });
        //Campo: Nome do Produto
        $('#registration_product_name').on('keyup', function() {
            if ($(this).val().length >= 3 && $(this).val() != Item.typeahead.last) {
                clearTimeout(Item.typeahead.timer);
                Item.typeahead.last = $(this).val();
                Item.typeahead.timer = setTimeout(function() {
                    global.autocomplete({
                        items: 'all',
                        selector: '#registration_product_name',
                        data: {
                            type: 'P',
                            limit: Item.typeahead.items,
                            //  company_id: Budget.budget.company_id,
                            item_name: $('#registration_product_name').val()
                        },
                        url: global.uri.uri_public_api + 'product_group.php?action=typeahead',
                        callBack: function(item) {
                            console.log(item);
                            Product = item;
                            console.log(item);
                            Registration.product = item;
                            Registration.showInfo();
                        }
                    });
                }, Item.typeahead.delay);
            }
        });
        //Verificação antes de upar a imagem
        $('#file-image-product').change(function() {
            Registration.modification = true;
            Registration.img_act = 'I';
            Registration.imagePreview();
        });

        $('#product-tab').click(function(event) {
            if(Registration.modGroup){
                console.log("Registration.modGroup");
                event.preventDefault();
                event.stopPropagation();
                global.modal({
                    icon: 'fa-warning',
                    title: 'Atenção',
                    html: '<p>Algumas alterações ainda não foram salvas, deseja continuar?</p>',
                    buttons: [{
                        icon: 'fa-check',
                        title: 'Sim',
                        action: function() {
                            Registration.modGroup = false;
                            $('#product-tab').trigger("click");
                            // window.close();
                        }
                    }, {
                        icon: 'fa-times',
                        title: 'Não',
                        action: function() {
                            // window.close();
                        }
                    }, ],
                    hidden: function() {
                        // window.close();
                    }
                });
            } else {
                console.log("Else");
                Registration.type = 'P';
                Registration.modGroup = false;
                $('#registration_product_code').trigger("focus");
                // (Registration.product.product_image) ? Registration.disableImage() : Registration.enableImage();
                // $('#product-tab').trigger("click");
                if($('#registration_product_code').val()){
                    Registration.beforePost();
                } else {
                    Registration.disableImageGroup();
                }
                // window.close();
            }
            // console.log("clicou...")
            // Registration.type = 'P';
            // $('#product-image-cover').css({
            //     "background-image": "url(" + (Registration.product.product_image ||  global.uri.uri_public + "images/empty-image.png") + ")"
            // });
            // (Registration.product.product_image) ? Registration.disableImage() : Registration.enableImage();
            
        });

        $('#product-group-tab').click(function(event) {
            event.preventDefault();
            if (Registration.modification) {
                event.stopPropagation();
                global.modal({
                    icon: 'fa-warning',
                    title: 'Atenção',
                    html: '<p>Algumas alterações ainda não foram salvas, deseja continuar?</p>',
                    buttons: [{
                        icon: 'fa-check',
                        title: 'Sim',
                        action: function() {
                            // Registration.type = 'G';
                            Registration.modification = false;
                            $('#product-image-cover').css({
                                "background-image": "url(" + ( global.uri.uri_public + "images/empty-image.png") + ")"
                            });
                            $('#product-group-tab').trigger("click");
                            Registration.disableImageGroup();
                            // window.close();
                        }
                    }, {
                        icon: 'fa-times',
                        title: 'Não',
                        action: function() {
                            // Registration.modification = false;
                            // $('#product-tab').click();
                            // window.close();
                        }
                    }, ],
                    hidden: function() {
                        // window.close();
                    }
                });
            } else {
                Registration.type = 'G';
                // Registration.modification = false;
                $('#registration_product_group_code').trigger("focus");
                $('#product-image-cover').css({
                    "background-image": "url(" + ( global.uri.uri_public + "images/empty-image.png") + ")"
                });
                Registration.disableImageGroup();
                // window.close();
            }
        });
        $('#button-image-product-remove').prop("disabled", true);
        $('#button-image-product-remove').click(function() {
            Registration.imageRemove();
        });
        $('#registration_product_EAN').blur(function() {
            if ($('#registration_product_EAN').val() != Product.product_EAN)
                Registration.modification = true;
        });
        $('#save-product').click(function() {
            switch (Registration.type) {
                case 'P':
                    if (!Registration.eanValidate($('#registration_product_EAN').val()) && $('#registration_product_EAN').val()) {
                        global.modal({
                            icon: 'fa-warning',
                            title: 'Atenção',
                            html: '<p>O Codigo de barras informado não é valido, verifique.</p>',
                            buttons: [{
                                icon: 'fa-check',
                                title: 'Ok',
                                action: function() {
                                    // window.close();
                                }
                            }],
                            hidden: function() {
                                // window.close();
                            }
                        });
                    } else {
                        global.modal({
                            icon: 'fa-warning',
                            title: 'Confirmação',
                            html: '<p>Produto: ' + $('#registration_product_code').val() + ', Nome: ' + $('#registration_product_name').val() + ' e codigo de barras: ' + $('#registration_product_EAN').val() + '<br> Confirma gravar essas informações?</p>',
                            buttons: [{
                                icon: 'fa-times',
                                title: 'Não',
                                action: function() {
                                    // window.close();
                                }
                            }, {
                                icon: 'fa-check',
                                title: 'Sim',
                                action: function() {
                                    Product.product_EAN = $('#registration_product_EAN').val();
                                    ProductImage.up(Registration.imagem);
                                    // window.close();
                                }
                            }],
                            hidden: function() {
                                // window.close();
                            }
                        });
                    }
                    break;
                case 'G':
                    $('.product-check').each(function(key, item){
                      Registration.list.push($(this).closest(".product-check").attr("data-id"));
                    });
                    Registration.modGroup = false;
                    ProductImage.up(Registration.imagem);
                    break;
                default:
                    alert("Algo de errado não está certo.")
            }
        });  
        $("#image-input-area").on("paste drop", function (ev) {
            window.setTimeout(function (ev) {
            Registration.pasteImage();
            }, 300);
        });
    },
    beforePost: function(item) {
        switch (Registration.type) {
            case 'P':
                global.post({
                    url: global.uri.uri_public_api + 'product_group.php?action=get',
                    data: {
                        type: 'P',
                        product_code: $("#registration_product_code").val(),
                    },
                    dataType: "json"
                }, function(data) {
                    Product = data;
                    // Registration.product = null;
                    Registration.product = data;
                    Registration.modification = false;
                    console.log(Registration.product);
                    Registration.showInfo();
                    if (data.length) {
                      global.post({
                          url: global.uri.uri_public + 'api/modal.php?modal=modal-registration-product',
                          data: {product: Registration.product},
                          dataType: 'html'
                      },function(html){
                          global.modal({
                              id: 'modal-registration-product',
                              class: 'modal-registration-product',
                              icon: 'fa-cubes',
                              title: 'Listagem de produtos',
                              size: "big",
                              html: html,
                              buttons: [{
                                  icon: 'fa-times',
                                  title: 'Cancelar',
                                  class: 'pull-left'
                              },{
                                  icon: 'fa-pencil',
                                  title: 'Atualizar',
                                  unclose: true,
                                  id: 'button-pass-change'
                              }],
                              shown: function(){
                                ModalRegistrationProduct.success = function(product){
                                    $('#modal-registration-product').modal("hide");
                                    console.log(product);
                                    Registration.product = product;
                                    Registration.showInfo();                           
                                }
                              }
                          });
                      });
/*
                        global.modal({
                            icon: 'fa-warning',
                            title: 'Atenção',
                            html: function() {
                                var html = '';
                                $(data).each(function(key, item) {
                                    html += '<p>' + item.product_code + " | " + item.product_name + '</p>';
                                });
                                //console.log(data);
                                return html;
                            },
                            buttons: [{
                                icon: 'fa-check',
                                title: 'Ok',
                                action: function() {
                                    window.close();.
                                }
                            }]
                        });
                */    }
                });
                break;
            case 'G':
                global.post({
                    url: global.uri.uri_public_api + 'product_group.php?action=get',
                    data: {
                        type: 'G',
                        product_group_code: item,
                    },
                    dataType: "json"
                }, function(ret) {
                    if (ret) {
                        Registration.modGroup = false;
                        $('#registration_product_group').val(ret[0].group_info.product_group_name);
                        $('#registration_product_group_code').val(ret[0].group_info.product_group_code);
                        Registration.products = ret[0].product_info;
                        Registration.showList();
                    }
                });
                break;
            default:
        }
    },
    //Encaminha cada informação do retorno para o elemento correto na pagina
    showInfo: function() {
        $('#registration_product_name').val(Registration.product.product_name);
        $('#registration_product_code').val(Registration.product.product_code);
        $('#registration_product_EAN').prop("disabled", false);
        $("#image-input-area").prop("contenteditable", true);
        $('#registration_product_EAN').val(Registration.product.product_EAN);
        if(!Registration.product.product_image)
            Registration.enableImage();
        else{
            Registration.disableImage();
        }
            // $("#image-input-area").css("border", "none");
            // $('#product-image-cover').css({
            // "background-image": "url(" + (Registration.product.product_image ||  global.uri.uri_public + "images/empty-image.png") + ")"
        


        // if (Registration.product.product_image) {
        //     Registration.enableImage();
        // } else {
        //     Registration.disableImage();
        // }

        if (Registration.product.product_classification){
            $('#registration_product_classification').val(Registration.product.product_classification)
        }
        else {
            $('#registration_product_classification').val(" ");
        }
    },
    //Validação de codigo de barras
    eanValidate: function(EAN) {
        var soma = 0;
        switch (EAN.length){
            case 8:
            case 12:
            case 14:
                EAN = EAN.split("");
                for (i = 0; i < EAN.length - 1; i++) {
                    if (i % 2 == 0)
                        EAN[i] = EAN[i] * 3;
                    else
                        EAN[i] = parseInt(EAN[i]);
                    soma += EAN[i];
                }
                calc = soma / 10;
                calc = Math.floor(calc);
                calc += 1;
                calc *= 10;
                calc = calc - soma;
                if ((calc == 10 ? 0 : calc) == EAN[EAN.length - 1]) {
                    return true;
                } else {
                    return false;
                }
                break;
            case 13:
                EAN = EAN.split("");
                for (i = 0; i < EAN.length - 1; i++) {
                    (i % 2 == 1) ? EAN[i] = EAN[i] * 3 : EAN[i] = parseInt(EAN[i]);
                    soma += EAN[i];
                }
                calc = ((Math.floor(soma / 10) + 1) * 10) - soma;
                if ((calc == 10 ? 0 : calc) == EAN[12]) {
                    return true;
                } else {
                    return false;
                }
                break;
            default:
                return false;
        }
    },
    //Monta a tabela e exibe as informações
    showList: function() {
        //Limpa a tabela
        Registration.table.clear();
        //  Registration.productList = [];
        //  Registration.productList[1] = 1;
        //Entra no loop de exibição para cada item do array de produtos
        $.each(Registration.products, function(key, product){
            Registration.productList = {
              product_id: product.product_id,
              update_status: 'N'
            };
            //Adiciona uma nova linha na lista de exibição
            Registration.table.row.add([
                "<input data-id='" + product.product_id + "'id='" + product.product_id  + "' type='checkbox' class='product-check' data-key='1'>",
                product.product_code,
                product.product_name,
                '<div class="product-cover"' + ( product.product_image ? 'style="background-image:url(' + product.product_image+ ')"' : '' ) + '></div>',
            ])
        })
        Registration.table.draw();
        $('.product-check').click(function(){
            if($('#' + $(this).closest(".product-check").attr("data-id")).prop("checked")){
                Registration.numChecked++;
                console.log(Registration.numChecked);
            } else {
                Registration.numChecked--;
                console.log(Registration.numChecked);
            }
            console.log("Ta chegando no pre-if...");
            if(Registration.numChecked > 0){
                    $('#file-image-product').filestyle("disabled", false);
                    $('#image-input-area').css("border","2px #cccccc dashed");

            }
            else{
                $('#file-image-product').filestyle("disabled", true)
                $("#product-check-master").prop("checked", false);
                $('#image-input-area').css("border","none");
            }
        });
        $('#product-check-master').click(function() {
          console.log("click...");
          if( $('#product-check-master').is(":checked")){
            //Marca todos os checks
            $('.product-check').prop("checked", true);
            //Variavel
            Registration.numChecked = Registration.products.length;
            console.log(Registration.productList);
            console.log(Registration.numChecked);
            Registration.enableImage();
          }
          else {
            //Desmarca todos os checks
            $('.product-check').prop("checked", false)
            //Variavel
            Registration.numChecked = 0;
            Registration.disableImageGroup();
          }
        });
    },
    //Função para converter o link de uma imagem para base64
    toDataUrl: function (url, callback) {
        console.log("toDataUrl()");
        var xhr = new XMLHttpRequest();
        xhr.onload = function() {
            console.log("onload");
            var reader = new FileReader();
            reader.onloadend = function() {
                callback(reader.result);
            }
            reader.readAsDataURL(xhr.response);
        };
        console.log("depois do onload");
        xhr.open('GET', url);
        xhr.responseType = 'blob';
        xhr.send();
    },
    enableImage: function(){
        $('#file-image-product').filestyle("disabled", false);//Pode procurar
        $('#button-image-product-remove').prop("disabled", true);// Não Pode Remover
        $('#image-input-area').prop("contenteditable", true); // Pode Inserir Imagem
        $('#image-input-area').css({
            "border": "2px #cccccc dashed"
        }); //Borda habilitada
        $('#product-image-cover').css({
            "background-image": "url(" + (global.uri.uri_public + "images/empty-image.png") + ")"
        });
    },
    disableImage: function(){
        $('#file-image-product').filestyle("disabled", true);//Não pode procurar
        $('#button-image-product-remove').prop("disabled", false); //Pode remover
        $('#image-input-area').prop("contenteditable", false);
        $('#image-input-area').css("border","none");
        $('#product-image-cover').css({
            "background-image": "url(" + (Registration.product.product_image ||  global.uri.uri_public + "images/empty-image.png") + ")"
        });
    },
    disableImageGroup: function(){
        $('#file-image-product').filestyle("disabled", true);//Não pode procurar
        $('#button-image-product-remove').prop("disabled", true); // Não Pode remover
        $('#image-input-area').prop("contenteditable", false); // Não pode inserir
        $('#image-input-area').css("border","none"); // sem borda
    
    },
    pasteImage: function(){
        var regex = /base64$/;
        ImagePush.input = $("#image-input-area").children()[0].src;
        ImagePush.s = ImagePush.input.split(',');
        ImagePush.mime = ImagePush.s[0];
        ImagePush.data = ImagePush.s[1];
        $('#product-image-cover').css({
            "background-image": "url(" + ImagePush.s + ")"
        });
        $('#image-input-area').css("border","none");
        $("#image-input-area").empty();
        $("#image-input-area").prop("contenteditable", false);
        $('#button-image-product-remove').prop("disabled", false);

        $('#file-image-product').filestyle("disabled", false);

        if(!regex.exec(ImagePush.mime)){
            //Não é base 64
            console.log("Link Externo")
            Registration.toDataUrl(ImagePush.mime, function(img64) {
                console.log(img64);
                Registration.imagem = img64;
                Registration.img_act = 'I';
            });
        } else {
            // É Base 64
            Registration.imagem = (ImagePush.mime +',' + ImagePush.data);
            // console.log(Registration.imagem);
            Registration.img_act = 'I';
    
        }
    }

    
},

ImagePush = {}
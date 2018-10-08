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
        del: function() {
            global.modal({
                icon: 'fa-question-circle-o',
                title: 'Confirmação',
                html: '<p>Deseja realmente remover a imagem do produto?</p>',
                buttons: [{
                    icon: 'fa-times',
                    title: 'Não',
                    class: 'pull-left'
                }, {
                    icon: 'fa-check',
                    title: 'Sim',
                    action: function() {
                        global.post({
                            url: global.uri.uri_public_api + 'image.php?action=del',
                            data: {
                                image_id: Product.product_id,
                                image_dir: 'product'
                            }
                        }, function() {
                            $('#button-image-product-remove').prop('disabled', true);
                            Product.image = null;
                            ProductImage.show();
                        });
                    }
                }]
            });
        },
        events: function() {
            $('#file-image-product').change(function() {
                Product.form2data();
                ProductImage.up();
            });
            $('#button-image-product-remove').click(function() {
                Product.form2data();
                ProductImage.del();
            });
        },
        show: function() {
            if (!!Product.image) {
                $('#product-image-cover .text').hide();
            } else {
                $('#product-image-cover .text').show();
            }
            $('#product-image-cover').css({
                'background-image': !!Product.image ? 'url(' + Product.image + ')' : ''
            });
        },
        up: function(image) {
            switch (Registration.type) {
                case 'P':
                    product_image64 = image;
                    console.log("LOG 2");
                    global.post({
                        url: global.uri.uri_public_api + 'product_group.php?action=up',
                        data: {
                            product_id: Product.product_id,
                            product_EAN: Product.product_EAN,
                            product_image64: product_image64,
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
                                    window.close();
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
                                  window.close();
                              }
                          }]
                      });
                    });
                    break;
            }

            //
        }
    };
Registration = {
    //Flag de alteração de Imagem (N)othink, (R)emove, (I)nsert
    img_act: 'N',
    //Utiliza-se false para não houve alterações e true quando algo for alterado
    modification: false,
    //Define a aba das alterações que estão sendo feitas.
    type: 'P',

    product: {},
    table: global.table({
        selector: '#table-products',
        //searching: 1,
        scrollY: $(window).innerHeight() - 372,
        scrollCollapse: 1,
        noControls: [0, 2],
        order: [
            [1, 'desc']
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
            "background-image": "none"
        });
        $('#file-image-product').filestyle('clear');
        $('#button-image-product-remove').prop("disabled", true);
        Product.product_image = null;
        Registration.img_act = 'R';
    },

    events: function() {
        $('#registration_product_code').on('keyup', function() {
            var key = event.keyCode || event.wich;
            if (key == 13) {
                if (Registration.modification) {
                    global.modal({
                        icon: 'fa-warning',
                        title: 'Atenção',
                        html: '<p>Algumas alterações ainda não foram salvas, deseja continuar?</p>',
                        buttons: [{
                            icon: 'fa-check',
                            title: 'Sim',
                            action: function() {
                                window.close();
                                Registration.beforePost();
                            }
                        }, {
                            icon: 'fa-times',
                            title: 'Não',
                            action: function() {
                                window.close();
                            }
                        }, ],
                        hidden: function() {
                            window.close();
                        }
                    });
                } else {
                    Registration.beforePost();
                }
            }
        });

        $('#registration_product_group_code').on('keyup', function() {
            var key = event.keyCode || event.wich;
            if (key == 13) {
                global.post({
                    url: global.uri.uri_public_api + 'product_group.php?action=get',
                    data: {
                        type: 'G',
                        product_group_code: $("#registration_product_group_code").val(),
                    },
                    dataType: "json"
                }, function(ret) {
                    if (ret) {
                        $('#registration_product_group').val(ret[0].group_info.product_group_name);
                        $('#registration_product_group_code').val(ret[0].group_info.product_group_code);
                        Registration.products = ret[0].product_info;
                        Registration.showList();
                    }
                });
            }
        });

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
                            $('#registration_product_group').val(item.item_name);
                            $('#registration_product_group_code').val(item.item_code);
                        }
                    });
                }, Item.typeahead.delay);
            }
        });

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

        $('#product-tab').click(function() {
            console.log("clicou...")
            Registration.type = 'P';
        });

        $('#product-group-tab').click(function(event) {
            event.preventDefault();
            if (Registration.modification) {
                global.modal({
                    icon: 'fa-warning',
                    title: 'Atenção',
                    html: '<p>Algumas alterações ainda não foram salvas, deseja continuar?</p>',
                    buttons: [{
                        icon: 'fa-check',
                        title: 'Sim',
                        action: function() {
                            Registration.type = 'G';
                            $('#product-image-cover').css({
                                "background-image": "none"
                            });
                            $('#button-image-product-remove').prop("disabled", true);
                            $('#file-image-product').filestyle("disabled", true);
                            window.close();
                        }
                    }, {
                        icon: 'fa-times',
                        title: 'Não',
                        action: function() {
                            Registration.modification = false;
                            $('#product-tab').click();
                            window.close();
                        }
                    }, ],
                    hidden: function() {
                        window.close();
                    }
                });
            } else {

                Registration.type = 'G';
                $('#product-image-cover').css({
                    "background-image": "none"
                });

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
                    if (!Registration.EanValidate($('#registration_product_EAN').val()) && $('#registration_product_EAN').val()) {
                        global.modal({
                            icon: 'fa-warning',
                            title: 'Atenção',
                            html: '<p>O Codigo de barras informado não é valido, verifique.</p>',
                            buttons: [{
                                icon: 'fa-check',
                                title: 'Ok',
                                action: function() {
                                    window.close();
                                }
                            }],
                            hidden: function() {
                                window.close();
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
                                    window.close();
                                }
                            }, {
                                icon: 'fa-check',
                                title: 'Sim',
                                action: function() {
                                    Product.product_EAN = $('#registration_product_EAN').val();
                                    ProductImage.up(Registration.imagem);
                                    window.close();
                                }
                            }],
                            hidden: function() {
                                window.close();
                            }
                        });
                    }
                    break;
                case 'G':
                    $('.product-check').each(function(key, item){
                      Registration.list.push($(this).closest(".product-check").attr("data-id"));
                    });
                    ProductImage.up(Registration.imagem);
                    break;
                default:
                    alert("Algo de errado não está certo.")
            }
        });

    },
    //};

    beforePost: function() {
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
                    // console.log(data);
                    Product = data;
                    console.log(Product);
                    Registration.showInfo();
                    console.log(data.length);
                    if (data.length) {
                        global.modal({
                            icon: 'fa-warning',
                            title: 'Atenção',
                            html: function() {
                                var html = '';
                                $(data).each(function(key, item) {
                                    console.log(item);
                                    html += '<p>' + item.product_code + " | " + item.product_name + '</p>';
                                });
                                console.log(data);
                                return html;
                            },
                            buttons: [{
                                icon: 'fa-check',
                                title: 'Ok',
                                action: function() {
                                    window.close();
                                }
                            }]
                        });
                    }
                });
                break;
            case 'G':

                break;
            default:

        }
    },

    //Encaminha cada informação do retorno para o elemento correto na pagina
    showInfo: function() {

        $('#registration_product_name').val(Product.product_name);
        $('#registration_product_code').val(Product.product_code);
        $('#registration_product_EAN').prop("disabled", false);
        $('#registration_product_EAN').val(Product.product_EAN);
        $('#product-image-cover').css({
            "background-image": "url(" + (Product.product_image || "") + ")"
        });
        $('#file-image-product').filestyle("disabled", false);

        if (Product.product_image) {
            $('#button-image-product-remove').prop("disabled", false);
        } else {
            $('#button-image-product-remove').prop("disabled", true);
        }
    },

    //Validação de codigo de barras
    EanValidate: function(EAN) {
        var soma = 0;
        switch (EAN.length) {
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
                    if (i % 2 == 1)
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
                console.log(calc);
                console.log(EAN[12]);
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
        $.each(Registration.products, function(key, product) {
            Registration.productList= {
              product_id: product.product_id,
              update_status: 'N'
            };

            //Adiciona uma nova linha na lista de exibição
            Registration.table.row.add([
                '<input data-id="' + product.product_id + '" type="checkbox" class="product-check" data-key="1">',
                product.product_code,
                product.product_name,
                "♣"
            ])
        })
        Registration.table.draw();

        $('#product-check-master').click(function() {
          console.log("click...");
          if(  $('#product-check-master').is(":checked") ){
            $('.product-check').prop("checked", true);
          }
          else {
            $('.product-check').prop("checked", false)
          }

        });
        $('.product-check').click(function(event) {
            var id = $(this).closest(".product-check").attr("data-id");
            console.log(id);
            if ($('#product-check-master').prop("checked", true)) {
                $('#file-image-product').filestyle("disabled", false);
                console.log("Filestyle false")
            }
            if ($('#' + id).prop("checked", true))
                console.log("01")//Registration.productList[id] = 'Y'
            else {
                console.log("02")//Registration.productList[id] = 'N'
            }
        });
    }
}

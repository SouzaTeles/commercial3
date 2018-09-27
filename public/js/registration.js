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
    },
},
Product = {
    // data2form: function() {
    //   // $('#product_id').selectpicker('val', User.user.user_id).prop('disabled', !!User.user.user_id).selectpicker('refresh');
    //   // $('#file-image-product').filestyle('disabled', !Product.product_id);
    //   // $('#button-image-user-remove').prop('disabled', !User.user.image);
    //   Product.showUserCompany();
    //   Product.showUserPrice();
    // }
  },
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
      alert("Entrando no UP")
      var data = new FormData();
      console.log(data);
      data.append('product_id', Product.product_id);
      data.append('product_image64', Registration.imagem);
      // data.append('file[]', $('#file-image-product')[0].files[0]);
      // data.append('file[]', image);
      //
      // data = {
      //   //product_id: Product.product_id,
      //   product_image64: Registration.imagem
      // };
      console.log(data);
      global.post({
        url: global.uri.uri_public_api + 'product_group.php?action=up',
        data: data,
        cache: false,
        dataType: 'json',
        contentType: false,
        processData: false
      }, function(data) {
        console.log("response");
        console.log(data);
        //Product.image = data.images[0].image;
        //$('#button-image-product-remove').prop('disabled', false);
        //ProductImage.show();
      });
      $('#file-image-product').filestyle('clear');
    }
  };
Registration = {
    product: {},
    table: global.table({
        selector: '#table-products',
        //searching: 1,
        scrollY: $(window).innerHeight()-372,
        scrollCollapse: 1,
        noControls: [0,2],
        order: [[1,'desc']]
    }),
    imagem:{},
    imagePreview: function(){
        var reader = new FileReader();
        reader.onload = function(e){
            var image = e.target.result;
            Registration.imagem = e.target.result;
            $('#product-image-cover').css({
              "background-image": "url(" + image + ")"
            });
        };
        reader.readAsDataURL($('#file-image-product')[0].files[0]);
        $('#file-image-product').filestyle('clear');

    },

    events: function() {
      $('#registration_product_code').on('keyup', function() {
        var key = event.keyCode || event.wich;
        if (key == 13) {
          global.post({
            url: global.uri.uri_public_api + 'product_group.php?action=get',
            data: {
              type: 'P',
              product_code: $("#registration_product_code").val(),
            },
            dataType: "json"
          }, function(data) {
            console.log("Entrou na função");
            console.log(data);
            Product = data;

            $('#registration_product_name').val(data.product_name);
            $('#registration_product_code').val(data.product_code);
            $('#registration_product_EAN').val(data.product_EAN);
            $('#product-image-cover').css({
              "background-image": "url(" + (data.product_image || "") + ")"
            });
            $('#file-image-product').filestyle("disabled", false);
            //  $('#registration_product_code').val(data.product_code);
            /*  $('#picplace').css({
                  "background-image": "url(" + (data.product_image || "") +  ")"
              });*/
          });

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
          }, function(data) {
            console.log("Entrou na função");
            console.log(data);
            $('#registration_product_name').val(data.product_group_name);
            $('#registration_product_group_code').val(data.product_group_code);
            Registration.products = data;
            Registration.showList();


            //  $('#registration_product_EAN').val(data.product_EAN);
            //  $('#registration_product_code').val(data.product_code);
            /*  $('#picplace').css({
                  "background-image": "url(" + (data.product_image || "") +  ")"
              });*/
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
                //  company_id: Budget.budget.company_id,
                item_name: $('#registration_product_group').val()
              },
              url: global.uri.uri_public_api + 'product_group.php?action=typeahead',
              callBack: function(item) {
                $('#registration_product_group').val(item.item_name);
                $('#registration_product_group_code').val(item.item_code);
                //$('#registration_product_EAN').val(item.item_EAN);
              }
            });
          }, Item.typeahead.delay);
        }
      });

      $('#registration_product_name').on('keyup', function() {
        console.log("Ta entrando na função do typeahead");
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
                $('#registration_product_name').val(item.item_name);
                $('#registration_product_code').val(item.item_code);
                $('#registration_product_EAN').val(item.item_EAN);
              }
            });
          }, Item.typeahead.delay);
        }
      });

      //Verificação antes de upar a imagem
      $('#file-image-product').change(function() {
        Registration.imagePreview();
        // //if
        // if(Product.product_image){
        //   console.log("Já existe uma imagem.")
        // }
        // $('#product-image-cover').css({
        //   'background-image': !!Product.image ? 'url(' + Product.image + ')' : ''
        // });
        //  Registration.form2data();
        // // ProductImage.up();
      });

      $('#product-tab').click(function(){
        console.log("clicou...")
        global.modal({
          icon: 'fa-warning',
          title: 'Atenção',
          html: '<p>Todas as alterações não salvas serão perdidas, dejsa continuar?</p>',
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
      }),
    //  }),
      $('#save-product').click(function() {
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
                  //Registration.form2data();
                  window.close();
                }
              },
              {
                icon: 'fa-check',
                title: 'Sim',
                action: function() {
                  ProductImage.up(Registration.imagem);
                  window.close();
                }
              }
            ],
            hidden: function() {
              window.close();
            }
          });
        }
      });
    },

    EanValidate: function(EAN) {
      var soma = 0;
      console.log(EAN.length);
      switch (EAN.length) {
        case 8:
          EAN = EAN.split("");
            for (i = 0; i < EAN.length - 1; i++) {
              if (i % 2 == 0)
                EAN[i] = EAN[i] * 3;
              else
                EAN[i] = parseInt(EAN[i]);
              soma += EAN[i];
            }
            console.log(soma);
            calc = soma / 10;
            console.log(calc);
            calc = Math.floor(calc);
            console.log(calc);
            calc += 1;
            console.log(calc)
            calc *= 10;
            console.log(calc)
            calc = calc - soma;
            console.log(calc)
            if (calc == EAN[7]) {
              return true;
            } else {
              //"O Codigo de barras informado não é um EAN-13 valido";
              return false;
            }
        break;
        case 12:
          EAN = EAN.split("");
            for (i = 0; i < EAN.length - 1; i++) {
              if (i % 2 == 0)
                EAN[i] = EAN[i] * 3;
              else
                EAN[i] = parseInt(EAN[i]);
              soma += EAN[i];
            }
            console.log(soma);
            calc = soma / 10;
            console.log(calc);
            calc = Math.floor(calc);
            console.log(calc);
            calc += 1;
            console.log(calc)
            calc *= 10;
            console.log(calc)
            calc = calc - soma;
            console.log(calc)
            if (calc == EAN[11]) {
              return true;
            } else {
              //"O Codigo de barras informado não é um EAN-13 valido";
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
          if (calc == EAN[12]) {
            return true;
          } else {
            //"O Codigo de barras informado não é um EAN-13 valido";
            return false;
          }
        break;
        case 14:
        console.log("Ean 14")
        EAN = EAN.split("");
          for (i = 0; i < EAN.length - 1; i++) {
            if (i % 2 == 0)
              EAN[i] = EAN[i] * 3;
            else
              EAN[i] = parseInt(EAN[i]);
            soma += EAN[i];
          }
          console.log(soma);
          calc = soma / 10;
          console.log(calc);
          calc = Math.floor(calc);
          console.log(calc);
          calc += 1;
          console.log(calc)
          calc *= 10;
          console.log(calc)
          calc = calc - soma;
          console.log(calc)
          if (calc == EAN[13]) {
            return true;
          } else {
            //"O Codigo de barras informado não é um EAN-13 valido";
            return false;
          }
        break;
        default:
          return false;

      }
    },

  //   form2data: function() {
  //   Registration.product.client_id = $('#user_client_id') ? $('#user_client_id').val() : null;
  //   Registration.product.user_profile_id = $('#user_profile_id').selectpicker('val');
  //   Registration.product.user_name = $('#user_name').val();
  //   Registration.product.user_mail = $('#user_mail').val();
  //   Registration.product.user_user = $('#user_user').val();
  // },

  //Monta a tabela e exibe as informações
  showList: function(){

      //Limpa a tabela
      Registration.table.clear();

      //Entra no loop de exibição para cada item do array de produtos
      $.each(Registration.products, function (key, product) {

          //Adiciona uma nova linha na lista de exibição
          Registration.table.row.add([
              '<input data-id="'+product.product_id+'" type="checkbox" data-key="1">',
              product.product_code,
              product.product_name,
              "♣"
          ])
      })
      Registration.table.draw();
  },
}

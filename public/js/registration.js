$(document).ready(function() {
  global.unLoader();
  Registration.events();
});
UserImage = {
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
              image_id: User.user.user_id,
              image_dir: 'user'
            }
          }, function() {
            $('#button-image-user-remove').prop('disabled', true);
            User.user.image = null;
            UserImage.show();
          });
        }
      }]
    });
  },
  events: function() {
    $('#file-image-user').change(function() {
      User.form2data();
      UserImage.up();
    });
    $('#button-image-user-remove').click(function() {
      User.form2data();
      UserImage.del();
    });
  },
  show: function() {
    if (!!User.user.image) {
      $('#user-image-cover .text').hide();
    } else {
      $('#user-image-cover .text').show();
    }
    $('#user-image-cover').css({
      'background-image': !!User.user.image ? 'url(' + User.user.image + ')' : ''
    });
  },
  up: function() {
    var data = new FormData();
    data.append('image_id', User.user.user_id);
    data.append('image_dir', 'user');
    data.append('file[]', $('#file-image-user')[0].files[0]);
    global.post({
      url: global.uri.uri_public_api + 'image.php?action=up',
      data: data,
      cache: false,
      dataType: 'json',
      contentType: false,
      processData: false
    }, function(data) {
      User.user.image = data.images[0].image;
      $('#button-image-user-remove').prop('disabled', false);
      UserImage.show();
    });
    $('#file-image-user').filestyle('clear');
  }
};
Item = {
    typeahead: {
      items: 10,
      delay: 500,
      last: '',
      timer: 0
    },
  },
  Product = {
    data2form: function() {
      $('#product_id').selectpicker('val', User.user.user_id).prop('disabled', !!User.user.user_id).selectpicker('refresh');
      // $('#user_person_code').val(User.user.person ? User.user.person.person_code : '').attr('data-value',(User.user.person ? User.user.person.person_code : ''));
      // $('#user_person_name').val(User.user.person ? User.user.person.person_name : '').attr('data-value',(User.user.person ? User.user.person.person_name : ''));
      // $('#user_active').bootstrapToggle(User.user.user_active == 'Y' ? 'on' : 'off');
      // $('#user_name').val(User.user.user_name);
      // $('#person_id').selectpicker('val',User.user.person_id);
      // $('#user_profile_id').selectpicker('val',User.user.user_profile_id);
      // $('#user_mail').val(User.user.user_mail);
      // $('#user_user').val(User.user.user_user).prop('readonly',true);
      // $('#user_pass').val(User.user.user_id ? '******' : '').prop('readonly',true);
      // $('#user_pass_confirm').val(User.user.user_id ? '******' : '').prop('readonly',true);
      $('#file-image-product').filestyle('disabled', !Product.product_id);
      $('#button-image-user-remove').prop('disabled', !User.user.image);
      Product.showUserCompany();
      Product.showUserPrice();
    }
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
    up: function() {
      var data = new FormData();
      data.append('image_id', Product.product_id);
      data.append('image_dir', 'product');
      data.append('file[]', $('#file-image-product')[0].files[0]);
      global.post({
        url: global.uri.uri_public_api + 'image.php?action=up',
        data: data,
        cache: false,
        dataType: 'json',
        contentType: false,
        processData: false
      }, function(data) {
        Product.image = data.images[0].image;
        $('#button-image-product-remove').prop('disabled', false);
        ProductImage.show();
      });
      $('#file-image-product').filestyle('clear');
    }
  };
Registration = {
    product: {},
    table: global.table({
        selector: '#table-products',
        //searching: 1,
        // scrollY: $(window).innerHeight()-372,
        // scrollCollapse: 1,
        noControls: [0,2],
        order: [[1,'desc']]
    }),

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
            Product.product_id = data.product_id;

            $('#registration_product_name').val(data.product_name);
            $('#registration_product_code').val(data.product_code);
            $('#registration_product_EAN').val(data.product_EAN);
            $('#product-image-cover').css({
              "background-image": "url(" + (data.product_image || "") + ")"
            });
            $('#file-image-product').filestyle("disabled", false);
            console.log("ué...");
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

      $('#file-image-product').change(function() {
        Registration.form2data();
        ProductImage.up();
      });

      $('#save-product').click(function() {
        if (!Registration.EanValidate($('#registration_product_EAN').val())) {
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
              },
              {
                icon: 'fa-check',
                title: 'Sim',
                action: function() {
                  window.close();
                }
              }
            ],
            hidden: function() {
              window.close();
            }
          });
        }
      })
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

    form2data: function() {
    Registration.product.client_id = $('#user_client_id') ? $('#user_client_id').val() : null;
    Registration.product.user_profile_id = $('#user_profile_id').selectpicker('val');
    Registration.product.user_name = $('#user_name').val();
    Registration.product.user_mail = $('#user_mail').val();
    Registration.product.user_user = $('#user_user').val();
  },

  //Monta a tabela e exibe as informações
  showList: function(){

      //Limpa a tabela
      Registration.table.clear();

      //Entra no loop de exibição para cada item do array de produtos
      $.each(Registration.products, function (key, product) {

          console.log(product.product_name);
          //Adiciona uma nova linha na lista de exibição
        //  var status = Registration.status[document.shipment_status]

          //console.log(status)
          Registration.table.row.add([
              //'<i data-toggle="tooltip" title="' + status.title + '" class="fa fa-' + status.icon + ' text-' + status.color + '"></i>',
              product.product_code,
              product.product_name
              //document.shipment_date,
              //'<div class="person-cover"' + ( document.driver_image? 'style="background-image:url(' + document.driver_image+ ')"' : '' ) + '></div><label>' + document.driver_image + '</label><div class="seller">' + <!--( budget.seller.short_name || budget.seller.name )--> + '</div>',
              //div class="person-cover"' + ( document.driver_image? 'style="background-image:url(' + document.driver_image+ ')"' : '' ) + '></div><label>' + '</label><div class="seller">' + <!--( budget.seller.short_name || budget.seller.name )-->  '</div>',
            //  document.shipment_driver,
            //  '',
            //  ''
          ])
      })
      Registration.table.draw();
  },
}

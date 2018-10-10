$(document).ready(function(){
    ModalRegistrationProduct.events();
    ModalRegistrationProduct.table.draw();
});

ModalRegistrationProduct = {
  table: global.table({
      selector: '#modal-table-products',
      //searching: 1,
      scrollY: $(window).innerHeight() - 372,
      scrollCollapse: 1,
      noControls: [0, 2],
      order: [
          [1, 'desc']
      ]
  }),
  events: function(){
    ModalRegistrationProduct.showList();
  },
  showList: function(){
      console.log(ModalRegistrationProduct.product);
    ModalRegistrationProduct.table.clear();
    //console.log(ModalRegistrationProduct.product);
    //Adiciona uma nova linha na lista de exibição
    $.each(ModalRegistrationProduct.product, function(key, product){
      ModalRegistrationProduct.table.row.add([
          //'<input data-id="' + product.product_id + '" type="checkbox" class="product-check" data-key="1">',
          product.product_code,
          product.product_name,
          !!product.product_classification ? product.product_classification : '-',
          !!product.product_EAN ? product.product_EAN : '-'
      ])
      Registration.table.draw();
    })
  },
}

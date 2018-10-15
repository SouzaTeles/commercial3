<?php include "data.css.php"; ?>
:root{
    --imagepath: url(../images/empty-image.png);
}


body{
  background-color: white;
}
.container{
    padding: 110px 0 60px;
}
.image-place{
  height: 307px;
  margin-top: 40px;
  background-color: white;
  background-size: contain;
  background-position: center;
  background-repeat: no-repeat;
}

#product-group-registation{
  border-bottom: black;
}

#product-image-cover{
  background-image: var(--imagepath)
}

.overlay{
  background-color: white;
}

#registration{
  margin-top: 20px;
}

#profile{
  margin-top: 8px;
}

#table-products_wrapper{
    border-bottom: 1px solid rgb(221, 221, 221);
}

#image-input-area{
  display:flex;
  z-index: 1;
  /*width: 340px;*/
  height: 272px;
  position: absolute;
}




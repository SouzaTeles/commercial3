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

.product-cover
{
    width: 30px;
    height: 30px;
    margin: 0 auto;
    border-radius: 50%;
    background-size: cover;
    background-image: var(--imagepath);
    box-shadow: 0 4px 10px 0 rgba(0,0,0,0.2), 0 4px 20px 0 rgba(0,0,0,0.19);
}

#product-group-registation{
  border-bottom: black;
}

#product-image-cover{
  background-image: var(--imagepath);
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

#home{
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
  color: transparent;

}

.{
    opacity: 1 !important;
}



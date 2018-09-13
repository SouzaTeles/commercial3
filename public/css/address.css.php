<?php include "data.css.php"; ?>

.address-new
{
    width: 100%;
    padding: 66px 12px 56px;
    color: <?php echo $colors->hex->palette["green"]; ?>;
    border: 2px dashed <?php echo $colors->hex->palette["green"]; ?>;
}

.address-new i
{
    font-size: 34px;
}

.address-card
{
    margin: 0 0 30px;
    position: relative;
    padding: 20px 20px 50px;
    border: 2px solid <?php echo $colors->hex->palette["gray-light"]; ?>;
}

.address-card .address-header
{
    font-size: 18px;
}

.address-card .address-header button
{
    margin: 0 2px;
    font-size: 22px;
}

.address-card .address-header label
{
    color: #fff;
    float: right;
    display: none;
    font-size: 14px;
    padding: 2px 6px;
    margin-left: 2px;
    border-radius: 4px;
    font-weight: normal;
    background-color: <?php echo $colors->hex->palette["orange"]; ?>;
}


.address-card .address-footer button
{
    font-size: 18px;
}

.address-card .address-body
{
    margin: 10px 0;
}

.address-card .address-footer
{
    left: 0;
    right: 0;
    bottom: 0;
    padding: 10px;
    position: absolute;
    border-top: 1px solid;
}

.address-card .address-footer button.pull-right
{
    margin-left: 4px;
}

.address-card .address-footer button.pull-left
{
    margin-right: 4px;
}

.address-card-selected
{
    border: 2px solid <?php echo $colors->hex->palette["orange"]; ?>;
}

.address-card-selected .address-header label
{
    display: block;
}

/*#button-address-geo*/
/*{*/
/*    top: -4px;*/
/*    float: right;*/
/*    font-size: 28px;*/
/*    position: relative;*/
/*}*/
/**/
/*#modal-address-map*/
/*{*/
/*    height: 440px;*/
/*}*/
/**/
/*#address-panel-title*/
/*{*/
/*    padding: 2px 0 0 8px;*/
/*    border-left: 4px solid;*/
/*    text-transform: uppercase;*/
/*}*/
/**/
/*#form-address .panel-footer-address*/
/*{*/
/*    padding: 0 20px;*/
/*    border-top: none;*/
/*}*/
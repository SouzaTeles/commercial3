<?php include "../../data.css.php"; ?>

header
{
    top: 0;
    left: 0;
    width: 100%;
    padding: 20px;
    z-index: 1003;
    display: table;
    position: fixed;
    font-size: 22px;
    background-color: #fff;
}

header.new
{
    float: left;
    border-left: 10px solid <?php echo $colors->hex->palette["green"]; ?>
}

header.edit
{
    float: left;
    border-left: 10px solid <?php echo $colors->hex->palette["blue"]; ?>
}

header .title
{
    float: left;
}

header .user-image
{
    width: 40px;
    float: right;
    height: 40px;
    border-radius: 50%;
    border: 1px solid #ddd;
    margin: -5px 10px -4px;
    background-size: cover;
    background-position: center;
    background-image: url('../../../images/empty-image.png');
}

header .user-name
{
    float: right;
}

.panel-group
{
    width: 960px;
    margin: 0 auto 30px;
}

.panel-group .panel+.panel
{
    margin-top: 20px;
}

.cover
{
    width: 100%;
    height: 220px;
    margin: 0 0 15px;
    position: relative;
    border: 1px solid #ddd;
    background-size: cover;
    background-position: center;
    background-image: url('../../../images/empty-image.png');
}

.cover input
{
    display: none;
}

.cover button
{
    right: 0;
    bottom: 0;
    width: 50%;
    position: absolute;
}

.bootstrap-filestyle
{
    left: 0;
    bottom: 0;
    width: 50%;
    height: 34px;
    position: absolute;
}

.bootstrap-filestyle .badge
{
    display: none;
}

footer
{
    height: 66px;
}
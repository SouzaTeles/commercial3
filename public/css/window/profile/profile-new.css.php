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

header .profile-icon
{
    float: right;
    font-size: 30px;
    margin-left: 10px;
}

header .profile-name
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

.panel-group .panel-heading > i
{
    width: 30px;
    text-align: center;
}

footer
{
    height: 66px;
}
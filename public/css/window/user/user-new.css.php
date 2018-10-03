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

.panel-group
{
    width: 960px;
    margin: 30px auto;
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
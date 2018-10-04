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

header div
{
    float: left;
    margin-right: 60px;
}

header div.status
{
    margin-right: 0;
}

header div.status i
{
    font-size: 26px;
    margin-top: 5px;
}

header div.owner
{
    position: relative;
    padding-left: 50px;
}

header div span
{
    color: gray;
    font-size: 12px;
}

header .cover
{
    left: 0;
    width: 40px;
    bottom: 4px;
    height: 40px;
    position: absolute;
    margin-right: 10px;
    border-radius: 50%;
    background-size: cover;
    background-image: url('../../../images/empty-image.png');
}

.panel-group
{
    width: 960px;
    margin: 30px auto 0;
}

.panel-group .panel+.panel
{
    margin-top: 20px;
}

.panel-note
{
    margin-top: 30px;
}

.panel .panel-heading
{
    cursor: pointer;
    font-size: 22px;
    position: relative;
}

.panel .panel-heading .fa-chevron-up,
.panel .panel-heading .fa-chevron-down
{
    top: 10px;
    right: 10px;
    position: absolute;
}

.panel .panel-heading .fa-chevron-down
{
    display: none;
}

.panel .panel-heading.collapsed .fa-chevron-up
{
    display: none;
}

.panel .panel-heading.collapsed .fa-chevron-down
{
    display: block;
}

.notes .note
{
    position: relative;
    margin-bottom: 20px;
}

.notes .note .user
{
    width: 100%;
    height: 70px;
    display: table;
    float: left;
}

.notes .note .user .cover
{
    float: left;
    width: 60px;
    height: 60px;
    margin-right: 10px;
    border-radius: 50%;
    background-size: cover;
    background-image: url('../../../images/empty-image.png');
}

.notes .note .user .name
{
    font-size: 20px;
    margin-top: 5px;
}

.notes .note .info
{
    right: 0;
    top: 20px;
    position: absolute;
}

.notes .note .info > div
{
    float: left;
    margin: 0 10px;
    text-align: center;
}

.notes .note .info > div i
{
    margin: 2px 0;
    font-size: 16px;
}

.notes .note .info .cover
{
    width: 24px;
    height: 24px;
    margin: 0 auto;
    display: block;
    background-size: cover;
    background-color: #e5e5e5;
    border-radius: 50% !important;
    background-image: url('../../../images/empty-image.png');
}

.notes .note .info .cover:hover
{
    transform: scale(2,2);
}

.notes .note .attachment
{
    padding: 10px;
    background-color: #e5e5e5;
}

.notes .note .attachment i
{
    margin: 4px;
}

.notes .note .text
{
    clear: both;
    padding: 10px 20px;
    background-color: #eee;
}

.notes .note:nth-child(even)
{
    text-align: right;
}

.notes .note:nth-child(even) .user
{
    float: right;
}

.notes .note:nth-child(even) .user .cover
{
    float: right;
    margin-right: 0;
    margin-left: 10px;
}

.notes .note:nth-child(even) .info
{
    left: 0;
    right: auto;
}

.images
{
    width: 100%;
    height: 134px;
    display: table;
    margin: 2px 0 10px;
    padding: 20px 10px;
    background-color: lightgray;
}

.images .image
{
    width: 100%;
    height: 120px;
    margin-bottom: 20px;
    background-size: cover;
    background-color: gray;
    border: 1px solid #3c3c3c;
    background-position: center;
    background-repeat: no-repeat
}

.images .image i
{
    color: #fff;
    margin: 6px;
    float: right;
    cursor: pointer;
    font-size: 24px;
    text-shadow: 1px 1px 1px #333;
}

footer .logo
{
    top: 0;
    left: 0;
    width: 60px;
    height: 60px;
    position: absolute;
    background-size: cover;
    background-position: center;
}

footer .info
{
    margin-left: 60px;
}
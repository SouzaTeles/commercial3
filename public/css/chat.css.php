<?php include "data.css.php"; ?>

.chat
{
    top: 90px;
    bottom: 0;
    width: 220px;
    right: -220px;
    z-index: 1001;
    position: fixed;
    background: #333;
    overflow-y: auto;
    transition: all .3s ease;
}

.chat.open
{
    right: 0;
}

.chat ul
{
    padding: 0;
    list-style: none;
}

.chat ul li
{
    height: 64px;
    padding: 10px;
    cursor: pointer;
    position: relative;
    border-bottom: 1px solid #444;
}

.chat ul li:hover
{
    background-color: #444;
}

.chat ul li .cover
{
    float: left;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    margin-right: 10px;
    background-size: cover;
    background-color: #ddd;
    background-image: url('../images/empty-image.png');
}

.chat ul li .name,
.chat ul li .text
{
    float: left;
    width: 138px;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

.chat ul li .name
{
    color: #fff;
}

.chat ul li .text
{
    color: gray;
}

.dialog
{
    bottom: 0;
    width: 240px;
    height: 310px;
    position: fixed;
    transition: all .3s ease;
}

.dialog.dialog-minimized
{
    bottom: -270px;
}

.dialog .dialog-header
{
    color: #fff;
    padding: 10px;
    background-color: <?php echo $colors->hex->palette["blue"]; ?>;
}

.dialog .dialog-header button
{
    float: right;
    margin-left: 6px;
}

.dialog .dialog-header button[data-action="maximize"]
{
    display: none;
}

.dialog .dialog-body
{
    width: 100%;
    height: 240px;
    padding: 10px;
    overflow-y: auto;
    background-size: contain;
    background-image: url(https://us.123rf.com/450wm/aldanna/aldanna1506/aldanna150600008/40902796-ve…with-music-chat-gallery-speaking-bubble-email-magnifying-glass-s.jpg?ver=6);
}

.dialog .dialog-body .balloon
{
    color: #666;
    padding: 6px 10px;
    border-radius: 14px;
    margin-bottom: 10px;
    background-color: #fefefe;
}

.dialog .dialog-footer
{
    width: 100%;
    height: 30px;
}

.dialog .dialog-footer input
{
    width: 200px;
    height: 30px;
    border: none;
    padding: 0 4px;
}
.dialog .dialog-footer button
{
    height: 30px;
}

<?php include "../../data.css.php"; ?>

body > .container
{
    padding: 90px 0 90px;
}

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
    border-left: 8px solid <?php echo $colors->hex->palette["green"]; ?>;
}

.panel-post
{
    margin: 0 auto;
    max-width: 860px;
}

.panel-post .panel-body
{
    font-size: 18px;
}

#post-image
{
    width: 100%;
    display: none;
    margin-bottom: 30px;
}

#post-image img
{
    margin: 0 auto;
}

#post-title
{
    float: left;
    margin: 0 auto;
    max-width: 820px;
}

#post-date
{
    color: gray;
    float: right;
    padding: 6px 0;
    font-size: 14px;
}

#post-author
{
    font-size: 16px;
}
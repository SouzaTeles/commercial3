<?php include "data.css.php"; ?>

header
{
    top: 0;
    left: 0;
    right: 0;
    width: 100%;
    height: 90px;
    z-index: 1002;
    display: table;
    position: fixed;
    background-color: #fff;
    padding: 14px 20px 14px 80px;
}

header .home
{
    top: 0;
    left: 0;
    width: 60px;
    height: 90px;
    position: absolute;
    background-color: #0b4aa4;
}

header .home a
{
    color: #fff;
    width: 100%;
    height: 100%;
    display: block;
    font-size: 28px;
    padding: 30px 12px;
    transition: all ease 0.3s;
    border-left: 4px solid <?php echo $colors->hex->palette["blue-dark"]; ?>;
}

header .home a img
{
    width: 32px;
}

header .title
{
    top: 0;
    left: 60px;
    color: #fff;
    font-weight: bold;
    position: absolute;
    font-size: 34px;
    padding: 20px 20px 22px;
    text-transform: uppercase;
}

header .title:before
{
    top: 0;
    width: 0;
    height: 0;
    right: -45px;
    content: "";
    position: absolute;
    display: inline-block;
    vertical-align: middle;
    border-right: 15px solid transparent;
    border-bottom: 60px solid transparent;
}

<?php foreach( $colors->hex->pages as $page => $color ){ ?>
header .title-<?php echo $page; ?>
{
    background-color: <?php echo $color; ?>;
}

header .title-<?php echo $page; ?>:before
{
    border-left: 30px solid <?php echo $color ?>;
    border-top: 30px solid <?php echo $color ?>;
}
<?php } ?>

header .title i
{
    top: -3px;
    font-size: 28px;
    position: relative;
}

header .logo
{
    left: 0;
    right: 0;
    top: 18px;
    width: 200px;
    height: 54px;
    margin: 0 auto;
    position: absolute;
}

header .logo img
{
    max-width: 100%;
    max-height: 100%;
}

header .cover
{
    width: 60px;
    height: 60px;
    margin-left: 10px;
    border-radius: 50%;
    background-size: cover;
    background-position: center;
    background-image: url('../images/empty-image.png');
}

header .user .info
{
    margin: 10px;
}

header .user .info span
{
    color: gray;
    display: block;
}

header .user button
{
    margin: 15px 0;
    padding: 10px 10px 8px;
}

header .user .dropdown-menu
{
    width: 200px;
}

header .user .dropdown-menu li a
{
    padding: 10px 20px;
}

header .user .cover,
header .user .info,
header .user button
{
    float: left;
}

header .user button[data-action="chat"],
header .user button[data-action="alerts"]
{
    font-size: 18px;
    margin: 12px 4px;
    background: #efefef;
    border-radius: 50% !important;
}
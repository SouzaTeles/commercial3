<?php include "data.css.php"; ?>

.menu
{
    left: 0;
    right: 0;
    top: 90px;
    height: 100%;
    z-index: 1001;
    display: table;
    position: fixed;
}

.menu ul
{
    padding: 0;
    list-style: none;
}

.menu ul li a
{
    color: #fff;
    width: 60px;
    height: 50px;
    display: block;
    font-size: 18px;
    overflow: hidden;
    background-color: #333;
    text-decoration: none;
    transition: all .3s ease;
    text-transform: uppercase;
}

.menu ul li:hover a
{
    width: 268px;
    padding-right: 20px;
    background-color: <?php echo brightness("#333333",20); ?>;
}

.menu ul li a i
{
    width: 60px;
    height: 50px;
    padding: 15px;
    font-size: 20px;
    text-align: center;
}

.menu ul li .sub-menu
{
    width: 268px;
    float: right;
    display: none;
    margin-left: 60px;
    position: absolute;
    background-color: #666666;
}

.menu ul li:hover .sub-menu
{
    display: block;
}

<?php foreach( $colors->hex->pages as $page => $color ){ ?>

.menu ul li a.<?php echo $page . PHP_EOL; ?>
{
    border-left: 4px solid <?php echo $color; ?>;
}

.menu ul li a.<?php echo "{$page}:hover" . PHP_EOL; ?>
{
    border-width: 10px;
    background-color: <?php echo $color; ?>;
    border-color: <?php echo brightness($color,-20); ?>;
}

<?php } ?>

@media( max-height: 768px )
{
    .menu .sub-menu-crm {
        top: 250px;
    }

    .menu .sub-menu-config {
        top: 250px;
    }
}
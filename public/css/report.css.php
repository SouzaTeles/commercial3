<?php include "data.css.php"; ?>

@import url('https://fonts.googleapis.com/css?family=Oswald');

html, body
{
    overflow-x: hidden;
    padding: 0 !important;
    font-family: 'Oswald', sans-serif;
    background-color: <?php echo brightness($colors->hex->background,-20); ?>;
}

h1
{
    font-size: 32px;
    font-weight: bold;
    padding-left: 12px;
    margin-bottom: 20px;
    text-transform: uppercase;
    color: <?php echo $colors->hex->text; ?>;
    border-left: 4px solid <?php echo $colors->hex->primary; ?>;
}

.header
{
    top: 0;
    width: 100%;
    padding: 20px 0;
    display: table;
}

.header .logo img
{
    max-width: 100%;
}

.header .date
{
    color: <?php echo $colors->hex->background; ?>;
    width: 108px;
    float: right;
    margin: 44px 0;
    text-align: center;
    background-color: <?php echo $colors->hex->text; ?>;
}

.header .date .month
{
    color: #fff;
    font-size: 20px;
    background-color: <?php echo $colors->hex->primary; ?>;
}

.header .date .day
{
    font-size: 62px;
    font-weight: bold;
}

.nav-pages li a
{
    background-color: <?php echo brightness($colors->hex->background,0); ?>;
}

.footer
{
    width: 100%;
    padding: 10px;
    display: table;
    margin: 40px 0 0;
    color: <?php echo $colors->hex->text; ?>;
    background-color: <?php echo brightness($colors->hex->background,-40); ?>;
}

@media( max-width: 767px )
{
    body
    {
        margin-top: 77px;
        background-color: <?php echo brightness($colors->hex->background,-10); ?>;
    }

    .nav-pages li
    {
        width: 50%;
    }

    .header
    {
        top: 0;
        left: 0;
        z-index: 10;
        padding: 10px 20px;
        position: fixed;
        background-color: <?php echo brightness($colors->hex->background,-20); ?>;
    }

    .header .logo img
    {
        max-width: 80%;
    }

    .header .date
    {
        margin: 0;
        width: 100%;
    }

    .header .date .month
    {
        font-size: 12px;
    }

    .header .date .day
    {
        font-size: 28px;
    }
}
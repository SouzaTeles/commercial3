<?php include "data.css.php"; ?>

.slide
{
    width: 100%;
    height: 276px;
    background-size: contain;
    background-color: #9d9d9d;
    background-position: center;
    background-repeat: no-repeat;
    background-image: url('../images/empty-image.png');
    border-bottom: 6px solid <?php echo $colors->hex->palette["blue"]; ?>;
}

.slide .image
{
    height: 270px;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

.slide .carousel-caption h3
{
    font-size: 56px;
    padding: 4px 10px;
    display: inline-block;
}

.slide .carousel-caption p
{
    font-size: 22px;
}

.slide .carousel-caption button
{
    padding: 10px;
}

.slide .carousel-caption button a
{
    font-size: 16px;
}

.slide ol
{
    margin: 0;
    padding: 0;
    left: auto;
    width: 100%;
}

.slide ol li,
.slide ol li.active
{
    width: 20px;
    height: 6px;
    margin: 0 2px;
    border-radius: 0;
}

.slide ol li:hover,
.slide ol li.active
{
    background-color: <?php echo $colors->hex->palette["blue"]; ?>;
}
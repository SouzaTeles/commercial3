<?php include "data.css.php"; ?>

#slide
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

#slide .image
{
    height: 270px;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

#slide .carousel-caption h3
{
    font-size: 56px;
    padding: 4px 10px;
    display: inline-block;
}

#slide .carousel-caption p
{
    font-size: 22px;
}

#slide .carousel-caption button
{
    padding: 10px;
}

#slide .carousel-caption button a
{
    font-size: 16px;
}

#slide ol
{
    margin: 0;
    padding: 0;
    left: auto;
    width: 100%;
}

#slide ol li,
#slide ol li.active
{
    width: 20px;
    height: 6px;
    margin: 0 2px;
    border-radius: 0;
}

#slide ol li:hover,
#slide ol li.active
{
    background-color: <?php echo $colors->hex->palette["blue"]; ?>;
}

#birthdays
{
    width: 100%;
    height: 300px;
    margin-top: 20px;
    text-align: center;
    position: relative;
    padding: 56px 20px 20px;
    background-size: contain;
    background-color: #efefef;
    background-repeat: no-repeat;
    background-position: center top;
    border-bottom: 6px solid <?php echo $colors->hex->palette["green"]; ?>;
    border-bottom: 6px solid <?php echo $colors->hex->palette["green"]; ?>;
    background-image: url('https://wwwcdn.wthr.com/sites/wthr.com/files/styles/article_image/public/Balloon970.jpg?itok=mxndp96r');
}

#birthdays .image
{
    width: 80px;
    height: 80px;
    margin: 0 auto;
    border-radius: 50%;
    margin-bottom: 10px;
    background-size: cover;
    background-color: #efefef;
    background-position: center;
    background-image: url('../images/empty-image.png');
}

#birthdays .name
{
    font-size: 18px;
}

#birthdays .date
{
    font-size: 26px;
}

#birthdays .text
{
    line-height: 16px;
}

#birthdays a
{
    bottom: 6px;
    position: absolute;
}

#birthdays a.left
{
    left: 110px;
}

#birthdays a.right
{
    right: 110px;
}
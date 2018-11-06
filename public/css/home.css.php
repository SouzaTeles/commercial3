<?php include "data.css.php"; ?>

.intranet
{
    width: 1200px;
    margin: 0 auto;
}

#slide
{
    width: 100%;
    height: 376px;
    margin-top: 20px;
    background-size: contain;
    background-color: #9d9d9d;
    background-position: center;
    background-repeat: no-repeat;
    background-image: url('../images/empty-image.png');
    border-bottom: 6px solid <?php echo $colors->hex->palette["blue"]; ?>;
}

#slide .image
{
    height: 370px;
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

#slide .carousel-caption
{
    bottom: 68px;
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

#slide .author
{
    left: 0;
    bottom: 0;
    padding: 10px;
    position: absolute;
}

#slide .author .cover
{
    float: left;
    width: 60px;
    height: 60px;
    border: 2px solid #fff;
    border-radius: 50%;
    background-size: cover;
    background-position: center;
}

#slide .author .name
{
    color: #fff;
    float: right;
    font-size: 16px;
    max-width: 120px;
    margin: 8px 0 0 12px;
    text-shadow: 1px 1px 1px #333;
}

#blog
{
    margin-top: 30px;
}

#blog .title
{
    font-weight: bold;
    margin-bottom: 10px;
    text-transform: uppercase;
}

#blog .news .item
{
    height: 168px;
    cursor: pointer;
    margin: 0 0 20px;
    background-color: #fff;
}

#blog .news .item .post-cover
{
    float: left;
    width: 50%;
    height: 168px;
    background-size: cover;
    background-color: #eee;
    background-image: url('../images/empty-image.png');
    background-repeat: no-repeat;
    background-position: center;
}

#blog .news .item .post-data
{
    width: 50%;
    float: left;
    height: 168px;
    position: relative;
    padding: 50px 14px 34px;
}

#blog .news .item .post-data .post-category
{
    top: 0;
    left: 0;
    right: 0;
    color: #fff;
    padding: 8px 12px;
    position: absolute;
    text-transform: uppercase;
    background-color: <?php echo $colors->hex->palette["green"]; ?>;
}

#blog .news .item .post-data .post-title
{
    font-weight: bold;
    margin-bottom: 8px;
    text-transform: uppercase;
}

#blog .news .item .post-data .post-date
{
    color: gray;
    right: 12px;
    bottom: 8px;
    position: absolute;
    font-size: 12px;
    text-align: right;
}

#blog .news .loading .post-data .post-title
{
    width: 140px;
    background-color: #eee;
}

#blog .news .loading .post-data .post-preview
{
    width: 140px;
    height: 46px;
    background-color: #eee;
}

#birthdays
{
    width: 100%;
    height: 300px;
    padding: 20px;
    margin-top: 20px;
    text-align: center;
    position: relative;
    background-size: contain;
    background-color: #efefef;
    background-repeat: no-repeat;
    background-position: center -46px;
    background-image: url('../images/birthdays.jpg');
    border-bottom: 6px solid <?php echo $colors->hex->palette["green"]; ?>;
    border-bottom: 6px solid <?php echo $colors->hex->palette["green"]; ?>;
}

#birthdays .image
{
    width: 120px;
    height: 120px;
    border-radius: 50%;
    margin: 0 auto 10px;
    background-size: cover;
    background-color: #efefef;
    background-position: center;
    background-image: url('../images/empty-image.png');
}

#birthdays .carousel-inner
{
    height: 254px;
}

#birthdays .carousel-inner .name
{
    font-size: 18px;
}

#birthdays .carousel-inner .date
{
    font-size: 26px;
}

#birthdays .carousel-inner .text
{
    line-height: 16px;
}

.suggestion
{
    margin-top: 30px;
}

.suggestion textarea
{
    height: 184px;
}

#target
{
    height: 250px;
    margin: 20px 0;
    background-color: #fff;
    padding: 20px 20px 20px 60px;
}

#target .target-title
{
    color: #fff;
    position: absolute;
    left: -88px;
    font-size: 20px;
    top: 123px;
    padding: 8px;
    text-align: center;
    width: 250px;
    transform: rotate(-90deg);
    background-color: <?php echo $colors->hex->palette["green"]; ?>
}

#target .person
{
    left: -2px;
    top: -20px;
    width: 200px;
    height: 250px;
    margin: 0 auto;
    position: absolute;
    background-color: #ddd;
    background-size: cover;
    background-position: center;
    background-image: url('../images/empty-image.png');
}

#target .month
{
    font-size: 20px;
}

#target .month .info
{
    display: table;
    font-size: 18px;
    width: 100%;
}

#target .month .info label
{
    float: right;
    padding: 2px 4px;
    border-radius: 4px;
    font-size: 16px;
    font-weight: normal;
}

.donut-chart
{
    position: relative;
    border-radius: 50%;
    overflow: hidden;
}

.donut-chart.chart2
{
    width: 200px;
    height: 200px;
    margin: 0 auto;
    background: #666;
}

.donut-chart.chart2 .slice.one {
    clip: rect(0 200px 100px 0);
}

.donut-chart .slice {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.donut-chart.chart2 .slice.two {
    clip: rect(0 100px 200px 0);
    -webkit-transform: rotate(327.6deg);
    transform: rotate(327.6deg);
    background: #48b2c1;
}

.donut-chart.chart2 .chart-center {
    top: 25px;
    left: 25px;
    width: 150px;
    height: 150px;
    padding: 14px;
    line-height: 34px;
    background: #fff;
}

.donut-chart .chart-center {
    position: absolute;
    border-radius: 50%;
}

.donut-chart .chart-center span{
    display: block;
    text-align: center;
}

.donut-chart .chart-center span:nth-child(1){
    color: gray;
    font-size: 16px;
    line-height: 18px;
}

.donut-chart .chart-center span:nth-child(2) {
    font-size: 36px;
    color: #48b2c1;
    margin: 8px 0;
}

.donut-chart .chart-center span:nth-child(3){
    color: gray;
    font-size: 16px;
    line-height: 18px;
}

@media(max-width:1024px)
{
    .intranet
    {
        width: 960px;
    }
}
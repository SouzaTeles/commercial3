<?php include "data.css.php"; ?>

@import url('https://fonts.googleapis.com/css?family=Oswald');

html, body
{
    overflow-x: hidden;
    padding: 0 !important;
    font-family: 'Oswald', sans-serif;
    background-color: <?php echo $colors->hex->body; ?>;
}

h1
{
    height: 36px;
    font-size: 24px;
    padding: 6px 10px;
    margin-bottom: 20px;
    border-left: 6px solid <?php echo $colors->hex->primary; ?>;
}

hr
{
    border-color: <?php echo $colors->hex->primary; ?>;
}

body::-webkit-scrollbar,
div::-webkit-scrollbar
{
    width: 0.5em;
    background: #999;
}

body::-webkit-scrollbar-thumb,
div::-webkit-scrollbar-thumb
{
    background-color: <?php echo $colors->hex->primary; ?>;;
    outline: 1px solid #3c3c3c;
    position: relative;
    right: 0.5em;
}

.table-cover th:first-child,
.table-cover td:first-child
{
    width: 20px;
}

.table-cover td:first-child i
{
    font-size: 24px;
}

.table-cover td:first-child div
{
    width: 24px;
    height: 24px;
    margin: 0 auto;
    border-radius: 50%;
    background-size: cover;
    background-position: center center;
    box-shadow: 0 4px 10px 0 rgba(<?php echo $colors->rgb->primary->red; ?>,<?php echo $colors->rgb->primary->green; ?>,<?php echo $colors->rgb->primary->blue; ?>,0.2), 0 4px 20px 0 rgba(<?php echo $colors->rgb->primary->red; ?>,<?php echo $colors->rgb->primary->green; ?>,<?php echo $colors->rgb->primary->blue; ?>,0.19);
}

.table-cover tr:hover td:first-child div
{
    transform: scale(3,3);
}

.table-actions th:last-child,
.table-actions td:last-child
{
    width: 12px;
}

.container
{
    width: 100%;
    height: 100%;
    display: table;
    position: relative;
    padding: 90px 0 60px;
    border-left: 60px solid <?php echo $colors->hex->menu_link_background; ?>;
}

.overlay
{
    width: 100%;
    z-index: -1;
    height: 100%;
    position: fixed;
}

footer
{
    right: 0;
    bottom: 0;
    left: 60px;
    height: 60px;
    padding: 20px;
    z-index: 1001;
    position: fixed;
    background-color: <?php echo $colors->hex->background; ?>;
    box-shadow: 0 4px 20px 0 rgba(0,0,0,0.2), 0 4px 20px 0 rgba(0,0,0,0.19);
}

footer div,
footer button
{
    float: left;
    margin-right: 10px;
}

footer div span
{
    margin-left: 4px;
}

.dataTables_scrollBody
{
    min-height: 188px;
    background-color: lightgray;
}

.dataTables_scrollBody tr
{
    background-color: #fff;
}

.dataTables_scrollBody tr td.dataTables_empty
{
    border: none;
    height: 170px;
    background-color: lightgray;
}

.box-shadow
{
    box-shadow: 0 4px 10px 0 rgba(0,0,0,0.2), 0 4px 20px 0 rgba(0,0,0,0.19);
}

.form-group
{
    margin-bottom: 8px;
}

.form-group label
{
    margin-bottom: 0;
}
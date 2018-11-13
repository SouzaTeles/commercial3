<?php include "data.css.php"; ?>

@import url('https://fonts.googleapis.com/css?family=Oswald');

html, body
{
    min-height: 100%;
    padding: 0 !important;
    background-color: #ddd;
    font-family: 'Oswald', sans-serif;
}

body::-webkit-scrollbar,
div::-webkit-scrollbar,
ul::-webkit-scrollbar
{
    width: 0.5em;
    height: 0.5em;
    background: #999;
}

body::-webkit-scrollbar-thumb,
div::-webkit-scrollbar-thumb,
ul::-webkit-scrollbar-thumb
{
    background-color: <?php echo $colors->hex->palette["blue"]; ?>;
    outline: 1px solid #3c3c3c;
    position: relative;
    right: 0.5em;
}

.dataTables_scrollBody::-webkit-scrollbar
{
    width: 1.0em;
}

.dataTables_scrollBody::-webkit-scrollbar-thumb
{
    background-color: #3c3c3c;
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

body > .container
{
    width: 100%;
    height: 100%;
    display: table;
    min-width: 986px;
    position: relative;
    padding: 90px 0 60px;
    border-left: 60px solid #333;
}

.container > .panel
{
    margin-bottom: 0;
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
    z-index: 1010;
    position: fixed;
    background-color: #fff;
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
    box-shadow: 0 4px 10px 0 rgba(0,0,0,0.19), 0 4px 20px 0 rgba(0,0,0,0.19);
}

.form-group
{
    margin-bottom: 8px;
}

.form-group label
{
    margin-bottom: 0;
}

ul.typeahead
{
    overflow-y: auto;
    max-height: 220px;
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
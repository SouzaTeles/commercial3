<?php include "data.css.php"; ?>

.logo-commercial
{
    margin: 12px 0;
    text-align: right;
}

.logo-commercial img
{
    max-width: 100%;
    max-height: 48px;
}

#table-budgets_filter
{
    display: none;
}

table#table-budgets tr.idne1 td:nth-child(1){ border-left:4px solid red; }
table#table-budgets tr.idne2 td:nth-child(1){ border-left:4px solid orange; }
table#table-budgets tr.idne3 td:nth-child(1){ border-left:4px solid purple; }
table#table-budgets tr.idne4 td:nth-child(1){ border-left:4px solid blue; }
table#table-budgets tr.idne5 td:nth-child(1){ border-left:4px solid green; }

table#table-budgets tr td img
{
    max-width: 28px;
    max-height: 18px;
}

table#table-budgets tr td:first-child i
{
    margin: 2px 4px;
}

table#table-budgets tr td:first-child i.fa-mobile
{
    top: 2px;
    font-size: 20px;
    position: relative;
}

table#table-budgets tr td:nth-child(2),
table#table-budgets tr td:nth-child(5)
{

    text-align: left;
    padding-left: 20px;
}

table#table-budgets .client label
{
    float: left;
}

table#table-budgets .client,
table#table-budgets .seller
{
    float: left;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

footer .divider
{
    width: 2px;
    height: 16px;
    margin: 1px 15px 1px 5px;
    background-color: lightgray;
}

footer i
{
    font-size: 18px !important;
}

footer button
{
    padding: 4px !important;
}

footer button.selected
{
    background: lightgray;
    border-radius: 4px !important;
}

.dropdown-budget ul
{
    position: absolute;
    z-index: 1011;
}

@media(max-width:1300px) {
    table#table-budgets thead th:nth-child(5){
        width: 342px;
    }
}

@media(max-width:1250px) {
    table#table-budgets thead th:nth-child(5){
        width: 300px;
    }
    table#table-budgets .client{
        width: 240px;
    }
}

@media(max-width:1150px) {
    table#table-budgets thead th:nth-child(5){
        width: 280px;
    }
    table#table-budgets .client{
        width: 160px;
    }
}

@media(max-width:1080px) {
    table#table-budgets thead th:nth-child(5){
        width: 240px;
    }
    table#table-budgets .client{
        width: 140px;
    }
    table#table-budgets .seller{
        width: 140px;
    }
}
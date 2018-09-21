<?php include "data.css.php"; ?>

.nav-pages
{
    display: none;
}

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

table#table-budgets .client,
table#table-budgets .seller
{
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
}
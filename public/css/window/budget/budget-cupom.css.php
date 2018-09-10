<?php include "../../data.css.php"; ?>

body
{
    font-size: 12px;
}

.container
{
    padding: 20px;
}

.divider
{
    width: 100%;
    height: 1px;
    margin: 2px 0;
    background-color: #333;
}

.cupom
{
    width: 89mm;
    padding: 15px;
    margin: 0 auto;
    text-align: center;
    text-transform: uppercase;
    background-color: #FFF5BF;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
}

.cupom .company-name
{
    font-size: 18px;
    font-weight: bold;
}

.cupom .info
{
    width: 100%;
    display: table;
    margin: 6px 0 2px;
}

.cupom .info .time
{
    float: left;
}

.cupom .info .system
{
    float: right;
}

.cupom .budget
{
    width: 100%;
    display: table;
    margin: 2px 0 4px;
}

.cupom .budget .code
{
    float: left;
}

.cupom .budget .external
{
    float: right;
}

.cupom .separator
{
    font-size: 16px;
    font-weight: bold;
}

.cupom .client,
.cupom .seller
{
    text-align: left;
}

.cupom .seller
{
    margin-bottom: 6px;
}

table
{
    width: 100%;
}

table tr th,
table tr:nth-child(even)
{
    padding: 2px 4px;
}

button.btn
{
    right: 20px;
    width: 56px;
    height: 56px;
    bottom: 20px;
    position: fixed;
    font-size: 24px;
    border-radius: 50% !important;
}

@media print
{
    .container,
    .print-order
    {
        padding: 0;
    }

    button.btn
    {
        display: none;
    }

    .cupom
    {
        width: 80mm;
        padding: 0 0 0 15px;
    }
}

@page
{
    margin: 0;
}
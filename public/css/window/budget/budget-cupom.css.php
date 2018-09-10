<?php include "../../data.css.php"; ?>

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

.ticket
{
    width: 89mm;
    padding: 15px;
    margin: 0 auto;
    text-align: center;
    text-transform: uppercase;
    background-color: #FFF5BF;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
}

.ticket .company-name
{
    font-size: 18px;
    font-weight: bold;
}

.ticket .info
{
    width: 100%;
    display: table;
    margin: 6px 0 2px;
}

.ticket .info .time
{
    float: left;
}

.ticket .info .system
{
    float: right;
}

.ticket .budget
{
    width: 100%;
    display: table;
    margin: 2px 0 4px;
}

.ticket .budget .code
{
    float: left;
}

.ticket .budget .external
{
    float: right;
}

.ticket .separator
{
    font-size: 20px;
    font-weight: bold;
}

.ticket .client,
.ticket .seller
{
    text-align: left;
}

.ticket .seller
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
}

@page
{
    margin: 0;
}
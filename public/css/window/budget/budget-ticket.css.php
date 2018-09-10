<?php include "../../data.css.php"; ?>

.container
{
    padding: 20px;
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
    margin: 6px 0 10px;
    border-bottom: 1px solid #333;
}

.ticket .info .time
{
    float: left;
}

.ticket .info .system
{
    float: right;
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

.ticket .external
{
    font-size: 16px;
    font-weight: bold;
}

.ticket .budget
{
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 8px;
}

.ticket .payments
{
    width: 100%;
    font-size: 16px;
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
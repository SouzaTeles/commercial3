<?php include "../../data.css.php"; ?>

html, body
{
    height: 100%;
}

body
{
    font-size: 12px;
    background-color: #ddd;
    font-family: sans-serif;
}

table
{
    width: 100%;
    border-collapse: collapse;
}

th
{
    text-transform: uppercase;
    -webkit-print-color-adjust: exact;
    background-color: #a7a7a7 !important;
}

th, td
{
    font-size: 10px;
    overflow: hidden;
    display: table-cell;
    vertical-align: middle;
}

.container
{
    padding: 20px !important;
}

.table-border
{
    border-left: 1px solid #333;
    border-bottom: 1px solid #333;
}

.table-border th,
.table-border td
{
    border-top: 1px solid #333;
    border-right: 1px solid #333;
}

.text-right
{
    text-align: right;
}

.text-center
{
    text-align: center;
}

.print-order
{
    padding: 20px;
    margin: 0 auto;
    max-width: 780px;
    background-color: #fff;
}

.print-order .products table tr td,
.print-order .products table tr th
{
    text-align: center;
}

.print-order .products table tr td:nth-child(1),
.print-order .products table tr th:nth-child(1)
{
    text-align: left;
}

.print-order .products table tr td,
.print-order .products table tr th,
.address-values-payments table tr th,
.address-values-payments table tr td,
.note table tr th,
.note table tr td
{
    padding: 4px;
}

.logo
{
    width: 100px;
}

#company-logo
{
    max-width: 140px;
    max-height: 80px;
}

#budget-message
{
    padding: 10px;
    margin: 10px 0;
    font-size: 11px;
    font-weight: bold;
    text-align: center;
    color: #fff !important;
    text-transform: uppercase;
    background-color: #666 !important;
    -webkit-print-color-adjust: exact;
}

.company
{
    font-size: 12px;
}

.company #company-name
{
    font-size: 16px;
}

#external-code,
#budget-date
{
    font-size: 16px;
}

#client-info
{
    margin-bottom: 10px;
}

#client-info td
{
    font-size: 14px;
    text-transform: uppercase;
}

.print-order .products
{
    margin-bottom: 10px;
}

.print-order .address-values-payments
{
    text-transform: uppercase;
}

.print-order .address-values-payments .address
{
    padding-left: 0;
}

#budget-address
{
    font-weight: bold;
}

.print-order .address-values-payments .address,
.print-order .address-values-payments .values
{
    width: 30%;
    vertical-align: top;
}

.print-order .address-values-payments .payments
{
    width: 40%;
    padding-right: 0;
    vertical-align: top;
}

.print-order .note
{
    padding: 0;
    border: none;
    margin: 10px 0;
    text-transform: uppercase;
}

.print-order .note td,
.print-order .note th,
.print-order .message td,
.print-order .message th
{
    width: 50%;
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
    margin: 1cm;
}

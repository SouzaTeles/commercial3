<?php include "../../data.css.php"; ?>

body
{
    font-size: 12px;
    background-color: #fff;
    font-family: sans-serif;
    -webkit-print-color-adjust: exact !important;
}

table
{
    width: 100%;
    border-collapse: collapse;
}

th
{
    text-transform: uppercase;
    background-color: #a7a7a7;
    -webkit-print-color-adjust: exact;
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
    padding: 20px;
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
}

.print-order .products table tr td,
.print-order .products table tr th
{
    text-align: center;
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
    color: #fff;
    padding: 10px;
    margin: 10px 0;
    font-size: 11px;
    text-align: center;
    background-color: #666;
    text-transform: uppercase;
    -webkit-print-color-adjust: exact;
}

.company
{
    font-size: 14px;
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
    font-size: 16px;
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

<?php include "data.css.php"; ?>

.table-custom
{
    width: 100%;
    margin-top: 0 !important;
    border-left: 1px solid #efefef;
}

.table-custom > thead > tr > th
{
    color: #fff;
    border: none;
    background-color: gray;
}

.table-custom tbody tr:hover
{
    background-color: #ddd;
}

.table-custom thead tr th
{
    padding: 14px;
    text-align: center;
    text-transform: uppercase;
}

.table-custom>thead>tr>th:after
{
    bottom: 15px !important;
}

.table-custom td
{
    padding: 8px;
    text-align: center;
    vertical-align: middle;
    border-right: 1px solid #efefef;
    border-bottom: 1px solid #efefef;
}

.table-custom td span
{
    display: none;
}

.table-custom td label
{
    color: gray;
    display: block;
    font-size: 12px;
    font-weight: normal;
}

.table-custom td .person-cover
{
    float: left;
    width: 44px;
    height: 44px;
    margin-right: 10px;
    border-radius: 50%;
    background-size: cover;
    background-repeat: no-repeat;
    background-position: center center;
    background-image: url('../images/empty-image.png');
}

.table-custom thead .sorting:after,
.table-custom thead .sorting_asc:after,
.table-custom thead .sorting_desc:after,
.table-custom thead .sorting_asc_disabled:after,
.table-custom thead .sorting_desc_disabled:after
{
    opacity: 1;
}
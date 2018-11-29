<?php include "data.css.php"; ?>

#table-budgets_filter
{
    display: none;
}

table#table-budgets tr td:nth-child(2)
{
    text-align: left;
    padding-left: 20px;
}

table#table-budgets .client label
{
    float: left;
}

table#table-budgets .client
{
    float: left;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
}

table input[type="checkbox"]
{
    width: 16px;
    height: 16px;
}

#table-budgets_wrapper .dataTables_scrollBody tr td.dataTables_empty
{
    height: 300px;
}

#table-budgets_wrapper .dataTables_scrollBody
{
    min-height: 320px;
}

#table-budgets-selected_wrapper .dataTables_scrollBody tr td.dataTables_empty
{
    height: 270px;
}

#table-budgets-selected_wrapper .dataTables_scrollBody
{
    height: 286px;
}

table tr.selected
{
    background-color: #d0d0d0 !important;
}

#selected-box
{
    top: 120px;
    width: 480px;
    height: 420px;
    right: -466px;
    z-index: 1001;
    position: fixed;
    transition: all .3s ease;
    background-color: lightgray;
}

#selected-box .footer
{
    left: 0;
    right: 0;
    bottom: 34px;
    background: white;
    position: absolute;
    border-top: 2px solid #ccc;
}

#selected-box .footer .info
{
    margin: 4px;
    text-align: center;
}

#selected-box .footer .info span
{
    color: gray;
}

#selected-box table
{
    margin-top: 0 !important;
}

#selected-box table tbody
{
    background-color: white;
}

#selected-box table tbody tr
{
    cursor: all-scroll;
}

.ui-sortable-helper td:nth-child(1),
.ui-sortable-helper td:nth-child(2)
{
    width: 100px;
}

.ui-sortable-helper td:nth-child(3)
{
    width: 218px;
}

.ui-sortable-helper td:nth-child(4)
{
    width: 48px;
}

#selected-box .fa-chevron-right
{
    display: none;
}

#selected-box.visible
{
    right: 0;
}

#selected-box.visible .fa-chevron-left
{
    display: none;
}

#selected-box.visible .fa-chevron-right
{
    display: block;
}

#selected-box button#button-new-shipment,
#selected-box button#button-show-selected
{
    bottom: 0;
    position: absolute;
}

#button-show-selected
{
    left: -37px;
    height: 34px;
    border-radius: 4px 0 0 4px !important;
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
    table#table-budgets .client{
        width: 180px;
    }
}

@media(max-width:1080px) {
    table#table-budgets .client{
        width: 204px;
    }
}

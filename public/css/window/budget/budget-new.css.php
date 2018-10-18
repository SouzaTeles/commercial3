<?php include "../../data.css.php"; ?>

@import "includes/budget-tools.css";
@import "includes/budget-items.css";
@import "includes/budget-person.css";
@import "includes/budget-payment.css";

.dataTables_scrollHeadInner
{
    background-color: lightgray;
}

.dataTables_scrollBody,
.dataTables_scrollBody tr td.dataTables_empty
{
    background-color: #eee;
}

.panel
{
    margin-bottom: 20px;
    background-color: transparent;
}

.panel.panel-budget
{
    width: 960px;
    margin: 0 auto;
}

.panel .panel-heading
{
    font-size: 22px;
    padding: 15px 20px;
}

.panel .panel-body
{
    background-color: #fff;
}

hr
{
    margin: 4px 0 10px;
}

footer .logo
{
    top: 0;
    left: 0;
    width: 60px;
    height: 60px;
    position: absolute;
    background-size: cover;
    background-position: center;
}

footer .info
{
    margin-left: 60px;
}

footer .idne
{
    top: -5px;
    right: -10px;
    font-size: 70px;
    position: absolute;
}

footer .idne i.idne1{ color: red; }
footer .idne i.idne2{ color: orange; }
footer .idne i.idne3{ color: purple; }
footer .idne i.idne4{ color: blue; }
footer .idne i.idne5{ color: green; }
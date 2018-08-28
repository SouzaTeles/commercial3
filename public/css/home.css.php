<?php include "data.css.php"; ?>

/*.logo*/
/*{*/
/*    margin: 40px 0;*/
/*    text-align: center;*/
/*}*/
/**/
/*.logo img*/
/*{*/
/*    max-width: 380px;*/
/*    max-height: 140px;*/
/*}*/
/**/
/*.menu*/
/*{*/
/*    margin: 0 auto;*/
/*}*/
/**/
/*.menu .links*/
/*{*/
/*    display: table;*/
/*    text-align: center;*/
/*}*/
/**/
/*.menu .links > div*/
/*{*/
/*    padding: 0;*/
/*    width: 20%;*/
/*    display: inline-block;*/
/*}*/
/**/
/*.menu .links a*/
/*{*/
/*    color: #FFF;*/
/*    margin: 10px;*/
/*    height: 200px;*/
/*    display: block;*/
/*    font-size: 20px;*/
/*    padding: 40px 20px;*/
/*    text-align: center;*/
/*    text-decoration: none;*/
/*    transition: .3s all ease;*/
/*    background-color: */<?php //echo $colors->hex->background; ?>/*;*/
/*    box-shadow: 0 4px 10px 0 rgba(*/<?php //echo $colors->rgb->primary->red; ?>/*,*/<?php //echo $colors->rgb->primary->green; ?>/*,*/<?php //echo $colors->rgb->primary->blue; ?>/*,0.2), 0 4px 20px 0 rgba(*/<?php //echo $colors->rgb->primary->red; ?>/*,*/<?php //echo $colors->rgb->primary->green; ?>/*,*/<?php //echo $colors->rgb->primary->blue; ?>/*,0.19);*/
/*}*/
/**/
/*.menu .links a:hover*/
/*{*/
/*    transform: translateY(-10px);*/
/*}*/
/**/
<?php //foreach( $colors->hex->pages as $page => $color ){ ?>
/*.menu .links a.*/<?php //echo $page . PHP_EOL; ?>
/*{*/
/*    border-left: 10px solid */<?php //echo $color; ?>/*;*/
/*}*/
/**/
/*.menu .links a.*/<?php //echo "{$page}:hover" . PHP_EOL; ?>
/*{*/
/*    box-shadow: 0 4px 10px 0 rgba(*/<?php //echo $colors->rgb->pages->$page->red; ?>/*,*/<?php //echo $colors->rgb->pages->$page->green; ?>/*,*/<?php //echo $colors->rgb->pages->$page->blue; ?>/*,0.2), 0 4px 20px 0 rgba(*/<?php //echo $colors->rgb->pages->$page->red; ?>/*,*/<?php //echo $colors->rgb->pages->$page->green; ?>/*,*/<?php //echo $colors->rgb->pages->$page->blue; ?>/*,0.19);*/
/*}*/
<?php //} ?>
/**/
/*.menu .links a i*/
/*{*/
/*    display: block;*/
/*    font-size: 60px;*/
/*    margin-bottom: 10px;*/
/*}*/
/**/
/*.menu .links a span*/
/*{*/
/*    display: block;*/
/*    font-size: 12px;*/
/*}*/
/**/
/*@media( max-width: 767px )*/
/*{*/
/*    .logo img*/
/*    {*/
/*        max-width: 90%;*/
/*        max-height: 120px;*/
/*    }*/
/**/
/*    .menu .links > div*/
/*    {*/
/*        width: 100%;*/
/*    }*/
/*}*/
/**/
/*@media( max-width: 1199px )*/
/*{*/
/*    .menu .links > div*/
/*    {*/
/*        width: 25%;*/
/*    }*/
/*}*/
/**/
/*@media( max-width: 991px )*/
/*{*/
/*    .menu .links > div*/
/*    {*/
/*        width: 30%;*/
/*    }*/
/*}*/
/**/
/*@media( max-width: 767px )*/
/*{*/
/*    .logo img*/
/*    {*/
/*        max-width: 100%;*/
/*    }*/
/**/
/*    header .dropdown ul.dropdown-user*/
/*    {*/
/*        right: -60px;*/
/*    }*/
/*}*/
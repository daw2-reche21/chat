<?php

//update_last_activity.php
require("class.pdofactory.php");
require("abstract.databoundobject.php");
require("class.LoginDetails.php");

        //CONEXIO A LA BASE DE DADES
        $strDSN = "mysql:dbname=chat;host=localhost;port=3306";
        $objPDO = PDOFactory::GetPDO($strDSN, "pere", "root",array());
        $objPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

session_start();

$obj=new LoginDetails($objPDO, $_SESSION["login_details_id"]);

$hoy = getdate();

$obj->setLast_activity($hoy)->save;


?>



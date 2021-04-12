<?php

//update_is_type_status.php

require("class.pdofactory.php");
require("abstract.databoundobject.php");
require("class.Chatmessage.php");

        //CONEXIO A LA BASE DE DADES
        $strDSN = "mysql:dbname=chat;host=localhost;port=3306";
        $objPDO = PDOFactory::GetPDO($strDSN, "pere", "root",array());
        $objPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$chatId=$_POST["chat_message_id"];
echo $chatId; 
session_start();
if(isset($_POST["chat_message_id"]))
{
        $Obj=new Chatmessage($objPDO,$chatId);
        $Obj->setStatus(2)->save();
}

?>
<?php
class ChatMessage extends DataBoundObject{

        protected $From_user_id;
        protected $Chat_message;
        protected $Timestamp;
        protected $Status;
       
//metodes on definim el nom de la taula i fem el mapeiat amb la bd i els atributs definits anteriorment
        protected function DefineTableName() {
                return("chat_message");
        }

        protected function DefineRelationMap() {
                return(array(
                        "id" => "ID",
                        "to_user_id" => "To_user_id",
                        "from_user_id" => "From_user_id",
                        "chat_message" => "Chat_message",
                        "timestamp" => "Timestamp",
                        "status" => "Status"
                        ));
        }
    }
?>
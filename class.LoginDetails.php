<?php
class LoginDetails extends DataBoundObject{

 

        protected $User_id;
        protected $Last_activity;
        protected $Is_type;
       
       
//metodes on definim el nom de la taula i fem el mapeiat amb la bd i els atributs definits anteriorment
        protected function DefineTableName() {
                return("login_details");
        }

        protected function DefineRelationMap() {
                return(array(
                        "id" => "ID",
                        "user_id" => "User_id",
                        "last_activity" => "Last_activity",
                        "is_type" => "Is_type"
                        ));
        }
    }
?>
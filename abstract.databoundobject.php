<?php

abstract class DataBoundObject { //clase abstracta que tindran totes les clases creades 

    //atributos para la conexión con la base de datos 
   protected $ID;
   protected $objPDO;
   protected $strTableName;
   protected $arRelationMap;
   protected $blForDeletion;
   protected $blIsLoaded;
   protected $arModifiedRelations;


   //metodes que hauran d'estar definitis obligatoriament en la clase User(en aquest cas)
   abstract protected function DefineTableName(); //definir nom de la taula
   abstract protected function DefineRelationMap(); //definir mapeiat

   public function __construct(PDO $objPDO, $id = NULL) { //funcio per construir qualsevol objecte amb dos parametres, 1r conexio amb la bd, 2n id(opcional)
      $this->strTableName = $this->DefineTableName(); //definir nom de la taula amb la funcio
      $this->arRelationMap = $this->DefineRelationMap(); //definim el mapeiat amb la funcio
      $this->objPDO = $objPDO; //conexio a la bd
      $this->blIsLoaded = false; //quan es crea no es carrega directament
      if (isset($id)) { //si hi ha id es posa la id 
         $this->ID = $id;
      };
      $this->arModifiedRelations = array(); //es crea un array buit 
   }

   public function Load() { //funcio que fa un select de tota la taula i atributs
      if (isset($this->ID)) {
		$strQuery = "SELECT ";
        foreach ($this->arRelationMap as $key => $value) {
			$strQuery .= "" . $key . ",";
        }
        $strQuery = substr($strQuery, 0, strlen($strQuery)-1);
        $strQuery .= " FROM `" . $this->strTableName . "` WHERE id = :eid";
        $objStatement = $this->objPDO->prepare($strQuery);
        $objStatement->bindParam(':eid', $this->ID, PDO::PARAM_INT);
        $objStatement->execute();
        $arRow = $objStatement->fetch(PDO::FETCH_ASSOC);
        foreach($arRow as $key => $value) {
            $strMember = $this->arRelationMap[$key];
            if (property_exists($this, $strMember)) {
                if (is_numeric($value)) {
                   eval('$this->'.$strMember.' = '.$value.';');
                } else {
                   eval('$this->'.$strMember.' = "'.$value.'";');
                };
            };
         };
         $this->blIsLoaded = true; //despres de rebre tots els atributs i definir-los es carrega
      };
   }

   public function Save() { //funcio que fa un update dels atributs o si no està creat l'objecte fa un insert 
      if (isset($this->ID)) {
         $strQuery = 'UPDATE `' . $this->strTableName . '` SET ';
         foreach ($this->arRelationMap as $key => $value) {
            eval('$actualVal = &$this->' . $value . ';');
            if (array_key_exists($value, $this->arModifiedRelations)) {
               $strQuery .= '' . $key . " = :$value, ";
            };
         }
         $strQuery = substr($strQuery, 0, strlen($strQuery)-2);
         $strQuery .= ' WHERE id = :eid';
         unset($objStatement);
         $objStatement = $this->objPDO->prepare($strQuery);
         $objStatement->bindValue(':eid', $this->ID, PDO::PARAM_INT);
         foreach ($this->arRelationMap as $key => $value) {
            eval('$actualVal = &$this->' . $value . ';');
            if (array_key_exists($value, $this->arModifiedRelations)) {
               if ((is_int($actualVal)) || ($actualVal == NULL)) {
                  $objStatement->bindValue(':' . $value, $actualVal,PDO::PARAM_INT);
               } else {
                  $objStatement->bindValue(':' . $value, $actualVal,PDO::PARAM_STR);
               };
            };
         };
         $objStatement->execute();
      } else {
         $strValueList = "";
         $strQuery = 'INSERT INTO `' . $this->strTableName . '`(';
         foreach ($this->arRelationMap as $key => $value) {
            eval('$actualVal = &$this->' . $value . ';');
            if (isset($actualVal)) {
               if (array_key_exists($value, $this->arModifiedRelations)) {
                  $strQuery .= '' . $key . ', ';
                  $strValueList .= ":$value, ";
               };
            };
         }
         $strQuery = substr($strQuery, 0, strlen($strQuery) - 2);
         $strValueList = substr($strValueList, 0, strlen($strValueList) - 2);
         $strQuery .= ") VALUES (";
         $strQuery .= $strValueList;
         $strQuery .= ")";

         unset($objStatement);
         $objStatement = $this->objPDO->prepare($strQuery);
         foreach ($this->arRelationMap as $key => $value) {
            eval('$actualVal = &$this->' . $value . ';');
            if (isset($actualVal)) {   
               if (array_key_exists($value, $this->arModifiedRelations)) {
                  if ((is_int($actualVal)) || ($actualVal == NULL)) {
                     $objStatement->bindValue(':' . $value, $actualVal, PDO::PARAM_INT);
                  } else {
                     $objStatement->bindValue(':' . $value, $actualVal, PDO::PARAM_STR);
                  };
               };
            };
         }
         $objStatement->execute();
         $this->ID = $this->objPDO->lastInsertId($this->strTableName . "_id_seq");
   }
}
   

   public function MarkForDeletion() { //funcio que  fa una comprobació abans d'eliminar l'objecte
      $this->blForDeletion = true;
   }
   
   public function __destruct() { //elimina l'objecte dins la taula
      if (isset($this->ID)) {   
         if ($this->blForDeletion == true) {
            $strQuery = 'DELETE FROM ' . $this->strTableName . ' WHERE id = :eid';
            $objStatement = $this->objPDO->prepare($strQuery);
            $objStatement->bindValue(':eid', $this->ID, PDO::PARAM_INT);   
            $objStatement->execute();
         };
      }
   }
//funcion magica call que recibe 2 parametros: el nombre de la funcion y el contenido en forma de array
   public function __call($strFunction, $arArguments) {
    ////con la funcion sbstr(se divide el nombre de la funcion y se guardan las 3 primeras posiciones, set o get)
//se divide la funcion y se escoge a partir de la posicion 3, da nombre al atributo 
//segun el caso set o get se llama a la funcion SetAccessor  o Get accesor
//funcion que recibe dos parametros, el atributo y la primera posicion del array del contenido a introducir 
//solamente recibe el atributo y lo muesta con la funcion GetAccesor
//si algo está mal solo devuelve false

      $strMethodType = substr($strFunction, 0, 3);
      $strMethodMember = substr($strFunction, 3);
      switch ($strMethodType) {
         case "set":
            return($this->SetAccessor($strMethodMember, $arArguments[0]));
            break;
         case "get":
            return($this->GetAccessor($strMethodMember));   
      };
      return(false);   
   }

   private function SetAccessor($strMember, $strNewValue) {//funcion que inserta los datos en el atributo recibiendo el nombre del atributo y el dato a introducir
//si existe el atributo
//si es numerico se inserta sin comillas dobles el dato en el atributo
//si es string se inserta con comillas dobles
//si no devuelve falso
      if (property_exists($this, $strMember)) {
         if (is_numeric($strNewValue)) { 
            eval('$this->' . $strMember . ' = ' . $strNewValue . ';');
         } else {
            eval('$this->' . $strMember . ' = "' . $strNewValue . '";');
         };
         $this->arModifiedRelations[$strMember] = "1";
         return $this;
      } else {
         return(false);
      };   
   }


   private function GetAccessor($strMember) {
      //funcion que inserta los datos en el atributo recibiendo el nombre del atributo y el dato a introducir
//si existe el atributo
//si es numerico se inserta sin comillas dobles el dato en el atributo
//si es string se inserta con comillas dobles
//si no devuelve falso
//funcion que muestra el dato del atributo del objeto al recibir solo el atributo
//si existe el atributo muestra los datos con la funcion eval()en una variable
//si no existe el atribute devuelve false
      if ($this->blIsLoaded != true) {
         $this->Load();
      }
      if (property_exists($this, $strMember)) {
         eval('$strRetVal = $this->' . $strMember . ';');
         return($strRetVal);
      } else {
         return(false);
      };   
   }
   
}

?>
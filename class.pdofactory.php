<?php
global $PDOS; //variable on es guarden les conexions

class PDOFactory { //clase per crear una conexio on reb 4 parametres: base de dades, usuari, contrasenya i un array amb parametres del usuari
	public static function GetPDO( $strDSN, $strUser, $strPass, $arParms) {
		
		$strKey = md5(serialize(array( $strDSN, $strUser, $strPass, $arParms))); //a la variable strKey es crea una clau unica per la conexio
		
		if (!( $GLOBALS["PDOS"][ $strKey] instanceof PDO)) { //si la conexio no existeix a la variable PDO llavors es crea 
			$GLOBALS["PDOS"][ $strKey] = new PDO( $strDSN, $strUser, $strPass, $arParms); //es guarda la conexio dins del array PDOS amb la clau abans creada
		};
		
		return($GLOBALS["PDOS"][$strKey]); //retorna la conexio creada o que ja existia
	}
}
?>
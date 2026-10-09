<?php

define ("EMERGENCY",0);
define("ALERT",1);
define("CRITICAL",2);
define("ERROR",3);
define("WARNING",4);
define("NOTICE",5);
define("INFO",6);
define("DEBUG",7);



/**
* Clase para escribir logs desde php
*/
class Logger{

    
    
    const EMERGENCY = 0;
    const ALERT = 1;
    const CRITICAL = 2;
    const ERROR = 3;
    const WARNING = 4;
    const NOTICE = 5;
    const INFO = 6;
    const DEBUG = 7;
    
	/**
	 * Texto para los niveles del log
	 * @var array
	 */
	private $types   = array('emergency','alert','critical','error','warning','notice','info','debug');

	/**
	 * Formato de la fecha del log
	 * @var string
	 */
	private $dateFormat = 'd.m.Y h:i:s';
    private $errorLevel = WARNING; // WARNING

	/**
	 * Funcion para escribir los logs
	 * @param  string  $message     Cadena de texto con el mensaje se desea mandar al log
	 * @param  integer $type        Nivel del error con el que queremos etiquetar el log
	 * @param  string  $destination Cadena de texto con el email destinatario del log o la ruta absoluta del archivo donde se desea almacenar el log
	 * @param  array   $headers     Arreglo asociativo con las cabeceras adicionales correspondientes a un email
	 * @return void
	 */
	public function log($source, $msg='',$type = 3)	{
        if ($type <= $this->getErrorLevel()){
            $message  = "[".$this->getDate()."][$source][".$this->getType($type)."] $msg";
            error_log($message.PHP_EOL, 3, $this->getPathFile("my-errors.log"));
        }
	}

	/**
	 * Devuelve la fecha para el log
	 * @return string
	 */
	private function getDate()
	{
		return date($this->dateFormat);
	}

	private function getErrorLevel()
	{
		return $this->errorLevel;
	}

    private function getPathFile($fileName){
        return dirname(__FILE__)."/".$fileName;
    }

	/**
	 * Devuelve una cadena de texto con el tipo de log
	 * @param  integer $type Numero de tipo de log que se desea
	 * @return string
	 */
	private function getType($type)
	{
		return $this->types[$type<0||$type>7?3:$type];
	}


}
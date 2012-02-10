<?php  defined('BASEPATH') OR exit('No direct script access allowed');

function LdapErrorHandler($errno, $errstr, $errfile, $errline)
{
	static $OUT_OF_RANGE_ERROR_CODE = 8;
	
	switch ($errno) {
		case 8:
			log_message('DEBUG','!!#### '.$errstr.' ##### '.$errfile.' '.$errline);
			try{
				if(true) throw new OutOfRangeException($errstr, 0);
			}catch (Exception $e) {
				return $e;
			}
		break;
		
		default:
			log_message('DEBUG','##### '.$errstr.' ##### '.$errfile.' '.$errline);
		break;
	}

	return false;
}
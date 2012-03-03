<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// Created on Oct 9, 2011 by Damiano Venturin @ Squadra Informatica

function isTime($time) {
	return preg_match("#([0-1]{1}[0-9]{1}|[2]{1}[0-3]{1}):[0-5]{1}[0-9]{1}#", $time,$matches);
}

function methods_HTML($methods,$object)
{
	$methods_html = '<h4>Object: '.$object.'</h4>';
	$methods_html .= '<dl>';
	foreach ( $methods[$object]['functions'] as  $method) {
		if(empty($method->docstring))
		{
			$methods_html .= '<dt>'.$method->function.'</dt><dd>No description available</dd>';
		} else {
			$methods_html .= '<dt>'.$method->function.'</dt><dd>'.$method->docstring.'</dd>';
		}
	}
	$methods_html .= '</dl>';
	return $methods_html;
}

function dimensions($input)
{
	if(!is_array($input)) return null;
	
	foreach ($input as $key => $value) {
		return is_array($value) ? 2 : 1;
	}
}

function get_PHP_error_codes()
{
	$php_error_numbers = array(
								'2' 	=> 'E_WARNING',
								'8' 	=> 'E_NOTICE',
								'256' 	=> 'E_USER_ERROR',
								'512' 	=> 'E_USER_WARNING',
								'1024' 	=> 'E_USER_NOTICE',
								'4096' 	=> 'E_RECOVERABLE_ERROR',
								'8191' 	=> 'E_ALL'		
	);
	
	return $php_error_numbers;
}

//Check: http://restpatterns.org/HTTP_Status_Codes
function get_HTTP_status_codes()
{
	$informational = array( '100' => 'Continue',
							'101' => 'Switching Protocols'
	);
	
	$success = array(
					 '200' => 'OK',
					 '201' => 'Created',
					 '202' => 'Accepted',
					 '203' => 'Non-Authoritative Information',
					 '204' => 'No Content',
					 '205' => 'Reset Content',
					 '206' => 'Partial Content',
					 '207' => 'Multi-Status',
	);
	
	$redirection = array(
						 '300' => 'Multiple Choices',
						 '301' => 'Moved Permanently',
						 '302' => 'Found',
						 '303' => 'See Other',
						 '304' => 'Not Modified',
						 '305' => 'Use Proxy',
						 '307' => 'Temporary Redirect',
	);
	
	$client_error = array(
						 '400' => 'Bad Request',
						 '401' => 'Unauthorized',
						 '402' => 'Payment Required',
						 '403' => 'Forbidden',
						 '404' => 'Not Found',
						 '405' => 'Method Not Allowed',
						 '406' => 'Not Acceptable',
						 '407' => 'Proxy Authentication',
						 '408' => 'Request Timeout',
						 '409' => 'Conflict',
						 '410' => 'Gone',
						 '411' => 'Length Required',
						 '412' => 'Precondition Failed',
						 '413' => 'Request Entity Too Large',
						 '414' => 'Request-URI Too Long',
						 '415' => 'Unsupported Media Type',
						 '416' => 'Requested Range Not Satisfiable',
						 '417' => 'Expectation Failed',
						 '422' => 'Unprocessable Entity',
						 '423' => 'Locked',
						 '424' => 'Failed Dependency',
	);
	
	$server_error = array(
						 '500' => 'Internal Server Error',
						 '501' => 'Not Implemented',
						 '502' => 'Bad Gateway',
						 '503' => 'Service Unavailable',
						 '504' => 'Gateway Timeout',
						 '505' => 'HTTP Version Not Supported',
						 '507' => 'Insufficient Storage',
	);

	$http_status_codes = array(
								'all' => $informational + $success + $redirection + $client_error + $server_error,
								'all_errors' => $redirection + $client_error + $server_error,
								'informational' => $informational,
								'success' => $success,
								'redirection' => $redirection,
								'client_error' => $client_error,
								'server_error' => $server_error
	);
	
	return $http_status_codes;
}
/* End of restigniter_helper.php */
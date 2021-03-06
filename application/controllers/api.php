<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Example
 *
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array.
 *
 * @package		CodeIgniter
 * @subpackage	Rest Server
 * @category	Controller
 * @author		Phil Sturgeon
 * @link		http://philsturgeon.co.uk/code/
 */

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require APPPATH.'/libraries/REST_Controller.php';

class Api extends REST_Controller
{	
	protected $builtInMethods;
	
	//protected $conf;
	protected $started;
	protected $finished;
	protected $duration;
	protected $status_code;
	protected $error_message;
	
	protected $exposedObjects = array();

	public function __construct($class=null)
	{
		date_default_timezone_set('Europe/Rome');
		$this->started = microtime(true);
		
		parent::__construct();
		
		//a bit of security
		$this->config->load('restigniter');
		if($this->config->item('only_localhost')) {
			if($_SERVER['REMOTE_ADDR'] != '127.0.0.1' && $_SERVER['REMOTE_ADDR'] != '127.0.1.1') return false;
		}		
		
		$this->__getExposedObjs();
		
		$this->loadSparks();
		
		$this->__getMyMethods();
	}

	public function __getExposedObjs()
	{
		$exposedObjects = $this->config->item('exposeObj');
		
		if(!empty($exposedObjects)) $this->exposedObjects = $exposedObjects;
	}
	
	private function loadSparks()
	{
		if(!empty($this->exposedObjects))
		{
			foreach ($this->exposedObjects as $item => $value) {
				if(preg_match('/\//i', $value))
				{
					$this->load->spark($value);
				}
			}
			
		}
	}
	
	/**
	 *
	 * Analizes self methods using reflection
	 * @return Boolean
	 */
	private function __getMyMethods($object = null)
	{
		if(is_null($object))
		{
			$obj = $this;
		} else {
			if($this->remoteObject($object))
			{
				$obj = $this->$object;
			} else {
				return false;
			}
		}
		
		$builtInMethods = array();
		
		$reflection = new ReflectionClass($obj);

		//get all methods
		$methods = $reflection->getMethods();

		//get properties for each method
		if(!empty($methods))
		{
			foreach ($methods as $method) {
				if(!empty($method->name))
				{
					$methodProp = new ReflectionMethod($obj, $method->name);
						
					//saves all methods names found
					$builtInMethods['all'][] = $method->name;
						
					//saves all private methods names found
					if($methodProp->isPrivate())
					{
						$builtInMethods['private'][] = $method->name;
					}
						
					//saves all private methods names found
					if($methodProp->isPublic())
					{
						$builtInMethods['public'][] = $method->name;

						// gets info about the method and saves them. These info will be used for the xmlrpc server configuration.
						// (only for public methods => avoids also all the public methods starting with '_')
						if(!preg_match('/^_/', $method->name, $matches))
						{
							//consider only the methods having "_" inside their name
							if(preg_match('/_/', $method->name, $matches))
							{
								//don't consider the methods get_instance and validation_errors
								if($method->name != 'get_instance' AND $method->name != 'validation_errors')
								{
									// -method name: user_get becomes [GET] user
									$name_split = explode("_", $method->name);
									$builtInMethods['functions'][$method->name]['function'] = $name_split['0'].' [method: '.$name_split['1'].']';
										
									// -method DocString
									$builtInMethods['functions'][$method->name]['docstring'] =  $this->__extractDocString($methodProp->getDocComment());
								}
							} else {
								//documentation for the exposed Object
								if(!is_null($object))
								{
									// -method name: user_get becomes [GET] user
									$builtInMethods['functions'][$method->name]['function'] = $method->name;
										
									// -method DocString
									$builtInMethods['functions'][$method->name]['docstring'] =  $this->__extractDocString($methodProp->getDocComment());
								}
							}
						}
					}
				}
			}
		} else {
			return false;
		}
		
		if(is_null($object)) 
		{ 
			$this->builtInMethods = $builtInMethods;
		} else {
			$this->builtInMethods[$object] = $builtInMethods;
		}
		
		return true;
	}

	/**
	 *
	 * Manipulates a DocString and returns a readable string
	 * @param String $DocComment
	 * @return Array $_tmp
	 */
	private function __extractDocString($DocComment)
	{
		$split = preg_split("/\r\n|\n|\r/", $DocComment);
		$_tmp = array();
		foreach ($split as $id => $row)
		{
			//clean up: removes useless chars like new-lines, tabs and *
			$_tmp[] = trim($row, "* /\n\t\r");
		}
		return trim(implode("\n",$_tmp));
	}

	/**
	 * 
	 * Returns the list of all the RestIgniter API built-in methods
	 */
	public function methods_get()
	{
		$input = $this->getInput();
		
		if(!empty($input['object']) and $this->loadObject($input['object']))
		{
			//make the analysis of the required object
			$this->__getMyMethods($input['object']);
		} else {
			//make the analysis of the REST server itself
			$this->__getMyMethods();				
		}
		
		//return the methods
		$this->response($this->builtInMethods, 200);
		
	}

	/**
	 *
	 * Removes from the given string everything different from characters and numbers
	 * @param string $string
	 */
	private function __cleanString($string)
	{
		if(empty($string) or is_array($string))
		{
			return false;
		} else {
			return preg_replace("[^A-Za-z0-9]", "", $string);
		}
	}
	
	
	/**
	 * 
	 * Grabs all the parameters from $this->get,put,post,delete and merges them together
	 */
	private function getInput()
	{
		$output = array();
		$httpMethods = array('delete','get','post','put');
		foreach ($httpMethods as $httpMethod)
		{
			if(count($this->$httpMethod()) > 0)
			{
				$output = array_merge($output, $this->$httpMethod());
			}
		}
		return $output;
	}

	/**
	 * 
	 * If the first segment of the url contains 'exposeObj' then the controller tries to expose the specified the method (segment3) 
	 * of the specified class (segment2) returning the result with the format specified in segment4
	 * @param string $segment1	It's the first segment of the url
	 * @param array $params		All the segmnents after the first one
	 */
	public function _remap($segment1,$params=array())
	{
		//if not required to expose an object call back the parent _remap
		if($segment1 != 'exposeObj')
		{
			parent::_remap($segment1);
			return;
		}

		// => it's required to expose the object
		$model = $this->__cleanString($this->uri->segment(3,false));
		$calledMethod = $this->__cleanString($this->uri->segment(4,false));
		$format = $this->__cleanString($this->uri->segment(6,'xml'));  //default is xml
		
		if($model and $calledMethod) $this->remoteObject($model, $calledMethod, $this->getInput());
	}
	
	/**
	 * 
	 * Discovers and load the object to expose over REST
	 * @param string $model The name of the object class
	 * @return boolean
	 */
	private function loadObject($model)
	{
		//looks like there is no better way to check if a model exists. Check this:
		//http://stackoverflow.com/questions/7017810/php-and-codeigniter-how-do-you-check-if-a-model-exists-and-or-not-throw-an-error
		if(isset($this->exposedObjects[$model]) & file_exists(APPPATH."models/$model.php"))	
		{
			$input = array('a' => 'b');
			$this->load->model($model,'',$input);
			return true;
		}

		//it might be an object contained in a spark but then it should be already loaded by the autoload file. Let's check
		return is_object($this->$model) ? true : false; 
	}
	
	
	/**
	 * 
	 * This is the core of RestIgniter. 
	 * Loads the requested object and via reflection exposes the requested method over REST.
	 * If the method doesn't exist for the object return an error
	 * If the method is not specified returns true
	 * 
	 * @param string $model
	 * @param string $calledMethod
	 * @param array $input
	 */
	private function remoteObject($model, $calledMethod, $input=array())
	{
		if(!empty($model) and is_array($model)) return false;
		
		if(!$this->loadObject($model)) return false; // TODO maybe this might be more meaninful than just false
			
		//Using reflection to get the class methods
		$reflection = new ReflectionClass($this->$model);
			
		//get all methods
		$methods = $reflection->getMethods();

		//call the object method and return via REST
		if(!empty($methods))
		{
			foreach ($methods as $method) 
			{
				if(!empty($method->name))
				{
					$methodProp = new ReflectionMethod($this->$model, $method->name);
						
					if($methodProp->isPublic())
					{
						// passes the input to the called method of the exposed object
						if(!preg_match('/^_/', $method->name, $matches))
						{
							$method_short_name = strstr($method->name, '_',true); //remove _get _post _delete _update if present otherwise returns false
							if(!$method_short_name) $method_short_name = $method->name;
							$method_name = $method->name;
							if($method_short_name == $calledMethod)
							{					

								//DEBUG POINT
								$output = $this->$model->$method_name($input);

								$this->finished = microtime(true);
								$this->duration = $this->finished - $this->started;

								
								$output['status']['finished'] = $this->finished;
								 
								$output['status']['duration'] = $this->duration;
								
								$this->response($output, $output['status']['status_code']);

							}
						}
					}		
				}
			}
			
			//if we are still here than a wrong method has been passed
			if(!empty($calledMethod))
			{
				$this->result = new Ce_Return_Object();
				$this->result->data = array();
				$this->result->status_code = '404';
				$this->result->message = 'The object '.$model.' has no public method called '.$calledMethod;
				$this->result->results_number = '0';
				$this->result->results_got_number = '0';
				
				return $this->result->returnAsArray();
			}			
		}
		return true;
	}
}
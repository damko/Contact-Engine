<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// Created on Sep 8, 2011 by Damiano Venturin @ squadrainformatica.com

class Ce {
	//TODO probably this should go into a config file
	private $other_sub_attrs = array('syntax', 'single-value', 'desc', 'no-user-modification', 'max-length');
	
	//here there is the syntax list for the inetOrgPerson attributes http://www.alvestrand.no/objectid/1.3.6.1.4.1.1466.115.121.1.html 
	private $binary_syntaxes = array(
										'1.3.6.1.4.1.1466.115.121.1.5', //binary
										'1.3.6.1.4.1.1466.115.121.1.23', //fax - tif
										'1.3.6.1.4.1.1466.115.121.1.28', //jpeg
									);
	
	private $boolean_syntaxes = array('1.3.6.1.4.1.1466.115.121.1.7'); 

	private $integer_syntaxes = array('1.3.6.1.4.1.1466.115.121.1.27');
		
	public function __construct() {
	
	}
	
	public function __destruct() {
		$CI =& get_instance();
		$CI->ri_ldap->__destruct();
	}
	
	public function loadClassAttributes($object_class, &$class, $callback, $period = NULL){
		$xml_file = APPPATH.'xml/'.$object_class.'.xml';
		if(file_exists($xml_file))
		{
			$this->parseXml($object_class, $xml_file, &$class, $callback, $period);
		} else {
			if($this->readSchemaWriteXml($object_class))
			{
				$this->loadClassAttributes($object_class, &$class, $callback, $period);
				return true;
			} else {
				log_message('debug', 'The xml file can not be written on the filesystem. Please check folder permissions.');
				return false;
			}
		}	
	}
	
	private function readSchemaWriteXml($object_class)
	{
		//Benchmark
		//$this->benchmark->mark('code_start');

		//load the zend_ldap class which is capable to dig into the rootDse and get the required schema
		$CI =& get_instance();
		//$CI->load->model('zend/Ldap/Zend_Ldap','zend_ldap');

		//let's connect to a LDAP server possibly a slave one
		//TODO this is a quick and dirty. Can be improved
		$servers = $CI->ri_ldap->getServers();
		if(!empty($servers['slave'][0]['url']))
		{
			$ldap_url = $servers['slave'][0]['url'];
			$username = $servers['slave'][0]['binddn'];
			$password = $servers['slave'][0]['bindpw'];
			
		} else {
			$ldap_url = $servers['master'][0]['url'];
			$username = $servers['master'][0]['binddn'];
			$password = $servers['master'][0]['bindpw'];
		}
		$CI->zend_ldap->connect($ldap_url);
		
		$opts = array('username' => $username,
						'password' => $password);
		$CI->zend_ldap->setOptions($opts);
		
		//get the rootDse: not needed. Stays here in case of troubles
		//$rootDse = $CI->zend_ldap->getRootDse();
		
		//get the schema, the objectClasses and the attributeTypes
		$schema = $CI->zend_ldap->getSchema();
		$object_classes = $schema->getObjectClasses();
		$attribute_types = $schema->getAttributeTypes();
		
		if(empty($object_classes) or empty($attribute_types)) return false;
		
		//find the superior objectClass for every objectClass involved
		$sups = $this->getSups($object_classes,$object_class);
		
		//get the attributes for all the objectClasses involved
		$object_class_attributes = array_merge($object_classes[$object_class]['must'],$object_classes[$object_class]['may']);
		$object_class_attributes_required = $object_classes[$object_class]['must'];
		foreach ($sups as $sup)
		{
			$object_class_attributes = array_merge($object_class_attributes, array_merge($object_classes[$sup]['must'],$object_classes[$sup]['may']));
			if($sup != "top") $object_class_attributes_required = array_merge($object_class_attributes_required, $object_classes[$sup]['must']);
		}		
		sort($object_class_attributes);
				
		//write the xml content
		$xml = new DOMDocument("1.0", "UTF-8");
		$xml_class = $xml->createElement('objectClass');
		$xml_class->appendChild($xml->createElement('name', $object_class));
		$xml_class->appendChild($xml->createElement('lastAttributesUpdate', time()));		
		//$xml_attributes = $xml->createElement("attributes");
		foreach ($object_class_attributes as $attribute) {
			$xml_attribute = $xml->createElement('attribute', $attribute);
			$attribute_node = $xml_class->appendChild($xml_attribute);

			//add sub-attributes to the attribute node
			//required
			in_array($attribute, $object_class_attributes_required) ? $attribute_node->setAttribute("required",1) : $attribute_node->setAttribute("required",0);
			//binary
			in_array($attribute, $this->binary_syntaxes) ? $attribute_node->setAttribute("binary",1) : $attribute_node->setAttribute("binary",0);
			//boolean
			in_array($attribute, $this->boolean_syntaxes) ? $attribute_node->setAttribute("boolean",1) : $attribute_node->setAttribute("boolean",0);
			//integer
			in_array($attribute, $this->integer_syntaxes) ? $attribute_node->setAttribute("integer",1) : $attribute_node->setAttribute("integer",0);
			//additional sub attributes specified in the $this->other_sub_attrs array 
			foreach ($this->other_sub_attrs as $sub_attr) {
				$attribute_node->setAttribute($sub_attr,$attribute_types[$attribute][$sub_attr]);
			}
		}
		$xml->appendChild($xml_class);
		
		$dest = APPPATH.'xml/';
		if(!is_dir($dest))
		{
			if(!mkdir($dest))
			{
				log_message('debug', 'The directory '.$dest.' can not be created on the filesystem. Please check folder permissions.');
				return false; 
			}
		}

		//Benchmark
		//$this->benchmark->mark('code_end');
		//echo $this->benchmark->elapsed_time('code_start', 'code_end');
		
		//write the xml file
		$dest = APPPATH.'xml/'.$object_class.'.xml';
		return $xml->save($dest) ? TRUE : FALSE;		
	}	
	
	private function getSups(array $object_classes, $object_class, array $sups = null)
	{
		if(is_null($sups))
		{
			$sups = array();
		}

		foreach ($object_classes[$object_class]['sup'] as $sup) {
			if(!in_array($sup, $sups))
			{
				$sups[] = $sup;
				$sups = $this->getSups($object_classes, $sup, $sups);
			}
		}
		return $sups;
	}	
	
	private function parseXml($object_class, $xml_file, &$class, $callback, $period = NULL)
	{
		$xml = simplexml_load_file($xml_file);
		
		//the xml could be outdated: let's remake it
		if(is_null($period)) $period = 86400; //one day
		if((time() - (string)$xml->lastAttributesUpdate)> $period)
		{
			unlink($xml_file);
			$this->readSchemaWriteXml($object_class);
			$this->parseXml($object_class, $xml_file, $class, $callback);
			return;
		}
		
		foreach ($xml->attribute as $element)
		{
			$element_subattrs = array();
			foreach ($element->attributes() as $item => $value) {
				$element_subattrs[$item] = (string) $element->attributes()->$item;
			}
			$class->$callback((string) $element,  $element_subattrs);
		}
	}
}

/* End of ce.php */
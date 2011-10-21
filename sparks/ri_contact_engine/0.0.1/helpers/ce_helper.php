<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// Created on Oct 21, 2011 by Damiano Venturin @ squadrainformatica.com

function pagination_setup($input)
{
	log_message('debug','Ce_helper has been loaded');
	
	$return = array();
	
	if(isset($input['filter'])) $return['filter'] = $input['filter'];
	
	//who wants empty_fields in return has to specify it otherwise they will be skipped
	isset($input['empty_fields']) ? $return['empty_fields'] = $input['empty_fields'] : $return['empty_fields'] = FALSE;
	isset($input['sort_by']) ? $return['sort_by'] = $input['sort_by'] : $return['sort_by'] = NULL; //$sort_by = array('sn');
	isset($input['flow_order']) ? $return['flow_order'] = $input['flow_order'] : $return['flow_order'] = 'asc';
	isset($input['wanted_page']) ? $return['wanted_page'] = $input['wanted_page'] : $return['wanted_page'] = NULL;
	isset($input['items_page']) ? $return['items_page'] = $input['items_page']  : $return['items_page'] = NULL;
	
	return $return;
}

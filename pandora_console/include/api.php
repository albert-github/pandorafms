<?php
//Pandora FMS- http://pandorafms.com
// ==================================================
// Copyright (c) 2005-2011 Artica Soluciones Tecnologicas
// Please see http://pandorafms.org for full contribution list

// This program is free software; you can redistribute it and/or
// modify it under the terms of the  GNU Lesser General Public License
// as published by the Free Software Foundation; version 2

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

require_once ("config.php");
require_once("functions_api.php");

global $config;

define("DEBUG", 0);
define("VERBOSE", 0);


enterprise_include_once ('include/functions_enterprise_api.php');

$ipOrigin = $_SERVER['REMOTE_ADDR'];

//Get the parameters and parse if necesary.
$op = get_parameter('op');
$op2 = get_parameter('op2');
$id = get_parameter('id');
$id2 = get_parameter('id2');
$otherSerialize = get_parameter('other');
$otherMode = get_parameter('other_mode', 'url_encode');
$returnType = get_parameter('return_type', 'string');
$api_password = get_parameter('apipass', '');
$password = get_parameter('pass', '');
$user = get_parameter('user', '');

$other = parseOtherParameter($otherSerialize, $otherMode);

$apiPassword = db_get_value_filter('value', 'tconfig', array('token' => 'api_password'));

$correctLogin = false;
$user_in_db = null;
$no_login_msg = "";

if (isInACL($ipOrigin)) {
	if(empty($apiPassword) || (!empty($apiPassword) && $api_password === $apiPassword)) {
		$user_in_db = process_user_login($user, $password);
		if ($user_in_db !== false) {
			$config['id_user'] = $user_in_db;
			$correctLogin = true;
		}
		else {
			$no_login_msg = "Incorrect user credentials";
		}
	}
	else {
		$no_login_msg = "Incorrect given API password";
	}
}
else {
	$no_login_msg = "IP $ipOrigin is not in ACL list";
}

if ($correctLogin) {
	if (($op !== 'get') && ($op !== 'set') && ($op !== 'help'))
			returnError('no_set_no_get_no_help', $returnType);
	else {
		if (!function_exists($op.'_'.$op2))
			returnError('no_exist_operation', $returnType);
		else {
			if (!DEBUG) {
				error_reporting(0);
			}
			if (VERBOSE) {
				error_reporting(E_ALL);
				ini_set("display_errors", 1);
			}
			
			call_user_func($op.'_'.$op2, $id, $id2, $other, $returnType, $user_in_db);
		}
	}
}
else {
	db_pandora_audit("API access Failed", $no_login_msg, $user, $ipOrigin);
	echo 'auth error';
}
?>

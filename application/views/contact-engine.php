<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Contact Engine</title>

<style type="text/css">
body {
	background-color: #fff;
	margin: 40px;
	font-family: Lucida Grande, Verdana, Sans-serif;
	font-size: 14px;
	color: #4F5155;
}

a {
	color: #003399;
	background-color: transparent;
	font-weight: normal;
}

h1 {
	color: #000;
	background-color: transparent;
	border-bottom: 1px solid #D0D0D0;
	font-size: 24px;
	font-weight: bold;
	margin: 24px 0 2px 0;
	padding: 5px 0 6px 0;
}

h2 {
	background-color: transparent;
	font-size: 20px;
	font-weight: bold;
	margin: 0px;
	padding: 5px 0 6px 0;
}

code {
	font-family: Monaco, Verdana, Sans-serif;
	font-size: 12px;
	background-color: #f9f9f9;
	border: 1px solid #D0D0D0;
	color: #002166;
	display: block;
	margin: 14px 0 14px 0;
	padding: 12px 10px 12px 10px;
}

dt {
	font-weight: bold;
}

dd {
	margin-bottom: 10px;
}

pre {
	font-style: italic;
	margin-left: 10px;
	color: black;
	background-color: #999;
	padding: 6px;
}

div#container {
	float: left;
	width: 100%;
	background: #FFF;
}

div#right{
	float:right;
	display:inline;
	width:400px;
    /* border: 1px solid #CCC; */
    margin:5px;
    margin-top: 0px;
    background: #FFF;
} 

div#left{
    margin-top: 12px;
    background: #FFF;
} 
</style>
</head>
<body>

<h1>Contact Engine</h1>

<p>
It's an application meant to handle via REST informations about <b>People</b>, <b>Organizations</b>, <b>Locations</b> and 
the relationships between the three objects.<br/><br/>

Tecnically speaking Contact Engine is a Code Igniter Spark loaded into <a href="<?php echo site_url('home')?>">RestIgniter</a>.
</p>
<p>
The backend is LDAP and natively supports master-slave infrastructure.
</p>
<p>
The properties of the 3 objects are automatically set by parsing the ldap schema, so adding a new attribute to the schema gives a new attribute to the object.
It's something like the opposite of an ORM. You can play with the 3 objects as you like without rewriting a single line of code.
 
</p>
<div id="container">
    <div id="right">
    	<h2>References</h2>
    	<ul>
    		<li><a href="https://github.com/damko/Contact-Engine" target="_blank">Project Page</a></li>
    		<!--  <li><a href="<?php echo site_url('rest_client'); ?>">Documentation</a></li>  -->
    		<li><a href="<?php echo site_url('home/documentation'); ?>">Documentation</a></li>
    		<li><a href="<?php echo site_url('make_tests'); ?>">Tests Page</a></li>
    		<li><a href="https://github.com/damko/Contact-Engine/wiki/How-to-install" target="_blank">How to install</a></li>
    	</ul>
    	
    </div>
    <div id="left"><img src="images/contact-engine.png"></div>
</div>
<p>
<b>Project mantra:</b> <i>One contact, one record.</i><br/>
<b>Project mission:</b> <i>To make contacts unique data accessible from every application.</i>
</p>
 
</body>
</html>

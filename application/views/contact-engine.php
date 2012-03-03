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
<br/><br/>
<h2>What is Contact Engine</h2>
<p>
It's a middleware service meant to handle via API-REST information about <b>People</b>, <b>Organizations</b>, <b>Locations</b> and 
the relationships between the three objects.<br/>
<br/>
</p>

<div id="container">
    <div id="right" style="border-left: 1px dashed gray; padding-left: 10px;">
	<p>
	<b>Project mantra:</b> <i>One contact, one record.</i><br/>
	<b>Project mission:</b> <i>To make contacts data accessible from any application.</i>
	</p>
    	<h2>Sponsors</h2>
	<p>We gratefully thank our sponsors who are currently supporting the development of Contact Engine:</p>
	<ul>
		<li><a href="http://www.tradesmap.com" target="_blank"><img src="http://beesmap.com/wp-content/uploads/2012/01/tradesmap_logo1_smaller.png" /></a><br/>
		Would you like to be a <a href="http://beesmap.com/solutions/for-contributors/" target="_blank">contributor</a> too?
		</li>
	</ul>

    	<h2>References for developers</h2>
    	<ul>
    		<li><a href="https://github.com/damko/Contact-Engine" target="_blank">Code</a></li>
		<li><a href="https://github.com/damko/Contact-Engine/issues" target="_blank">Issues</a></li>
		<li><a href="https://github.com/damko/Contact-Engine/wiki">Wiki</a></li>
    		<li><a href="<?php echo site_url('home/documentation'); ?>">Documentation</a></li>
    		<li><a href="<?php echo site_url('ce_tests'); ?>">C.E. Unit Tests Page (verbose on)</a></li>
    		<li><a href="<?php echo site_url('ce_tests?verbose=off'); ?>">C.E. Unit Tests Page (verbose off)</a></li>
    		<li><a href="<?php echo site_url('rildap_tests'); ?>">Ri-Ldap Unit Tests Page</a></li>
    		<li><a href="https://github.com/damko/Contact-Engine/wiki/How-to-install" target="_blank">How to install</a></li>
    	</ul>
    	
	<h2>Related applications</h2>
	<ul>
		<li><a href="https://github.com/damko/RestIgniter" target="_blank">Rest Igniter</a>: CodeIgniter with REST support.</li>
		<li><a href="https://github.com/damko/ri_ldap" target="_blank">Ri Ldap</a>: CodeIgniter Spark LDAP library.</li>
		<li><a href="https://github.com/damko/ri_contact_engine" target="_blank">Ri Contact Engine</a>: CodeIgniter Spark application to handle people, organization and location.</li>
	</ul>
    	<h2>Applications using CE</h2>
    	<ul>
    		<li><a href="http://www.tradesmap.com" target="_blank">Tradesmap</a>: an innovative web application to find a tradesman who is in your area and to get estimated time of arrival, prices and rating.</li>
    		<li><a href="https://www.squadrainformatica.com/en/development#mcbsb">MCB-SB</a>: an invoice manager for small businesses based on <a href="http://www.myclientbase.com" target="_blank">MCB</a></li>
    	</ul>    	 	    	

	<h2>Roadmap</h2>
	<p>The next development stage will bring some other advantages:</p>
	<ul>
		<li>Built-in authentication support</li>
		<li>Built-in authentication support throught social networks</li>
		<li>Social networks data retrieving</li>
	</ul>

	<h2>Contribute</h2>
	<p>If you are a php developer willing to contribute to the project please feel free to <a href="mailto:contact@beesmap.com?subject=I want to contribute to Contact Engine">contact us</a>.</p>    	 	
    </div>
    <div id="left">
	<h2>Technical details</h2>
	<p>
	Contact Engine is a <a href="http://www.codeigniter.com" target="_blank">Code Igniter</a> application (with <a href="http://getsparks.org/" target="_blank">Spark</a>) loaded into <a href="<?php echo site_url('home')?>">RestIgniter</a>.
	</p>
	<p>
	The programming language is of PHP and the backend is LDAP and natively supports master-slave LDAP infrastructures.
	</p>

	<h2>How it works</h2>
	<p>
	Contact Engine acts like the opposite of a common <a href="http://en.wikipedia.org/wiki/Object-relational_mapping">ORM</a>: the properties (attributes) of the 3 objects are automatically set by parsing the ldap schema. For this reason, adding a new attribute to the schema for one o more objects is enough to add a new attribute to the object.<br/>
	You can refine the attributes of the 3 objects as you like without rewriting a single line of code.
	</p>

	<h2>Architecture</h2>
	<p>The architecture of CE allows data access through 2 possible path: via LDAP and via REST as shown in the picture below.</p>
	<img src="images/contact-engine.png"><br/><br/>

	<h2>Advantages</h2>
	<p>Because CE's architecture, it has some unique advantages:</p>
	<ul>		
		<li><b>Any web application can connect to CE</b>, doesn't matter the programming language used.</li>
		<li><b>One centralized repository for all your contacts</b></li>
		<li><b>Programmers do not reinvent the wheel</b>: all yours applications have a unique way to access contacts data and you can forget about it.</li>
		<li><b>Taking the advantage of the community</b>: updates of the code are immediately released and everybody can benefit.</li>
		<li><b>Opensourse code released under GPL</b>: in case of need you can modify the code as you like.</li>
		<li><b>You own your data</b>. Your data are on your server in your ldap.</li>
		<li><b><a href="https://www.forge.funambol.org" target="_blank">Funambol</a> ready</b>: All the contacts stored by CE can be handled (one way) by a Funambol server <b>without writing a single line of code</b>. In this way contacts data can be kept synchronized on mobile phones and desktop applications even if they are not ldap-aware or rest-aware (they still funambol connector though, but there is large support for devices).</li>
		<li><b>Strong and mature software</b>: CE is new and still in development but is based on solid and well tested software.</li>
	</ul>
    </div>
</div>
 
</body>
</html>
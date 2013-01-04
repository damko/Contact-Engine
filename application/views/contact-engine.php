<?php  include 'include/header.php'; ?>

<body>

<h1>Contact Engine</h1><br/>

<div id="container">
    <div id="right" style="border-left: 1px dashed gray; padding-left: 10px;">

    <h2>References for developers</h2>
    <ul>
    	<li><a href="https://github.com/damko/Contact-Engine" target="_blank">Code</a></li>
		<li><a href="https://github.com/damko/Contact-Engine/issues" target="_blank">Issues</a></li>
		<li><a href="https://github.com/damko/Contact-Engine/wiki/How-to-install" target="_blank">How to install</a></li>
    	<li><a href="<?php echo site_url('/documentation'); ?>">Documentation</a></li>
    	<li><a href="<?php echo site_url('unit_tests'); ?>">Unit Tests</a></li>
    </ul>
    
    <!-- 	
	<h2>Related applications</h2>
	<ul>
		<li><a href="https://github.com/damko/RestIgniter" target="_blank">Rest Igniter</a>: CodeIgniter with REST support.</li>
		<li><a href="https://github.com/damko/ri_ldap" target="_blank">Ri Ldap</a>: CodeIgniter Spark LDAP library.</li>
		<li><a href="https://github.com/damko/ri_contact_engine" target="_blank">Ri Contact Engine</a>: CodeIgniter Spark application to handle people, organization and location.</li>
	</ul>
    	<h2>Applications using CE</h2>
    	<ul>
    		<li><a href="http://www.mcbsb.com" target="_blank">MCB-SB</a>: a back office application for small businesses based on <a href="http://www.myclientbase.com" target="_blank">MCB</a></li>
    	</ul>    	 	    	

	<h2>Roadmap</h2>
	<p>The next development stage will bring some other advantages:</p>
	<ul>
		<li>Built-in authentication support</li>
		<li>Built-in authentication support throught social networks</li>
		<li>Social networks data retrieving</li>
	</ul>
     -->
     	
    </div>
    
<!-- --------------------------------------------  -->
    
    <div id="left" style="min-width: 650px; max-width: 650px;">    
	
	<h2>What is Contact Engine</h2>
	<p>
		It's a middleware service meant to handle via API-REST information about <b>People</b>, <b>Organizations</b>, 
		<b>Locations</b> and the relationships between the three objects.
	</p>    
	<p>
		<b>Project mantra:</b> <i>One contact, one record.</i><br/>
		<b>Project mission:</b> <i>To make contacts data accessible from any application.</i>
	</p>
	    
	<h2>Technical details</h2>
	<p>
		Contact Engine is a <a href="http://www.codeigniter.com" target="_blank">Code Igniter</a> 
		application (with <a href="http://getsparks.org/" target="_blank">Spark</a>) loaded into 
		<a href="https://github.com/damko/RestIgniter" target="_blank" >RestIgniter</a>.
	</p>
	
	<p>
		The programming language is of PHP and the backend is LDAP and natively supports master-slave LDAP infrastructures.
	</p>

	<h2>How it works</h2>
	<p>
		Contact Engine acts like the opposite of a common <a href="http://en.wikipedia.org/wiki/Object-relational_mapping">ORM</a>: 
		the properties (attributes) of the 3 objects are automatically set by parsing the ldap schema. For this reason, 
		adding a new attribute to the schema for one o more objects is enough to add a new attribute to the object.<br/>
		You can refine the attributes of the 3 objects as you like without rewriting a single line of code.
	</p>

	<h2>Architecture</h2>
	
	<p>
		The architecture of CE allows data access through 2 possible path: via LDAP and via REST as shown in the picture below.
		<img style="margin-top: 20px;" src="images/contact-engine.png">
	</p>

	<!-- 
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
	 -->
    </div>
</div>
 
</body>
</html>
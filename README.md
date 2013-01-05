##What is Contact Engine
It's a middleware service meant to handle via API-REST information about People, Organizations, Locations and the relationships between the three objects.

Project mantra: One contact, one record.

Project mission: To make contacts data accessible from any application.

##Technical details
Contact Engine is a Code Igniter application (with Spark) loaded into RestIgniter.

The programming language is PHP and the backend is LDAP and natively supports master-slave LDAP infrastructures.

##How it works
Contact Engine acts like the opposite of a common ORM: the properties (attributes) of the 3 objects are automatically set by parsing the ldap schema. 

For this reason, adding a new attribute to the schema for one o more objects is enough to add a new attribute to the object and so
you can refine the attributes of the 3 objects as you like without rewriting a single line of code.

##Architecture
The architecture of CE allows data access through 2 possible path: via LDAP and via REST 

##How to install
> * Enable mod-rewrite for apache: ´a2enmod rewrite´
> * Download this code or clone the repository into your web root
> * Create a vhost following the example at the bottom of the page
> * Add the vhost name to your /etc/hosts file: ex "contactengine 127.0.0.1"
> * Be sure that the constant ENVIRONMENT is set as production 
> * Configure an LDAP server. Dynamic installation is highly encouraged.
> * Create the required LDAP branches (follow the config files in ri_ldap configuration folder to understand the structure)
> * Load the ldap schemas include in the ldap_schemas directory into your LDAP server
> * Create the mysql table using the sql file included in the sql folder
> * Configure the config/database.php file accordingly
> * Extract the package ci213_system in /var/ci213_system
> * Create the folder /var/sparks and install in it the following sparks
> * * chartex 0.0.1 http://getsparks.org/packages/chartex/show
> * * curl 1.2.1
> * * restclient 2.1.0
> * * ri_ldap 0.0.2
> * * ri_contact_engine 0.0.2
> * Check every single config file in the sparks directory and modify it accordingly to your system settings

Be also sure to give the proper rights to all the folders: particularly give write access to the following folders:
> * application/logs
> * application/xml

##How to start
If you followed all the instructions above you should be capable to see the home page by pointing your browser at http://contactengine 
and then you can read the documentation and run the tests. Don't forget to have a look at the tests code to see how it works. 


	<VirtualHost *:80>
		ServerAdmin you@yourdomain.com
		ServerName contactengine
		DocumentRoot /var/www/contactengine
	
		<Directory /var/www/contactengine>
			Options Indexes FollowSymLinks MultiViews
			AllowOverride all
	
			Order allow,deny
			allow from all
	
	        RewriteEngine on
	        RewriteCond %{HTTP_HOST} !^contactengine [NC]
	        RewriteRule ^(.*)$ http://contactengine/$1 [R=301,L]
	
	
	        RewriteCond %{REQUEST_FILENAME} !-f
	        RewriteCond %{REQUEST_FILENAME} !-d
	        RewriteRule ^(.*)$ /index.php?$1 [L]	
	
		</Directory>
	
		## Logfiles configuration
		LogLevel warn
		ErrorLog /var/log/apache2/contactengine.log
	</VirtualHost>

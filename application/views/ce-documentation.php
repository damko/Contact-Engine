<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Contact Engine Documentation</title>

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

dl {
	margin-left: 30px;
	background-color: #eee;
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
</style>
</head>
<body>

<h1>Contact Engine documentation</h1>
[<a href="<?php echo site_url(''); ?>">Home</a>]

<h3>API public methods</h3>
<p>This is a self generated list of the API public methods available at this URL: <a href="<?php echo site_url('api'); ?>" target="_blank"><?php echo site_url('api'); ?></a></p>
<?php echo !empty($methods_list) ? $methods_list : 'No public methods available'; ?>
You can see all the available methods for the REST server at this <a href="<?php echo site_url('api/methods')?>">URL</a>
<hr />

<h3>How to install</h3>
<p>
This will download the code to your /var/www/contactengine folder</p>
<pre>
cd /var/www/
git clone https://damko@github.com/damko/Contact-Engine.git contactengine
</pre>
<p>
Edit application/config/rest.php and edit the $config['rest_server'] changing the hostname accordingly to your needs.
</p>


<hr />
[<a href="<?php echo site_url(''); ?>">Home</a>]
</body>
</html>

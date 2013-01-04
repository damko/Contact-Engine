<?php  include 'include/header.php'; ?>

<body>

<h1>Contact Engine documentation</h1>
[<a href="<?php echo site_url(''); ?>">Home</a>]

<h3>API public methods</h3>
<p>This is a self generated list of the API public methods available at this URL: <a href="<?php echo site_url('api'); ?>" target="_blank"><?php echo site_url('api'); ?></a></p>
<?php echo !empty($methods_list) ? $methods_list : 'No public methods available<br>'; ?>
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

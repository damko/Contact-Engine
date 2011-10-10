<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>CodeIgniter 2 - XMLRPC Example</title>

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
	background-color: #CCC;
	padding: 6px;
	font-size: 10px;
}
</style>
</head>
<body>

[<a href="<?php echo site_url(''); ?>">Home</a>]
<h1>TEST PAGE</h1>
<h2><?php echo(ucwords($testname)); ?></h2>
<?php echo($testdesc); ?><br/><br/>
Test made:<br/>
-> calling url: <?php echo($url); ?> <br/>

<?php 
	if(isset($filter)) { 
		echo '-> sending filter: <b>'; 
		print_r($filter);
		if(isset($method)) echo '</b> via method: <b>'.$method.'</b>'; 
		echo '<br/>'; 
	} 
?>
<h3>Test result</h3>
<?php 	
    if(isset($attributes)) { 
    	echo '-> Returned attributes: <b>'; 
    	print_r($attributes); 
    	echo'</b></br/>';
    }
    
	if(isset($benchmark)) echo '<br/>Benchmark time: '.$benchmark; 
	?>
<pre>
	<?php print_r($response); ?>
</pre><br/>
[<a href="<?php echo site_url(''); ?>">Home</a>]
</body>
</html>

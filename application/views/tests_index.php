<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Contact Engine - Tests list</title>
</head>
<body>
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

table {
	margin-top: 10px;
	border: 1px solid black;
	width: 97%;
}

tr td {
	padding-top: 2px;
	padding-bottom: 2px;
}
tr.gray td{
	background-color: #ddd;
	color: black;
	//border-top: 1px solid black;
}

tr.white td{
	background-color: white;
	color: black;
	//border-top: 1px solid black;
}

</style>

<h1>Index of all available tests</h1>
[<a href="<?php echo site_url(''); ?>">Home</a>]
<p>
This tests are the best way to understand how Contact Engine works. 
Have a look to the make tests page to understand how to retrieve and set informations in Contact Engine.
</p>
<p>
You can run all tests in a row with this <a href="<?php echo site_url('/make_tests/runall'); ?>" target="_blank">link</a>
</p>
<table>
<tr>
	<th>Object</th>
	<th>Test</th>
	<th>Description</th>
</tr>
<?php 
	foreach ($tests_list as $test) {
		echo $test;
	}
?>
</table><br/>

<h3>The tests code</h3>
<?php $filename = 'application/controllers/make_tests.php'; ?>
<p>
This is the code contained in the controller file <?php echo $filename;?>:
<?php highlight_file($filename); ?>
</p>

[<a href="<?php echo site_url(''); ?>">Home</a>]
</body>
</html>

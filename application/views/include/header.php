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

h3 {
	background-color: transparent;
	font-size: 17px;
	font-weight: bold;
	margin-top: 15px;
	margin-bottom: 3px;
	padding-bottom: 0px;
}

h3.blue {
	color: blue;
	font-size: 17px;
}

h4 {
	color: green;
	background-color: transparent;
	font-size: 16px;
	font-weight: bold;
	margin-left: 10px;
	margin-top: 2px;
	margin-bottom: 0px;
}

h4.subtest {
	color: #258bd2;
	font-size: 14px;
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

div#container {
	float: left;
	width: 100%;
	background: #FFF;
}

div#right{
	float:right;
	display:inline;
	width:350px;
    /* border: 1px solid #CCC; */
    margin:5px;
    margin-top: 0px;
    margin-left: 20px;
    background: #FFF;
} 

div#left{
    margin-top: 12px;
    background: #FFF;
    min-width: 700px;
    max-width: 700px;
} 

pre {
	font-style: italic;
	margin-left: 10px;
	color: black;
	background-color: #999;
	padding: 6px;
}

pre.properties {
	font-style: italic;
	margin-left: 30px;
	font-size: 10px;
	color: black;
	background-color: #c8c8c8;
	padding: 6px;
}

.obj_pm_title {
	margin-left: 20px;
	font-size: 14px;
	font-weight: bold;
	margin-bottom: -8px;
	color: blue;
}

.obj_pm_link {
	margin-left: 30px;
	font-size: 14px;
	font-weight: bold;
	margin-bottom: 30px;
	color: blue;
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

.button {
    padding: 5px 10px;
    display: inline;
    background: #777;
    border: none;
    color: #fff;
    cursor: pointer;
    font-weight: bold;
    border-radius: 5px;
    -moz-border-radius: 5px;
    -webkit-border-radius: 5px;
    text-shadow: 1px 1px #666;
}

.button:hover {
    background-position: 0 -48px;
}

.button:active {
    background-position: 0 top;
    position: relative;
    top: 1px;
    padding: 6px 10px 4px;
}
</style>

<script type="text/javascript">
function verbose(value)
{
  	location.href="?verbose="+value;
}
</script>	
</head>
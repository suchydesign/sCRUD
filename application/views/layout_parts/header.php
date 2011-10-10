<!DOCTYPE html>
<html lang="en">
<head>
<title>sCRUD</title>
<style type="text/css">

::selection{ background-color: #E13300; color: white; }
::moz-selection{ background-color: #E13300; color: white; }
::webkit-selection{ background-color: #E13300; color: white; }

body {
	background-color: #fff;
	margin: 40px;
	font: 13px/20px normal Helvetica, Arial, sans-serif;
	color: #4F5155;
}

a {
	color: #003399;
	background-color: transparent;
	font-weight: normal;
}

h1 {
	color: #444;
	background-color: transparent;
	border-bottom: 1px solid #D0D0D0;
	font-size: 19px;
	font-weight: normal;
	margin: 0 0 14px 0;
	padding: 14px 15px 10px 15px;
}

code {
	font-family: Consolas, Monaco, Courier New, Courier, monospace;
	font-size: 12px;
	background-color: #f9f9f9;
	border: 1px solid #D0D0D0;
	color: #002166;
	display: block;
	margin: 14px 0 14px 0;
	padding: 12px 10px 12px 10px;
}

#container {
	margin: auto 100px;
	border: 1px solid #D0D0D0;
	-webkit-box-shadow: 0 0 8px #D0D0D0;
	
}

#container #crud {
	padding:10px;
}

#error_message p {
	border: 1px solid red;
	padding: 12px 15px 12px 15px;
	font-weight: bold;
	color: red;
	background: #ffd6d6;
}

#error_message p.success {
	border: 1px solid #349d00;
	color: #349d00;
	background: #c6ffa9;
}

p {
	margin: 12px 15px 12px 15px;
}

input, textarea {
	border: 1px solid #D0D0D0;
}

table th {
	background: #505050;
	color: #f1f1f1;
	padding: 5px;
}

table td {
	background: #f1f1f1;
	padding: 5px;
}

ul#menu li {
	display: inline-block;
	padding: 2px 6px;
}

.center {text-align:center;}
</style>
</head>
<body>
	<div id="container">
		<h1>sCRUD</h1>
		<div id="crud">
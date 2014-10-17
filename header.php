<?php
ob_start();
error_reporting(E_ALL ^ E_NOTICE);
if (ob_get_contents()!='') {
	ob_get_clean();
	session_start();
}
session_start();
date_default_timezone_set('Asia/Manila');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"[]>
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US" xml:lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Main Page</title>
	<meta name="description" content="Description" />
	<meta name="keywords" content="Keywords" />
<!--container for all scripts-->
<?php include_once 'scripts.php'; ?>
</head>
<body>
<div id="art-main">
	<div class="cleared reset-box"></div>
	<div class="art-sheet">
		<div class="art-sheet-tl"></div>
		<div class="art-sheet-tr"></div>
		<div class="art-sheet-bl"></div>
		<div class="art-sheet-br"></div>
		<div class="art-sheet-tc"></div>
		<div class="art-sheet-bc"></div>
		<div class="art-sheet-cl"></div>
		<div class="art-sheet-cr"></div>
		<div class="art-sheet-cc"></div>
		<div class="art-sheet-body">
			<div class="art-header">
				<div class="art-header-clip">
				<div class="art-header-center">
					<div class="art-header-jpeg"></div>
				</div>
				</div>
				<div class="art-logo">
								 <h1 class="art-logo-name"><a href="./index.html">Juban Water System</a></h1>
												 <h2 class="art-logo-text"><!--Enter Site Slogan!--></h2>
								</div>
			</div>
			<div class="cleared reset-box"></div>
<?php include "menu.php";?>		
<?php
define("EW_PAGE_ID", "welcome", TRUE); // Page ID
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "usuariosinfo.php" ?>
<?php include "userfn50.php" ?>
<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // Always modified
header("Cache-Control: private, no-store, no-cache, must-revalidate"); // HTTP/1.1 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); // HTTP/1.0
?>
<?php

// Open connection to the database
$conn = ew_Connect();
?>
<?php
$Security = new cAdvancedSecurity();
?>
<?php

// Common page loading event (in userfn*.php)
Page_Loading();
?>
<?php

// Page load event, used in current page
Page_Load();
?>
<?php
$sLastUrl = $Security->LastUrl(); // Get Last Url
if ($sLastUrl == "") $sLastUrl = "login.php";
if (!$Security->IsLoggedIn()) $Security->AutoLogin();
$Security->LoadUserLevel(); // Load User Level
$bValidate = FALSE;

if (!$Security->IsLoggedIn()) {
    Page_Terminate($sLastUrl); // Return to last accessed page
}
?>
<?php include "header.php" ?>
<script language="JavaScript" type="text/javascript">
<!--

// Write your client script here, no need to add script tags.
// To include another .js script, use:
// ew_ClientScriptInclude("my_javascript.js"); 
//-->

</script>
<script type="text/javascript">

</script>
<p><span class="phpmaker"><strong>BIENVENIDO</strong></span></p>


<div id="WELCOMEMSG">
       <?=@$_SESSION['WELCOMEMSG']?>
       <?unset($_SESSION['WELCOMEMSG']);?>
       <BR><BR>
       Seleccione una opción del menú principal para comenzar
</div>


<?php include "footer.php" ?>
<?php

// If control is passed here, simply terminate the page without redirect
Page_Terminate();

// -----------------------------------------------------------------
//  Subroutine Page_Terminate
//  - called when exit page
//  - clean up connection and objects
//  - if url specified, redirect to url, otherwise end response
function Page_Terminate($url = "") {
	global $conn;

	// Page unload event, used in current page
	Page_Unload();

	// Global page unloaded event (in userfn*.php)
	Page_Unloaded();

	 // Close Connection
	$conn->Close();

	// Go to url if specified
	if ($url <> "") {
		ob_end_clean();
		header("Location: $url");
	}
	exit();
}
?>
<?php

// Page Load event
function Page_Load() {

	//echo "Page Load";
}

// Page Unload event
function Page_Unload() {

	//echo "Page Unload";
}
?>
<?php

// User Logging In event
function User_LoggingIn($usr, $pwd) {

	// Enter your code here
	// To cancel, set return value to False

	return TRUE;
}

// User Logged In event
function User_LoggedIn($usr) {

	//echo "User Logged In";
}
?>

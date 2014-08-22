<?php  
 
// Perform application-specific setup 
require '../application/bootstrap.php';  

// To deploy to a live server, use 'production' instead
$bootstrap = new Bootstrap('development');
$bootstrap->runApp();
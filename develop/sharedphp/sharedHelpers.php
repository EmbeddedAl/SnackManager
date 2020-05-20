<?php


function sharedHelpers_getUserImageFile($userid)
{
    require 'config.php';

    $imgUserFileName = "users/" . $userid . ".jpg";
    $imgFileName = file_exists($imgUserFileName) ? $imgUserFileName : $GLOBALS['ConfigNoImageForUser'];

    return $imgFileName;
}



function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}


function writeErrorPage($message, $next_page="index.php")
{
    echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">";
    echo "<html> <body>";
    echo "<h1>ERROR!</h1>";
    echo "$message<br><br>";
    echo "You will be redirected in 3 seconds<br>";
    echo "<meta http-equiv=\"refresh\" content=\"3; url=$next_page\">";
    echo "</body> </html>";
}

function formatCurrency($amount)
{
    return sprintf("%6.2f &euro;", $amount);
}
?>

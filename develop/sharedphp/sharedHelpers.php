<?php


function sharedHelpers_getUserImageFile($userid)
{
    include 'config.php';

    $imgUserFileName = "users/" . $userid . ".jpg";
    $imgFileName = file_exists($imgUserFileName) ? $imgUserFileName : $ConfigNoImageForUser;

    return $imgFileName;
}



function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}


?>
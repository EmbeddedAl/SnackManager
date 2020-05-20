<?php


function sharedInputCheck_isUsernameValid($username)
{
    include 'config.php';

    // Check: not empty
    if (strlen($username) == 0)
        return 0;

    // Check: not default
    if ($username === $DefaultUserName)
        return 0;

    // Check: Only valid characters
    if (strlen(!preg_match("/^[a-zA-Z0-9]+$/", $username)) != 0)
        return 0;

    // Check: Not exceeding maxlen
    if (strlen($username) > $MaxCharUsername)
        return 0;

    // input seems valid
    return 1;
}


function sharedInputCheck_isFirstnameValid($firstname)
{
    include 'config.php';

    // Check: not empty
    if (strlen($firstname) == 0)
        return 0;

    // Check: not default
    if ($firstname === $DefaultFirstName)
        return 0;

    // Check: Only valid characters
    if (strlen(!preg_match("/^[a-zA-Z]+$/", $firstname)) != 0)
        return 0;

     // Check: Not exceeding maxlen
    if (strlen($firstname) > $MaxCharFirstname)
        return 0;

    // input seems valid
    return 1;
}


function sharedInputCheck_isLastnameValid($lastname)
{
    include 'config.php';

    // Check: not empty
    if (strlen($lastname) == 0)
        return 0;

    // Check: not default
    if ($lastname === $DefaultLastName)
        return 0;

    // Check: Only valid characters
    if (strlen(!preg_match("/^[a-zA-Z]+$/", $lastname)) != 0)
        return 0;

    // Check: Not exceeding maxlen
    if (strlen($lastname) > $MaxCharLastname)
        return 0;

    // input seems valid
    return 1;
}


function sharedInputCheck_isEmailValid($email)
{
    include 'config.php';

    // Check: not empty
    if (strlen($email) == 0)
        return 0;

    // Check: not default
    if ($email === $DefaultEmail)
        return 0;

    // Check: email is well formed
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        return 0;

    // Check: Not exceeding maxlen
    if (strlen($email) > $MaxCharEmail)
        return 0;

    // input seems valid
    return 1;
}


function sharedInputCheck_isCityValid($city)
{
    include 'config.php';

    // Check: not empty
    if (strlen($city) == 0)
        return 0;

    // Check: not default
    if ($city === $DefaultCity)
        return 0;

    // Check: Only valid characters
    if (strlen(!preg_match("/^[a-zA-Z]+$/", $city)) != 0)
        return 0;

    // Check: Not exceeding maxlen
    if (strlen($city) > $MaxCharCity)
        return 0;

    // input seems valid
    return 1;
}


function sharedInputCheck_isPasswordValid($pass)
{
    include 'config.php';
    
    global $MinPasswordLen;
    global $DefaultPassword;
    
    // Check: not default
    if ($pass === $DefaultPassword)
    {
        return "The password must not be '" . $DefaultPassword . "'";
    }
        
    
    // Check: Needs minimum len
    if (strlen($pass) < $MinPasswordLen)
        return 'Minimum password length is ' . $MinPasswordLen . 'characters';


    // input seems valid
    return "";
}


function sharedInputCheck_isAmountValid($amount)
{
    return preg_match("/^[0-9]{1,4}([.,][0-9]{1,2})?$/", $amount);
}

?>

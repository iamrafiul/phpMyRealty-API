<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rafiul
 * Date: 8/13/12
 * Time: 5:36 AM
 * To change this template use File | Settings | File Templates.
 */

global $db;

$form = array();

// safehtml() all the POST variables
// to insert into the database or
// print the form again if errors
// found
$form = array_map('safehtml', $_POST);

// Make login and password lower case
$login = strtolower ($_POST['realtor_login']);
$password = $_POST['realtor_password'];

// Cut the description size to the allowed minimum set in config
$form['Realtor_Description'] = substr ($form['realtor_description'], 0, $conf['realtor_description_size']);

// Initially we think that no errors were found
$count_error = 0;

// Check if login is already exist
$sql = 'SELECT login FROM ' . USERS_TABLE . ' WHERE login = "' . safehtml($login) . '"';
$r = $db->query($sql) or error ('Critical Error', mysql_error () );

if ($db->numrows($r) > 0 )
{
    $this->response($this->json($lang['Login_Used']),303);
    $count_error++;
}

// Check if email is banned
$sql = 'SELECT * FROM ' . BANS_TABLE . ' WHERE name = "' . $form['realtor_e_mail'] . '" LIMIT 1';
$r = $db->query($sql) or error ('Critical Error', mysql_error () );

if ($db->numrows($r) > 0 )
{
    $this->response($this->json($lang['e_mail_Banned']),303);
    $count_error++;
}

// Check if this email is already used
if (strcasecmp($conf['allow_same_e_mail'], 'ON')) {
    $sql = 'SELECT id FROM ' . USERS_TABLE . ' WHERE email = "' . $form['realtor_e_mail'] . '"';
    $r = $db->query($sql) or error ('Critical Error', mysql_error () );

    if ($db->fetcharray($r) > 0 )
    {
        $this->response($this->json($lang['Email_Used']),303);
        $count_error++;
    }
}

//            if (!eregi('^[a-z0-9]+$', $login))
//            { echo $lang['Login_Incorrect'] . '<br />'; $count_error++;}
//
//            if (!eregi('^[a-z0-9]+$', $password))
//            { echo $lang['Password_Incorrect'] . '<br />'; $count_error++;}
//
//            // Check if both passwords are equal
//            if ($form['realtor_password'] != $form['realtor_password_2'])
//            { echo $lang['Passwords_Missmatch'] . '<br />'; $count_error++;}
//
//            if ($count_error > '0')
//                echo '<br /><span class="warning">' . $lang['Errors_Found'] . ': ' . $count_error . '</span><br />';

// If no errors were found during the above checks we continue
if ($count_error == '0')
{

    // Add realtor listing into the database

    if ($conf['approve_realtors'] == 'ON')
        $approved = 0;
    else
        // if you want all the new accounts to be approved without admin or email
        // validation, please, set the following variable $approved to 1
        $approved = 0;

    // Get the user IP address
    $user_ip = $_SERVER['REMOTE_ADDR'];
    // If there is more than one IP
    // get the first one from the
    // comma separated list
    if ( strstr($user_ip, ', ') )
    {
        $ips = explode(', ', $user_ip);
        $user_ip = $ips[0];
    }

    // Generate random number for the email validation link
    $number = rand (1000000, 9999999);

    // Create a mysql query
    $sql = 'INSERT INTO '. USERS_TABLE .
        ' (approved, first_name, last_name, company_name,
                        description, location, city, zip, address,
                        phone, fax, mobile, email, website, rating,
                        votes, date_added, ip_added, login, password, number) VALUES
                        (' . $approved . ', "' . $form['realtor_first_name'] . '", "' . $form['realtor_last_name']. '", "' . $form['realtor_company_name'] . '", "'
        . $form['realtor_description'] . '", ' . $form['realtor_location'] . ', "' . $form['realtor_city'] . '", "' . $form['realtor_zip_code'] . '", "' . $form['realtor_address'] . '", "'
        . $form['realtor_phone'] . '", "' . $form['realtor_fax'] . '", "' . $form['realtor_mobile'] . '", "' . $form['realtor_e_mail'] . '", "' . $form['realtor_website'] . '", 0,
                         0, "' . date ('Y-m-d') . '", "' . $user_ip . '", "' . $login . '", "' . md5($password) . '", "' . $number . '")';


    $db->query($sql) or error ('Critical Error', mysql_error ());

    // Fetch the last auto incremented listing id
    $id = mysql_insert_id();


}

?>
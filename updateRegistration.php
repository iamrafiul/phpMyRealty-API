<?php

    global $db;

    $form = array();


    $form = array_map('safehtml', $_GET);

           // Create a mysql query
           $sql = 'UPDATE '. USERS_TABLE .
               ' SET first_name = "' . $form['realtor_first_name'] . '",
	                 last_name = "' . $form['realtor_last_name']. '",
                     company_name = "' . $form['realtor_company_name'] . '",
                     description = "' . $form['realtor_description'] . '",
                     location = "' . $form['realtor_location'] . '",
                     city = "' . $form['realtor_city'] . '",
                     zip = "' . $form['realtor_zip_code'] . '",
                     address = "' . $form['realtor_address'] . '",
                     phone = "' . $form['realtor_phone'] . '",
                     fax = "' . $form['realtor_fax'] . '",
                     mobile = "' . $form['realtor_mobile'] . '",
                     email = "' . $form['realtor_e_mail'] . '",
                     website = "' . $form['realtor_website'] . '",
                     date_updated = "' . date('Y-m-d') . '",
                     password = "' . $form['realtor_password'] . '" WHERE id = "' .$form['user_id']. '"';


           echo $sql;
           //$rs = $db->query($sql) or error ('Critical Error', mysql_error ());

?>
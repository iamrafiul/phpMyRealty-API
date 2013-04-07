<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rafiul
 * Date: 8/13/12
 * Time: 5:21 AM
 * To change this template use File | Settings | File Templates.
 */

    global $db;

    //$s = array('realtor_first_name'=>'asm');
    //$search = array_map ('safehtml', $s);
    $data  = array();


        $search = array_map ('safehtml', $_POST);
        $search = array_map ('safehtml', $_POST);

    if (isset($search['sort']))
    {

        if ($search['sort'] == 1)
        {
            $search['order_by'] = 'first_name';
            $search['order_by_type'] = 'ASC';
        }

        if ($search['sort'] == 2)
        {
            $search['order_by'] = 'first_name';
            $search['order_by_type'] = 'DESC';
        }

        if ($search['sort'] == 3)
        {
            $search['order_by'] = 'last_name';
            $search['order_by_type'] = 'ASC';
        }

        if ($search['sort'] == 4)
        {
            $search['order_by'] = 'last_name';
            $search['order_by_type'] = 'DESC';
        }

    }

    unset($search['sort']);


    $count_error = 0;

    if ($count_error == 0)
    {
        // Generate the SQL query
        $sql = 'SELECT * from ' . USERS_TABLE . ' WHERE ';

        $sql.= 'approved = 1 ';

        if (isset($search['realtor_first_name']) && !empty($search['realtor_first_name']))
            $sql.= 'AND first_name LIKE "%' . $search['realtor_first_name'] . '%" ';

        if (isset($search['realtor_last_name']) && !empty($search['realtor_last_name']))
            $sql.= 'AND last_name LIKE "%' . $search['realtor_last_name'] . '%" ';

        if (isset($search['realtor_company_name']) && !empty($search['realtor_company_name']))
            $sql.= 'AND company_name LIKE "%' . $search['realtor_company_name'] . '%" ';

        if (isset($search['realtor_description']) && !empty($search['realtor_description']))
            $sql.= 'AND description LIKE "%' . $search['realtor_description'] . '%" ';

        if (isset($search['realtor_location']) && $search['realtor_location'] != 'ANY')
            $sql.= 'AND location = "' . $search['realtor_location'] . '" ';

        if (isset($search['realtor_city']) && !empty($search['realtor_city']))
            $sql.= 'AND city = "' . $search['realtor_city'] . '" ';

        if (isset($search['realtor_address']) && !empty($search['realtor_address']))
            $sql.= 'AND address LIKE "%' . $search['realtor_address'] . '%" ';

        if (isset($search['realtor_zip_code']) && !empty($search['realtor_zip_code']))
            $sql.= 'AND zip = "' . $search['realtor_zip_code'] . '" ';

        if (isset($search['realtor_phone']) && !empty($search['realtor_phone']))
            $sql.= 'AND phone LIKE "%' . $search['realtor_phone'] . '%" ';

        if (isset($search['realtor_fax'])  && !empty($search['realtor_fax']))
            $sql.= 'AND fax LIKE "%' . $search['realtor_fax'] . '%" ';

        if (isset($search['realtor_mobile']) && !empty($search['realtor_mobile']))
            $sql.= 'AND mobile LIKE "%' . $search['realtor_mobile'] . '%" ';

        if (isset($search['realtor_e_mail']) && !empty($search['realtor_e_mail']))
            $sql.= 'AND email = "' . $search['realtor_e_mail'] . '" ';

        if (isset($search['realtor_website']) && !empty($search['realtor_website']))
            $sql.= 'AND website LIKE "%' . $search['realtor_website'] . '%" ';

        if (isset($search['realtor_login'])  && !empty($search['realtor_login']))
            $sql.= 'AND login = "' . $search['realtor_login'] . '" ';

        if (isset($search['image_uploaded']))
            $sql.= 'AND image_uploaded = "' . $search['image_uploaded'] . '" ';

        $sql2 = $sql;

        if (isset($search['order_by']) && $search['order_by'] != 'ANY')
            $sql2.= 'ORDER BY ' . $search['order_by'] . ' ';
        else
            $sql2.= 'ORDER BY id DESC ';

        if (isset($search['order_by']) && $search['order_by'] != 'ANY'
            && isset($search['order_by_type']) && $search['order_by_type'] != 'ANY' )
            $sql2.= $search['order_by_type'];

        $results_page = 0;
        // Add LIMIT function to the query for the pagination
        $sql2 .= ' LIMIT 0,10';


        $r = $db->query($sql) or error ('Critical Error', mysql_error ());

        // Results per page (using LIMIT)
        $r2 = $db->query($sql2) or error ('Critical Error', mysql_error ());
        // Return 'Nothing was found' error
        if ($db->numrows($r) == 0)
            $data = array('msg'=>'Nothing_Found');

        $i = 0;



        while ($f = $db->fetcharray( $r2 ))
        {


            if ( file_exists ( PATH . '/photos/' . $f['id'] . '-resampled.jpg' ))
                $img = URL . '/photos/' . $f['id'] . '-resampled.jpg?nocache=' . rand ( 0, 999999 );
            else
                $img = URL . '/photos/empty.jpg?nocache=' . rand ( 0, 999999 );

            $data[] = array(
                'first_name'=>$f['first_name'],
                'last_name'=>$f['last_name'],
                'company_name'=>$f['company_name'],
                'description'=>$f['description'],
                'location'=>$f['location'],
                'city'=>$f['city'],
                'zip'=>$f['zip'],
                'address'=>$f['address'],
                'website'=>$f['website'],
                'image'=>$img
            );

           $i++;

        }

    }

?>
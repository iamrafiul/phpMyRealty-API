<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rafiul
 * Date: 8/19/12
 * Time: 8:41 AM
 * To change this template use File | Settings | File Templates.
 */
global $db;
global $language_in;
global $conf;
$output = array();

if(is_numeric($_GET['user_id']))
    $user_id = intval($_GET['user_id']);

if(is_int($user_id))
{

    // Fetching the user data
    $sql = 'SELECT * FROM ' . USERS_TABLE . ' WHERE id = "' . $user_id . '" LIMIT 1';
    $res = $db->query( $sql );
    $f_res = $db->fetcharray( $res );


    // Order by
    if ($_GET['order_by'] == '')
    {
        $order_by = 'id';
        $order_by2 = 'DESC';
    }
    else
    {
        $order_by = $_GET['order_by'];
        $order_by2 = $_GET['order_by2'];
    }

    // Title/descr language to use (if available)
    $title = str_replace( 'name', 'title', $language_in );
    $description = str_replace( 'name', 'description', $language_in );

    // Fetching the listings data
    $sql = 'SELECT * FROM ' . PROPERTIES_TABLE . ' WHERE userid = "' . safehtml($f_res['id']) . '"';

    // Pagination
    if (isset($_GET['page']))
        $page = $_GET['page'];
    else
        $page = 0;

    $results_page = $page * $conf['search_results'];

    // Fetching the listings data with the LIMIT function for pagination
    $sql2 = 'SELECT ' . $title . ', ' . $description . ', ' . PROPERTIES_TABLE . '.* FROM ' . PROPERTIES_TABLE  . ' WHERE userid = "' . intval($f_res['id']) . '"  ORDER BY ' . addslashes($order_by) . ' ' . addslashes($order_by2) . ' LIMIT ' . $results_page . ', ' . $conf['search_results'];

    $r = $db->query( $sql ) or error ('Critical Error', mysql_error () );
    $r2 = $db->query( $sql2 ) or error ('Critical Error', mysql_error () );

    $i = 0;

    while ($f = $db->fetcharray( $r2 ))
    {
        $data = array();
        // Change all empty values in the array to 'n/a'
        $f = array_map ( 'if_empty', $f);

        // Starting a new template
        $template = new Template;


        // Default
        if ($f[0] == '')
        {
            $data['title'] = $f['title'];
            $f['title'] = $f['title'];
        }
        else
        {
            $f['title'] = $f[0];
            $data['title'] = $f[0];
        }

        if ($f[1] == '')
        {
            $f['description'] = $f['description'];
            $data['description'] = $f['description'];
        }
        else
        {
            $f['description'] = $f[1];
            $data['description'] = $f[1];
        }




        if ( file_exists ( PATH . '/' . 'images' . '/' . $f['id'] . '-resampled.jpg' ))
            $images =  URL . '/' . 'images' . '/' . $f['id'] . '-resampled.jpg?nocache=' . rand ( 0, 999999 );
        else
            $images = URL . '/' . 'images' . '/empty.jpg?nocache=' . rand ( 0, 999999 );


        $data['image'] = $images;

        $description = substr(removehtml(unsafehtml($f['description'])), 0, $conf['search_description']);
        $description = substr($description, 0, strrpos($description, ' ')) . ' ... ';

        $data['mls'] = $f['mls'] ;
        $data['title'] = $f['title'] ;
        $data['type'] = getnamebyid ( TYPES_TABLE, $f['type'] );
        $data['type2'] = getnamebyid ( TYPES2_TABLE, $f['type2'] );
        $data['style'] = getnamebyid ( STYLES_TABLE, $f['style'] );
        $data['description'] =  $description;

        unset ($description);

        $data['lot_size'] =  $f['size'];
        $data['dimensions'] =  $f['dimensions'];
        $data['dimensions'] =  $f['dimensions'];


        if ($f['bathrooms'] < 1)
            $data['dimensions'] =  '-';

        else
            $data['dimensions'] =  $f['bathrooms'];

        $data['half_bathrooms'] =  $f['half_bathrooms'] ;

        if ($f['bedrooms'] < 1)
            $data['bedrooms'] = '-';
        else
            $data['bedrooms'] =  $f['bedrooms'];

        if ($f['garage_cars'] < 1)
            $data['garage_cars'] =  '-' ;
        else
            $data['garage_cars'] =  $f['garage_cars'];

        $cat = explode ("#", $f['location2']);
        $r11 = $db->query('SELECT category FROM ' . LOCATION1_TABLE . ' WHERE selector = "' . $cat[0] . '"');
        $f11 = $db->fetcharray($r11);
        $data['location1']  = $f11['category'];


        $r22 = $db->query('SELECT subcategory FROM ' . LOCATION2_TABLE . ' WHERE catsubsel = "' . $cat[1] . '"');
        $f22 = $db->fetcharray($r22);
        $data['location2']  = $f11['subcategory'] ;

        $r33 = $db->query('SELECT subsubcategory FROM ' . LOCATION3_TABLE . ' WHERE catsubsubsel = "' . $cat[2] . '"');
        $f33 = $db->fetcharray($r33);
        $template->set ( 'location3', $f33['subsubcategory'] );
        $data['location3']  = $f11['subsubcategory'];

        // If address is not allowed to be displayed
        if ($f['display_address'] == 'YES')
        {
            $data['address1']  = $f['address1'];
            $data['address2']  = $f['address2'];
            $data['zip']  = $f['zip'];

        }
        else
        {
            $data['address1']  = ' ';
            $data['address2']  = ' ';

        }

        $data['price']  = pmr_number_format($f['price']);
        $data['currency']  = $conf['currency'];
        $data['directions']  = $f['directions'];
        $data['year_built']  =$f['year_built'];
        $data['buildings']  = show_multiple ( BUILDINGS_TABLE, $f['buildings'] );
        $data['appliances']  = show_multiple ( APPLIANCES_TABLE, $f['appliances'] );
        $data['features']  =  show_multiple ( FEATURES_TABLE, $f['features'] );
        $data['garage']  = getnamebyid ( GARAGE_TABLE, $f['garage'] );
        $data['basement']  = getnamebyid ( BASEMENT_TABLE, $f['basement'] );
        $data['hits']  =$f['hits'];

        $data['new']  =  newitem ( PROPERTIES_TABLE, $f['id'], $conf['new_days']);
        $data['updated']  = updateditem ( PROPERTIES_TABLE, $f['id'], $conf['updated_days']);
        $data['featured']  = featureditem ( $f['featured'] );

        // Realtor link
        $sql = 'SELECT * FROM ' . USERS_TABLE  . ' WHERE approved = 1 AND id = ' . $f['userid'] . ' LIMIT 1';
        $r_user = $db->query ( $sql ) or error ('Critical Error' , mysql_error());
        $f_user = $db->fetcharray ($r_user);

        $i++;

       $output [] = array(
            'title'=>$data['title'],
            'description'=>$data['description'],
            'image'=>$images,
            'mls'=>$data['mls'],
            'type'=>$data['type'],
            'type2'=>$data['type2'],
            'style'=>$data['style'],
            'description'=>$data['description'],
            'lot_size'=>$data['lot_size'],
            'dimensions'=>$data['dimensions'],
            'half_bathrooms'=>$data['half_bathrooms'],
            'bedrooms'=>$data['bedrooms'],
            'garage_cars'=>$data['garage_cars'],
            'location1'=>$data['location1'],
            'location2'=>$data['location2'],
            'location3'=>$data['location3'],
            'address1'=>$data['address1'],
            'address2'=>$data['address2'],
            'zip'=>$data['zip'],
            'price'=>$data['price'],
            'currency'=>$data['currency'],
            'directions'=>$data['directions'],
            'year_built'=>$data['year_built'],
            'buildings'=>$data['buildings'],
            'appliances'=>$data['appliances'],
            'features'=>$data['features'],
            'garage'=>$data['garage'],
            'basement'=>$data['basement'],
            'hits'=>$data['hits'],
            'new'=>$data['new'],
            'updated'=>$data['updated'],
            'featured'=>$data['featured']
        );

        //utput[] = $data;

    }


}

else
    $output = array ('msg'=>'Critical Error , Please login to access this script.');

?>
<?php

global $db;
global $language_in;
global $conf;
$output = array();

$i = 0;
$icount = 0;

$title = str_replace( 'name', 'title', $language_in );
$description = str_replace( 'name', 'description', $language_in );

$sql = 'SELECT ' . $title . ', ' . $description . ', ' .PROPERTIES_TABLE . '.* FROM ' . PROPERTIES_TABLE  . ' WHERE approved = 1 AND featured = "A"  ORDER BY id DESC LIMIT '. $conf['recent_limit'];


$r = $db->query ( $sql );


while ($f = $db->fetcharray( $r ))
{
    $data = array();


    $f = array_map ( 'if_empty', $f);

    // Default
    if (trim($f[0]) != '')
        $f['title'] = trim($f[0]);

    if (trim($f[1]) != '')
        $f['description'] = trim($f[1]);


    if ($conf['rewrite'] == 'ON')
        $data['link']=  URL . '/Listing/' . rewrite ( getnamebyid ( TYPES_TABLE, $f['type'] ) ) . '/' . $f['id'] . '_' . rewrite($f['title']) . '.html';
    //$template->set ( 'link', URL . '/Listing/' . rewrite ( getnamebyid ( TYPES_TABLE, $f['type'] ) ) . '/' . $f['id'] . '_' . rewrite($f['title']) . '.html'  );
    else
        $data['link'] = URL . '/viewlisting.php?id=' . $f['id'];


    if ( file_exists ( PATH . '/' . 'images' . '/' . $f['id'] . '-resampled.jpg' ))
        $data['image'] =  URL . '/' . 'images' . '/' . $f['id'] . '-resampled.jpg?nocache=' . rand ( 0, 999999 );
    else
        $data['image'] =  URL . '/' . 'images' . '/empty.jpg?nocache=' . rand ( 0, 999999 );


    $data['mls'] =  $f['mls'] ;
    $data['title'] = $f['title'] ;
    $data['type'] = getnamebyid ( TYPES_TABLE, $f['type'] );
    $data['type2'] =  getnamebyid ( TYPES2_TABLE, $f['type2'] );
    $data['style'] = getnamebyid ( STYLES_TABLE, $f['style'] );


    $description = substr(removehtml(unsafehtml($f['description'])), 0, $conf['search_description']);
    $description = substr($description, 0, strrpos($description, ' ')) . ' ... ';
    $data['description'] =  $description ;
    unset ($description);

    $data['lot_size'] =  $f['size'] ;
    $data['dimensions'] = $f['dimensions'];

    if ($f['bathrooms'] < 1)
        $data['bathrooms'] =  '-';
    else
        $data['bathrooms'] = $f['bathrooms'];


    $data['half_bathrooms'] = $f['half_bathrooms'];


    if ($f['bedrooms'] < 1)
        $data['bedrooms'] = '-';

    else
        $data['bedrooms'] = $f['bedrooms'];

    if ($f['garage_cars'] < 1)
        $data['garage_cars'] = '-';

    else
        $data['garage_cars'] = $f['garage_cars'];


    $cat = explode ("#", $f['location2']);
    $r11 = $db->query('SELECT category FROM ' . LOCATION1_TABLE . ' WHERE selector = "' . $cat[0] . '"');
    $f11 = $db->fetcharray($r11);
    $data['garage_cars'] = $f11['category'];


    $r22 = $db->query('SELECT subcategory FROM ' . LOCATION2_TABLE . ' WHERE catsubsel = "' . $cat[1] . '"');
    $f22 = $db->fetcharray($r22);
    $data['location2'] = $f22['subcategory'];

    $r33 = $db->query('SELECT subsubcategory FROM ' . LOCATION3_TABLE . ' WHERE catsubsubsel = "' . $cat[2] . '"');
    $f33 = $db->fetcharray($r33);
    $data['location3'] =  $f33['subsubcategory'];

    if ($f['display_address'] == 'YES' && $f_package['address'] == 'ON')
    {
        $data['address1'] = $f['address1'];
        $data['address2'] = $f['address2'];
        $data['zip'] = $f['zip'];

    }
    elseif ($f['display_address'] != 'YES' || $f_package['address'] != 'ON')
    {
        $data['address1'] = ' ';
        $data['address1'] = ' ';
        $data['address1'] = ' ';
    }

    $data['price'] = pmr_number_format($f['price']);
    $data['currency'] = $conf['currency'];
    $data['directions'] = $f['directions'];
    $data['year_built'] = $f['year_built'];
    $data['buildings'] = show_multiple ( BUILDINGS_TABLE, $f['buildings'] );
    $data['appliances'] = show_multiple ( APPLIANCES_TABLE, $f['appliances'] );
    $data['features'] = show_multiple ( FEATURES_TABLE, $f['features'] );
    $data['garage'] = getnamebyid ( BASEMENT_TABLE, $f['basement'] );


    $data['new'] = newitem ( PROPERTIES_TABLE, $f['id'], $conf['new_days']);
    $data['updated'] = updateditem ( PROPERTIES_TABLE, $f['id'], $conf['updated_days']) ;
    $data['featured'] = featureditem ( $f['featured'] );

    $data['hits'] = $f['hits'];

    $i++;

    $icount++;

    if ($icount == 3) { $icount = 0; }

    $output[] = $data;




}

if($output==null)
   $output = array('msg'=>'not_found');


?>
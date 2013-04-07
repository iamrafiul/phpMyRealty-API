<?php

global $db;
global $language_in;

$output = array();

$i = 0;
$icount = 0;

$search = array();

$search = array_map ('safehtml', $_GET);



if (isset($search['your_zip']) && isset($search['miles']))
{

    $found_zip_codes = get_zips_in_range ($search['your_zip'], $search['miles']);

    // If some zips were found we create an IN query.
    if (count($found_zip_codes) > 0)
    {

        $sql_zip_codes = 'IN (';

        foreach($found_zip_codes as $zips => $dists)
            $sql_zip_codes.= '"' . $zips . '", ';

        $sql_zip_codes.= '"00000")';

    }

    // Or put an unreal zip code not to show results at all
    else $sql_zip_codes = ' = "ZZZZZ"';
}


// Title/descr language to use (if available)
$title = str_replace( 'name', 'title', $language_in );
$description = str_replace( 'name', 'description', $language_in );

$sql = 'SELECT ' . $title . ', ' . $description . ', ' . PROPERTIES_TABLE . '.* FROM ' . PROPERTIES_TABLE . ' WHERE ';

$sql.= 'approved = 1 ';

if (isset($search['userid']) && !empty($search['userid']))
    $sql.= ' AND userid = "' . $search['userid'] . '" ';

if (isset($search['id']) && !empty($search['id']))
    $sql.= ' AND id = "' . $search['id'] . '" ';

if (isset($search['mls']) && !empty($search['mls']))
    $sql.= ' AND (mls = "' . $search['mls'] . '" OR id = "' . $search['mls'] . '")';

if (isset($search['type']) && $search['type'] != 'ANY')
    $sql.= ' AND type = "' . $search['type'] . '" ';

if (isset($search['type2']) && $search['type2'] != 'ANY')
    $sql.= ' AND type2 = "' . $search['type2'] . '" ';

if (isset($search['style']) && $search['style'] != 'ANY')
    $sql.= ' AND style = "' . $search['style'] . '" ';

if (isset($search['status']) && $search['status'] != 'ANY')
    $sql.= ' AND status = "' . $search['status'] . '" ';

// Multiple keyword search
if (isset($search['keyword']) && !empty($search['keyword'])) {
    $inkey = explode (",", $search['keyword']);

    $sql.= ' AND ( description = "0" ';

    foreach($inkey as $key => $value) {

        $sql.= ' OR mls = "' . trim($value) . '" OR id = "' . trim($value) . '" ';
        $sql.= ' OR description LIKE "%' . trim($value) . '%" OR title LIKE "%' . trim($value) . '%" ';
        $sql.= ' OR zip = "' . trim($value) . '" ';

        $r00 = $db->query('SELECT id FROM ' . TYPES_TABLE . ' WHERE name = "' . trim($value) . '"');

        if ($db->numrows($r00) > 0) {
            $f00 = $db->fetcharray($r00);
            $sql.= ' OR type = "' . $f00['id'] . '" ';
        }

        $r01 = $db->query('SELECT selector FROM ' . LOCATION1_TABLE . ' WHERE category = "' . trim($value) . '"');

        if ($db->numrows($r01) > 0) {
            $f01 = $db->fetcharray($r01);
            $sql.= ' OR location2 LIKE "' . $f01['selector'] . '#%#%" ';
        }

        $r02 = $db->query('SELECT catsubsel FROM ' . LOCATION2_TABLE . ' WHERE subcategory = "' . trim($value) . '"');

        if ($db->numrows($r02) > 0) {
            $f02 = $db->fetcharray($r02);
            $sql.= ' OR location2 LIKE "%#' . $f02['catsubsel'] . '#%" ';
        }

        $r03 = $db->query('SELECT catsubsubsel FROM ' . LOCATION3_TABLE . ' WHERE subsubcategory = "' . trim($value) . '"');

        if ($db->numrows($r03) > 0) {
            $f03 = $db->fetcharray($r03);
            $sql.= ' OR location2 LIKE "%#%#' . $f03['catsubsubsel'] . '" ';
        }
    }
    $sql.= ' ) ';
}

//3 level locations

if (!isset($search['location1']) || $search['location1'] == 'ANY') {
    $search['location1'] = '%';
    $search['location2'] = '%';
    $search['location3'] = '%';
}

if (!isset($search['location2']) || $search['location2'] == 'ANY') {
    $search['location2'] = '%';
    $search['location3'] = '%';
}

if (!isset($search['location3']) || $search['location3'] == 'ANY')
    $search['location3'] = '%';

if ($search['location1'] != '%' && !eregi('^[0-9]+$', $search['location1'] && $search['location1'] != 'ANY')) die('Unknown input');
if ($search['location2'] != '%' && !eregi('^[0-9]+$', $search['location2'] && $search['location2'] != 'ANY')) die('Unknown input');
if ($search['location3'] != '%' && !eregi('^[0-9]+$', $search['location3'] && $search['location3'] != 'ANY')) die('Unknown input');

$locationsearch = $search['location1'] . '#' . $search['location2'] . '#' . $search['location3'];

if (isset($search['location1']))
    $sql.= ' AND location2 LIKE "' . $locationsearch . '" ';

//

if (isset($search['your_zip']) && isset($search['miles']) && $search['your_zip'] != '' && $search['miles'] != '' && count($found_zip_codes) > 0)
    $sql.= ' AND zip ' . $sql_zip_codes;

if (isset($search['your_zip']) && isset($search['miles']) && $search['your_zip'] != '' && $search['miles'] != '' && count($found_zip_codes) == 0)
    $sql.= ' AND zip ' . $sql_zip_codes;

if (isset($search['zip']) && !empty($search['zip']))
    $sql.= ' AND zip = "' . $search['zip'] . '" ';


if (isset($search['price_max']) && $search['price_max'] == 'ANY') $search['price_max'] = '';
if (isset($search['price_min']) && $search['price_min'] == 'ANY') $search['price_min'] = '';

if (isset($search['price_max']) && isset($search['price_min']) )
{
    if ($search['price_max'] != '' && $search['price_min'] == '' && eregi('^[0-9]+$', $search['price_max']))
        $sql.= ' AND price <= "' . $search['price_max'] . '" ';

    if ($search['price_max'] == '' && $search['price_min'] != '' && eregi('^[0-9]+$', $search['price_min']))
        $sql.= ' AND price >= "' . $search['price_min'] . '" ';

    if ($search['price_max'] != '' && $search['price_min'] != '' && eregi('^[0-9]+$', $search['price_min']) && eregi('^[0-9]+$', $search['price_max']))
        $sql.= ' AND price >= "' . $search['price_min'] . '" AND price <= "' . $search['price_max'] . '" ';
}




// Fetch all the approved listings and sort them by
// id (auto incremented) in the descending order
//$sql = 'SELECT  * FROM ' . PROPERTIES_TABLE  . ' WHERE approved = 1 ORDER BY id DESC LIMIT 0,5';

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
    $data['garage'] = getnamebyid ( BASEMENT_TABLE, $f['garage'] );
    $data['basement'] = getnamebyid ( BASEMENT_TABLE, $f['basement'] );

    $data['latitude'] = $f['latitude'] ;
    $data['longitude'] =  $f['longitude'] ;


    $data['new'] = newitem ( PROPERTIES_TABLE, $f['id'], $conf['new_days']);
    $data['updated'] = updateditem ( PROPERTIES_TABLE, $f['id'], $conf['updated_days']) ;
    $data['featured'] = featureditem ( $f['featured'] );

    $data['hits'] = $f['hits'];

    $i++;

    $icount++;

    if ($icount == 3) { $icount = 0; }

    $output[] = $data;




}


?>
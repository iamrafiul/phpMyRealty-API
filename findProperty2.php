<?php

global $db;

$output = array();

$i = 0;
$icount = 0;

$search = array();

$search = array_map ('safehtml', $_POST);

print_r($search);
print_r($_POST);


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




// Fetch all the approved listings and sort them by
// id (auto incremented) in the descending order
$sql = 'SELECT  * FROM ' . PROPERTIES_TABLE  . ' WHERE approved = 1 ORDER BY id DESC LIMIT 0,5';

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

    if ($f_package['mainimage'] == 'ON')
    {
        $template->set ( 'image', show_image ('images', $f['id']) );

        $data['image'] =  URL . '/' . $folder . '/' . $f['id'] . '-resampled.jpg?nocache=' . rand ( 0, 999999 );
    }
    else
    {
        $data['image'] =  URL . '/' . $folder . '/empty.jpg?nocache=' . rand ( 0, 999999 );
    }


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


?>
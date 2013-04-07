<?php

// phpMyRealty 3
//
// File Name: usersearch.php
// File Location : ./
//
// Copyright (c)2009 phpMyRealty.com
//
// e-mail: support@phpMyRealty.com

// Include configuration file and general functions
define('PMR', 'true');

include ( './config.php' );
include ( PATH . '/defaults.php' );

// ----------------------------------------------------------------------
// USER SEARCH SECTION

// Title tag content
$title = $conf['website_name_short'] . ' - ' . $lang['Realtor_Search'];

// Template header
include ( PATH . '/templates/' . $cookie_template . '/header.php' );

// Define an array of all the search elements
$search = array();

// If this is the first time we call this script
// we use the _POST'ed variable, if not, we use
// _GET method
if (isset($_POST['realtor_search']))
{

    $search = array_map ('safehtml', $_POST);
}
else
{
    $search = array_map ('rawurldecode', $_GET);
    $search = array_map ('safehtml', $search);
}

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


// Search cache
// - caches the search variables for the user
// to be able to return to the latest user
// search results from a different page

// Generate GET vars and push it into the session variable
// to be able to return to this search page later
$session->varunset('usersearchvariables');
$searchURL = ''; foreach ($search as $key => $value) $searchURL.= $key . '=' . rawurlencode(unsafehtml($value)) . '&amp;';
$session->set('usersearchvariables', $searchURL);

// Pagination
if (isset($_GET['page'])) $page = $search['page']; else $page = 0;
unset($search['page']);
$results_page = $page * $conf['search_results'];

// Errors output if needed

// Initially we think that no errors were found
$count_error = 0;

/*

// If keyword was not specified we stop processing
if (isset($search['realtor_description']) && empty($search['realtor_description']))
{ $output_error.= $lang['Field_Empty'] . ' <strong><span class="warning">' . $lang['Search_Keyword'] . '</span></strong><br />'; $count_error++; }
//

*/

// If there were errors found we print the 
// errors descriptions
if ($count_error > 0)
{
    echo table_header ( $lang['Information'] );

    echo $lang['Errors_Found'] . ': ' . $count_error;

    echo table_footer ();
}

// Output the results
//
// If no errors found we output the results
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

    // Add LIMIT function to the query for the pagination
    $sql2 .= ' LIMIT ' . $results_page . ', ' . $conf['search_results'];

    // End creating sql query

    echo ' <br /><div align="left">' . $lang['Order_By'] . ' ';

    echo '<a href="' . URL . '/usersearch.php?' . $session->fetch('usersearchvariablespage') . 'sort=1">' . $lang['Realtor_First_Name'] . ' (ASC)</a> | ';
    echo '<a href="' . URL . '/usersearch.php?' . $session->fetch('usersearchvariablespage') . 'sort=2">' . $lang['Realtor_First_Name'] . ' (DESC)</a> | ';
    echo '<a href="' . URL . '/usersearch.php?' . $session->fetch('usersearchvariablespage') . 'sort=3">' . $lang['Realtor_Last_Name'] . ' (ASC)</a> | ';
    echo '<a href="' . URL . '/usersearch.php?' . $session->fetch('usersearchvariablespage') . 'sort=4">' . $lang['Realtor_Last_Name'] . ' (DESC)</a> | ';

    echo '</div><br />';

    echo table_header ( $lang['Realtor_Search'] );

    echo '
   <table width="100%" cellpadding="5" cellspacing="0" border="0">
       ';

    // Fetch number of all possible results
    $r = $db->query($sql) or error ('Critical Error', mysql_error ());

    // Results per page (using LIMIT)
    $r2 = $db->query($sql2) or error ('Critical Error', mysql_error ());
    // Return 'Nothing was found' error
    if ($db->numrows($r) == 0)
        echo '<tr><td align="center"><span class="warning">' . $lang['Nothing_Found'] . '</span></td></tr>';

    $i = 0;

    $tpl = implode ('', file( PATH . '/templates/' . $cookie_template . '/tpl/realtor_search_short.tpl' ));

    while ($f = $db->fetcharray( $r2 ))
    {

        $f = array_map ( 'if_empty', $f);

        // Starting a new template
        $template = new Template;

        // Load user short search results template
        $template->load ( $tpl );

        if ($f['package'] != '0' && $f['package'] != '')
        {

            $sql = 'SELECT * FROM ' . PACKAGES_AGENT_TABLE  . ' WHERE id = ' . $f['package'] . ' LIMIT 1';
            $r_package = $db->query ( $sql );
            $f_package = $db->fetcharray ( $r_package );

        }

        else

        {

            $f_package['listings'] = $conf['free_listings'];
            $f_package['gallery'] = $conf['free_gallery'];
            $f_package['mainimage'] = $conf['free_mainimage'];
            $f_package['photo'] = $conf['free_photo'];
            $f_package['phone'] = $conf['free_phone'];
            $f_package['address'] = $conf['free_address'];

        }

        // Replace the template variables

        if ($conf['rewrite'] == 'ON')
            $template->set ( 'link', URL . '/Realtor/' . $f['id'] . '.html'  );
        else
            $template->set ( 'link', URL . '/viewuser.php?id=' . $f['id']);

        if ($f_package['photo'] == 'ON')
            $template->set ( 'photo', show_image ('photos', $f['id']) );
        else
            $template->set ( 'photo', '' );

        $template->set ( 'first_name', $f['first_name'] );
        $template->set ( 'last_name', $f['last_name'] );
        $template->set ( 'company_name', $f['company_name'] );

        $description = substr(removehtml(unsafehtml($f['description'])), 0, $conf['search_description']);
        $description = substr($description, 0, strrpos($description, ' ')) . ' ... ';
        $template->set ( 'description', $description );
        unset ($description);

        $template->set ( 'location', getnamebyid ( LOCATIONS_TABLE, $f['location'] ) );

        if ($f_package['address'] == 'ON')
        {
            $template->set ( 'address', $f['address'] );
            $template->set ( 'city', $f['city'] );
            $template->set ( 'zip', $f['zip'] );
        }
        else
        {
            $template->set ( 'address', '' );
            $template->set ( 'city', '' );
            $template->set ( 'zip', '' );
        }


        if ($f_package['phone'] == 'ON')
        {
            $template->set ( 'phone', $f['phone'] );
            $template->set ( 'fax', $f['fax'] );
            $template->set ( 'mobile', $f['mobile'] );
        }
        else
        {
            $template->set ( 'phone', '' );
            $template->set ( 'fax', '' );
            $template->set ( 'mobile', '' );
        }


        $template->set ( 'email', validateemail ( $f['id'], $f['email'] ) );
        $template->set ( 'website', validatewebsite ( $f['id'], $f['website'] ) );

        $template->set ( 'view_user_listings', viewuserlistings ( $f['id'] )  );

        $template->set ( 'date_added', $f['date_added'] );
        $template->set ( 'date_updated', $f['date_updated'] );

        $template->set ( 'ip_added', $f['ip_added'] );
        $template->set ( 'ip_updated', $f['ip_updated'] );

        $template->set ( 'hits', $f['hits'] );

        $template->set ( 'new', newitem ( USERS_TABLE, $f['id'], $conf['new_days']) );
        $template->set ( 'updated', updateditem ( USERS_TABLE, $f['id'], $conf['updated_days']) );
        $template->set ( 'top', topitem ( $f['rating'], $f['votes'] ) );

        $template->set ( 'rating', rating ( $f['rating'], $f['votes'] ) );

        // Set background color
        $bgcolor = ''; // Background color for all odd listings
        $bgcolor2 = $conf['list_background_color_even']; // Background color for all even listings

        if ( $i%2 == 0 )
            $template->set ( 'bgcolor', $bgcolor );
        else
            $template->set ( 'bgcolor', $bgcolor2 );

        // Names

        $template->set ( '@first_name', $lang['Realtor_First_Name'] );
        $template->set ( '@last_name', $lang['Realtor_Last_Name'] );
        $template->set ( '@company_name', $lang['Realtor_Company_Name'] );
        $template->set ( '@description', $lang['Realtor_Description'] );
        $template->set ( '@location', $lang['Location'] );
        $template->set ( '@city', $lang['City'] );
        $template->set ( '@address', $lang['Realtor_Address'] );
        $template->set ( '@zip', $lang['Zip_Code'] );
        $template->set ( '@phone', $lang['Realtor_Phone'] );
        $template->set ( '@fax', $lang['Realtor_Fax'] );
        $template->set ( '@mobile', $lang['Realtor_Mobile'] );
        $template->set ( '@email', $lang['Realtor_e_mail'] );
        $template->set ( '@website', $lang['Realtor_Website'] );
        $template->set ( '@date_added', $lang['Date_Added'] );
        $template->set ( '@date_updated', $lang['Date_Updated'] );
        $template->set ( '@hits', $lang['Hits'] );

        $template->set ( '@image_url', URL . '/templates/' . $cookie_template . '/images' );

        // Publish template
        $template->publish();

        $i++;

    }

    echo '
   </table>
       ';

    // Pagination

    echo '<br />';

    // Generate GET vars and push it into the session variable
    // to be able to return to this search page later
    $session->varunset('usersearchvariablespage');
    $searchURL = ''; foreach ($search as $key => $value) $searchURL.= $key . '=' . rawurlencode(unsafehtml($value)) . '&amp;';
    $session->set('usersearchvariablespage', $searchURL);

    $results = $db->numrows( $r );
    // If the number of results is bigger than the
    // minimum allowed in config we print out the
    // pagination
    if ( $results > $conf['search_results'] )

    {
        // Calculating the first and the last pages to show
        // - this makes the pages list smaller by showing the
        // relative results - 3 to the left and 3 to the right
        // from the current page
        if ((($page*$conf['search_results'])-($conf['search_results']*5)) >= 0)
            $first=($page*$conf['search_results'])-($conf['search_results']*5);
        else
            $first=0;

        if ((($page*$conf['search_results'])+($conf['search_results']*6)) <= $results)
            $last =($page*$conf['search_results'])+($conf['search_results']*6);
        else
            $last = $results;

        @    $i=$first/$conf['search_results'];

        // Previous Link
        if ($page > 0)
        {
            $pagenum = $page - 1;
            echo ' <a href="' . URL . '/usersearch.php?page=' . $pagenum  . '&amp;' . $session->fetch('usersearchvariablespage') . '">' . $lang['Previous'] . '</a> | ';
        }

        // Pagination List
        for ( $step = $first; $step < $last; $step=$step+$conf['search_results'] )

        {

            if ( $i == $page )

            {
                $pagenum = $i+1;
                echo ' <span class="warning">' . $pagenum . '</span> | ';
                $i++;
            }

            else

            {

                $pagenum = $i+1;
                echo ' <a href="' . URL . '/usersearch.php?page=' . $i  . '&amp;' . $session->fetch('usersearchvariablespage') . '">' . $pagenum . '</a> | ';
                $i++;

            }

        }

        // Next Link
        if ($page - (($results / $conf['search_results']) - 1) < 0)
        {
            $pagenum = $page+1;
            echo ' <a href="' . URL . '/usersearch.php?page=' . $pagenum  . '&amp;' . $session->fetch('usersearchvariablespage') . '">' . $lang['Next'] . '</a>';
        }

    }

    // Pagination : END

    echo table_footer ();

}

// Template footer
include ( PATH . '/templates/' . $cookie_template . '/footer.php' );

?>
<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rafiul
 * Date: 8/13/12
 * Time: 5:19 AM
 * To change this template use File | Settings | File Templates.
 */

    global $db;
    global $language_in;


    $sql = 'SELECT id, ' . $language_in . ', name ';
    if ($class !== null) {
        $sql .= ', class ';
    }
    $sql .= ' FROM ' . LOCATIONS_TABLE . ' ORDER BY ' . $language_in;
    $r = $db->query ($sql) or error ('Critical Error', mysql_error () );

    $output = array();
    $className = '';
    while ($f = $db->fetcharray ($r) ) {
        // Default value
        if ($f[1] == '') {
            $f[1] = $f['name'];
        }
        if ($class !== null) {
            $className = ' class="'.$class.$f['class'].'"';
        }

        $output []= array('id'=>$f['id'], 'name'=>$f[1]);
    }



?>
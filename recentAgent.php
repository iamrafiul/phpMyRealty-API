<?php
/**
 * Created by JetBrains PhpStorm.
 * User: rafiul
 * Date: 8/13/12
 * Time: 5:24 AM
 * To change this template use File | Settings | File Templates.
 */

                 global $db;

                 $setup_rows = 3;

                $sql = 'SELECT * FROM ' . USERS_TABLE . ' WHERE approved = 1 ORDER BY id DESC LIMIT 30';
                $r = $db->query ( $sql ) or error ('Critical Error', mysql_error () );

                $results_amount = ceil ($db->numrows($r) / $setup_rows);
                $results_total = $db->numrows($r);

                $results = 0;
                $rows = 0;

                $data = array();
                while ($f = $db->fetcharray($r))
                {

                    $rows++;
                    $results++;

                    $data[] = array('first_name'=>$f['first_name'],
                        'last_name'=> $f['last_name'],
                        'location' => getnamebyid(LOCATIONS_TABLE, $f['location'])
                    );
                }

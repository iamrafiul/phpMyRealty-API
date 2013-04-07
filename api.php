<?php
    
    define('PMR', 'true');

    include ( '../config.php' );

    include ( PATH . '/defaults.php' );

	require_once("Rest.inc.php");
	
	class API extends REST
    {
	
		public $data = "";


		public function __construct()
        {
    		parent::__construct();				// Init parent contructor
    	}

     	/*
		 * Public method for access api.
		 * This method dynmically call the method based on the query string
		 *
		 */


		public function processApi($hasMethod)
        {
            if($hasMethod)
            {
			    $func = $_REQUEST['rquest'];
                $this->$func();
            }
            else
            {
                $this->response('',405);
            }
	    }
		
		private function json($data)
        {
			if(is_array($data))
            {
				return json_encode($data);
			}
		}


        private function doRegistration()
        {

            if($this->get_request_method() != "POST")
            {
                $this->response('',406);
            }
            else
            {
                include 'registration.php';
                if ($count_error == '0')
                    $this->response($this->json(array('success'=>'registration successful')), 200);
            }

        }

        private function updateRegistration()
        {

            if($this->get_request_method() != "POST")
            {
                $this->response('',406);
            }
            else
            {
                include 'updateRegistration.php';
                if($rs==false)
                {
                    $this->response($this->json(array('msg'=>'update failed')), 200);
                }
                else
                {
                    $this->response($this->json(array('msg'=>'update success')), 200);
                }

            }



        }

        private function getLocation3()
        {
            if($this->get_request_method() != "GET")
            {
                $this->response('',406);
            }
            else
            {
                global $db;
                global $language_in;

                $location2 = $_GET['catsubsel'];
                $sql = 'SELECT catsubsubsel,subsubcategory FROM ' . LOCATION3_TABLE . ' WHERE catsubsubsel = "' . $location2 . '"';
                $r33 = $db->query($sql);
                $f33 = $db->fetcharray($r33);
                $data['category'] =  $f33['subsubcategory'];
                $data['catsubsubsel'] =  $f33['catsubsubsel'];

                if($data['catsubsubsel']!=null)
                    $this->response($this->json($data), 200);
                else
                    $this->response($this->json(array('msg'=>'no location found')), 200);
            }

        }


        private function getLocation2()
        {


            if($this->get_request_method() != "GET")
            {
                $this->response('',406);
            }
            else
            {
                global $db;
                global $language_in;
                $location = $_GET['selector'];
                $sql = 'SELECT catsubsel,subcategory FROM ' . LOCATION2_TABLE . ' WHERE catsubsel = "' . $location. '"';
                $r22 = $db->query($sql);
                $f22 = $db->fetcharray($r22);
                $data['category'] = $f22['subcategory'];
                $data['catsubsel'] = $f22['catsubsel'];

                if($data['catsubsel']!=null)
                    $this->response($this->json($data), 200);
                else
                    $this->response($this->json(array('msg'=>'no location found')), 200);
            }

        }

        private function getLocation1()
        {

            if($this->get_request_method() != "GET")
            {
                $this->response('',406);
            }
            else
            {
                global $db;
                global $language_in;
                $data = array();
                $location = $_GET['selector'];
                $sql = 'SELECT category,selector FROM ' . LOCATION1_TABLE ;
                $r = $db->query($sql);
                while( $f = $db->fetcharray( $r ) )
                {
                    $data['category'] = $f['category'];
                    $data['selector'] = $f['selector'];
                }

                $this->response($this->json($data), 200);
            }

        }

    //viewuserlistings.php
        private function getOwnList()
        {
            include 'ownlist.php';

            if($this->get_request_method() != "GET")
            {
                $this->response('',406);
            }
            else
            {
                $this->response($this->json($output), 200);
            }
        }



        private function getUserInfo()
        {
            global $db;

            if($this->get_request_method() != "POST")
            {
                $this->response('',406);
            }
            else
            {
                $sql = "SELECT * FROM " . USERS_TABLE . " WHERE approved = 1 AND  id='".$_POST['id']."'";
                $r = $db->query( $sql );
                $f = $db->fetcharray( $r );

                // If this account is approved and approval is required, allow them to continue..
                if ((isset($f['approved']) && $f['approved'] == 1))
                {
                    $data =array( 'success'=>'valid user',
                                  'user_id'=>$f['id'],
                                  'password'=>$f['password'],
                                  'first_name'=>$f['first_name'],
                                  'last_name'=>$f['last_name'],
                                  'company_name'=>$f['company_name'],
                                  'description'=>$f['description'],
                                  'location'=>$f['location'],
                                  'city'=>$f['city'],
                                  'zip'=>$f['zip'],
                                  'address'=>$f['address'],
                                  'email'=>$f['email'],
                                  'fax'=>$f['fax'],
                                  'mobile'=>$f['mobile'],
                                  'phone'=>$f['phone'],
                                  'website'=>$f['website']);

                    $this->response($this->json($data), 200);
                }
                else
                {
                    $this->response($this->json(array('success'=>'invalid')), 200);
                }
            }



        }


        private function authenticateCheck()
        {
            global $db;

            if($this->get_request_method() != "POST")
            {
                $this->response('',406);
            }
            else
            {
                $sql = "SELECT approved, id, package FROM " . USERS_TABLE . " WHERE approved = 1 AND  login = '" . strtolower($_POST['login']) . "' AND password='".md5($_POST['password'])."' LIMIT 1";
                $r = $db->query( $sql );
                $f = $db->fetcharray( $r );

                // If this account is approved and approval is required, allow them to continue..
                if ((isset($f['approved']) && $f['approved'] == 1))
                {
                    $data =array( 'success'=>'valid user',
                              'user_id'=>$f['id']);
                    $this->response($this->json($data), 200);
                }
                else
                {
                    $this->response($this->json(array('success'=>'invalid')), 200);
                }
            }



        }


        private function recentAgent()
        {

            //Array ( [0] => Array ( [first_name] => asm [last_name] => hossain [location] => Alabama ) )

            if($this->get_request_method() != "GET")
            {
                $this->response('',406);
            }
            else
            {
                include 'recentAgent.php';
                $this->response($this->json($data), 200);
            }

        }

        private function findAgents()
        {
            // For INPUT  [realtor_first_name] => asm [realtor_last_name] => [realtor_company_name] => [realtor_description] => [realtor_location] => 1 [realtor_city] => [realtor_address] => [realtor_zip_code] => [realtor_phone] => [realtor_fax] => [realtor_mobile] => [realtor_e_mail] => [realtor_website] => [realtor_login]
            // For Output Array ( [0] => Array ( [first_name] => asm [last_name] => hossain [company_name] => self [description] => noting [location] => 1 [city] => CA [zip] => 4234 [address] => CA, House No: 24/C, CAL [website] => [image] => http://localhost/pmr//photos/1-resampled.jpg?nocache=827453 ) )

            if($this->get_request_method() != "POST")
            {
                $this->response('',406);
            }
            else
            {
                include 'findAgents.php';
                $this->response($this->json($data), 200);
            }

        }

        private function getLocation()
        {
            if($this->get_request_method() != "GET")
            {
                $this->response('',406);
            }
            else
            {
                include 'getLocation.php';
                $this->response($this->json($output), 200);
            }

        }

        private function contactUS()
        {
            if($this->get_request_method() != "POST")
            {
                $this->response('',406);
            }
            else
            {
                include 'contactus.php';
                $this->response($this->json(array('success'=>'Thank you message for contact with US.')), 200);
            }
        }

        private function recentListing()
        {
            if($this->get_request_method() != "GET")
            {
                $this->response('',406);
            }
            else
            {
                include 'recentListing.php';
                $this->response($this->json($output), 200);
            }
        }

       private function featureListing()
        {
            if($this->get_request_method() != "GET")
            {
                $this->response('',406);
            }
            else
            {
                include 'featurelisting.php';
                $this->response($this->json($output), 200);
            }
        }

        private function findProperty()
        {
            if($this->get_request_method() != "GET")
            {
                $this->response('',406);
            }
            else
            {
                include 'findProperty.php';
                $this->response($this->json($output), 200);
            }
        }

    }


	$api = new API;

    if(method_exists($api,$_REQUEST['rquest']))
        $api->processApi(true);
    else
        $api->processApi(false);
?>
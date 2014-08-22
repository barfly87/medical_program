<?php
class Compass {
    
    private static $user = null;
    private static $uid = null;
    private static $uidCounts = 0;
    private static $config = array();
    private static $relativeUrl = '';
    private static $displayError = null;
    
    public static function baseUrl() {
        return Zend_Controller_Front::getInstance()->getBaseUrl();
    }
    
    public static function getRelativeUrl() {
        if(empty(self::$relativeUrl)) {
            if(isset($_SERVER['PHP_SELF'])) {
                $self = $_SERVER['PHP_SELF'];
                $self = str_replace('/index.php/', '/', $self);
                $query = !empty($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : null;
                $url = !empty($query) ? "$self?$query" : $self;
                $url = str_replace(Compass::baseUrl().'/htdocs', Compass::baseUrl(), $url);
                self::$relativeUrl = $url;
            }
        }
        return self::$relativeUrl;
    }
    
    public static function request($key = null) {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $return['module']       = $request->getModuleName();
        $return['controller']   = $request->getControllerName();
        $return['action']       = $request->getActionName();
        $return['searchType']   = $request->getParam('searchtype','');
        $return['context']      = $request->getParam('context','');
        $return['name']        = $request->getParam('name','');
        if(!is_null($key) && isset($return[$key])) {
            return $return[$key];
        }
        return $return;
    }
    
    public static function userInfoHtml($uidStr) {
        try {
            $uidStr = trim($uidStr);
            $return = $uidStr;
            $returnHtml = '';
            if(strlen($uidStr) > 0 ) {
                if(strpos($uidStr,',')) {
                    $uids = explode(',',$uidStr);
                    foreach($uids as $uid) {
                        if(strlen(trim($uid)) > 0) {
                            $returnHtml .= self::getUserInfoHtml($uid); 
                        }
                    }
                    if(!empty($returnHtml)) {
                        return $returnHtml;
                    }
                } else {
                    return self::getUserInfoHtml($uidStr);
                }
                
            }
            return $return;
        } catch (Exception $ex) {
            return '';
        }        
    }

    public static function userInfoHtmlOnly($uid) {
    	try {
    		$user = LdapCache::getUserDetails($uid);
    		if($user !== false) {
    			self::$user     = $user; 
    			self::$uid      = $uid;
    			
    			$image_html                 = self::getImageHtmlOnly("60");
    			$fullname_html              = self::getFullNameHtmlOnly();
    			$phone_html                 = self::getPhoneHtml();
    			$titlefaculty_html          = self::getTitleFacultyHtml();
    			$email_profile_accttool_html= self::getEmailProfileAccttoolHtml();
    			$return = <<<HTML
    				<div style="clear:left">
    					<div style="float:left;" >{$image_html}</div>
    					{$fullname_html}<br />
    					{$titlefaculty_html}
    					{$phone_html}
    					{$email_profile_accttool_html}
    				</div>
HTML;
    		}
    		return $return;
    	} catch (Exception $ex) {
    		return '';
    	}
    }
    
    public static function getUserName($uid) {
    	$return = $uid;
    	$user = LdapCache::getUserDetails($uid);
    }
    
    private static function getUserInfoHtml($uid) {
        $return = $uid;
        $user       = LdapCache::getUserDetails($uid);
        if($user !== false) {
            self::$user     = $user;    
            self::$uid      = $uid;
            $uidCount       = ++self::$uidCounts;
            
            $image_html                 = self::getImageHtml();
            $fullname_html              = self::getFullNameHtml($uidCount);
            $phone_html                 = self::getPhoneHtml();                    
            $titlefaculty_html          = self::getTitleFacultyHtml();
            $email_profile_accttool_html= self::getEmailProfileAccttoolHtml();
            
            $return = <<<HTML
                <div>{$fullname_html}   
                    <div id="user_{$uidCount}" style="display:none;padding-bottom:4px;">  
                        <div style="float:left;">
                            {$image_html}
                        </div>
                        {$titlefaculty_html}
                        {$phone_html}
                        {$email_profile_accttool_html}
                    </div>
                </div>                                                    
HTML;
            self::$user     = null;    
            self::$uid      = null;
        }
        return $return;
    }
    
    public static function getField($fieldName) {
        $result = (isset(self::$user[$fieldName][0]) && !empty(self::$user[$fieldName][0])) ? trim(self::$user[$fieldName][0]):'';
        if ($fieldName == 'mail') {
        	$fromemail = Compass::getConfig("studentemaildomain.from");
			$toemail = Compass::getConfig("studentemaildomain.to");
	        if (Utilities::isStudent(self::$uid)) {
				$result = str_replace($fromemail, $toemail, $result);
			}
        }
        return $result;
    }
    
    private static function getTitleFacultyHtml() {
        $titlefaculty_html = '';
        $title = self::getField('title');
        $faculty = self::getField('o');
        $titlefaculty   = '';
        if(!empty($title) && !empty($faculty)) {
            $titlefaculty = $title.', '.$faculty;
        } else if(!empty($faculty)) {
            $titlefaculty = $faculty;
        } else if(!empty($title)) {
            $titlefaculty = $title;    
        }
        if(!empty($titlefaculty)) {
            $titlefaculty_html = $titlefaculty.'<br />';
        }
        return $titlefaculty_html;     
    }    
    
    private static function getPhoneHtml() {
        $phone_html = '';
        $phone = self::getField('telephonenumber');
        if(!empty($phone)) {
            $phone_html = $phone.'<br />';
        }
        return $phone_html;
    }
    
    private static function getFullNameHtmlOnly() {
    	return strip_tags(self::getFullNameHtml(1));
    }
    
    private static function getFullNameHtml($uidCount) {
        $fullname_html = '';
        $salutation = self::getField('chsedupersonsalutation');
        $name = self::getField('cn');
        $fullname       = (!empty($salutation)) ? $salutation.' '.$name : $name ;
        $uid = self::$uid;
        if(!empty($fullname)) {
            $fullname_html = <<<FULLNAME
<a href="javascript:void(0);" onclick="javascript:$('#user_{$uidCount}').toggle();" class="username" style="color:blue;text-decoration:none;">{$fullname}</a>
FULLNAME;
        }
        return $fullname_html;
    }
    
    private static function getEmailProfileAccttoolHtml() {
        $email_profile_accttool_html = '';
        //Email
        $email = self::getField('mail');
        if(!empty($email)) {
            $email_profile_accttool_html .= "<a href='mailto:{$email}'>{$email}</a>&nbsp;&nbsp;&nbsp;"; 
        }
        
/*        //Profile
        $profile = sprintf(UserService::$profile, self::$uid);
        $email_profile_accttool_html .= "<a href='{$profile}'>profile</a>&nbsp;&nbsp;&nbsp;";
        
        //Account Tool
        if(UserAcl::isAdmin()) {
            $account_tool = sprintf(UserService::$accountTool,self::$uid);
            $email_profile_accttool_html .= "<a href='{$account_tool}'>acct tool</a>&nbsp;&nbsp;";    
        }*/
        return $email_profile_accttool_html.'<br />';
    }
    
    public static function getImageHtmlOnly($width = 36) {
    	$image_html = '';
/*    	$tempimage = sprintf(UserService::$image, self::$uid);
    	$image = '';
    	if (@getimagesize($tempimage) !== false) {
    		$image = $tempimage;
    	}
    	if (!empty($image)) {
    		$image_html =  "<img width=\"$width\" src=\"$image\" style=\"padding: 5px 20px 5px 0px\" />";
    	}*/
    	return $image_html; 
    }
    
    private static function getImageHtml() {
        $image_html = '';
/*        $tempimage      = sprintf(UserService::$image, self::$uid);
        $image          = '';
        if (@getimagesize($tempimage) !== false) {
            $image = $tempimage;
        }
        
        if(!empty($image)) {
            $onerrorUrl = self::baseUrl().'/img/noimage/empty1x1.gif';
            $image_html .= <<<IMAGE
<a href="{$image}" rel="prettyPhoto" style="color:#FFF;">            
<img width="36" style="padding: 5px 5px 35px 0px;cursor:pointer;border:0px;" src="{$image}" onerror="this.src='{$onerrorUrl}'; this.width='1';"/></a>
IMAGE;
        }*/
        return $image_html; 
    }
    
    public static function addRouters($router) {
        $router->addRoutes(
            array(
                'podcast_resource_subaction'    => new Zend_Controller_Router_Route(
                                                    '/podcast/resource/:subaction/*',
                                                    array('controller'=>'podcast', 'action'=>'resource', 'module'=>'default', 'subaction'=>'')
                                                ),
                'resource_loose_subaction'      => new Zend_Controller_Router_Route(
                                                    '/resource/loose/:subaction/*',
                                                    array('controller'=>'resource', 'action'=>'loose', 'module'=>'default', 'route'=>'default','subaction'=> '')
                                                )
            )
        );
        
    }
    
    public static function removeDirectory($dir) {
        try {
            foreach (glob($dir.'/*') as $filename) {
                if(is_file($filename)) {
                    unlink($filename);
                } else if(is_dir($filename)) {
                    self::removeDirectory($filename);
                    rmdir($filename);
                }
            }
            return true;
         } catch (Exception $ex) {
            $error = $ex->getMessage().PHP_EOL.$ex->getTraceAsString().PHP_EOL;
            Zend_Registry::get('logger')->warn(PHP_EOL."CLASS\t: ".__CLASS__.PHP_EOL."METHOD\t: ".__METHOD__.PHP_EOL."Error\t: ".$error.PHP_EOL);
            return false;
         }
    }        
    
    /**
     * Returns string or array depending upon what is requested through $option
     * @param $option e.g podcast.rss.channel.title or podcast
     * @return mixed
     */
    public static function getConfig($option) {
        // If empty string received return back
        $option = trim($option);
        if(empty($option)) {
            return '';
        }
        if(isset(self::$config[$option])) {
            return self::$config[$option];
        }
        //get config object
        $config = Zend_Registry::get('config');
        $keyValues = explode('.', $option);

        $return = '';
        $stack = $config;
        foreach($keyValues as $keyValue) {
            //$stack should not be empty
            if(!empty($stack)) {
                if(isset($stack->$keyValue)) {
                    $stack = $stack->$keyValue;
                } else {
                    //If we do not find key or value $stack value should be empty 
                    //and further looping would be stopped automatically since we are 
                    //checking for if $stack is empty or not
                    $stack = '';
                }
            }
        }
        // If its a string return as string
        // else if its an array return as an array
        if(is_string($stack)) {
            $return = $stack;
        } else if( is_object($stack) && method_exists($stack, 'toArray')){
            $return = $stack->toArray();
        }
        self::$config[$option] = $return;
        return $return;
    }
    
    public static function displayError() {
        if(is_null(self::$displayError)) {
            $displayError = false;
            $uidsString = Compass::getConfig('devteam.uids');
            if(!empty($uidsString)) {
                $explode = explode(",", $uidsString);
                $uids = array();
                foreach($explode as $uid) {
                    $uids[] = trim($uid);
                }
                $loggedInUid = UserAcl::getUid();
                if(in_array($loggedInUid, $uids)) {
                    self::$displayError = true;
                } else {
                    self::$displayError = false;
                }
            } else {
                self::$displayError = false;
            }
        }
        return self::$displayError;
    }
    
    public static function error($error, $class = "", $lineNo="", $errorTitle = "") {
        if(!empty($error)) {
            if(empty($errorTitle)) {
                $errorTitle = "Error\t";
            }
            $classText = '';
            if(!empty($class)) {
                $classText = PHP_EOL."Class\t: ".$class;
            }
            $lineNoText = '';
            if((int)$lineNo > 0) {
                $lineNoText = PHP_EOL."Line No\t: ".$lineNo;
            }
            Zend_Registry::get('logger')->warn(PHP_EOL.$errorTitle.": ".$error.$classText.$lineNoText);
            if(self::displayError()) {
                $tr = '<tr><td style="border:1px solid #FBC490;padding:2px 6px;"><span class="red">%s</span></td><td style="border:1px solid #FBC490;padding:2px 6px;">%s</td></tr>';
                $displayError = sprintf($tr, 'Error', $error);
                if(!empty($class)) {
                    $displayError .= sprintf($tr, 'Class', $class);
                }
                if(!empty($lineNo)) {
                    $displayError .= sprintf($tr, 'Line no', $lineNo);
                }
                echo '<div><table style="border:1px solid #FBC490; border-collapse:collapse;">'.$displayError.'</table></div>';
            }
        }
    }
    
    public static function csvToArray($str) {
        if(!is_string($str)) {
            echo 'Parameter given should be a string and not '. gettype($str);
            return $str;
        }
        if(strstr($str, ',')) {
            $return = array();
            $parts = explode(',', $str);
            foreach($parts as $part) {
                $return[] = trim($part);
            }
            return $return;
        } else {
            return trim($str);
        }
    }
    
    public static function isConnectedToEvents() {
        return (Compass::getConfig('event_wsdl_uri') != '') ? true : false;
    }
    
}
?>
<?php
class PeopleService {
    private static $ds = null; 

    public  function __construct() {
        self::$ds = Zend_Registry::get('ds');
    }
    /**
     * returns a list of photos for a uid or list of uids
     * if it is passed a string, it will return an array of MIDs
     * if it is passed an array, it will return an array, mapping uids to arrays if MIDs
     */
    public static function getPhotoList($uid, $query=null) {
    	$config = Zend_Registry::get('config');
		$mediabankConstants = new MediabankConstants();
        $mediabank = $mediabankConstants->mediabank;
        $uidtxt = $uid;
        if(is_array($uid))
        	$uidtxt = '('.implode(' ',$uid).')';
        $searchQuery = "+collectionID:".$config->people->studentphotocollection." +native.uid:".$uidtxt;
        if($query != null)
        	$searchQuery .= ' '.$query;
        //$resourceService = new MediabankResourceService();
        $user = MediabankResourceConstants::getMediabankuserObjForSearch();
        
        $hits = $mediabank->search("Lucene Index", $searchQuery, $user, false);

        $mids = array();
        if (!empty($hits)) {
			foreach($hits as $hit) {
				$mid=$hit->mepositoryID->__toString();				
				if(is_array($uid))
					$mids[$hit->attributes['uid']][]=$mid;
				else
					$mids[]=$mid;
			}
			if (is_array($uid)) {
				foreach ($mids as $v) {
					natsort($v);
				}
			} else {
				natsort($mids);
			}
        }        
		return($mids);
    }
    
	public static function getEditablePhotoList($uid) {
		return(PeopleService::getPhotoList($uid, '-native.phototype:official'));
	}
	/**
	 * Gets the default photo. Returns a list of MIDs, in case by some error there is more than one photo
	 * Modified by daniel, 2011-12-28
	 */
	public static function getDefaultPhotoList($uid) {
		return(PeopleService::getPhotoList($uid, '+native.defaultphoto:true'));
		
	}
	
	public static function getOfficialPhotoList($uid) {
		return(PeopleService::getPhotoList($uid, '+native.phototype:official'));
	}
	
	/**
	 * Gets the default photo. Gets the first default photo, and failing that, gets the first photo
	 * Modified by daniel, 2011-12-28
	 */
	public static function getDefaultPhoto($uid) {
		$mids = PeopleService::getDefaultPhotoList($uid);
		if(count($mids)==0) {
			$mids = PeopleService::getPhotoList($uid);
		}
		if(count($mids)>0) {
			if(!isset($mids[0]) || is_array($mids[0])) {
				foreach($mids as $uid => $midlist) 
					$mids[$uid] = $midlist[0];
				return($mids);
			} else
				return($mids[0]);
		} else
			return false;
		
	}
	
	public static function getOfficialPhoto($uid) {
		$mids = PeopleService::getOfficialPhotoList($uid);
		if(count($mids)>0) {
			if(!isset($mids[0]) || is_array($mids[0])) {
				foreach($mids as $uid => $midlist) 
					$mids[$uid] = $midlist[0];
			} else {
				return($mids[0]);
			}
		} else {
			return false;
		}
	}
	/**
	 * Sets the default photo, unsets all other default photos
	 * Modified by daniel, 2011-12-28
	 */
	public static function setDefaultPhoto($uid, $mid) {
		//first, unset all the old photos
		$old_defaults = PeopleService::getDefaultPhotoList($uid);
		foreach($old_defaults as $old_default) {
			$changes = array("defaultphoto" => "false");
			if($mid != $old_default)
				PeopleService::modifyPhoto($old_default, $changes);
		}
		//then, set the new one
		if(!in_array($mid, $old_defaults)) {
			$changes = array("defaultphoto" => "true");
			PeopleService::modifyPhoto($mid, $changes);
		}
		return(true);
	}
	
	public static function setOfficialPhoto($uid, $mid) {
		//first, unset all the old official photos
		$old_officials = PeopleService::getOfficialPhotoList($uid);
		foreach($old_officials as $old_official) {
			$changes = array("phototype" => 'user');
			if($mid != $old_official)
				PeopleService::modifyPhoto($old_official, $changes);
		}
		//then, set the new one
		if(!in_array($mid, $old_officials)) {
			$changes = array("phototype" => "official");
			PeopleService::modifyPhoto($mid, $changes);
		}
		return(true);
	}
	
    public static function modifyPhoto($mid, $changes) {
    	//check if it's in the right collection
		$config = Zend_Registry::get('config');
    	$collID = explode('|', $mid);
    	$collID=$collID[1];
    	if($collID != $config->people->studentphotocollection) {
    		echo "Error: not in student photo collection";
    		return(false);
    	}
    	//load metadata
    	$mediabankConstants = new MediabankConstants();
        $mediabank = $mediabankConstants->mediabank;
        $user = MediabankResourceConstants::getMediabankuserObj($mid);
        $metadata = $mediabank->getMetadata($mid,MediabankResourceConstants::$SCHEMA_native, $user);

        $mdarr = XMLService::createArrayFromXml($metadata);
        $root = array_keys($mdarr);
        $root = $root[0];
        foreach($changes as $field => $value) {
        	$mdarr[$root][$field] = $value;
        }
        $mdarr[$root]['lastmodified'] = time();
        $mdarr[$root]['modifiedby'] = UserAcl::getUid();;
        
        $newMetadata = XMLService::createXMLfromArray($mdarr);
        // Now upload the modification
        $cid = $config->people->studentphotocollection;
        $dir = "/tmp";//sys_get_temp_dir();//MediabankResourceConstants::$tempDir;
        $metafile =  $dir.'/'.UserAcl::getUid().time();
        file_put_contents($metafile, $newMetadata);
		$postData = array(
			"mid" => $mid,
			"cid" => $cid,
			"metadataFile" => '@'.$metafile,
			);
		$mediabankUtility = new MediabankUtility();
		$mid = $mediabankUtility->updateResource($postData);
        
        //print_r($mediabank);        
    	return(true);
    }
	
}
?>

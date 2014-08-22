<?php
class MediabankUtility {
    private $ifModifiedSince = null;
    private $imageHeight = null;
    private $imageWidth = null;
    
    public function __construct($data = array()) {
        if(isset($data['ifModifiedSince'])) {
            $this->ifModifiedSince = $data['ifModifiedSince'];
        }
        if(isset($data['imageHeight'])) {
            $this->imageHeight = $data['imageHeight'];
        }
        if(isset($data['imageWidth'])) {
            $this->imageWidth = $data['imageWidth'];
        }
    }
    
    /*
     * This function takes $postData array with information about post params
     * and returns the output which is mid
     * 
     * @param array $postData
     * @param string $url
     * @return string $mid
     */
    public function curlPostData ($postData, $url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        //if($fp = tmpfile()){
        //	  curl_setopt ($ch, CURLOPT_STDERR, $fp);
        //}
        ob_start();
        curl_exec($ch);
        $mid = ob_get_contents();
        ob_end_clean();
        curl_close($ch);
        return trim($mid);
    }

    /*
     * This function creates url and downloads resource
     * 
     * @param string $mid 
     */
    public function curlDownloadResource ($mid, $passthruheaders=false){
        if(is_object($mid)) {
            $mid = $mid->__toString();
        }
        $downloadUrl = MediabankConstants::downloadUrl().$mid;
        $this->curlUrlGet($downloadUrl,$passthruheaders);
    }

    function hasheader($header){
    	$len = strlen($header);
    	foreach(headers_list() as $sentheader) {
    		if(!strncasecmp($sentheader,$header,$len))
    			return(true);
    	}
    	return(false);
    }
    function header_callback($ch, $header_line){
       if(!strncasecmp($header_line,'Content-type',12))
        	if(strpos($header_line,"xml")<=0)
        		if(!$this->hasheader('Content-type'))
        			header($header_line);
        if(!strncasecmp($header_line,'Content-Disposition',12))
        	if(!$this->hasheader('Content-Disposition'))
        		header($header_line);
        if(!strncasecmp($header_line,'Content-Length',12))
        	if(!$this->hasheader('Content-Length'))
        		header($header_line);
       
        if(!strncasecmp($header_line,'X-Mediabank',11))
        	if(!$this->hasheader('X-Mediabank'))
        		header($header_line);
        
        if(!strncasecmp($header_line,'ETag',4))
        	if(!$this->hasheader('Etag'))
        		header($header_line);
        
        if(!strncasecmp($header_line,'Accept-Ranges',13))
        	if(!$this->hasheader('Accept-Ranges'))
        		header($header_line);
        
        if(!strncasecmp($header_line,'Content-Range',13))
        	if(!$this->hasheader('Content-Range'))
        		header($header_line);
        
        if(!strncasecmp($header_line,'Content-Length',14))
        	if(!$this->hasheader('Content-Length'))
        		header($header_line);
        
        if(!strncasecmp($header_line,'Last-modified',13))
        	if(!$this->hasheader('Last-modified'))
        		header($header_line);
        
        if(!strncasecmp($header_line,'HTTP/',5)){
            if(!empty($this->imageWidth) && !empty($this->imageHeight)) {
                $parts = explode(' ',$header_line);
                if(in_array($parts[1], array('200','304'))) {
                    header($header_line);
                    header("Pragma: public");
                } else {
                    $url = MediabankResourceConstants::getNoImageFoundURL($this->imageWidth, $this->imageHeight);
                    if(!empty($url)) {
                        header('Location: '.$url);
                    } else {
                        throw new Zend_Controller_Action_Exception("Page not found.", 404);
                    }
                    exit;
                }
            }
        }
        return strlen($header_line);
    }
    /* 
     * This function opens the url and dumps the output
     * 
     * @param string $url // http://www.domain.com.au?param1=value1&param2=value2
     */
    public function curlUrlGet ($url, $passthruheaders=false){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        if($passthruheaders) {
        	curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, 'header_callback')); 
        }
        if(! is_null($this->ifModifiedSince)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("If-Modified-Since: ".$ifModifiedSince));
        } 
        
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        if($fp = tmpfile()){
            curl_setopt ($ch, CURLOPT_STDERR, $fp);
        }
        curl_exec($ch);
        curl_close($ch);
    }
    
    /* $postData param looks like for compass resources
     * array (
     *     'cid' 			=> 'compassresources',		
     *     'metadataFile' 	=> '@/tmp/userId1243216195', 
     *     'dataFile0' 		=> '@/tmp/php8YTRf3',
     *   )
     *   
     * $postData['metadataFile'] contains the location of XML file one the file system. 
     * It contains metadata info. '@' sign is prepended so curl knows it is file we are trying to send.
     * XML file for compass resources looks like
     * <?xml version="1.0"?>
     * 	<metadata>
     * 		<title>ctitle</title>
     * 		<description>MYDESC</description>
     * 		<copyright>University of Sydney</copyright>
     * 		<uid>ksoni</uid>
     * 		<firstname>Kamal</firstname>
     * 		<lastname>Soni</lastname>
     * 		<ipaddress>127.0.0.1</ipaddress>
     * </metadata>
     * 
     * $postData['dataFile0'] contains the location of the actual resource on the file system you trying
     * to send. '@' sign is prepended so curl knows it is file we are trying to send.
     * 
     * @param array $postData
     * @return string $mid
     */
    public function addResource($postData) {
        $addUrl = MediabankConstants::addUrl();
        return $this->curlPostData($postData, $addUrl);
    }

    /* If you don't send $postData['metadataFile'] or $postData['dataFile0'] the resource is unchanged. But if any
     * of this files are passed it replaces the old ones.
     * 
     * $postData param for compass resources looks like 
     * array (
     * 		'mid' 			=> 'http://smp.sydney.edu.au/mediabank/|compassresources|23',
     *     	'cid' 			=> 'compassresources',		
     *     	'metadataFile' 	=> '@/tmp/UPDATEDuserId1243216195', 
     *     	'dataFile0' 	=> '@/tmp/UPDATEDphp8YTRf3',
     *   )
     * 
     * $postData['mid'] contains the mid for which the update is happening
     *   
     * $postData['metadataFile'] contains the location of 'XML file' in the file system. 
     * '@' sign is prepended so curl knows it is file we are trying to send. It contains metadata info.
     * XML file for compass resources looks like
     * <?xml version="1.0"?>
     * 	<metadata>
     * 		<title>UPDATEDctitle</title>
     * 		<description>UPDATEDMYDESC</description>
     * 		<copyright>UPDATEDUniversity of Sydney</copyright>
     * 		<uid>UPDATEDksoni</uid>
     * 		<firstname>UPDATEDKamal</firstname>
     * 		<lastname>UPDATEDSoni</lastname>
     * 		<ipaddress>UPDATED127.0.0.1</ipaddress>
     * </metadata>
     * 
     * $postData['dataFile0'] contains the location of the actual resource on the file system you trying
     * to send. '@' sign is prepended so curl knows it is file we are trying to send.
     * 
     * @param array $postData
     * @return string $mid
     */
    public function updateResource($postData) {
        $updateUrl = MediabankConstants::updateUrl();
        return $this->curlPostData($postData, $updateUrl);
    }
    
}
?>
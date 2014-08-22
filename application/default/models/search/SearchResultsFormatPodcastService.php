<?php
class SearchResultsFormatPodcastService {
    
    private $_typeXpaths = array(); 
    private $_yearXpaths = array();
    private $_recordingsOnlyXpath = array();
    private $_podcastLuceneField = null;
    private $_xpath = null;
    private $_podcastTitle = '';
    private $_itemAttributeFileFormat = null;
    private $_itemAttributeYear = null;
    private $_itemAttributeMediabankCollection = null;
    private $_pid = '';
    
    public function __construct() {
        $this->_itemAttributeFileFormat             = RssPodcastItem::attributeFileFormat;
        $this->_itemAttributeYear                   = RssPodcastItem::attributeYear;
        $this->_itemAttributeMediabankCollection    = RssPodcastItem::attributeMediabankCollection;
    }
    
    public function process($searchResults, $request) {
        $this->_processHeaders();
        // Get podcast details to create XPath which would be searched against the XML string 
        // as stored in lucenefield 'ta_resource_podcast'
        $this->_processRequest($request);
        // Get Lucenefield 'ta_resource_podcast' which contains xml string and will 
        // be used for producing rss
        $columns = SearchConstants::columns();
        if(isset($columns[54]['luceneIndex'])) {
            $this->_podcastLuceneField = $columns[54]['luceneIndex'];
        } else {
            die('Podcast lucene field has been changed or removed.');
        }
        $this->getPodcast($searchResults);
    }
    
    private function _processRequest(Zend_Controller_Request_Abstract $request) {
        $paths = array();
        //Process request param 'podcasts'
        $podcasts = $request->getParam(PodcastConst::formPodcasts,array());
        $this->_pid = $request->getParam('pid', '');
        if(!empty($podcasts)) {
            foreach($podcasts as $podcast) {
                if(strlen(trim($podcast)) > 0) {
                    $this->_typeXpaths[] = "@{$this->_itemAttributeFileFormat}='$podcast'";
                }
            }
            if(!empty($this->_typeXpaths)) {
                $paths[] ='('. implode(' or ', $this->_typeXpaths).')';
            }
        }
        //Process request param 'podcast-years'
        $years = $request->getParam(PodcastConst::formPodcastYears,array());
        if(!empty($years)) {
            if(count($years) == 1 && $years[0] == PodcastConst::formYearsAll) {
                $years = PodcastUrlService::getNoOfYearsAllowedToCreateUrl();
            }
            foreach($years as $year) {
                if((int)$year > 0) {
                    $this->_yearXpaths[] = "@{$this->_itemAttributeYear}='".(int)$year."'";
                }
            }
        }
        //Process request param 'podcast-resource-types'
        $resourceType = $request->getParam(PodcastConst::formResourceType, null);
        if(!empty($resourceType) && $resourceType == PodcastConst::formResourceTypeRecordingsOnly) {
            $recordingMediabankCollections = array(MediabankResourceConstants::$COLLECTION_echo360, MediabankResourceConstants::$COLLECTION_lectopia);
            foreach($recordingMediabankCollections as $collection) {
                $this->_recordingsOnlyXpath[] = "@{$this->_itemAttributeMediabankCollection}='$collection'";
            }
            $paths[] ='('. implode(' or ', $this->_recordingsOnlyXpath).')';
        } else {
            $this->_yearXpaths[] = "@{$this->_itemAttributeYear}='n/a'";
        }
        
        if(!empty($this->_yearXpaths)) {
            $paths[] ='('. implode(' or ', $this->_yearXpaths).')';
        }
        
        //Process request param 'podcast_title'
        $podcastTitle = $request->getParam('podcast_title', '');
        if(!empty($podcastTitle)) {
            $this->_podcastTitle = base64_decode($podcastTitle);
        }
        
        //Based on request params the XPath might look like this. 
        //eg. $this->_xpath =  "//item[
        //                                (@file-format='audio' or @file-format='video' or @file-format='image' or @file-format='pdf') 
        //                            and (@year='2012' or @year='2011' or @year='2010' or @year='2009' or @year='n/a')
        //                            and (@mediabank-collection='blah' or @mediabank-collection='blah')
        //                      ]";
        if(!empty($paths)) {
            $this->_xpath = '//item['.implode(' and ', $paths).']';
        } 
    }
    
    private function _processHeaders() {
        //header('Content-Type: text/xml');        
    }   
    
    private function getPodcast($searchResults) {
        $xmlString = '';
        $data = array();
        
        //Process lucene results which were returned after performing a search.
        if(isset($searchResults['results']['context']) && !empty($searchResults['results']['context'])) {
            $data = $searchResults['results']['context'];    
        }
        //Get Rss Xml Template
        $domDest = $this->getPodcastRssTemplate();
        $taTitles = array();
        
        $channel = $domDest->getElementsByTagName('channel')->item(0);
        if(!is_null($this->_xpath)) {
            foreach($data as $taId => $taData) {
                //Grab lucene field 'ta_resource_podcast'
                if(isset($taData[$this->_podcastLuceneField])) {
                    $podcastXml = trim($taData[$this->_podcastLuceneField]);
                    if(!empty($podcastXml)) {
                        //Create DOMDocument using xml string returned from lucene field 'ta_resource_podcast'
                        $domSource = new DOMDocument();
                        $domSource->loadXML($podcastXml);
                        
                        //Perform xpath query based on the request parameters received.
                        $xpath = new DOMXPath($domSource);
                        $items = $xpath->query($this->_xpath);
                        
                        if(count($items) > 0) {
                            foreach($items as $item) {
                                // 'type' attribute is added for every 'item' element stored in 'ta_resource_podcast' xml
                                // It needs to be removed for rss purposes
                                if($item->hasAttribute($this->_itemAttributeFileFormat)) {
                                    $item->removeAttribute($this->_itemAttributeFileFormat);
                                }
                                if($item->hasAttribute($this->_itemAttributeYear)) {
                                    $item->removeAttribute($this->_itemAttributeYear);
                                }
                                if($item->hasAttribute($this->_itemAttributeMediabankCollection)) {
                                    $item->removeAttribute($this->_itemAttributeMediabankCollection);
                                }
                                $guidArr = array();
                                $guidArr[] = $this->_pid;
                                foreach($item->childNodes as $childNode) {
                                    if($childNode->nodeName == 'title') {
                                        $taTitle = trim($childNode->nodeValue);
                                        if(isset($taTitles[$taTitle])) {
                                            $taTitle = $taTitle . ' - ' .$taTitles[$taTitle]++; 
                                        } else {
                                            $taTitles[$taTitle] = 1;
                                        }
                                        $childNode->nodeValue = $taTitle;
                                        $guidArr[] = $taTitle;
                                    } 
                                }
                                $guid = base64_encode(implode(' - ', $guidArr));
                                $element = $domSource->createElement('guid',$guid);
                                $item->appendChild($element);

                                //import the 'item' from 'ta_resource_podcast' and append it to our rss template xml
                                $item = $domDest->importNode($item, true);
                                $channel->appendChild($item);
                            }
                        }
                    }
                }
            }
        }
        echo $domDest->saveXML();
        exit;
    }
    
    private function getPodcastRssTemplate() {
        $title            = htmlentities($this->_podcastTitle, ENT_QUOTES);
        $link             = 'http://my.university.com';
        $language         = 'en-au';
        $copyright        = 'Copyright University';
        $lastBuildDate    = date('r', time());
        $webmaster        = 'myuniversity@university.com (My University)';
        $ttl              = 60;

        $channel          = Compass::getConfig('podcast.rss.channel');
        if(!empty($channel)) {
            foreach($channel as &$val) {
                $val = htmlentities($val, ENT_QUOTES);
            }
            if(empty($title)) {
                $title = $channel['title'];
            }
            (isset($channel['link'])) ?             $link               = $channel['link']              : '';
            (isset($channel['language'])) ?         $language           = $channel['language']          : '';
            (isset($channel['copyright'])) ?        $copyright          = $channel['copyright']         : '';
            (isset($channel['webmaster'])) ?        $webmaster          = $channel['webmaster']         : '';
            (isset($channel['ttl'])) ?              $ttl                = $channel['ttl']               : '';
        }
        $xml = <<<XML
<rss xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd" version="2.0">
    <channel>
        <title>{$title}</title>
        <link>{$link}</link>
        <description>{$title}</description>
        <language>{$language}</language>
        <copyright>{$copyright}</copyright>
        <lastBuildDate>{$lastBuildDate}</lastBuildDate>
        <webMaster>{$webmaster}</webMaster>
        <ttl>{$ttl}</ttl>
    </channel>
</rss>
XML;
        $dom = new DOMDocument('1.0');
        $dom->loadXML($xml);
        return $dom;
    }
    
}
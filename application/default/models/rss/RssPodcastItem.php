<?php 
class RssPodcastItem {
    
    CONST attributeFileFormat           = 'file-format';
    CONST attributeYear                 = 'year';
    CONST attributeMediabankCollection  = 'mediabank-collection';
    
    protected $_title                   = '';
    protected $_link                    = '';
    protected $_description             = '';
    protected $_pubDate                 = '';
    protected $_enclosureAttrUrl        = '';
    protected $_enclosureAttrLength     = '';
    protected $_enclosureAttrType       = '';
    protected $_itunesAuthor            = '';
    protected $_itunesDuration          = '';
    
    
    public function saveAsXml(){
        return <<<XML

    <item>
        <title>{$this->getTitle()}</title>
        <link>{$this->getLink()}</link>
        <description>{$this->getDescription()}</description>
        <pubDate>{$this->getPubDate()}</pubDate>
        <enclosure url="{$this->getEnclosureAttrUrl()}" length="{$this->getEnclosureAttrLength()}" type="{$this->getEnclosureAttrType()}"/>
        <itunes:author>{$this->getItunesAuthor()}</itunes:author>
        <itunes:duration>{$this->getItunesDuration()}</itunes:duration>
    </item>

XML;
    }
    
    public function setTitle ($title) {
        $this->_title = htmlentities($title, ENT_QUOTES );
    }

    public function setLink ($link) {
        $this->_link = htmlentities($link, ENT_QUOTES );
    }

    public function setDescription ($description) {
        $this->_description = htmlentities($description, ENT_QUOTES );
    }

    public function setPubDate ($pubDate) {
        $this->_pubDate = htmlentities($pubDate, ENT_QUOTES );
    }

    public function setEnclosureAttrUrl ($enclosureAttrUrl) {
        $this->_enclosureAttrUrl = htmlentities($enclosureAttrUrl, ENT_QUOTES );
    }

    public function setEnclosureAttrLength ($enclosureAttrLength) {
        $this->_enclosureAttrLength = htmlentities($enclosureAttrLength, ENT_QUOTES );
    }

    public function setEnclosureAttrType ($enclosureAttrType) {
        $this->_enclosureAttrType = htmlentities($enclosureAttrType, ENT_QUOTES );
    }

    public function setItunesAuthor ($itunesAuthor) {
        $this->_itunesAuthor = htmlentities($itunesAuthor, ENT_QUOTES );
    }

    public function setItunesDuration ($itunesDuration) {
        $this->_itunesDuration = htmlentities($itunesDuration, ENT_QUOTES );
    }

    public function getTitle () {
        return trim($this->_title);
    }

    public function getLink () {
        return trim($this->_link);
    }

    public function getDescription () {
        return trim($this->_description);
    }

    public function getPubDate () {
        return trim($this->_pubDate);
    }

    public function getEnclosureAttrUrl () {
        return trim($this->_enclosureAttrUrl);
    }

    public function getEnclosureAttrLength () {
        return trim($this->_enclosureAttrLength);
    }

    public function getEnclosureAttrType () {
        return trim($this->_enclosureAttrType);
    }

    public function getItunesAuthor () {
        return trim($this->_itunesAuthor);
    }

    public function getItunesDuration () {
        return trim($this->_itunesDuration);
    }
    
}
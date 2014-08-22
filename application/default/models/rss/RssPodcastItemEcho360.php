<?php
class RssPodcastItemEcho360  extends RssPodcastItem {
    
    protected $_itemFileFormat          = '';
    protected $_itemYear                = '';
    protected $_itemMediabankCollection = '';
    
    public function saveAsXml(){
        $attributeFileFormat            = RssPodcastItem::attributeFileFormat;
        $attributeYear                  = RssPodcastItem::attributeYear;
        $attributeMediabankCollection   = RssPodcastItem::attributeMediabankCollection;
        
        return <<<XML
        <item {$attributeFileFormat}="{$this->getItemFileFormat()}" {$attributeYear}="{$this->getItemYear()}" {$attributeMediabankCollection}="{$this->getItemMediabankCollection()}">
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
    
    public function setItemFileFormat($fileFormat) {
        $this->_itemFileFormat = htmlentities($fileFormat, ENT_QUOTES );
    }
    
    public function getItemFileFormat() {
        return $this->_itemFileFormat;
    }
    
    public function setItemYear($itemYear) {
        $this->_itemYear = htmlentities($itemYear, ENT_QUOTES );
    }
    
    public function getItemYear() {
        return $this->_itemYear;
    }
    
    public function setItemMediabankCollection($mediabankCollection) {
        $this->_itemMediabankCollection = htmlentities($mediabankCollection, ENT_QUOTES );
    }
    
    public function getItemMediabankCollection() {
        return $this->_itemMediabankCollection;
    }
        
}
?>
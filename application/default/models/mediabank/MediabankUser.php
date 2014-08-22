<?php

class MediabankUser {
    public $frontend;
    public $username;
    public $roles;
    public $mepositoryID;

    function __construct($frontend,$username,$roles,$mepositoryID) {
        $this->frontend = $frontend;
        $this->username = $username;
        $this->roles = $roles;
        $this->mepositoryID = $mepositoryID;
    }

    public function __toString() {
        return $this->username."@".$this->frontend." for ".$this->mepositoryID." (".$this->roles.")";
    }

}

?>

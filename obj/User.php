<?php
require_once('obj/page.php');

class User extends Page {
	protected $uid;
	private $username;
	private $join_date;
	private $rank;
	private $postcount;
  private $profilePic;
  private $bnet_id;
  private $bnet_name;
  private $char_code;
  private $bnet_league;
  private $bnet_url;

	public function __construct($uid) {
		parent::__construct();
		$arr = $this->db->getUser($uid);
		$this->uid = $arr['data']['id'];
		$this->username = $arr['data']['username'];
		$this->join_date = $arr['data']['join_date'];
		$this->rank = $arr['data']['rank'];
		$this->postcount = $arr['data']['postcount'];
    $this->profilePic = $arr['data']['profile_pic'];
    $this->bnet_id = $arr['data']['bnet_id'];
    $this->bnet_name = $arr['data']['bnet_name'];
    $this->char_code = $arr['data']['char_code'];
    $this->bnet_league = $arr['data']['bnet_league'];
    $this->bnet_url = $arr['data']['bnet_url'];
  }

  public function __toString() {
    return $this->uid;
  }

	function getUsername() {
		return $this->username;
	}

	function getUID() {
		return $this->uid;
	}

	function getJoinDate() {
		return $this->join_date;
	}
	
	function getRank() {
		return $this->rank;
	}
	
	function getPostcount() {
		return $this->postcount;
	}
	
	function getProfilePic() {
		return $this->profilePic;
  }

  function getBnetID() {
    return $this->bnet_id;
  }

  function getBnetUrl() {
    return $this->bnet_url;
  }

  function getBnetName() {
    return $this->bnet_name;
  }

  function getCharCode() {
    return $this->char_code;
  }

  function getLeague() {
    return $this->bnet_league; 
  }

  function hasBnet() {
    if ($this->bnet_name && $this->bnet_id) {
      return true;
    } else return false;
  }

  function isInMatch($tourn_id) {
    return $this->db->isInMatch($this->uid, $tourn_id);
  }
}


?>

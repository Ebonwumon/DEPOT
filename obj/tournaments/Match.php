<?php
require_once('obj/page.php');
require_once('obj/User.php');
class Match extends Page {

  private $mid;
  private $player_1;
  private $player_2;
  private $replay;
  private $winner;
  private $tourn_id;

  public function __construct($mid) {
    parent::__construct();
    $result = $this->db->getMatch($mid);
    $this->tourn_id = $result['in_tournament'];
    $this->player_1 = $result['player_1'];
    $this->player_2 = $result['player_2'];
    $this->mid = $mid;
    $this->replay = $result['replay'];
    $this->winner = $result['winner'];
  }

  function getPlayer1() {
    return $this->player_1;
  }

  function getPlayer2() {
    return $this->player_2;
  }

  function getPlayer2UID() {
    return $this->player_2;
  }

  function hasWinner() {
    return ($this->winner);
  }

  function getWinner() {
    return $this->winner;
  }

  function getMID() {
    return $this->mid;
  }

  function getReplay() {
    return $this->replay;
  }

  function getTournID() {
    return $this->tourn_id;
  }

  function isMatchSet() {
    $bo = $this->db->getBoFromMatch($this->mid);
    if ($bo > 1) return $this->mid;
    return false;
  }

}

?>

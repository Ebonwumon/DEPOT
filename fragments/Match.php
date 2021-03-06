<?php
require_once('obj/User.php');
require_once('obj/page.php');
class MatchBox extends Page {

  private $match;

  public function __construct($match, $bo = FALSE) {
    parent::__construct();
    $this->match = $match;
  }

  function getPlayerButton($i, $match) {
    $href = "";
    $uid = ($i == 1) ? $match->getPlayer1() : $match->getPlayer2();
    switch ($uid) {
      case -1: $class = 'disabled'; $name = 'Bye'; break;
      case 0: $class='disabled'; $name = 'TBD'; break;
      default: if ($match->hasWinner()) {
        if ($match->getWinner() == $uid) {
          $class='btn-success';
          } else {
            $class='btn-danger';
          } 
          } else {
            $class='btn-primary';
          }
          $user = new User($uid);
          $name = $user->getBnetName() . "." . $user->getCharCode(); 
          $href= '?page=userProfile.php&uid=' . $uid; break;
    }
    return "<a class='player" . $i ." btn " . $class . "' href='" . $href . "'>".$name . "</a>";
  }

  function getWrench() {
    if ($this->permissions(array('admin'))) {
      return "<p><a name='matchEditButton' role='button' href='#editMatchModal' data-toggle='modal' class='btn pull-right'>
          <i class='icon-wrench'></i>
        </a></p>";
    }
  }

  function getReplay($match) {
    if ($match->getReplay()) {
      return "<p><a href='?page=viewReplay.php&rid=" . $match->getReplay() . "' class='btn pull-left'>
          <i class='icon-play'></i>
        </a></p>";
    }
  }

  function getBox() {
    $str = "
     <div class='well'>
      <div class='btn-group btn-group-vertical btn-block'>";
    $str .= $this->getPlayerButton(1, $this->match); 
    $str .= $this->getPlayerButton(2, $this->match);
    $str .= "
      </div>
      ";
    $str .= "<div class='row-fluid'>
      <div class='span6'>";
    $str .= $this->getReplay($this->match);
    $str .= "</div>
      <div class='span6'>";
    $str .= $this->getWrench();
    $str .= " </div>";
    $str .= "</div>";
    $str .=" </div>";
    return $str;
  }
}

class BoMatchBox extends MatchBox {

  private $matchset;

  public function __construct($matchset) {
    $this->matchset = $matchset;
  }

  function getBox() {
    $str = "
      <div class='well'>
      <div class='btn-group btn-group-vertical btn-block'>
      <div class='btn-group btn-block'>";
    $str .= parent::getPlayerButton(1, new Match($this->matchset->getCurrentMatch()));
    $str .= "<a class='btn' disabled>" . $this->matchset->getPlayer1Score() . "</a>";
    $str .= "</div><br />
           <div class='btn-group btn-block'>";
    $str .= parent::getPlayerButton(2, new Match($this->matchset->getCurrentMatch()));
    $str .= "<a class='btn' disabled>" . $this->matchset->getPlayer2Score() . "</a>";
    $str .= "  </div>
      </div>";
    $str .= "<div class='row-fluid'>
      <div class='span6'>";
    $str .= parent::getReplay($this->matchset->getCurrentMatch(TRUE));
    $str .= "</div>
      <div class='span6'>";
    $str .= parent::getWrench(); 
    $str .= "</div>";
    $str .="</div>";
    $str .="</div>";
    return $str;
  }

}
?>

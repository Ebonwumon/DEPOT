<?php

class Replay extends Page {

    private $rid;
    private $path;
    private $submitter;

    public function __construct($rid) {
        parent::__construct();
        $data = $this->db->getReplay($rid);
        $this->rid = $rid;
        $this->path = $data['path'];
        $this->submitter = $data['submitter'];
    }

    function getSubmitter() {
      return $this->submitter;
    }

    function getRID() {
        return $this->rid;
    }

    function getPath() {
        return $this->path;
    }

    function hasMatch() {
      $result = $this->db->getMatchFromReplay($this->rid);
      if ($result) return $result['match_id'];
      else return false;
    }

}

?>

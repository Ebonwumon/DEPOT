<?php

class Stream extends Page {

    private $streamer;
    private $title;
    private $desc;
    private $twitch_user;

    public function __construct($uid) {
        parent::__construct();
        $this->streamer = $uid;
        $data = $this->db->getStream($uid);
        $this->title = $data['title'];
        $this->desc = $data['description'];
        $this->twitch_user = $data['twitch_user'];
    }

    function getTitle() {
        return $this->title;
    }

    function getDescription() {
        return $this->desc;
    }

    function getTwitchUser() {
        return $this->twitch_user;
    }
}

?>

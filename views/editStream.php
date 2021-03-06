<?php
require_once('obj/Stream.php');
if (!isset($_GET['user'])) {
    echo "Not allowed"; // TODO more stuff
    die();
}

$stream =  $_GET['user'];

if (!($page->permissions(array('admin')) || $_SESSION['uid'] == $stream)) {
    echo "not allowed";
    die();
}

$stream = new Stream($stream);

?>

<form action='api.php?type=stream&method=editStream' method="POST" target="submit-iframe">
    <label for='title'>Title: </label>
    <input type='text' name='title' value='<?php echo $stream->getTitle(); ?>' />
    <label for='description'>Description: </label>
    <textarea name='description'><?php echo $stream->getDescription(); ?></textarea>
    <label for='twitch_user'>Twitch Username: </label>
    <input type='text' name='twitch_user' value='<?php echo $stream->getTwitchUser();?>' />
    <input type='hidden' name='uid' value='<?php echo $_SESSION['uid']; ?>' />
    <input class='btn btn-success' type="submit" value="Save">
</form>

<script>
$('#submit-iframe-dood').load(function() {
  location.reload();
});
</script>

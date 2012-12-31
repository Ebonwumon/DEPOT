<?php
require_once('/home/ebon/DEPOT/funcs/verify.php');
require_once('/home/ebon/DEPOT/config.php');
class DB {

	private $pdo;

	public function __construct($username, $password, $dbName, $host) {
		global $DATABASE;
		$this->pdo = new PDO("mysql:host=" . $host . ";dbname=" . $dbName,$username,$password);
	}

  static function getInstance() {
    global $DATABASE;
		return new DB($DATABASE['username'],$DATABASE['password'],$DATABASE['name'],$DATABASE['host']);
	}
	
	function getPDO() {
		return $this->pdo;
	}

	/**
	* Adds to the database. Takes an associative array, with 'table' => table_name
	* and 'fields' => array(':field_name' => 'value', etc.)
	* Returns associative array, with status and message.
	*/
	function add($args) {
		$query = "INSERT into " . $args['table'] . " (";
		$queryPart2 = "VALUES (";
		$i = 1;
    foreach ($args['fields'] as $a=>$v) {
      $query .=  str_replace(":","",$a);
			$queryPart2 .= $a; 
      if ($i < count($args['fields'])) {
        $query .= ", ";
				$queryPart2 .= ",";
      } else {
        $query .= ") ";
				$queryPart2 .= ")";
      }
      $i++;
    }
		$query .= $queryPart2;
		$queryPrepared = $this->pdo->prepare($query);
		if ($queryPrepared->execute($args['fields'])) 
			return (array('status' => 0, 'message' => 'Successfully added to database'));
		else
			return (array('status' => 1, 'message' => 'Adding to database 
			failed. <br> ' .implode($queryPrepared->errorInfo()) ));
	}
	
	function update($args) { 
		$query = "UPDATE " . $args['table'] . " set ";
		$i = 1;
		foreach ($args['fields'] as $a => $v) {
			$query .=  str_replace(":","",$a);
			$query .= " = " . $a;
      if ($i < count($args['fields']))
        $query .= ", ";
      else $query .= " ";
      $i++;
		}
		$i = 1;
		$query .= "WHERE ";
		foreach ($args['where'] as $a => $v) {
			$query .=  str_replace(":","",$a);
			$query .= " = " . $a;
      if ($i < count($args['where']))
        $query .= " AND ";
      else $query .= " ";
      $i++;
		}
		$queryPrepared = $this->pdo->prepare($query);
		if ($queryPrepared->execute(array_merge($args['fields'], $args['where']))) 
			return (array('status' => 0, 'message' => 'Successfully updated.'));
		else
			return (array('status' => 1, 'message' => 'Adding to database failed.'));
	}
	
	function delete($args) {
		$query = "DELETE from " . $args['table'];
		$query .= " WHERE ";
		$i = 1;
    foreach ($args['fields'] as $a=>$v) {
      $query .=  str_replace(":","",$a) . " = " . $a;
      if ($i < count($args['fields'])) {
        $query .= " AND ";
      }
      $i++;
    }
		$queryPrepared = $this->pdo->prepare($query);
		if ($queryPrepared->execute($args['fields']))
			return (array('status' => 0, 'message' => 'Successfully deleted from database'));
		else
			return (array('status' => 1, 'message' => 'Could not delete from database'));
	}
	
	function addTopic($args) {
		if (!$this->isInDatabase(array('type' => 'forum', 'value' => $args['fields'][':in_forum'])))
			return array('status' => 1, 'message' => "That forum doesn't exist, you hobo");
		else {
			$ver1 = verifyString($args['fields'][':subject'], $GLOBALS['SUBJECT_MIN_LENGTH'], $GLOBALS['SUBJECT_MAX_LENGTH']);
			$ver2 = verifyString($args['fields'][':message'], $GLOBALS['POST_MIN_LENGTH'], $GLOBALS['POST_MAX_LENGTH']);
			if ($ver1['status'] == 1) {
				$ver1['message'] = "Subject " . $ver1['message'];
				return $ver1;
			} else $args['fields'][':subject'] = validateString($args['fields'][':subject']);
			if ($ver2['status'] == 1) {
				$ver2['message'] = "Message " . $ver2['message'];
				return $ver2;
			} else $args['fields'][':message'] = validateString($args['fields'][':message']);
			$result = $this->add($args);
			if ($result['status'] == 0) {
				require_once('obj/forum/Topic.php');
				$this->updateForumList(new Topic($this->getLastInsertId()));
				$this->updatePostCount($args['fields'][':author_uid']);
			} 
			return $result;
		}
	}
	
	function editPost($args) {
		if (!$this->isInDatabase(array('type' => 'post', 'value' => $args['where'][':pid'])))
			return array('status' => 1, 'message' => "That post doesn't exist, gtfo");
		else {
			$ver1 = verifyString($args['fields'][':subject'], $GLOBALS['SUBJECT_MIN_LENGTH'], $GLOBALS['SUBJECT_MAX_LENGTH']);
			$ver2 = verifyString($args['fields'][':message'], $GLOBALS['POST_MIN_LENGTH'], $GLOBALS['POST_MAX_LENGTH']);
			if ($ver1['status'] == 1) {
				$ver1['message'] = "Subject " . $ver1['message'];
				return $ver1;
			} else $args['fields'][':subject'] = validateString($args['fields'][':subject']);
			if ($ver2['status'] == 1) {
				$ver2['message'] = "Message " . $ver2['message'];
				return $ver2;
			} else $args['fields'][':message'] = validateString($args['fields'][':message']);
			$args['fields'] = array_merge($args['fields'], array(':edit_count' => 'edit_count +1'));
			return $this->update($args);
		}
	}
	
  function addPost($args) {
		if (!$this->isInDatabase(array('type' => 'topic', 'value' => $args['fields'][':in_reply_to'])))
			return array('status' => 1, 'message' => "That topic doesn't exist, you hobo");
		else {
			$ver1 = verifyString($args['fields'][':subject'], $GLOBALS['SUBJECT_MIN_LENGTH'], $GLOBALS['SUBJECT_MAX_LENGTH']);
			$ver2 = verifyString($args['fields'][':message'], $GLOBALS['POST_MIN_LENGTH'], $GLOBALS['POST_MAX_LENGTH']);
			if ($ver1['status'] == 1) {
				$ver1['message'] = "Subject " . $ver1['message'];
				return $ver1;
			} else $args['fields'][':subject'] = validateString($args['fields'][':subject']);
			if ($ver2['status'] == 1) {
				$ver2['message'] = "Message " . $ver2['message'];
				return $ver2;
			} else $args['fields'][':message'] = validateString($args['fields'][':message']);
			$result = $this->add($args);
			if ($result['status'] == 0) {
				require_once('obj/forum/Post.php');
				require_once('obj/forum/Topic.php');
				$post = new Post($this->getLastInsertId());
				$this->updateTopicList($post);
				$this->updateForumList(new Topic($post->getTID()));
				$this->updatePostCount($args['fields'][':author_uid']);
			}
			return $result;
    }
	}

	function updateTopicList($post) {
		$query = "UPDATE topics set last_poster = :last_poster, replies = replies + 1, last_reply = :last_reply, last_reply_uid = :last_reply_uid, last_reply_pid = :last_reply_pid where id = :tid";
		$queryPrepared = $this->pdo->prepare($query);
		$queryPrepared->bindParam(':last_poster', $post->getAuthor());
		$queryPrepared->bindParam(':last_reply', $post->getDate());
		$queryPrepared->bindParam(':last_reply_uid', $post->getAuthorUID());
		$queryPrepared->bindParam(':last_reply_pid', $post->getPID());
		$queryPrepared->bindParam(':tid', $post->getTID());
		if ($queryPrepared->execute())
			return array('status' => 0, 'message' => 'Successfully updated topic list');
		else
			return array('status' => 1, 'message' => 'Failed to update topic list'); 
		
	}
	
	function deleteTopic($tid) {
		require_once('obj/forum/Topic.php');
		$result = $this->getRepliesAsPDOStatement($tid);
		if ($result['status'] == 1) return $result;
		$data = $result['data'];
		$topic = new Topic($tid);
		$result = $this->delete(array('table' => 'topics', 'fields' => array(':tid' => $tid)));
		if ($result['status'] == 1) return $result;
		else $this->updatePostCount($topic->getAuthorUID(), -1);
		while ($item = $data->fetch()) {
			$this->deletePost($item['pid']);
		}
		$newTid = $this->getLastTopic($topic->getFID());
		$this->updateForumList(new Topic($newTid['data']['tid']));
		return array('status' => 0, 'message' => 'Everything is gone');
	}
	
	function deletePost($pid) {
		require_once('obj/forum/Post.php');
		require_once('obj/forum/Topic.php');
		$post = new Post($pid);
		$result = $this->delete(array('table' => 'posts', 'fields' => array(':pid' => $pid)));
		if ($result['status'] == 1) return $result;
		$this->updatePostCount($post->getAuthorUID(), -1);
		$topic = new Topic($post->getTID());
		if ($topic->getLastReplyPID() == $post->getPID()) {
			$newPid = $this->getLastReply($post->getTID());
			$this->updateTopicList(new Post($newPid['data']['pid']));
		}
		return array('status' => 0, 'message' => 'Post deleted successfully');
	}
	
	function getLastReply($tid) {
		$query = "SELECT id from posts where in_reply_to = :tid ORDER BY STR_TO_DATE(date, '%d-%m-%Y %H:%i:%s') DESC LIMIT 0,1";
		$queryPrepared = $this->pdo->prepare($query);
		$queryPrepared->bindParam(':tid', $tid);
		if (!$queryPrepared->execute()) return array('status' => 1, 'message' => 'Could not get last post');
		return array('status' => 0, 'data' => $queryPrepared->fetch());
	}
	
	function getLastTopic($fid) {
		$query = "SELECT id from topics where in_forum = :fid ORDER BY STR_TO_DATE(date, '%d-%m-%Y %H:%i:%s') DESC";
		$queryPrepared = $this->pdo->prepare($query);
		$queryPrepared->bindParam(':fid', $fid);
		if (!$queryPrepared->execute()) return array('status' => 1, 'message' => 'Could not get last topic');
		else return array('status' => 0, 'data' => $queryPrepared->fetch());
	}

	function isBanned($uid) {
		$query = "SELECT rank from user where id = :uid";
		$queryPrepared = $this->pdo->prepare($query);
		
		$queryPrepared->bindParam(':uid', $uid);
		$queryPrepared->execute();
		$arr = $queryPrepared->fetch();
		return ($arr['rank'] == "banned");
	}

	function isInDatabase($args) {
		switch ($args['type']) {
			case 'email': $query = "SELECT id from user where email = :data"; break;
			case 'username': $query = "SELECT id from user where username = :data";
			break;
			case 'topic': $query = "SELECT id from topics where id = :data"; break;
			case 'forum': $query = "SELECT id from forums where id = :data"; break;
			case 'post': $query = "SELECT id from posts where id = :data"; break;
		}
		$queryPrepared = $this->pdo->prepare($query);
		$queryPrepared->bindParam(':data',$args['value']);
		$queryPrepared->execute();
		if ($queryPrepared->rowCount() != 0) return true;
		else return false;
	}
	
	function isTopicAuthor($tid, $uid) {
		$query = "SELECT author_uid from topics where id = :tid";
		$queryPrepared = $this->pdo->prepare($query);
		$queryPrepared->bindParam(':tid', $tid);
		$queryPrepared->execute();
		$data = $queryPrepared->fetch();
		return ($data['author_uid'] == $uid);
	}
	
	function isPostAuthor($pid, $uid) {
		$query = "SELECT author_uid from posts where id = :pid";
		$queryPrepared = $this->pdo->prepare($query);
		$queryPrepared->bindParam(':pid', $pid);
		$queryPrepared->execute();
		$data = $queryPrepared->fetch();
		return ($data['author_uid'] == $uid);
	}

	function getUserByEmail($email, $args) {
		$query = "SELECT ";
		$i = 1;
		foreach ($args as $a) {
			$query .=  $a;
			if ($i < count($args)) {
				$query .= ", ";
			} else {
				$query .= " ";
			}
			$i++;
		}
		$query .= "from user where email = :email";
		$queryPrepared = $this->pdo->prepare($query);
		$queryPrepared->bindParam(':email', $email);
		$queryPrepared->execute();
		if ($queryPrepared->rowCount() == 0) {
			return (array('status' => 1, 'message' => "User not found in database"));
		}
		return $queryPrepared->fetch();
	}

  function getForumList() { //TODO status messages
    require_once('obj/Forum.php');
		$query = "SELECT id from forums";
		$queryPrepared = $this->pdo->prepare($query);
		$queryPrepared->execute();
    $arr = $queryPrepared->fetchAll();
    $forumList = array();
    foreach ($arr as $id) {
      $forumList[] = new Forum($id['id']);
    }
    return $forumList;
  }

  function getForum($fid) {
    $query = "SELECT * from forums where id = :fid";
    $queryPrepared = $this->pdo->prepare($query);
    $queryPrepared->bindParam(':fid', $fid);
    $queryPrepared->execute();
    return $queryPrepared->fetch();
  }

	function getUserList($args) { //TODO status messages and pagination
		$query = "SELECT id from user ORDER BY ";
		switch ($args['orderBy']) {
			case 'join_date': $query .= "STR_TO_DATE(join_date, '%d-%m-%Y %H:%i:%s')";  break;
			case 'postcount': $query .= "postcount DESC"; break;
			default: $query .= "username"; break;
		}
		$queryPrepared = $this->pdo->prepare($query);
		if (!$queryPrepared->execute()) {
			return array('status' => 1, 'message' => 'Could not get user list');
		} else {
			require_once('obj/User.php');
			$arr = $queryPrepared->fetchAll();
			$users = array();
			foreach ($arr as $a) {
				$users[] = new User($a['id']);
			}
			return array('status' => 0, 'data' => $users);
		}
	}
	
	function getUser($uid) {
		$query = "SELECT id, username, rank, email, join_date, postcount, profile_pic from user WHERE id = :uid";
    $queryPrepared = $this->pdo->prepare($query);
		$queryPrepared->bindParam(':uid', $uid);
		if (!$queryPrepared->execute()) 
			return array('status' => 1, 'message' => 'Could not read user data');
		else
			return array('status' => 0, 'data' => $queryPrepared->fetch());
	}

	function updateForumList($topic) {
		$query = "UPDATE forums set last_topic = :last_topic, last_topic_id =
			:last_topic_id, last_poster = :last_poster, last_poster_id = 
			:last_poster_id, last_poster_pid = :last_poster_pid where id = :fid";
		$queryPrepared = $this->pdo->prepare($query);
		$queryPrepared->bindParam(':last_topic', $topic->getSubject());
		$queryPrepared->bindParam(':last_topic_id', $topic->getTid());
		$queryPrepared->bindParam(':last_poster', $topic->getAuthor());
		$queryPrepared->bindParam(':last_poster_id', $topic->getAuthorUID());
		$queryPrepared->bindParam(':last_poster_pid', $topic->getLastReplyPID());
		$queryPrepared->bindParam(':fid', $topic->getFID());
		if ($queryPrepared->execute())
			return array('status' => 0, 'message' => 'Successfully updated forum list');
		else
			return array('status' => 1, 'message' => 'Failed to update forum list');
	}
	
	function updatePostCount($uid, $num=1) {
		$query = "UPDATE user set postcount = postcount + :num where id = :uid";
		$queryPrepared = $this->pdo->prepare($query);
		$queryPrepared->bindParam(':num', $num);
		$queryPrepared->bindParam(':uid', $uid);
		if($queryPrepared->execute())
			return array('status' => 0, 'message' => 'Post count updated successfully');
		else
			return array('status' => 0, 'message' => 'Post count could not be updated');
	}
	
	function updateProfilePic($uid, $path) {
		$query = "UPDATE user set profile_pic = :profile_pic where id = :uid";
		$queryPrepared = $this->pdo->prepare($query);
		$queryPrepared->bindParam(':profile_pic', $path);
		$queryPrepared->bindParam(':uid', $uid);
		if ($queryPrepared->execute())
			return array('status' => 0, 'message' => 'Successfully changed profile picture');
		else
			return array('status' => 1, 'message' => 'Failed to update the profile picture in the database');
	}

	function getTopicsInForumByPage($fid, $pageNum, $pageLimit) {
		$query = "SELECT id from topics where in_forum = :fid ORDER by 
			STR_TO_DATE(last_reply, '%d-%m-%Y %H:%i:%s')
			DESC LIMIT " . ($pageNum * $pageLimit) . ", " 
			. $pageLimit;
		$queryPrepared = $this->pdo->prepare($query);
		$queryPrepared->bindParam(':fid', $fid);
		if (!$queryPrepared->execute())
			return array('status' => 1, 'message' => 'Could not retrieve 
			topics '. implode($queryPrepared->errorInfo()));
		else {
			require_once('/home/ebon/DEPOT/obj/forum/Topic.php');
			$arr = $queryPrepared->fetchAll();
			$data = array();
			foreach ($arr as $a) {
				$data[] = new Topic($a['id']);
			}
			return array('status' => 0, 'data' => $data);
		}
  }

  function getNumberOfTopicPages($tid, $pageLimit) {
    $query = "SELECT id from posts where in_reply_to = :tid";
    $queryPrepared = $this->pdo->prepare($query);
    $queryPrepared->bindParam(':tid', $tid);
    $queryPrepared->execute();
    $num = $queryPrepared->rowCount() + 1; 
    if ($num % $pageLimit == 0) return $num / $pageLimit;
    else return (int)(($num / $pageLimit) + 1);
  }

  function getNumberOfForumPages($fid, $pageLimit) {
    $query = "SELECT id from topics where in_forum = :fid";
    $queryPrepared = $this->pdo->prepare($query);
    $queryPrepared->bindParam(':fid', $fid);
    $queryPrepared->execute();
    $num = $queryPrepared->rowCount() + 1;
    if ($num % $pageLimit == 0) return $num / $pageLimit;
    else return (int)(($num / $pageLimit) + 1);
  }
	
	function getRepliesAsPDOStatement($tid) {
		$query = "SELECT id from posts where in_reply_to = :tid";
		$queryPrepared = $this->pdo->prepare($query);
		$queryPrepared->bindParam(':tid', $tid);
		if (!$queryPrepared->execute()) return array('status' => 1, 'message' => 'Could not retrieve posts');
		else return array('status' => 0, 'data' => $queryPrepared);
	}
	
	function getRepliesInTopicByPage($tid, $pageNum, $postsPerPage) {
		$query = "SELECT id from posts where in_reply_to = :tid ORDER by 
			STR_TO_DATE(date, '%d-%m-%Y %H:%i:%s')
		  LIMIT " . ($pageNum * $postsPerPage) . ", " 
			. $postsPerPage;
		$queryPrepared = $this->pdo->prepare($query);
		$queryPrepared->bindParam(':tid', $tid);
		if (!$queryPrepared->execute())
			return array('status' => 1, 'message' => 'Could not retrieve posts 
			<br> ' . implode($queryPrepared->errorInfo()));
		else {
			require_once('obj/forum/Post.php');
			$arr = $queryPrepared->fetchAll();
			$data = array();
      foreach ($arr as $a) {
				$data[] = new Post($a['id']);
			}
			return array('status' => 0, 'data' => $data);
		}
	}
	
	function getPost($pid) {
		$query = "SELECT id, author, subject, message, in_reply_to, date, author_uid from posts where id = :pid";
		$queryPrepared = $this->pdo->prepare($query);
		$queryPrepared->bindParam(':pid', $pid);
		if (!$queryPrepared->execute())
			return array('status' => 1, 'message' => 'could not get post');
		else {
			return array('status' => 0, 'data' => $queryPrepared->fetch());
		}
	}

	function getTopic($tid) {
    $query = "SELECT subject, author, last_poster, id, author_uid, 
    message, in_forum, last_reply_uid, last_reply_pid from 
			topics WHERE id = :tid";
    $queryPrepared = $this->pdo->prepare($query);
		$queryPrepared->bindParam(':tid', $tid);
		if (!$queryPrepared->execute()) 
			return array('status' => 1, 'message' => 'Could not read topic from database');
		else
			return array('status' => 0, 'data' => $queryPrepared->fetch());
	}
	
	function getTopicSubject($tid) {
		$query = "SELECT subject from topics WHERE id = :tid";
    $queryPrepared = $this->pdo->prepare($query);
		$queryPrepared->bindParam(':tid', $tid);
		if (!$queryPrepared->execute()) 
			return "";
		else {
			$data = $queryPrepared->fetch();
			return $data['subject'];
		}
	}
	
	function isAdmin($uid) {
		if (isset($_SESSION['rank']))
			return ($_SESSION['rank'] == 'admin');
		else {
			$query = "SELECT rank from user where id = :uid";
			$queryPrepared = $this->pdo->prepare($query);
			$queryPrepared->bindParam(':uid', $uid);
			$queryPrepared->execute();
			$data = $queryPrepared->fetch();
			return ($data['rank'] == 'admin');
		}
	}

	function getLastInsertId() {
		return $this->pdo->lastInsertId();
  }
// TODO remove magic rank/profile_pic
  function registerUser($username, $email, $join_date) {
    return $this->add(array('table' => 'user', 'fields' => array(':username' => $username, ':email' => $email, ':join_date' => $join_date, ':rank' => "user", ':profile_pic' => "assets/profile/uid_0.gif")));
  }

}
?>

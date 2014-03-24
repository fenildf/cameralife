<?php
# Handle the LOGIN form action from login.php
#
# Pass me variables:
# action = login or register
# param1 = username
# param2 = password
# param3 = email (optional)
# target = where to go afterwards or 'ajax'
/**Handles the LOGIN form directed from login.php
 *
 * Pass the following variables:
 *<ul>
 *<li>action = login or register</li>
 *<li>param1 = username</li>
 *<li>param2 = password</li>
 *<li>param3 = email (optional)</li>
 *<li>target = where to go afterwards or 'ajax'</li></ul>
 * @todo The following code requires rework
 *<code>elseif(strtolower($_POST['action']) == 'register')
 *{
 * $result = $cameralife->security->register($_POST['param1'], $_POST['param2'], $_POST['param3']);
 * if (is_string($result))
 *   $cameralife->error($result);
 *}
 *if ($_POST['target'] == 'ajax')
 *  exit(0);
 *else
 *header("Location: ".$_POST['target']);</code>
 * @author William Entriken <cameralife@phor.net>
 * @copyright Copyright (c) 2001-2009 William Entriken
 * @access public
 */

/**
 */

$features = array('theme', 'security');
require 'main.inc';

if (strtolower($_POST['action']) == 'login') {
    $result = $cameralife->security->Login($_POST['param1'], $_POST['param2']);
    if (is_string($result)) {
        $cameralife->error($result);
    }
} //@todo rework required
elseif (strtolower($_POST['action']) == 'register') {
    $result = $cameralife->security->register($_POST['param1'], $_POST['param2'], $_POST['param3']);
    if (is_string($result)) {
        $cameralife->error($result);
    }
}

if ($_POST['target'] == 'ajax') {
    exit(0);
} else {
    header("Location: " . $_POST['target']);
}

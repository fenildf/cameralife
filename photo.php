<?php
/**
 *@link http://fdcl.sourceforge.net
 *@version 2.6.3b5
 *@author Will Entriken <cameralife@phor.net>
 *@copyright Copyright (c) 2001-2009 Will Entriken
 *@access public
 */
  $features=array('database','theme');
  require "main.inc";

  $photo = new Photo($_GET['id']);
  $photo->Set('hits', $photo->Get('hits') + 1);

  $photo->ShowPage();
?>

<?php
  # Displays a category of photo albums "topic"
  $features=array('database','security', 'theme', 'photostore');
  require "main.inc";

  $topic = new Topic($_GET['name']);
  $albums = $topic->GetAlbums();
  $counts = $topic->GetCounts();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <title><?= $cameralife->GetPref('sitename').' : '.$_GET['name'] ?></title>
  <?php if($cameralife->Theme->cssURL()) {
    echo '  <link rel="stylesheet" href="'.$cameralife->Theme->cssURL()."\">\n";
  } ?>
    <meta http-equiv="Content-Type" content="text/html; charset= ISO-8859-1">
</head>
<body>
<form name="form1" method=post action="album_controller.php">
<?php
  $menu = array();
  $menu[] = $cameralife->GetIcon('small');
  if ($cameralife->Security->authorize('admin_albums'))
  {
    $menu[] = array('name'=>"Add an album to \"".$_GET['name']."\"",
                    'href'=>"search.php&#63;albumhelp=1",
                    'image'=>'small-album');
  }
  $menu[] = array('name'=>"Search for \"".$_GET['name']."\"",
                  'href'=>'search.php&#63;q='.$_GET['name'],
                  'image'=>'small-search');

  $cameralife->Theme->TitleBar('Photo albums of '.$_GET['name'],
                               'topic',
                               "(".$counts['albums']." albums)",
                               $menu);

  if ($_GET['edit'])
  {
    $cameralife->Security->authorize('admin_albums',1);
?>
  <div class="administrative" align=center>
    <input type="hidden" name="param4" value="<?= $_GET['term'] ?>">
    <input type="hidden" name="target" value="<?= $cameralife->base_url ?>/index.php">

    <p class="info" align=left>
      You are about to create an Album of photos whose descriptions contain "<b><?= $_GET['term'] ?></b>".
      <?php if (file_exists('setup/albums.html')) echo "<a href=\"setup/albums.html\">Click here for more information about albums</a>"; ?>
    </p>
    <table>
      <tr>
        <td>The Album's name:
        <td><input type="text" name="param3" value="<?= ucwords($_GET['term']) ?>">
      <tr>
        <td>This album and others like it are:
        <td><select name="param1">
          <?php 
            $result = $cameralife->Database->Select('albums','DISTINCT topic');
            while ($topics = $result->FetchAssoc())
            {
                $topic = $topics['topic'];
                $selected = ($album['topic'] == $topic) ? 'selected' : '';
                if ($album['topic'] == $topic) $preselect = true;
                echo "<option $selected onclick=\"javascript:document.form1.topicother.disabled=true\" value=\"$topic\">$topic</option>\n";
            }
          ?>
            <option value="othertopic" <?= $preselect ? '' : 'selected' ?> onclick="javascript:document.form1.topicother.disabled=false">(A new topic)</option>
          </select>
          <input <?= $preselect ? 'disabled' : '' ?> type="text" name="param2" name="topicother" id="topicother" value="NEW TOPIC">
      <tr>
        <td>
        <td>
          <input type=submit name="action" value="Create">
          <a href="search.php&#63;q=<?= $_GET['term'] ?>">(cancel)</a>
    </table>
  </div>
<?php
    exit();
  }

  $cameralife->Theme->Grid($albums);
  
  if ($sort == 'rand()') $start = -1;
  $cameralife->Theme->PageSelector($start,$total,12,"name=".$_GET["name"]);
?>
</form>

<form method="POST" action="">
<p>
  Sort by <select name="sort">
<?php
    $options = $topic->SortOptions();
    foreach($options as $option)
      echo "    <option ".$option[2]." value=\"".$option[0]."\">".$option[1]."</option>\n";
?>
  </select>
  <input type=submit value="Sort">
</p>
</form>


</body>
</html>

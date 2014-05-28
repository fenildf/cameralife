<?php
namespace CameraLife;

/**
 * Allows you to search for photos, albums, and folders in the system
 * @author William Entriken <cameralife@phor.net>
 * @access public
 * @copyright 2001-2014 William Entriken
 */
class Search extends View
{
    public $mySearchPhotoCondition;
    public $mySearchAlbumCondition;
    public $mySearchFolderCondition;
    public $mySort;
    public $myStart;
    public $myLimitCount;
    public $myBinds;
    private $myLimit;
    private $myQuery;
    private $myCounts;

    public function __construct($query = '')
    {
        global $_POST, $_GET;
        parent::__construct();

        if (!get_magic_quotes_gpc()) {
            addslashes($this->myQuery = $query);
        } else {
            $this->myQuery = $query;
        }
        $this->myExtra = '';
        $special = array('?', '.');
        $specialEscaped = array('[?]', '[.]');
        foreach (explode(' ', $query) as $term) {
            $term = addslashes($term);
            $term = str_replace($special, $specialEscaped, $term);
            $searchPhotoConditions[] = "concat(description,' ',keywords) REGEXP '(^|[[:blank:]])" . addslashes(
                preg_quote(stripslashes($term))
            ) . "'";
            $searchAlbumConditions[] = "name LIKE '%$term%'";
            $searchFolderConditions[] = "path LIKE '%$term%'";
        }
        $this->mySearchPhotoCondition = implode(' AND ', $searchPhotoConditions);
        $this->mySearchAlbumCondition = implode(' AND ', $searchAlbumConditions);
        $this->mySearchFolderCondition = implode(' AND ', $searchFolderConditions);

        if (isset($_POST['sort'])) {
            $this->mySort = $_POST['sort'];
            setcookie("sort", $this->mySort);
        } elseif (isset($_GET['sort'])) {
            $this->mySort = $_GET['sort'];
        } elseif (isset($_COOKIE['sort'])) {
            $this->mySort = $_COOKIE['sort'];
        } else {
            $this->mySort = 'newest';
        }

        if (isset($_GET['start']) && is_numeric($_GET['start'])) {
            $this->myStart = $_GET['start'];
        } else {
            $this->myStart = 0;
        }
        $this->myLimitCount = 12;
        $this->myLimit = "LIMIT " . $this->myStart . "," . $this->myLimitCount;
    }

    public function setSort($sort)
    {
        $this->mySort = $sort;
    }

    # static function, and a not static function...
    public function sortOptions()
    {
        $retval = array();
        $retval[] = array('newest', 'Newest First');
        $retval[] = array('oldest', 'Oldest First');
        $retval[] = array('az', 'Alphabetically (A-Z)');
        $retval[] = array('za', 'Alphabetically (Z-A)');
        $retval[] = array('popular', 'Popular First');
        $retval[] = array('unpopular', 'Unpopular First');
        $retval[] = array('rand', 'Random');
        foreach ($retval as &$item) {
            list($id, $desc) = $item;
            if (is_object($this) && isset($this->mySort) && $this->mySort == $id) {
                $item[] = "selected";
            }
        }

        return $retval;
    }

    public function getCounts()
    {
        global $cameralife;

        if (!isset($this->myCounts)) {
            $this->myCounts = array();
            $this->myCounts['photos'] = $cameralife->database->SelectOne(
                'photos',
                'COUNT(*)',
                $this->mySearchPhotoCondition . ' AND status=0',
                null,
                null,
                $this->myBinds
            );
            $this->myCounts['albums'] = $cameralife->database->SelectOne(
                'albums',
                'COUNT(*)',
                $this->mySearchAlbumCondition,
                null,
                null,
                $this->myBinds
            );
            $this->myCounts['folders'] = $cameralife->database->SelectOne(
                'photos',
                'COUNT(DISTINCT path)',
                $this->mySearchFolderCondition . ' AND status=0',
                null,
                null,
                $this->myBinds
            );
        }

        return $this->myCounts;
    }

    public function setPage($start, $pagesize = 12)
    {
        $this->myStart = $start;
        $this->myLimitCount = $pagesize;
        $this->myLimit = "LIMIT " . $this->myStart . "," . $this->myLimitCount;
    }

    public function getPhotos()
    {
        global $cameralife;

        switch ($this->mySort) {
        case 'newest':
            $sort = 'value desc, id desc';
                break;
        case 'oldest':
            $sort = 'value, id';
                break;
        case 'az':
            $sort = 'description';
                break;
        case 'za':
            $sort = 'description desc';
                break;
        case 'popular':
            $sort = 'hits desc';
                break;
        case 'unpopular':
            $sort = 'hits';
                break;
        case 'rand':
            $sort = 'rand()';
                break;
        default:
            $sort = 'id desc';
        }

        $condition = $this->mySearchPhotoCondition . ' AND status=0';
        $query = $cameralife->database->Select(
            'photos',
            'id',
            $condition,
            'ORDER BY ' . $sort . ' ' . $this->myLimit,
            'LEFT JOIN exif ON photos.id=exif.photoid and exif.tag="Date taken"',
            $this->myBinds
        );
        $photos = array();
        while ($row = $query->fetchAssoc()) {
            $photos[] = new Photo($row['id']);
        }

        return $photos;
    }

    public function getAlbums()
    {
        global $cameralife;

        switch ($this->mySort) {
        case 'newest':
            $sort = 'albums.id desc';
                break;
        case 'oldest':
            $sort = 'albums.id';
                break;
        case 'az':
            $sort = 'description';
                break;
        case 'za':
            $sort = 'description desc';
                break;
        case 'popular':
            $sort = 'albums.hits desc';
                break;
        case 'unpopular':
            $sort = 'albums.hits';
                break;
        case 'rand':
            $sort = 'rand()';
                break;
        default:
            $sort = 'albums.id desc';
        }

        $condition = $this->mySearchAlbumCondition;
        $query = $cameralife->database->Select(
            'albums', 
            'id', 
            $condition, 
            'ORDER BY ' . $sort . ' ' . $this->myLimit, 
            null, 
            $this->myBinds
        );

        $albums = array();
        while ($row = $query->fetchAssoc()) {
            $albums[] = new Album($row['id']);
        }

        return $albums;
    }

    public function getFolders()
    {
        global $cameralife;
        switch ($this->mySort) {
        case 'newest':
            $sort = 'id desc';
                break;
        case 'oldest':
            $sort = 'id';
                break;
        case 'az':
            $sort = 'path';
                break;
        case 'za':
            $sort = 'path desc';
                break;
        case 'popular':
            $sort = 'hits desc';
                break;
        case 'unpopular':
            $sort = 'hits';
                break;
        case 'rand':
            $sort = 'rand()';
                break;
        default:
            $sort = 'id desc';
        }

        // Another way to do it "DISTINCT SUBSTRING_INDEX(SUBSTR(path,".(strlen($this->path)+1)."),'/',1) AS basename";
        $condition = $this->mySearchFolderCondition . ' AND status=0';
        $query = $cameralife->database->Select(
            'photos',
            'path, MAX(mtime) as date',
            $condition,
            'GROUP BY path ORDER BY ' . $sort . ' ' . $this->myLimit, 
            null, 
            $this->myBinds
        );
        $folders = array();
        while ($row = $query->fetchAssoc()) {
            $folders[] = new Folder($row['path'], false, $row['date']);
        }

        return $folders;
    }

    public function getQuery()
    {
        return $this->myQuery;
    }

    public function getOpenGraph()
    {
        global $cameralife;
        $retval = array();
        $retval['og:title'] = 'Search for: ' . $this->myQuery;
        $retval['og:type'] = 'website';
        //TODO see https://stackoverflow.com/questions/22571355/the-correct-way-to-encode-url-path-parts
        $retval['og:url'] = $cameralife->baseURL . '/search.php?q=' . str_replace(" ", "%20", $this->myQuery);
        $retval['og:image'] = $cameralife->iconURL('search');
        $retval['og:image:type'] = 'image/png';
        //$retval['og:image:width'] =
        //$retval['og:image:height'] =
        return $retval;
    }

}

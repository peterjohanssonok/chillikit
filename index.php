<?php
/* ------------------------------------------------------------------------
   Part of the Chilli kit — https://chillikit.com

   Free to use, modify, and share (including commercially).
   Please keep this note as a reference to the origin.
------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------
    Set the defaults
------------------------------------------------------------------------ */
define('version', '2604');
define('homedir', __DIR__.'/');
define('homeurl', buildhomeurl());

function buildhomeurl(){
// must live in index.php due to SCRIPT_NAME
    $url = empty($_SERVER['HTTPS']) ? 'http://' : 'https://';
    $url.= $_SERVER['SERVER_NAME'];
    $url.= ($_SERVER['SERVER_PORT'] == '80') ? '' : ':'.$_SERVER['SERVER_PORT'];
    $url.= dirname($_SERVER['SCRIPT_NAME']);
    $url = str_replace('\\', '/', $url);
    $url = rtrim($url,"/");
    $url.= '/';
    return $url;
}

ini_set('session.name', 'chilli');
ini_set('session.use_cookies', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_lifetime', 0);

/* ------------------------------------------------------------------------
    /Set the defaults
------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------
    Initialization class
    We need a place to store user settings
------------------------------------------------------------------------ */
class iniClass{
    private $fileName;
    private $buffer;
    private $updatePending = false;

    public function preset($key, $value){
        if(! property_exists($this->buffer, $key)){
            $this->buffer->$key = $value;
            $this->updatePending = true;
        }
    }
    public function get($key){
        return $this->buffer->$key;
    }
    public function set($key, $value){
        $this->buffer->$key = $value;
        $this->updatePending = true;
    }
    public function update(){
        if($this->updatePending){
            $this->buffer->lastupdate = date('Y-m-d H:i:s', time());
            file_put_contents($this->fileName, json_encode($this->buffer, JSON_PRETTY_PRINT));
            $this->updatePending = false;
        }
    }
    public function __construct($fileName){
        $this->fileName = $fileName;
        if(file_exists($this->fileName)){
            $this->buffer = json_decode(file_get_contents($this->fileName));
            $this->updatePending = false;
        }
        else{
            $this->buffer = new stdClass();
        }
    }
}
/* ------------------------------------------------------------------------
    /Initialization class
------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------
    steps class
    Sometimes we want to execute functions in a different order
    than they are loaded in. This script call all functions
    that are assigned to: afterincludes, afterrequest and cron.
------------------------------------------------------------------------ */
class stepsClass{
    private $items = array();
    
    public function add($name, $call){
        $this->items[$name][] = $call;
    }
    public function remove($name){
        unset($this->items[$name]);
    }
    public function exist($name){
        return (! empty($this->items[$name]));
    }
    public function run($name){
        if($this->exist($name)){
            foreach($this->items[$name] as $call){
                call_user_func($call);
            }
        }
    }
}
/* ------------------------------------------------------------------------
    /steps class
------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------
    Session class
    Session give us a place to store data throughout multible page loads.
------------------------------------------------------------------------ */
class sessionClass{
    public $admin;

    public function start(){
        if(session_status() === PHP_SESSION_NONE){
            session_start();
        }
    }
    public function get($key, $default = ''){
        return empty($_SESSION[$key]) ? $default : $_SESSION[$key];
    }
    public function set($key, $value){
        $this->start();
        $_SESSION[$key] = $value;
    }
    public function remove($key){
        $this->start();
        unset($_SESSION[$key]);
    }
    public function clear(){
        $this->start();
        $_SESSION = [];
    }
    public function setAdmin($admin){
        $this->admin = $admin;
        if($this->admin){
            $this->set('admin', 'true');            
        }
        else{
            $this->remove('admin');
        }
    }
    public function __construct(){
        if(! empty($_COOKIE[ini_get('session.name')])){
            session_start();
        }
        $this->admin = ($this->get('admin') === 'true');
    }
}
/* ------------------------------------------------------------------------
    /Session class
------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------
    Database class extends the build-in SQLite3.
    We use SQLite because it is quick
    and doesn't require any external connections.
------------------------------------------------------------------------ */
class sqlite3plus extends SQLite3{

    public function tableExist($tbl){
        $result = $this->querySingle("SELECT count(*) FROM sqlite_master WHERE type='table' AND name='$tbl';");
        return ($result > 0);
    }
    public function columnExist($tbl, $col){
        $result = false;
        $list = $this->query("PRAGMA table_info('$tbl')");
        while ($row = $list->fetchArray(SQLITE3_ASSOC)) {
            if ($row['name'] === $col) {
                $result = true;
                break;
            }
        }
        return $result;
    }
    public function append($tbl){
        $this->exec("INSERT INTO $tbl (Id) VALUES(NULL);");
        return $this->lastInsertRowID();
    }
    public function fetch($res){
        return $res->fetchArray(SQLITE3_ASSOC);
    }
    public function __construct($fileName){
        parent::__construct($fileName, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
        $this->busyTimeout(1000);
    }
}
/* ------------------------------------------------------------------------
    /Database class
------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------
    Utility functions
------------------------------------------------------------------------ */
function getGet($key, $default = ''){
    return empty($_GET[$key]) ? $default : $_GET[$key];
}
function getPost($key, $default = ''){
    return empty($_POST[$key]) ? $default : $_POST[$key];
}
function hasPost(){
    return (! empty($_POST));
}
function sayHello(){
?>
    <!doctype html><html lang="en-US"><head><meta charset="utf-8">
    <title>Hello world</title>
    </head><body>
    <h1>Hello World!</h1>
    <p>If you see this, the main app is working. Now it's time to include an user app.</p>
    </body></html>
<?php
}
/* ------------------------------------------------------------------------
    /Utility functions
------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------
    Create the app
------------------------------------------------------------------------ */
$app = new stdClass();
$app->ini = new iniClass(homedir.'.htini');
$app->steps = new stepsClass();
$app->session = new sessionClass();
$app->db = new sqlite3plus(homedir.'.htdata');
$app->q = getGet('q');
$app->respond = 'sayHello';
$app->ini->preset('nextcron', 0);


function shutdownUpdate(){
    global $app;
    $app->ini->update();
}
register_shutdown_function('shutdownUpdate');
/* ------------------------------------------------------------------------
    /Create the app
------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------
    Include scripts
------------------------------------------------------------------------ */
// Example - Default response and how use the router q_function
include(homedir.'chilli-example.php');

// Environment Example - multiple functions, steps, admin access.
//include(homedir.'chilli-html.php');
//include(homedir.'chilli-pass.php');
//include(homedir.'chilli-env.php');

// CMS - depends on chilli-html.php and chilli-pass.php
//include(homedir.'chilli-edit.php');
//include(homedir.'chilli-pages.php');

// Alternatives
//include(homedir.'chilli-user.php');
//include(homedir.'chilli-dbuser.php');

/* ------------------------------------------------------------------------
    /Include scripts
------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------
    Routing and execution
------------------------------------------------------------------------ */
$app->steps->run('afterincludes');
$app->ini->set('nextcron', time() + 86400); // 24*60*60 - one day
$app->steps->run('cron');
if(function_exists('q_'.$app->q)){
    call_user_func('q_'.$app->q);
}
else{
    call_user_func($app->respond);
}
$app->steps->run('afterrequest');
/* ------------------------------------------------------------------------
    /Routing and execution
------------------------------------------------------------------------ */
?>
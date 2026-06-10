<?php
/* ------------------------------------------------------------------------
   Part of the Chilli kit — https://chillikit.com

   Free to use, modify, and share (including commercially).
   Please keep this note as a reference to the origin.
------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------
    Html response functions
------------------------------------------------------------------------ */
function echoEnvheader(){
    echoHTMLHeader('sendheaders');
    echo('<div class="container">');
    echo('<a class="homebar" href="'.homeurl.'">go to front page</a>'.PHP_EOL);
}

function echoEnvfooter(){
    echo('</div>');
    echoHTMLFooter('sendfooter');
}

function sendStyle(){
?>
<style>
.homebar{
    display: block;
    background-color: #d0e0f0;
    text-align: center;
    padding: 0.5rem;
}
table{
    margin-bottom: 1rem;
}
th{
    background-color: #d0e0f0;
}
table, td{
    border: 1px solid #c0c0c0;
}
.h{
    text-align: center;
    font-weight: bold;
}
</style>
<?php
}
$app->steps->add('sendheaders', 'sendStyle');
/* ------------------------------------------------------------------------
    /Html response functions
------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------
    Report functions
------------------------------------------------------------------------ */
function q_whomadephp(){
    echoEnvheader();
?>
<h1>Who made PHP</h1>
<?php
    phpcredits(CREDITS_GENERAL);
    echoEnvfooter();    
}

function q_allphp(){
    echoEnvheader();
    phpcredits(CREDITS_GENERAL | CREDITS_GROUP | CREDITS_DOCS | CREDITS_MODULES);
    echoEnvfooter();    
}

function q_loadedmodules(){
    echoEnvheader();
?>
<h1>Currently loaded modules</h1>
<p>Modules extends the functionality of PHP</p>
<?php
    $modules = get_loaded_extensions();
    echo('<ul>'.PHP_EOL);
    foreach ($modules as $value) {
        echo("<li>$value</li>\n");
    }
    echo('</ul>'.PHP_EOL);
    echoEnvfooter();    
}

function q_neededmodules(){
    echoEnvheader();
?>
<h1>Modules needed</h1>
<p>Modules extends the functionality of PHP and are needed for Chilli to work</p>
<ul>
<?php
    echo('<li>GD is '.(extension_loaded('gd') ? 'Loaded' : 'Not loaded').'</li>');
    echo('<li>Core is '.(extension_loaded('Core') ? 'Loaded' : 'Not loaded').'</li>');
    echo('<li>date is '.(extension_loaded('date') ? 'Loaded' : 'Not loaded').'</li>');
    echo('<li>hash is '.(extension_loaded('hash') ? 'Loaded' : 'Not loaded').'</li>');
    echo('<li>json is '.(extension_loaded('json') ? 'Loaded' : 'Not loaded').'</li>');
    echo('<li>session is '.(extension_loaded('session') ? 'Loaded' : 'Not loaded').'</li>');
    echo('<li>sqlite3 is '.(extension_loaded('sqlite3') ? 'Loaded' : 'Not loaded').'</li>');
    echo('</ul>'.PHP_EOL);
    echoEnvfooter();    
}

function q_viewini(){
    echoEnvheader();
?>
<h1>Ini file content</h1>
<p>See how Chilli is set up.</p>
<ul>
<?php
    $buffer = json_decode(file_get_contents(homedir.'.htini'));
    foreach ($buffer as $prop => $value) {
        echo("<li>$prop: $value</li>\n");
    }
    echo('</ul>'.PHP_EOL);
    echoEnvfooter();
}

function q_viewtables(){
    global $app;
    echoEnvheader();
?>
<h1>Database tables</h1>
<p>See the tables in .htdata file.</p>
<ul>
<?php
    $res = $app->db->query("SELECT name FROM sqlite_master WHERE type='table' order by name;");
    while($row = $app->db->fetch($res)){
        echo('<li>');
        echo($row['name']);
        echo('</li>');
    }
    echo('</ul>'.PHP_EOL);
    echoEnvfooter();
}
/* ------------------------------------------------------------------------
    /Report functions
------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------
    Dashboard - Homepage
------------------------------------------------------------------------ */
function dashBoard(){
    global $app;
    echoEnvheader();
?>
<h1>Environment information</h1>
<p>Look around to see how PHP is set up and have a look at the ini file.</p>
<p><a href="<?php echoUrl('?q=whomadephp'); ?>">Who made PHP</a></p>
<p><a href="<?php echoUrl('?q=allphp'); ?>">Detailed PHP information</a></p>
<p><a href="<?php echoUrl('?q=loadedmodules'); ?>">See loaded modules</a></p>
<p><a href="<?php echoUrl('?q=neededmodules'); ?>">See modules needed</a></p>
<p><a href="<?php echoUrl('?q=viewini'); ?>">See Ini file content</a></p>
<p><a href="<?php echoUrl('?q=viewtables'); ?>">See database tables</a></p>
<?php if($app->session->admin): ?>
<p><a href="<?php echoUrl('?q=manage'); ?>">Manage</a></p>
<p><a href="<?php echoUrl('?q=logout'); ?>">Logout</a></p>
<?php else: ?>
<p><a href="<?php echoUrl('?q=login'); ?>">Login</a></p>
<?php
    endif;
    echoEnvfooter();
}
$app->respond = 'dashBoard';
/* ------------------------------------------------------------------------
    /Dashboard - Homepage
------------------------------------------------------------------------ */

?>
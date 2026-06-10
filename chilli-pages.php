<?php
/* ------------------------------------------------------------------------
   Part of the Chilli kit — https://chillikit.com

   Free to use, modify, and share (including commercially).
   Please keep this note as a reference to the origin.
------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------
    Set the defaults
------------------------------------------------------------------------ */
$app->ini->preset('showlatest', 1);
$app->ini->preset('latestlimit', 6);
$app->ini->preset('archivemenu', 1);
$app->ini->preset('postlimit', 10);
$app->ini->preset('loginmenu', 1);

/* ------------------------------------------------------------------------
    /Set the defaults
------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------
    Html output functions
------------------------------------------------------------------------ */
function echoContent($field){
    global $app;
    echo($app->post[$field]);
}
function echoSlug(){
    global $app;
    $url = homeurl;
    if(! $app->ini->get('useprettyurl')){
        $url.= '?q=';
    }
    $url.= $app->post['slug'];
    echo($url);
}
function echoCss(){
    global $app;
    echo($app->post['cssclass']);
}
function hasImage(){
    global $app;
    return file_exists(mediadir.'image'.$app->post['id'].'.jpg');
}
function echoImage(){
    global $app;
    printf('<img class="featured-image" src="%simage%s.jpg">%s', mediaurl, $app->post['id'], PHP_EOL);
}
function echoThumbnail(){
    global $app;
    printf('<img class="thumbnail" src="%simage%s-small.jpg">%s', mediaurl, $app->post['id'], PHP_EOL);
}


// non post functions like main menu etc.
function echoMainmenu(){
    global $app;
    echo('<ul class="mainmenu">'.PHP_EOL);
    printf('<li><a href="%s">Home</a></li>%s', homeurl, PHP_EOL);
    if($app->session->admin){
        printf('<li><a href="%s?q=manage">Manage</a></li>%s', homeurl, PHP_EOL);        
    }
    elseif($app->ini->get('loginmenu')){
        printf('<li><a href="%s?q=login">Login</a>%s', homeurl, PHP_EOL);
    }
    $list = $app->db->query('select title, slug from posts where menu = 1 and publish = 1 order by promote desc, lastedit desc;');
    while($post = $app->db->fetch($list)){
        printf('<li><a href="%s">%s</a></li>%s', getFullurl($post['slug']), $post['title'], PHP_EOL);
    }
    if($app->ini->get('archivemenu')){
        printf('<li><a href="%s">Archive</a></li>%s', getFullurl('archive'), PHP_EOL);
    }
    echo('</ul>'.PHP_EOL);
}

/* ------------------------------------------------------------------------
    Html output functions
------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------
    Page parts   
------------------------------------------------------------------------ */
function pageHeader(){
    global $app;
?>
    <link rel="stylesheet" href="chilli-pages.css" media="all">
    <script src="chilli-pages.js" defer></script>
    <meta name="robots" content="index, follow">
    <title><?php echo($app->ini->get('sitename')); ?></title>
<?php
}
$app->steps->add('sendheader', 'pageHeader');

function pageTop(){
    global $app;
?>
    <div id="gotopbutton">&#x2630;</div>
    <main id="page-container">
        <div id="header">
            <div><img id="logo" src="media/logo64.png"></div>
            <div><span id="title"><?php echo($app->ini->get('sitename')); ?></span></div>
            <div><span class="hamburger"></span></div>
        </div>
        <div class="dropdown">
            <?php echoMainmenu(); ?>
        </div>
<?php    
}
function pageFooter(){
?>
    </main>
    <p class="copyright">Copyright Chilli kit 2025 - <?php echo(date('Y', time())); ?></p>
<?php
}
/* ------------------------------------------------------------------------
    Page parts
------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------
    Html response functions
------------------------------------------------------------------------ */
function q_archive(){
    global $app;
    echoHTMLheader('sendheader');
    pageTop();
    $posttotal = $app->db->querySingle('select count(*) from posts where publish = 1;');
    $postoffset = (int)getUrlpart(1, 0);
    $postlimit = (int)$app->ini->get('postlimit');
    $app->postposition = $postoffset;

    $stmt = $app->db->prepare('select * from posts where publish = 1 order by homepage desc, promote desc, lastedit desc limit :postlimit offset :postoffset;');
    $stmt->bindValue(':postlimit', $postlimit, SQLITE3_INTEGER);
    $stmt->bindParam(':postoffset', $postoffset, SQLITE3_INTEGER);
    $postlist = $stmt->execute();

?>
        <h3>Archive</h3>
<?php
    if($postoffset >= $postlimit){
        printf('<a class="previouspage" href="%s">&#x25B2; Previous %s of %s</a>%s',
        getFullurl('archive', $postoffset - $postlimit) , $postlimit, $postoffset, PHP_EOL);
    }
    while($app->post = $app->db->fetch($postlist)){
        $app->postposition++;
?>
        <a class="archive-item <?php echoCss(); ?>" href="<?php echoSlug(); ?>">
            <?php if(hasImage()) echoThumbnail(); ?>
            <p><b><?php echoContent('title'); ?></b></p>
            <p><?php echoContent('excerpt'); ?></p>
        </a>
<?php
    }
    if($posttotal > ($postoffset + $postlimit)){
        printf('<a class="nextpage" href="%s">&#x25BC; Next %s of %s</a>%s',
        getFullurl('archive', $postoffset + $postlimit), $postlimit, $posttotal, PHP_EOL);
    }
    pageFooter();
    echoHTMLfooter('sendfooter');
}

function sendPage(){
    global $app;
    $app->post = $app->db->querySingle('select * from posts where slug = "'.$app->db->escapeString($app->q).'";', true);
    if($app->post){
?>
        <article class="single-page <?php echoCss(); ?>">
            <?php if(hasImage()) echoImage(); ?>
            <h1><?php echoContent('title'); ?></h1>
            <?php echoContent('content');
            echo(PHP_EOL); ?>
        </article>
<?php        
    }
    else{
?>
        <article class="no-page <?php echoCss(); ?>">
            <h2>Not found!</h2>
            <p>We did'n find what you are looking for. The post may have been deleted og the url is for some reason not valid.</p>
            <p>See if you can find it in the list below.</p>
        </article>
<?php    
    }    
}
function sendHomepage(){
    global $app;
    $app->post = $app->db->querySingle('select * from posts where publish = 1 order by homepage desc, promote desc, lastedit desc', true);
?>
        <article class="home-page <?php echoCss(); ?>">
            <?php if(hasImage()) echoImage(); ?>
            <h1><?php echoContent('title'); ?></h1>
            <?php echoContent('content');
            echo(PHP_EOL); ?>
        </article>
<?php
}

function echoLatest(){
    global $app;
    $app->postposition = 0;
    $stmt = $app->db->prepare('select * from posts where publish = 1 order by promote desc, lastedit desc limit :limit;');
    $stmt->bindValue(':limit', $app->ini->get('latestlimit'), SQLITE3_INTEGER);
    $postlist = $stmt->execute();    
    while($app->post = $app->db->fetch($postlist)){
        $app->postposition++;
?>
        <a class="latest-item <?php echoCss(); ?>" href="<?php echoSlug(); ?>">
            <?php if(hasImage()) echoThumbnail(); ?>
            <p><b><?php echoContent('title'); ?></b></p>
            <p><?php echoContent('excerpt'); ?></p>
        </a>
<?php
    }
}

// Routing
function pageResponse(){
    global $app;
    echoHTMLheader('sendheader');
    pageTop();
    if($app->q){// page or 404
        sendPage();
    }
    else{// homepage
        sendHomepage();
        if($app->ini->get('showlatest')){
            echoLatest();
        }
    }
    pageFooter();
    echoHTMLfooter('sendfooter');
}
$app->respond = 'pageResponse';
/* ------------------------------------------------------------------------
    /Html response functions
------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------
    Pages manage functions
------------------------------------------------------------------------ */
if($app->session->admin):

function showSiteSetupBlock(){
    global $app;
?>
<hr>
<h3>Latest posts</h3>
<p>Show a list of the latest posts on the home page</p>
<?php $showlatest = (int)$app->ini->get('showlatest'); ?>
<form method="post" action="?q=dosetshowlatest">
    <label>Show latest posts</label>
    <select name="showlatest">
        <option <?php if($showlatest == 0) echo('selected'); ?> value="0">No</option>
        <option <?php if($showlatest == 1) echo('selected'); ?> value="1">Yes</option>
    </select>
    <input type="submit" value="Change">
</form>

<form method="post" action="?q=dochangelatestlimit">
<p>Select how many posts to display.</p>
    <label>Number of posts</label>
<select name="latestlimit">
<?php
    $options = array(6, 10, 20, 30, 50, 100);
    foreach($options as $opt){
        if($opt == $app->ini->get('latestlimit')){
            echo('<option selected>');
        }
        else{
            echo('<option>');
        }
        echo($opt.'</option>'.PHP_EOL);
    }
?>
</select>
    <input type="submit" value="Change">
</form>


<hr>
<h3>Archive</h3>
<p>Show an archive link in the menu.</p>
<?php $archivemenu = (int)$app->ini->get('archivemenu'); ?>
<form method="post" action="?q=dosetarchivemenu">
    <label>Show archive in menu</label>
    <select name="archivemenu">
        <option <?php if($archivemenu == 0) echo('selected'); ?> value="0">No</option>
        <option <?php if($archivemenu == 1) echo('selected'); ?> value="1">Yes</option>
    </select>
    <input type="submit" value="Change">
</form>

<form method="post" action="?q=dochangepostlimit">
<p>Select how many posts to display per page while browsing the archive.</p>
    <label>Number of posts</label>
<select name="postlimit">
<?php
    $options = array(6, 10, 20, 30, 50, 100);
    foreach($options as $opt){
        if($opt == $app->ini->get('postlimit')){
            echo('<option selected>');
        }
        else{
            echo('<option>');
        }
        echo($opt.'</option>'.PHP_EOL);
    }
?>
</select>
<input type="submit" id="postlimitbutton" value="Change">
</form>

<hr>
<h3>Menu login</h3>
<p>Show a login menu item. Helpful on a trusted network, but a security risk if your site is public.</p>
<?php $loginmenu = (int)$app->ini->get('loginmenu'); ?>
<form method="post" action="?q=dosetloginmenu">
    <label>Show login in menu</label>
    <select name="loginmenu">
        <option <?php if($loginmenu == 0) echo('selected'); ?> value="0">No</option>
        <option <?php if($loginmenu == 1) echo('selected'); ?> value="1">Yes</option>
    </select>
    <input type="submit" value="Change">
</form>

<?php
}
$app->steps->add('showsetup', 'showSiteSetupBlock');

// API =================================================================
function q_dochangepostlimit(){
    global $app;    
    $app->ini->set('postlimit', getPost('postlimit'));
    gotoUrl('?q=manage&m=Posts per page has changed');
}

function q_dosetarchivemenu(){
    global $app;    
    $archivemenu = (int)getPost('archivemenu', 0);
    $app->ini->set('archivemenu', $archivemenu);
    gotoUrl('?q=manage&m=Main menu has changed');        
}

function q_dosetloginmenu(){
    global $app;    
    $loginmenu = (int)getPost('loginmenu', 0);
    $app->ini->set('loginmenu', $loginmenu);
    gotoUrl('?q=manage&m=Main menu has changed');
}

function q_dosetshowlatest(){
    global $app;    
    $showlatest = (int)getPost('showlatest', 0);
    $app->ini->set('showlatest', $showlatest);
    gotoUrl('?q=manage&m=Show latest has changed');
}
function q_dochangelatestlimit(){
    global $app;    
    $latestlimit = (int)getPost('latestlimit', 0);
    $app->ini->set('latestlimit', $latestlimit);
    gotoUrl('?q=manage&m=Show latest has changed');    
}
/* ------------------------------------------------------------------------
    /Pages manage functions
------------------------------------------------------------------------ */
endif;
?>
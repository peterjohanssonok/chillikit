<?php
/* ------------------------------------------------------------------------
   Part of the Chilli kit — https://chillikit.com

   Free to use, modify, and share (including commercially).
   Please keep this note as a reference to the origin.
------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------
    Set the defaults
------------------------------------------------------------------------ */
define('postlistmax', 20); // Max posts in manager

if(! $app->db->tableexist('posts')){
    $app->db->exec("create table posts (id INTEGER PRIMARY KEY, publish INTEGER, menu INTEGER, promote INTEGER, homepage INTEGER, lastedit INTEGER, sluglock INTEGER, slug TEXT, cssclass TEXT, title TEXT, content TEXT, excerpt TEXT);");
    
    $app->db->exec(
    "insert into posts (publish, menu, promote, homepage, lastedit, sluglock, slug, cssclass, title, content, excerpt) 
    values(1, 0, 0, 0, 1753814686, 1, 'hello-world', 'post', 'Hello World', '<p>Congratulations. This is the first post on your journey as a content writer.</p>', 'Congratulations. This is the first post');");
}

/* ------------------------------------------------------------------------
    Site manage functions
------------------------------------------------------------------------ */
if($app->session->admin):

function showSitePostblock(){
    global $app;
    // new post
?>
    <hr>
    <h3>New post</h3>
    <p>Add a new post on your site.</p>
    <a class="button" href="?q=editpost&id=0">Create post</a>
    <hr>
    <h3>Recent posts</h3>
<?php
    
    // post list
    
    $postoffset = (int)getGet('o', 0);
    $postcount = $app->db->querySingle('select count(*) from posts;');
    $res = $app->db->query('select * from posts order by homepage desc, promote desc, lastedit desc limit '.postlistmax.' offset '.$postoffset.';');
    
    echo('<div class="postlist">'.PHP_EOL);
    if($postoffset >= postlistmax){
        printf('<a class="pagination" href="?q=manage&o=%s">&#x25B2; Previous %s of %s</a>%s',
        $postoffset - postlistmax, postlistmax, $postoffset, PHP_EOL);
    }
    while($post = $app->db->fetch($res)){
        echo('<div class="draft">'.PHP_EOL);
        echo('<a class="publiclink" href="'.$post['slug'].'" target="_blank">View</a> - ');
        echo('<a class="titlelink" href="?q=editpost&id='.$post['id'].'">'.$post['title'].'</a><br>'.PHP_EOL);
        echo('<span class="lastedit">'.date($app->ini->get('timeformat'), $post['lastedit']).' </span>');
        echo('<span class="status">');
        if($post['publish']) echo('Published ');
        if($post['menu']) echo('Menu ');
        if($post['promote']) echo('promote ');
        if($post['homepage']) echo('Home page ');
        echo('</span>');
        echo('</div>'.PHP_EOL);
    }
    if($postcount > ($postoffset + postlistmax)){
        printf('<a class="pagination" href="?q=manage&o=%s">&#x25BC; Next %s of %s</a>%s',
        $postoffset + postlistmax, postlistmax, $postcount, PHP_EOL);
    }
    echo('</div>'.PHP_EOL);    

    // Reset homepage
?>
    <hr>
    <h3>Reset home page</h3>
    <p>If no home page is selected, Chilli automatically chooses one from the latest promoted posts.</p>
    <p>Your current homepage is set to: 
<?php

    $currenthome = $app->db->querySingle('select title from posts where homepage = 1;');
    if($currenthome){
        echo($currenthome);
    }
    else{
        echo('Latest or promoted post');
    }
?>
    </p>
    <p><a class="button" href="?q=sethomepage">Reset home page</a></p>
<?php
}
$app->steps->add('showmanager', 'showSitePostblock');


// API =================================================================
function q_sethomepage(){
    global $app;
    $app->db->exec('update posts set homepage = 0 where homepage = 1;');
    $id = (int)getGet('id', 0);
    if($id){
        $app->db->exec('update posts set homepage = 1 where id = '. $id);        
    }
    gotoUrl('?q=manage&m=Homepage has changed');
}

/* ------------------------------------------------------------------------
    /Site manage functions
------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------
    Site edit post functions
------------------------------------------------------------------------ */
// edit post helpers
function getExcerpt($words, $limit, $append = ' &hellip;'){
    $limit = $limit+1;
    $words = strip_tags($words);
    $words = explode(' ', $words, $limit);
    array_pop($words);
    $words = implode(' ', $words) . $append;
    return $words;
}
function getSlug($text){
    $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $text);
    return strtolower($slug);
}
// edit post functions
function insertEditorscript(){
?>
    <script src="chilli-edit.js" defer></script>
<?php
}

function q_editpost(){
    global $app;    
    $id = (int)getGet('id');
    if($id){
        $post = $app->db->querySingle('select * from posts where id = '.$id, true);
    }
    else{
        $post = array();
        $post['publish'] = '0';
        $post['menu'] = '0';
        $post['promote'] = '0';
        $post['homepage'] = '0';
        $post['lastedit'] = time();
        $post['sluglock'] = '0';
        $post['slug'] = 'new-post';
        $post['cssclass'] = 'post';
        $post['title'] = 'New post';
        $post['content'] = '<p>Write content here</p>';
        $post['excerpt'] = 'Write content here';
    }
    
    $app->steps->add('sendmanageheader', 'insertEditorscript');
    echoManageheader();
    echoManagenavigation();
?>
    <h2>Edit post</h2>
    <form method="post" action="?q=dosavepost" enctype="multipart/form-data">
        <input type="hidden" id="postid" name="postid" value="<?php echo($id); ?>">
        <label>Title</label>
        <input type="text" name="title" value="<?php echo($post['title']); ?>">
        <label>Content</label>
        <textarea name="content" class="post-edit-content"><?php echo(htmlentities($post['content'], ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8')); ?></textarea>

        <p><label>Featured picture</label>
        <input type="file" id="imageinput" name="image" class="spacer" value=""><br>
<?php 
$imgurl = '';
if(file_exists(mediadir.'image'.$id.'.jpg')){
    $imgurl = mediaurl.'image'.$id.'.jpg';
}
?>
        <img src="<?php echo($imgurl); ?>" id="postimage" class="post-edit-image"></p>

        <p><label>published</label>
        <select name="publish">
            <option <?php if($post['publish'] == 0) echo('selected'); ?> value="0">No</option>
            <option <?php if($post['publish'] == 1) echo('selected'); ?> value="1">Yes</option>
        </select>
        <label>In menu</label>
        <select name="menu">
            <option <?php if($post['menu'] == 0) echo('selected'); ?> value="0">No</option>
            <option <?php if($post['menu'] == 1) echo('selected'); ?> value="1">Yes</option>
        </select>
        <label>Ranking</label>
        <select name="promote">
            <option <?php if($post['promote'] == 0) echo('selected'); ?> value="0">Normal</option>
            <option <?php if($post['promote'] == 1) echo('selected'); ?> value="1">Promote</option>
        </select>

        <input type="hidden" name="homepage" value="<?php echo($post['homepage']); ?>">

        <p><label>Update url</label>
        <select name="sluglock" id="sluglock">
            <option <?php if($post['sluglock'] == 0) echo('selected'); ?> value="0">Automatic</option>
            <option <?php if($post['sluglock'] == 1) echo('selected'); ?> value="1">Manual</option>
        </select>
        <label id="sluglabel">URL</label>
        <input type="text" id="slug" name="slug" value="<?php echo($post['slug']); ?>"></p>

        <p><label>CSS Class</label>
        <input type="text" name="cssclass" value="<?php echo($post['cssclass']); ?>"></p>

        <p><input type="submit" id="submit" value="Save post">
        <a class="button" href="?q=manage">Cancel</a></p>
        <hr class="spacer">
        <p>
        <?php if($id): ?>
        <a class="button" href="<?php echo('?q=sethomepage&id='.$id); ?>">Set as home page</a>
        <?php endif; ?>
        <a class="button" href="<?php echo('?q=dodeletepost&postid='.$id); ?>" onclick="return confirm('Delete post?')">Delete post</a>
        </p>
    </form>
<?php
    echoManagefooter();
}

// API =================================================================
function q_dosavepost(){
    global $app;
$updsql = <<<UPDSQL
update posts set
publish = :publish,
menu = :menu,
promote = :promote,
homepage = :homepage,
lastedit = :lastedit,
sluglock = :sluglock,
slug = :slug,
cssclass = :cssclass,
title = :title,
content = :content,
excerpt = :excerpt
where id = :id;
UPDSQL;

    $id = (int)getPost('postid', 0);
    if($id == 0){
        $id = $app->db->append('posts');
    }
    $sluglock = (int)getPost('sluglock', 0);
    if($sluglock == 1){
        $slug = getPost('slug');
    }
    else{
        $slug = getSlug(getPost('title'));
    }
    $xst = function_exists('q_'.$slug);
    if(! $xst){
        $xst = ($app->db->querySingle('select count(*) from posts where slug = "'.$slug.'" and id <> '.$id, false) > 0);
    }
    if($xst){
        $slug = $slug.$id;
    }

    $stmt = $app->db->prepare($updsql);
    $stmt->bindValue(':publish', (int)getPost('publish'), SQLITE3_INTEGER);
    $stmt->bindValue(':menu', (int)getPost('menu'), SQLITE3_INTEGER);
    $stmt->bindValue(':promote', (int)getPost('promote'), SQLITE3_INTEGER);
    $stmt->bindValue(':homepage', (int)getPost('homepage'), SQLITE3_INTEGER);
    $stmt->bindValue(':lastedit', time(), SQLITE3_INTEGER);
    $stmt->bindValue(':sluglock', $sluglock, SQLITE3_INTEGER);
    $stmt->bindValue(':slug', $slug, SQLITE3_TEXT);
    $stmt->bindValue(':cssclass', getPost('cssclass'), SQLITE3_TEXT);
    $stmt->bindValue(':title', getPost('title'), SQLITE3_TEXT);
    
    $stmt->bindValue(':content', getPost('content'), SQLITE3_TEXT);
    $stmt->bindValue(':excerpt', getExcerpt(getPost('content'), 30), SQLITE3_TEXT);
    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
    $stmt->execute();

    if($_FILES['image']['error'] == UPLOAD_ERR_OK){
        scaleJpg($_FILES['image']['tmp_name'], mediadir.'image'.$id.'.jpg', 1000);
        scaleJpg($_FILES['image']['tmp_name'], mediadir.'image'.$id.'-small.jpg', 300);
    }
    gotoUrl('?q=manage');
}


function q_dodeletepost(){
    global $app;
    $id = (int)getGet('postid', 0);
    if($id > 0){
        $app->db->exec('delete from posts where id = '. $id);
        if(file_exists(mediadir.'image'.$id.'.jpg')){
            unlink(mediadir.'image'.$id.'.jpg');
            unlink(mediadir.'image'.$id.'-small.jpg');
        }
    }
    gotoUrl();
}
/* ------------------------------------------------------------------------
    Site edit post functions
------------------------------------------------------------------------ */
endif;

?>
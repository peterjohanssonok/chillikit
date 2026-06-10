<?php
/* ------------------------------------------------------------------------
   Part of the Chilli kit — https://chillikit.com

   Free to use, modify, and share (including commercially).
   Please keep this note as a reference to the origin.
------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------
    Set the defaults
------------------------------------------------------------------------ */
define('mediadir', homedir.'media/');
define('mediaurl', homeurl.'media/');
$app->ini->preset('sitename', 'My new homepage');
$app->ini->preset('countrycode', 'en-US');
$app->ini->preset('timeformat', 'F j, Y, g:i a');
$app->ini->preset('useprettyurl', 0); // Pretty url's is off

ini_set('post_max_size', '20M');
ini_set('upload_max_filesize', '20M');
ini_set('max_file_uploads', '2');

// Pretty url routing =====================================================
// mysite.com/archive/20 rather than mysite/?q=archive&offset=20
$app->urlparts = explode('/', $app->q);
$app->q = $app->urlparts[0];

/* ------------------------------------------------------------------------
    /Set the defaults
------------------------------------------------------------------------ */
/* Steps used in this script:

sendheader
sendfooter

sendmanageheader
sendmanagefooter

showmanager
showmanagerlast
showsetup
showsetuplast

used in index:

afterincludes
cron
afterrequest
*/
/* ------------------------------------------------------------------------
    Utillity functions
------------------------------------------------------------------------ */
function getUrlpart($index, $default = ''){
    global $app;
    return empty($app->urlparts[$index]) ? $default : $app->urlparts[$index];
}
function getFullurl($location = '', $part = ''){
    global $app;
    $result = $app->ini->get('useprettyurl') ? homeurl.$location : homeurl.'?q='.$location;
    $result.= $part ? '/'.$part : '';
    return $result;
}
function gotoUrl($q = ''){
    header('Location: '.homeurl.$q);
}
function echoUrl($q = ''){
    echo(homeurl.$q);
}
function resetContent(){
    header('HTTP/1.0 205 Reset Content');
}
function send404(){
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
}
function uploadErr($index){
/* UPLOAD_ERR_OK, UPLOAD_ERR_config_SIZE, UPLOAD_ERR_FORM_SIZE, UPLOAD_ERR_PARTIAL,
UPLOAD_ERR_NO_FILE, unused, UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_EXTENSION */
    $err = array(
        'The file uploaded with success.',
        'The uploaded file exceeds the upload_max_filesize directive in php.config.',
        'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
        'The uploaded file was only partially uploaded.',
        'No file was uploaded.',
        'Not used',
        'Missing a temporary folder.',
        'Failed to write file to disk.',
        'The uploaded file extension is not supported.'
    );
    return $err[$index];
}
function scalePng($src, $dest, $width){
    $png = imagecreatefrompng($src);
    if($png){
        $w = imagesx($png);
        $h = imagesy($png);
        $ratio = $width / $w;
        $height = intval($h * $ratio);
        $newPng = imagecreatetruecolor($width, $height);
        imagealphablending($newPng, false);
        imagesavealpha($newPng, true);
        imagecopyresampled($newPng, $png, 0, 0, 0, 0, $width, $height, $w, $h);
        imagepng($newPng, $dest);
    }
}
function scaleJpg($src, $dest, $width){
    $picture = imagecreatefromjpeg($src);
    if($picture){
        $w = imagesx($picture);
        $h = imagesy($picture);
        $ratio = $width / $w;
        $height = intval($h * $ratio);
        $newPicture = imagecreatetruecolor($width, $height);
        imagecopyresampled($newPicture, $picture, 0, 0, 0, 0, $width, $height, $w, $h);
        imagejpeg($newPicture, $dest, 60);
    }
}
/* ------------------------------------------------------------------------
    /Utillity functions
------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------
    Html response functions
------------------------------------------------------------------------ */
function echoHTMLheader($step){
    global $app;
?>
<!doctype html>
<html lang="<?php $app->ini->get('countrycode'); ?>">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, user-scalable=yes">
    <meta charset="utf-8">
    <base href="<?php echo(homeurl); ?>">
    <link rel="icon" href="media/logo32.png" sizes="32x32">
    <link rel="icon" href="media/logo192.png" sizes="192x192">
    <link rel="apple-touch-icon" href="media/logo180.png">
    <link rel="stylesheet" href="chilli-reset.css" media="all">
<?php $app->steps->run($step); ?>
</head>
<body>
<?php
}
function echoHTMLfooter($step){
    global $app;
    $app->steps->run($step);
?>
</body>
</html>
<?php
}

function manageHeaders(){
    global $app;
?>
    <link rel="stylesheet" href="chilli-html.css" media="all">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo($app->ini->get('sitename')); ?> | Manage</title>
<?php
}
$app->steps->add('sendmanageheader', 'manageHeaders');

function echoManageheader(){
    global $app;
    echoHTMLheader('sendmanageheader');
?>
<div class="page-container">
<?php
}
function echoManagefooter(){
    global $app;
?>
</div>
<?php
    echoHTMLfooter('sendmanagefooter');
}
function echoManagenavigation(){
    global $app;
    echo('<ul class="navigation">'.PHP_EOL);
    echo('<li><a href="'.homeurl.'">Home</a></li>'.PHP_EOL);
    if($app->session->admin){
        echo('<li><a href="?q=manage">Manage</a></li>'.PHP_EOL);
        echo('<li><a href="?q=logout">Logout</a></li>'.PHP_EOL);
    }
    else{
        echo('<li><a href="?q=login">Login</a></li>'.PHP_EOL);
    }
    echo('</ul>'.PHP_EOL);
}
function echoMessage(){
    if(getGet('m')){
        echo('<p class="message">'. getGet('m').'</p>'.PHP_EOL);
    }
}
/* ------------------------------------------------------------------------
    /Html response functions
------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------
    Managing API framework
------------------------------------------------------------------------ */
if($app->session->admin):


function q_manage(){
    global $app;
    echoManageheader();
    echoManagenavigation();
    echo('<h1>Manage your site</h1>'.PHP_EOL);
    echo('<p>Manage your content and site settings.</p>');
    echoMessage();
    $app->steps->run('showmanager'); // Used for daily management
    $app->steps->run('showmanagerlast'); // Used for daily management
    $app->steps->run('showsetup'); // Used for logo, language and time format etc.
    $app->steps->run('showsetuplast'); // Used for logo, language and time format etc.
    echoManagefooter();
}
/* ------------------------------------------------------------------------
    /Managing API framework
------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------
    Setup API framework
------------------------------------------------------------------------ */
function q_dochangesitename(){
    global $app;
    $app->ini->set('sitename', getPost('sitename'));
    gotoUrl('?q=manage&m=Site name changed');
}
function q_dochangeprettyurl(){
    global $app;
    $app->ini->set('useprettyurl', getPost('useprettyurl'));
    gotourl('?q=manage&m=Pretty urls has changed');
}

function q_makehtaccess(){
$htaccessString = "
# CHILLI BEGIN
<IfModule mod_rewrite.c>
    Options -MultiViews
    RewriteEngine on";

    if(strlen(dirname($_SERVER['SCRIPT_NAME'])) > 1){
        $htaccessString.= "
    Rewritebase ".dirname($_SERVER['SCRIPT_NAME'])."/";
    }
    else{
        $htaccessString.= "
    Rewritebase /";
    }

$htaccessString.= "
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?q=$1 [L,QSA]
</IfModule>
# CHILLI END
";

    if(file_exists(homedir.'.htaccess')){
        $content = file_get_contents(homedir.'.htaccess');
        if(! strpos($content, 'CHILLI BEGIN')){
            file_put_contents(homedir.'.htaccess', $content.$htaccessString);
        }
    }
    else{
        file_put_contents(homedir.'.htaccess', $htaccessString);
    }
    gotourl('?q=manage&m=ready for pretty urls');
}

function q_dochangelanguage(){
    global $app;
    $app->ini->set('countrycode', getPost('countrycode'));
    gotoUrl('?q=manage&m=Country code changed');
}
function q_dochangetimeformat(){
    global $app;
    $app->ini->set('timeformat', getPost('timeformat'));
    gotoUrl('?q=manage&m=Time format changed');
}

function q_douploadlogo(){
    $errCode = $_FILES['logo']['error'];
    $sourceName = strtolower(basename($_FILES['logo']['name']));
    $tempName = $_FILES['logo']['tmp_name'];
    
    if ($errCode == UPLOAD_ERR_OK){
        if(str_ends_with ($sourceName, '.png')){
            if(is_uploaded_file($tempName)){
            $filepath = homedir.'media/logo.png';
                scalePng($tempName, str_replace('.png', '192.png', $filepath), 192);
                scalePng($tempName, str_replace('.png', '180.png', $filepath), 180);
                scalePng($tempName, str_replace('.png', '64.png', $filepath), 64);
                scalePng($tempName, str_replace('.png', '32.png', $filepath), 32);
            }
            else{
                $errCode = UPLOAD_ERR_PARTIAL;
            }
        }
        else{
            $errCode = UPLOAD_ERR_EXTENSION;
        }
    }
    gotourl('?q=manage&m='.uploadErr($errCode));
}

/* ------------------------------------------------------------------------
    /Setup API framework
------------------------------------------------------------------------ */
/* ------------------------------------------------------------------------
    Setup UX framework
------------------------------------------------------------------------ */
function showSiteBlock(){ // show at step showsetuplast
    global $app;
    $pretty = (int)$app->ini->get('useprettyurl');
?>
<hr>
<h3>Site name</h3>
<form method="post" action="?q=dochangesitename">
    <input type="text" id="sitetext" name="sitename" value="<?php echo($app->ini->get('sitename')); ?>">
    <input type="submit" id="sitebutton" value="Change">
</form>
<p>Change the name for this site.</p>

<hr>
<h3>Pretty URLs</h3>
<form method="post" action="?q=dochangeprettyurl">
    <label>Enable pretty URLs</label>
    <select name="useprettyurl">
        <option <?php if($pretty == 0) echo('selected'); ?> value="0">No</option>
        <option <?php if($pretty == 1) echo('selected'); ?> value="1">Yes</option>
    </select>
    <input type="submit" value="Change">
</form>
<p>If you want to use pretty URLs you need an <b>.htaccess</b> file to instruct the web server to change "mysite.com/?q=about" to a more readable mysite.com/about</p>
<p><a class="button" href="?q=makehtaccess">Create htaccess file</a></p>

<hr>
<h3>Upload your logo</h3>
<form method="post" action="?q=douploadlogo" enctype="multipart/form-data">
<input type="hidden" name="MAX_FILE_SIZE" value="10000000">
<input type="file" name="logo" accept=".png">
<input type="submit" value="Upload">
</form>
<p>Upload a PNG logo from your device. (max size 10 MB)</p>
<p><img src="<?php echo('media/logo180.png?rev=t'.time()); ?>"></p>

<hr>
<h3>Language</h3>
<form method="post" action="?q=dochangelanguage">
    <input type="text" name="countrycode" value="<?php echo($app->ini->get('countrycode')); ?>">
    <input type="submit" value="Change">
</form>
<p>Change the language and Country code for this site. The code consists of two elements. First the language and then the country.</p>
<p>Examples: en-US for US english, en-GB for Great Britain. More examples: da-DK, nn-NO, nb-NO, de-DE, de-AT, pt-PT, pt-BR</p>
<p>For more information on language codes <a target="_BLANK" href="https://www.w3schools.com/TAGs/ref_language_codes.asp">look here</a></p>
<p>For more information on country codes <a target="_BLANK" href="https://www.w3schools.com/TAGs/ref_country_codes.asp">look here</a></p>

<hr>
<h3>Time format</h3>
<p>Current time and format: <?php echo(date($app->ini->get('timeformat')))?></p>
<form method="post" action="?q=dochangetimeformat">
    <input type="text" name="timeformat" value="<?php echo($app->ini->get('timeformat')); ?>">
    <input type="submit" value="Change">
</form>
<p>Change the time and date format. </p>
<p>Examples: US: F j, Y, g:i a UK: d/m/Y H:i:s More: d m Y H:i and: Y-m-d H:i</p>
<p>For more information <a target="_BLANK" href="https://www.w3schools.com/php/func_date_date.asp">look here</a></p>
<p>
d - Day of the month; two digits with leading zeros (01 or 31)<br>
D - Day of the week in text as an abbreviation (Mon to Sun)<br>
m - Month in numbers with leading zeros (01 or 12)<br>
j - Day of the month without leading zeros<br>
M - Month in text, abbreviated (Jan to Dec)<br>
y - Year in two digits (08 or 14)<br>
Y - Year in four digits (2008 or 2014)<br>
l - (lowercase L) the day of the week<br>
F - A full textual representation of a month, such as January or March<br>
</p>
<p>
h - Hour in 12-hour format with leading zeros (01 to 12)<br>
g - 12-hour format of an hour without leading zeros<br>
H - Hour in 24-hour format with leading zeros (00 to 23)<br>
i - Minutes with leading zeros (00 to 59)<br>
s - Seconds with leading zeros (00 to 59)<br>
a - Lowercase ante meridiem and post meridiem (am or pm)<br>
A - Uppercase Ante meridiem and Post meridiem (AM or PM)<br>
</p>
<p>Other characters, like "/", ".", ":" or "-" can also be inserted between the characters to add additional formatting.</p>

<?php
}
$app->steps->add('showsetuplast', 'showSiteBlock');



endif; // $app->session->admin
/* ------------------------------------------------------------------------
    /Setup UX framework
------------------------------------------------------------------------ */

?>
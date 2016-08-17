<?php
include('../_template/database.php');
$subtitle = array('TC' => '研究著作','EN' => 'Publication');
include('../_template/header.php');
include('../_template/menu.php');
?>
<div id="main_content">
<?
$sec1 = array();
$sec1['title'] = array('TC' => '研究著作','EN' => 'Publications');
$sec1['image'] = array();
?>
    <div class="main_item">
        <h2><?=$sec1['title'][$lang]?></h2>
<?
switch($lang)
{
default:
?>
<?
$pub_query = $pdo->query("SELECT * FROM publications WHERE v_state='working' ORDER BY pub_year DESC, ordinal ASC");
$thisyear = 0;
while( $pub = $pub_query->fetch(PDO::FETCH_ASSOC) )
{
    $y = intval($pub['pub_year']);
    if( $thisyear != $y )
    {
        if( $thisyear != 0 ) { echo '</ul>'; }
        echo '<div id="pub_'.$y.'_title" class="year" onclick="turn($(this).next());">'.$y.'</div>';
        echo '<ul id="pub_'.$y.'_content" class="publication">';
        $thisyear = $y;
    }
    echo '<li><pre>';
    var_dump($pub);
    echo '</pre></li>';
}
?>
<?
    break;
}
?>
    </div>
</div>
<?
include('../_template/footer.php');
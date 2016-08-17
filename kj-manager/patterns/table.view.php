<?php
require_once('../mod_general.php');
require_once('../mod_database.php');
$dba = new Accessor();

if( !isset($_POST['table']) ) { die(); }
$table = array( 'name' => $_POST['table'] );
if( isset($_POST['where']) AND !empty($_POST['where']) ) { $table['where'] = $_POST['where']; }
if( isset($_POST['limit']) AND intval($_POST['limit']) > 0 ) { $table['limit'] = intval($_POST['limit']); }

$header = $dba->getSimpleHeader($table['name']);

$insert_form = '';
$table_width = null;
if( isset($_POST['controller']) )
{
    $ctrl_cfg = $_POST['controller'];
    $table_width = '75%';
    $ctrl_width = '25%';
    if( isset($ctrl_cfg['width']) )
    {
        if( substr($ctrl_cfg['width'],-1,1) == '%' )
        {
            $ctrl_wpr = intval(substr($ctrl_cfg['width'],0,-1));
            $table_width = ( 100 - $ctrl_wpr ) . '%';
            $ctrl_width = $ctrl_wpr . '%';
        }
        else
        {
            $ctrl_wpx = intval($ctrl_cfg['width']);
            $ctrl_width = $ctrl_wpx . 'px';
        }
    }
?>
<div class="-controller" style="float:right;width:<?=$ctrl_width?>">
    <a class="btn-like" href="javascript:MMNet.Controller.load('<?=$insert_form?>');">新增資料</a>
</div>
<?
}

if( isset($_POST['bound']) )
{
    if( isset($_POST['bound']['width']) )
    {
        $table_width = $_POST['bound']['width'];
        if( substr($ctrl_cfg['width'],-1,1) != '%' ) { $table_width .= 'px'; }
    }
    $style = array();
    if( $table_width != null ) { $style[] = 'width:'.$table_width; }
    if( isset($_POST['bound']['height']) ) { $style[] = 'height:'.$_POST['bound']['height'].'px'; }
    $style_str = empty($style) ? '' : ' style="'.implode(';',$style).'"';
    echo '<div class="viewport -v-scroll"'.$style_str.'">';
}

$dba->drawTable($table,$header,array());
if( isset($_POST['bound']) ) { echo '</div>'; }
<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

require_once('publications.config.php');

$dba = new Accessor();
$pub = $dba->get('publications',$_POST['id'],array(
    'id' => 'number:reference',
    'type' => 'string',
    'keyname' => 'string:unique',
    'title' => 'string',
    'abstract' => 'string:lines',
    'related' => 'list:comma',
    'material' => 'json',
    'bibtex' => 'string',
    'taglist' => 'string'
));

$act = '修改';
if( empty($pub) )
{
    $act =  '新增';
    $pub = array();
}
else
{
    require_once('../plugins/bibtex.php');
    $bib = new BibTeX($pub['bibtex']);
}
?>

<form method="post" action="extends/publications.update.php" target="_new">
<?
if( !empty($pub) )
{
    kjPHP\drawHTMLElement('input',array(
        'type' => 'hidden',
        'name' => 'id',
        'value' => $pub['id']
    ));
}
?>
    <table style="float:left;width:65%;">
        <tbody>
            <tr>
                <th>類型</th>
                <td class="form-border">
                    <select name="type">
                        <option value="<?=$pub['type']?>"><?=$pub['type']?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>篇名</th>
                <td class="form-border">
                    <input type="text" name="title" value="<?=$pub['title']?>" />
                </td>
            </tr>
            <tr>
                <th>摘要</th>
                <td class="form-border">
                    <textarea class="-h200" name="abstract"><?=$pub['abstract']?></textarea>
                </td>
            </tr>
            <tr>
                <th>BibTeX</th>
                <td class="form-border">
                    <textarea class="-h200" name="bibtex"><?=$bib->toString(array(),10,PHP_EOL);?></textarea>
                </td>
            </tr>
        </tbody>
    </table>
    <table style="float:left;width:35%;">
        <tbody>
            <tr>
                <th>相關篇目</th>
                <td class="form-border">
                    <textarea name="related"><?=implode(PHP_EOL,$pub['related'])?></textarea>
                </td>
            </tr>
            <tr>
                <th>標籤</th>
                <td class="form-border">
                    <textarea name="taglist"><?=$pub['taglist']?></textarea>
                </td>
            </tr>
            <tr>
                <th>檔案</th>
                <td class="no-border">
                    <table class="define-list">
                        <thead>
                            <tr>
                                <th width="80px">類型</th>
                                <td></td>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr><td colspan="2">
                                <select style="width:100%;" onchange="MMNet.insertRow(this,'material');">
                                    <option value="">增加相關檔案</option>
<?
foreach ( $publication_file_type_list as $file_type => $type_data )
{
    $attr = array( 'value' => $file_type );
    if( !empty($type_data['hint']) ) { $attr['title'] = $type_data['hint']; }
    kjPHP\drawHTMLElement('option',$attr,$type_data['name']);
}
?>
                                </select>
                            </td></tr>
                        </tfoot>
                        <tbody>
<?
foreach ( $pub['material'] as $key => $value )
{
    if( !isset($publication_file_type_list[$key]) ) { continue; }
    $type_data = $publication_file_type_list[$key];
    echo '<tr>';
    echo '<th title="'.$type_data['hint'].'">'.$type_data['name'].'</th>';
    echo '<td><input type="text" name="material['.$key.']" value="'.$value.'" /></td>';
    echo '</tr>';
}
?>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
    <div style="clear:both">
        <button type="button" onclick="MMNet.Controller.submit();"><?=$act?>著作</button>
        <button type="submit"><?=$act?>著作</button>
        <button type="reset">重填</button>
        <button type="button" onclick="MMNet.Controller.close();">取消</button>
    </div>
</form>
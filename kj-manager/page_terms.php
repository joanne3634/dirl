<?php
require_once('mod_general.php');
require_once('mod_database.php');

$dba = new Accessor();

$term_queue = $dba->query(
    'term_list',
    array( 'type' => array( 'value' => 0 ) ),
    array(
        'id' => 'number:reference',
        'type' => 'number:reference',
        'title' => 'json:language'
    )
);

$metaterms = array();
while( $term = array_shift($term_queue) )
{
    $metaid = 0;
    foreach( $metaterms as $t_id => $t_set )
    {
        if( in_array($term['type'],$t_set['contains']) )
        {
            $metaid = $t_id;
            break;
        }
    }

    if( $metaid == 0 )
    {
        $metaterms[$term['id']] = array(
            'id' => $term['id'],
            'title' => $term['title'][$dba->default_language],
            'contains' => array( $term['id'] ),
        );
    } else { $metaterms[$metaid]['contains'][] = $term['id']; }

    $terms = $dba->query(
        'term_list',
        array( 'type' => array( 'value' => $term['id'] ) ),
        array(
            'id' => 'number:reference',
            'type' => 'number:reference',
            'title' => 'json:language'
        )
    );
    foreach( $terms as $t ) { array_unshift($term_queue,$t); }
}
?>
<aside style="float:right;width:20%">
    <a class="btn-like" href="javascript:MMNet.Controller.load('term.form');">新增標籤</a>
</aside>
<?
foreach( $metaterms as $metaterm )
{
    $types = implode(',',$metaterm['contains']);
    $dba->drawTable(array(
        'name' => 'term_list',
        'title' => '標籤：' . $metaterm['title'],
        'class' => 'foldable -fold -unique',
        'style' => 'float:left;width:80%;',
        'where' => "NOT v_state = 'removed' AND type IN ({$types})",
        'sortable' => false
    ),array(
        'keyname' => array(
            'title' => '識別碼',
            'class' => '-tac',
            'width' => '150px'
        ),
        'title' => array(
            'type' => 'lang',
            'title' => '標題'
        ),
    ),array(
        'insert' => array(
            'type' => 'pattern',
            'pattern' => 'term.form',
            'data' => "{'parent':{$metaterm['id']}}"
        ),
        'display1' => array(
            'triggers' => array(
                'v_state' => array( 'working' )
            ),
            'class' => array( '-working' ),
            'title' => '停用標籤',
            'click' => "MMNet.alter('term_list','%id%','v_state','disabled',{'target':this,'turn':'working'});"
        ),
        'display2' => array(
            'triggers' => array(
                'v_state' => array( 'disabled' )
            ),
            'class' => array( '-disabled' ),
            'title' => '啟用標籤',
            'click' => "MMNet.alter('term_list','%id%','v_state','working',{'target':this,'turn':'disabled'});"
        ),
        'edit' => array(
            'class' => array( 'edit-icon' ),
            'title' => '修改標籤',
            'click' => "MMNet.Controller.load('term.form',{'term_id':%id%});"
        ),
        /*'import' => array(
            'triggers' => array(
                'type' => array( 0 )
            ),
            'title' => '匯入子標籤',
            'click' => "MMNet.Controller.load('csv.parse',{'table':'term_list','act':'act/fill-term.php','hidden[type]':%id%});"
        ),
        'remove' => array(
            'class' => array( 'remove-icon' ),
            'title' => '刪除標籤',
            'click' => "MMNet.alter('language','%id%','v_state','removed',{'redirect':'general'});"
        )*/
    ));
}
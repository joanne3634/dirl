<?php
require_once('../mod_general.php');
require_once('../mod_database.php');

if( !checkAdministrator() ) { die('{"error":401,"message":"No Authority."}'); }
if( !isset($_POST['table']) )
{
    header('Location: index.php');
    die();
}

$controller = isset($_POST['act']) ? $_POST['act'] : 'act/fill.php';

$dba = new Accessor();
$fields = array( '_none_' => '(不要填入)' );
$columns = $dba->_query('SHOW FULL COLUMNS FROM '.$_POST['table']);
while( $column = $columns->fetch(PDO::FETCH_ASSOC) )
{
    $title = $column['Field'];
    if( !empty($column['Comment']) ) { $title = $column['Comment'] . '(' . $title . ')'; }
    $fields[$column['Field']] = $title;
}
if( empty($fields) )
{
    header('Location: index.php');
    die();
}
?>
<datalist id="fields">
<?
foreach( $fields as $key => $title )
{
    echo '<option value="' . $key . '">' . $title . '</option>';
}
?>
</datalist>

<form method="post" action="<?=$controller?>" enctype="multipart/form-data">
    <input type="hidden" name="table" value="<?=$_POST['table']?>" />
<?
if( isset($_POST['hidden']) )
{
    foreach( $_POST['hidden'] as $name => $value )
    {
        echo '<input type="hidden" name="' . $name . '" value="' . $value . '" />';
    }
}
?>
    <table>
        <tbody>
            <tr>
                <th>CSV 檔案</th>
                <td class="form-border"><input type="file" id="csvfile" name="csvfile" accept=".csv" /></td>
            </tr>
        </tbody>
    </table>
    <table id="mapping">
        <thead>
            <tr>
                <th style="width:120px;">CSV 欄位</th>
                <th style="width:250px;">表格欄位</th>
                <th style="width:150px;">填入方式</th>
                <th>參數</th>
            </tr>
        </thead>
        <tfoot><tr><td class="btn-like no-border" colspan="4">填入</td></tr></tfoot>
        <tbody>
        </tbody>
    </table>
    <div>
        <button type="button" onclick="MMNet.Controller.close();">取消</button>
    </div>
</form>



<script>
$("#csvfile").change(function(e) {
    var files = e.target.files;
    var reader = new FileReader();
    reader.onload = function (e) {
        var rows = e.target.result.split("\n");
        var header = rows[0].split(",");
        var tbody = $("#mapping > tbody"), options = $("#fields");
        tbody.empty();
        for(var i=0;i<header.length;i++) {
            var head = header[i], prefix = "item[" + i + "]";
            var select = $("<select name=\""+prefix+"[field]\"></select>");
            options.children("option").clone().appendTo(select);
            //select.html();
            var tr = $(
                "<tr>"
                +   "<td class=\"-tac\">"+head+"</td>"
                +   "<td class=\"form-border\"></td>"
                +   "<td class=\"form-border\">"
                +       "<select name=\""+prefix+"[type]\">"
                +           "<option value=\"append\">附加</option>"
                +           "<option value=\"replace\">取代</option>"
                +           "<option value=\"json\">物件</option>"
                +           "<option value=\"list\">陣列</option>"
                +           "<option value=\"member\">成員</option>"
                +           "<option value=\"term\">標籤</option>"
                +       "</select>"
                +   "</td>"
                +   "<td class=\"form-border\"><input type=\"text\" name=\""+prefix+"[data]\" /></td>"
                + "</tr>");
            var tds = tr.children("td.form-border");
            $(tds[0]).html(select);
            tbody.append(tr);
        }
        $("table#mapping > tfoot td").attr("onclick","MMNet.Controller.submit(true);");
    };
    reader.readAsText(files[0]);
});
</script>
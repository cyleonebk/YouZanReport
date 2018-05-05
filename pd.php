<!DOCTYPE html />
<html>
<head>
 <?php
const debug = 1;
const SumBuyer = 0;
require_once 'function/main.php';

?>
	<title>团多多<?php echo getGoodsfileTime('goods'); ?>采购表</title>
    <link href='css/jquery-ui.css' rel='stylesheet' />
    <link href="css/bootstrap-combined.min.css?v4" rel="stylesheet" />
    <script src="css/jquery.min.js"></script>
    <script src="css/jquery-ui.min.js"></script>

    <script src='css/EditTable.js' type='text/javascript'></script>
<style type="text/css">

       th.disabled, td.disabled {
            opacity: .6;
            background: #ccc;
            font-size: 3em;
        }
.example {
            margin: 40px 0;
        }
@media print {
 .Noprn{ display:none;}
}
</style>

</head>
<body>
<div class='container'>
<?php
//读取商品表文件到数组
$goods_list = readcsv('goods');

isset($_GET['m']) ? $modeIs = true : $modeIs = false;

$arrGoods = array();

foreach ($goods_list as $key => $value) {
	$akey = array_search($value[4] . "||" . $value[7], array_column($arrGoods, 'name'));
	if ($akey !== false) {
		$arrGoods[$akey]['num'] = $value[13] + $arrGoods[$akey]['num'];
		//$arrGoods[$akey]['totalPay'] = $value[10] + $arrGoods[$akey]['num'];
	} else {
		$arrGoods[] = array('name' => $value[4] . "||" . $value[7],
			'num' => $value[13],
//'spec'    =>  $value[7],

		);
	}
}
foreach ($arrGoods as $key => $value) {
	$name[$key] = $value['name'];
}

array_multisort($name, SORT_DESC, SORT_STRING, $arrGoods); //按照商品的名称来排序
$doneGoods = array();
foreach ($arrGoods as $oK => $oV) {
	$oname = preg_replace("/\[.*?\]/", '', explode("||", $oV['name'])[0]);
	$doneGoods[] = array(
		'名称' => $oname,
		'规格' => explode("||", $oV['name'])[1],
		'数量' => $oV['num'] . ' 份',
		'备注' => '',

	);
}

?>

    <div class='example'>
        <h1 style="text-align: center;">团多多<?php echo getGoodsfileTime('goods'); ?>采购表</h1>


        <table id='bootstrap' class='table table-bordered table-striped'></table>

        <div style='margin-top: 20px;' class="Noprn">
            <button class='reset btn' type="button">重置</button>
            <button class='log btn' type="button">Console.log</button>
            <button class='addrow btn' type="button">增加一行</button>
            <button class='removerow btn' type="button">删除最后一行</button>
        </div>

        <script>
        (function() {
            $("#bootstrap")
                .editTable()
                .editTable('rows',<?php echo json_encode($doneGoods) ?>);
        })();
        </script>
    </div>
</div>

<script type='text/javascript'>
$(".example").each(function() {
    var table = $('table',$(this));

    // Store the original data so we can restore it later
    var orig = table.editTable('rows');
    $(".reset",$(this)).on('click',function() {
       table.editTable('rows',orig);
    });

    // Prints out the table's current value as an array of JSON objects
    $(".log",$(this)).on('click',function() {
       console.log(JSON.stringify(table.editTable('rows'),null,2));
    });

    // Adds a row to the bottom of the table
    $(".addrow",$(this)).on('click',function() {
       table.editTable('add');

       // To add more than 1 row at a time:
       // table.editTable('add', 5);
    });

    // Removes the last row of the table
    $(".removerow",$(this)).on('click',function() {
       table.editTable('remove');

       // To delete a specific row
       // Row index starts at 0, so this would delete the 3rd row
       // table.editTable('remove', 2)
    });
});

</script>
</body>
</html>

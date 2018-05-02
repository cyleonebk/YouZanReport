<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>

<head>
<?php
header('Content-Type:text/html; charset=utf-8');
error_reporting(E_ALL ^ E_NOTICE);
const debug = 1;
const SumBuyer = 0;

?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>团多多系统报表</title>

<?php
include 'css/style.css';
require_once 'function/main.php';
?>
</head>

<?php
//读取商品表文件到数组
$goods_list = readcsv('goods');

isset($_GET['m']) ? $modeIs = true : $modeIs = false;

if ($modeIs === false) {
	$arrGoods = array();
	foreach ($goods_list as $key => $value) {
		$akey = array_search($value[4] . "||" . $value[7], array_column($arrGoods, 'name'));
		if ($akey !== false) {
			$arrGoods[$akey]['num'] = $value[13] + $arrGoods[$akey]['num'];
			$arrGoods[$akey]['totalPay'] = $value[10] + $arrGoods[$akey]['num'];
		} else {
			$arrGoods[] = array('name' => $value[4] . "||" . $value[7],
				'num' => $value[13],
//'spec'    =>  $value[7],
				'totalPay' => $value[10],
			);
		}
	}
	foreach ($arrGoods as $key => $value) {
		$name[$key] = $value['name'];
	}
	array_multisort($name, SORT_DESC, SORT_STRING, $arrGoods); //按照商品的名称来排序
	?>
<table>
<tbody>
<tr>
<td colspan="4">
<h1 align="center">团多多<?php echo getGoodsfileTime('goods'); ?>采购表</h1>
</td>
</tr>
<tr>
<td align="center" width="35%"><strong>名称</strong>
</td>
<td align="center" width="15%"><strong>数量</strong>
</td>
<td align="center" width="25%"><strong>规格</strong>
</td>
<td align="center" width="25%"><strong>备注</strong>
</td>
</tr>
<?php
//商品总订单循环输出
	foreach ($arrGoods as $key => $value) {
		$outName = preg_replace("/\[.*?\]/", '', explode("||", $value['name'])[0]); //去掉标题里面的规格
		//$outName = explode("||", $value['name'])[0];
		echo '<tr><td>';
		echo $outName;
		echo '</td><td align=center>' . $value['num'] . '份</td><td>';
		echo explode("||", $value['name'])[1];
		echo '</td>
<td>&nbsp;</td>
</tr>';
	}
	?>

</tbody>
</table>
<?php
}

/*******************配送表制作**********************/
if ($modeIs === true) {

/*读取团长信息表*/
	$seller_list = readcsv('sellers');

	$atid = explode(PHP_EOL, file_get_contents('tempid.txt'));

	foreach ($atid as $atkey => $atvalue) {
		$tempKey = explode(',', $atvalue);
		$aryTempID[$tempKey[0]] = $tempKey[1];
	}

/*读取销售订单表*/

	$order_list = readcsv('orders');
	$order_list = arytrimKey($order_list, '22'); //妈的去掉空格, 电话号码从csv里面读取出来有莫名其妙的

/*有赞的订单信息调用*/
	require_once __DIR__ . '/lib/YZTokenClient.php';
	$token = '40a62c38c8df320fbcdd53a0abbf2cd8'; //请填入商家授权后获取的access_token
	$client = new YZTokenClient($token);
	$method = 'youzan.salesman.trades.account.get';
	$api_version = '3.0.0';

	$PeiSong = array(); //声明一个数组做配送表
	if (debug != 1) {

		foreach ($order_list as $key => $value) {
			//开始查询订单所属的团长电话

			$my_params = ['order_no' => $value[0]];
			$my_files = [];
			$Result_Response = $client->post($method, $api_version, $my_params, $my_files);
			if (array_key_exists('error_response', $Result_Response)) {
				echo "有错:", PHP_EOL;
				print_r($Result_Response['error_response']);
			}
			$head_tel = $Result_Response['response']['mobile'];

			$head_point = array_search($head_tel, array_column($seller_list, 4)); //去搜索团长的信息
			//这里是处理哪些电话确认团长的订单
			$SeckOrderID = $value[0];

			if (isset($aryTempID[$SeckOrderID])) {
				$head_point = $aryTempID[$SeckOrderID];
			}

			if ($head_point === false) {
				echo ('<br>找不到团长:订单号: ' . $value[0]), "<br/>";
				echo "收货人:", $value[21], "<br />";
				echo "电话:", $value[22], "<br />";
				echo "订购产品:", $value[17], "<br />";

			}

			$PeiSong[] = array(
				'order_id' => $value[0], //订单id
				'goods' => $value[17], //订单明细
				'custom_name' => $value[21], //客户名字
				'custom_tel' => $value[22], //客户电话
				'custom_memo' => $value[27], //客户备注
				'head_village' => $seller_list[$head_point][0], //小区名字
				'head_name' => $seller_list[$head_point][1], //团长姓名
				'head_division' => $seller_list[$head_point][2], //团长所在行政区
				'head_address' => $seller_list[$head_point][3], //团长地址
				'head_tel' => $seller_list[$head_point][4], //团长电话
				'driver_name' => $seller_list[$head_point][5], //司机名字
			);

		}

		//在按照小区来排序
		$tmp = array_column($PeiSong, 'head_village');
		array_multisort($tmp, SORT_DESC, SORT_STRING, $PeiSong);
	}
	if (debug == 1) {

		//$jsonPeiSong = json_encode($PeiSong);
		//file_put_contents('saveorder.txt', $jsonPeiSong);
		$jsonFile = file_get_contents('saveorder.txt');

		$PeiSong = json_decode($jsonFile, true);

	}

	?>
<?php
end($PeiSong);
	$key_last = key($PeiSong);

	$daguode = array();
	foreach ($PeiSong as $key => $value) {
		if ($thisVillage != '' && $thisVillage != $value['head_village']) {
			// 输出表格尾部
			printSiJi($Siji);
			echo "</div>"; //这个是split用的
		}
		if ($thisVillage != $value['head_village']) {
//判断一个小区的数据读取完成没有,假如小区的名字变了,说明读取完了

//开始计算订单汇总

			$arrTel = array_keys(array_column($PeiSong, 'head_tel'), $value['head_tel']); //通过团长电话找出所有订单
			$thisVillageGoods = '';
			foreach ($arrTel as $zzkey => $zzvalue) {
				$thisVillageGoods = $thisVillageGoods . $PeiSong[$zzvalue]['goods'] . ';'; //把所有的商品信息弄到一起
			}
			$ListSumOrder = explode(";", $thisVillageGoods);
			array_pop($ListSumOrder); //数组多了一行
			$sumListOrder = array();
			foreach ($ListSumOrder as $LSkey => $LSvalue) {
				preg_match("/\((\d+)件\)/", $LSvalue, $vNum); //匹配出数字
				$sum_goods = preg_replace("/\((\d+)件\)/", '', $LSvalue); //去掉数字
				$sumListOrder[$sum_goods] = $sumListOrder[$sum_goods] + $vNum[1]; //数字进行相加
			}

			echoln("<div class='split'>");
			echoln("<h2 style='margin-top:50px;'>$value[head_village]</h2>");
			echoln("<div class='hr'></div>");
			echoln("<h3><strong>小区地址: </strong><span>$value[head_address]</span><strong class='r'>行政区: </strong><span>$value[head_division]</span></h3>");
			echoln("<h3><strong>团长名字： </strong><span>$value[head_name]</span><strong class='r'>团长电话： </strong><span>$value[head_tel]</span></h3>");

			/******************打印订单汇总**********************/
			echoln("<div class='sumOrder'><h4> 订单汇总:</h4> ");
			echoln(" <ol>  ");
			foreach ($sumListOrder as $sumkey => $sumvalue) {
				$sumkey = trimGuiGe($sumkey);
				echoln("	<li>$sumkey<span class='red'>$sumvalue 份</span></li>");
			}

			echo "</ol>
					<div style='clear: both'></div>
			</div>";

			$thisVillage = $value['head_village'];
			/******************打印订单明细**********************/
			echoln("<h4>订单明细</h4>");
			echoln("<div class='masonry'>");
			printOrderList($value); //打印明细
			$Siji = $value['driver_name'];
		} else {
			printOrderList($value);
			$Siji = $value['driver_name'];
		}

		$key == $key_last && printSiJi($Siji);

	}

}
?>

<body>
</body>

</html>

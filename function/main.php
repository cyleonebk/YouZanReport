<?php
function ep($a) {

	echo "<pre style=color:red>";
	print_r($a);
	echo "</pre>";

}
function my_dir($dir) {
	$files = array();
	if (@$handle = opendir($dir)) {
		while (($file = readdir($handle)) !== false) {
			if ($file != ".." && $file != ".") {
				if (is_dir($dir . "/" . $file)) {
					$files[$file] = my_dir($dir . "/" . $file);
				} else {
					$files[] = $file;
				}

			}
		}
		closedir($handle);
		return $files;
	}
}
/*
 *
 *变换一下编码
 */

function aryGbk2UTF8($aArray) {
	foreach ($aArray as $key => $value) {
		foreach ($value as $subkey => $subvalue) {
			//	$aArray[$key][$subkey] = iconv('GBK//IGNORE', 'UTF-8', $subvalue);
			$aArray[$key][$subkey] = mb_convert_encoding($subvalue, 'UTF-8', 'GBK');
		}
	}
	return $aArray;
}

function arytrimKey($aArray, $akey) {
	foreach ($aArray as $key => $value) {
		$aArray[$key][$akey] = trim($value[$akey]);

	}
	return $aArray;
}

function readCSV($pathandfile) {

	$filename = "./csv/$pathandfile/" . my_dir("./csv/$pathandfile")[0];
	$csv = array_map('str_getcsv', file($filename)); //一行搞定读取csv
	$arrayfile = aryGBK2UTF8($csv);
	array_shift($arrayfile); //去掉表头

	return $arrayfile;
}

function echoGoods($aString) {
	$expGoods = explode(';', $aString);
	foreach ($expGoods as $skey => $svalue) {
		preg_match("/\((\d+)件\)/", $svalue, $custom_goods_num);
		$custom_goods = preg_replace("/\((\d+)件\)/", '', $svalue);
		echo " <li>$custom_goods<span>$custom_goods_num[1]份</span></li>";
	}
}

function getGoodsfileTime($filename) {
	$filename = "./csv/$filename/" . my_dir("./csv/$filename")[0];
	return date("n月d日", filemtime($filename));
}

function trimGuiGe($value) {
	return preg_replace("/\[.*?\]/", '', $value);

}

function echoln($s = '') {
	echo $s, PHP_EOL;
}

function printOrderList($value) {
	echoln("<div class='list'>");

	echoln("<div class='thead'><strong>收货人：</strong>$value[custom_name]<strong class='r'>电话： </strong><span>$value[custom_tel]</span>");
	echoln("<p> <strong>订单备注: </strong>$value[custom_memo]<span class='oid'>订单编号:$value[order_id]</span></p>");
	echoln("</div>");

	echoln("<ol>");

	$expGoods = explode(';', $value['goods']);
	foreach ($expGoods as $skey => $svalue) {
		preg_match("/\((\d+)件\)/", $svalue, $custom_goods_num);
		$custom_goods = preg_replace("/\((\d+)件\)/", '', $svalue);
		$custom_goods = trimGuiGe($custom_goods);
		echoln(" <li>$custom_goods<span>$custom_goods_num[1]份</span></li>");

	}
	echoln("</ol>");

	echoln("</div>");
}

function printSiJi($str) {
	echoln("</div>"); //这个是 masonry用的

	echoln("<div class='foot'>
        <div>订单签收（签名）：<span>____________________</span>
         <div style='float: right'><strong>送货人：</strong>$str</div>
        </div>
      </div>");
	echoln("<div class='hr'></div>");
}
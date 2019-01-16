<?php

require_once dirname(__FILE__) . '/headers.php';
require_once dirname(__FILE__) . '/workClass.php';
require_once dirname(__FILE__) . '/check_auth.php';
require_once dirname(__FILE__) . '/config/config.php';

try {
	$db = new PDO($config['db']['dns'], $config['db']['username'], $config['db']['password']);
} catch (PDOException $e) {
	die(var_dump($e->getMessage()));
}

$itemType = (isset($_POST['item_type'])) ? trim($_POST['item_type']) : null;
$itemID = (isset($_POST['item_id'])) ? (int)$_POST['item_id'] : null;
$itemCount = (isset($_POST['item_count'])) ? (int)$_POST['item_count'] : null;
$invID = (isset($_POST['inv_id'])) ? (int)$_POST['inv_id'] : null;

if ( $itemCount < 1 ) $itemCount = 1;
if(empty($itemType) or empty($itemID) or empty($itemCount)) exit();

$traderDiscount = 0.5;
$deepRate = 100;

$ItemWork = new ItemsWork($db, $_SESSION['uid']);
$StatsWork = new StatsWork($db, $_SESSION['uid']);
$MoneyWork = new MoneyWork($db, $_SESSION['uid']);
$MoneyWork->getMoney("steel");
$StatsWork->getStat("weight");
if ($ItemWork->getItem($invID)){
	if($ItemWork->accItem['count']<$itemCount){
		echo "you dnt have that much"; exit();
	}
	$ItemWork->getItemInfo($ItemWork->accItem['item_type'],$ItemWork->accItem['item_id']);
	if ($ItemWork->ItemInfo['curr']=="steel"){
		$price=$ItemWork->ItemInfo['price']*$traderDiscount;
	}
	elseif($ItemWork->ItemInfo['curr']=="deep"){
		$price=$ItemWork->ItemInfo['price']*$deepRate*$traderDiscount;
	}
	$percent_strength = $ItemWork->accItem['strength']/$ItemWork->accItem['strength_max'];
	$addSteel = $price*$percent_strength*$itemCount;
	$MoneyWork->addToMoney($addSteel);
	$MoneyWork->saveMoney();
	$StatsWork->addToStat($ItemWork->ItemInfo['weight']*$itemCount);
	$StatsWork->saveStat();
	$ItemWork->addToItemCount($itemCount*-1);
	$ItemWork->saveItem();
}
else{echo "you dnt have this item"; exit();}
#!/usr/bin/php
<?
/*------------------------------------------------------
external parameters: (ex.: "parameter=value")
	ename - name of type event,
	lid - site
	duplicate - is send duplicate
	message - message ID
-------------------------------------------------------*/
$_SERVER["DOCUMENT_ROOT"] = realpath(__DIR__);  //set your web-root directory or put this file there
define("NO_KEEP_STATISTIC", true); 
define("NOT_CHECK_PERMISSIONS", true); 
set_time_limit(0);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use \Bitrix\Main\Loader;
use \Bitrix\Main\Type\DateTime;

//check loading module
if(Loader::IncludeModule('sale')) {
	//set default variables for send mail
	$lid = 's1';
	$ename = 'CRON_SEND_WISHLIST';
	$duplicate = 'N';
	//if have external parameters
	if($argc > 1) {
		$arArgv = array(
			'ename',
			'lid',
			'duplicate',
			'message',
		);
		foreach ($argv as $arg) {
			$a=explode("=",$arg);
			if(count($a)==2 && in_array($a[0],$arArgv) && !empty($a[1])) {
				${$a[0]} = $a[1];
			}
		}
	}
	//check event type
	$type_search = CEventType::GetList(
		array(
			"TYPE_ID" => $ename,
			"LID" => $lid,
		),
		array()
	);
	if(($type = $type_search->fetch()) === FALSE) {
		$et = new CEventType;
		$type = $et->Add(array(
			"LID"           => $lid,
			"EVENT_NAME"    => $ename,
			"NAME"          => "Рассылка списка отложенных товаров",
			"DESCRIPTION"   => "
				#EMAIL_FROM# - адрес отправителя,
				#EMAIL_TO# - адрес получателя
				#SUBJECT# - заголовок письма
				#FIO# - ФИО получателя
				#ITEMS# - список товаров
			"
		));
	}
	//check event message
	if(!isset($message)) {
		if($type) {
			$em = new CEventMessage;
			$arMessage = array(
				'ACTIVE'     =>  'Y',
				'EVENT_NAME' =>  $ename,
				'LID' =>  $lid,
				'EMAIL_FROM' =>  '#EMAIL_FROM#',
				'EMAIL_TO' =>  '#EMAIL_TO#',
				'SUBJECT' =>  '#SUBJECT#',
				'BODY_TYPE' =>  'html',
				'MESSAGE' =>  'Добрый день, #FIO#.<br> В Вашей корзине хранятся товары:<br> #ITEMS#.',
			);
			$message = $em->Add($arMessage);
		}
	}	
	//check ready for send
	if($type && isset($message) && $message) {
		//find active users
		$arUsers = array();
		$arUsersFilter = array();
		$arUsersID = CUser::GetList(
			($by="id"),
			($order="asc"),
			array(
				'ACTIVE' => 'Y',
				// '!EMAIL' => false, //required in bitrix
			),
			array(
				'FIELDS' => array("ID","EMAIL","NAME","LAST_NAME","SECOND_NAME"),
			)
		);
		//and create arrays
		while($r = $arUsersID->fetch()) {
			$arUsersFilter[] = $r['ID'];
			$arUsers[$r['ID']] = array(
				'EMAIL' => $r['EMAIL'],
				'FIO' => "{$r['LAST_NAME']} {$r['NAME']} {$r['SECOND_NAME']}",
			);
		}
		//find all orders last 30 days
		$orders = CSaleOrder::GetList(
			array(),
			array(
				'USER_ID' => $arUsersFilter,
				'>DATE_INSERT' => DateTime::createFromTimestamp((time()-(3600*24*30))) //last 30 days
			),
			false,
			false,
			array("ID")
		);
		//create array for filter
		$arOrdersFilter = array();
		while($r = $orders->fetch()) {
			$arOrdersFilter[] = $r['ID'];
		}
		//find all items by filter
		$items = CSaleBasket::GetList(
			array(),
			array(
				'USER_ID' => $arUsersFilter,
				'=CAN_BUY' => 'Y',
				'+@ORDER_ID' => $arOrdersFilter,
				// 'DELAY' => 'Y',  //may be needed
				'>DATE_INSERT' => DateTime::createFromTimestamp((time()-(3600*24*30))) //last 30 days
			),
			false,
			false,
			array('ID','NAME','PRODUCT_ID','QUANTITY','ORDER_ID','USER_ID','CALLBACK_FUNC','PRODUCT_PROVIDER_CLASS')
		);
		//set arrays for parsing
		$arItems = array();
		$arHelp = array();
		//and create main array with items
		while($r = $items->fetch()) {
			if ('' != $r['PRODUCT_PROVIDER_CLASS'] || '' != $r["CALLBACK_FUNC"]) {
				if(!empty($r['ORDER_ID'])) {
					$arHelp[$r['USER_ID']][] = $r['PRODUCT_ID'];
					if(array_key_exists($r['USER_ID'],$arItems) 
					&& array_key_exists($r['PRODUCT_ID'],$arItems[$r['USER_ID']]['ITEMS'])
					)
						unset($arItems[$r['USER_ID']]['ITEMS'][$r['PRODUCT_ID']]);
				}elseif(!array_key_exists($r['USER_ID'],$arHelp) 
				|| !in_array($r['PRODUCT_ID'],$arHelp[$r['USER_ID']])
				) {
					if(!isset($arItems[$r['USER_ID']]['EMAIL']))
						$arItems[$r['USER_ID']]['EMAIL'] = trim($arUsers[$r['USER_ID']]['EMAIL']);
					if(!isset($arItems[$r['USER_ID']]['FIO']))
						$arItems[$r['USER_ID']]['FIO'] = trim($arUsers[$r['USER_ID']]['FIO']);
					$arItems[$r['USER_ID']]['ITEMS'][$r['PRODUCT_ID']] = " - {$r['NAME']} - {$r['QUANTITY']} шт.";
				}
			}	
		}
		//for all users send mail if had items
		foreach($arItems as $key=>$u) {
			if(is_array($u["ITEMS"]) && count($u["ITEMS"]) > 0) {
				$items = implode("<br>",$u["ITEMS"]);
				$e = CEvent::Send(
					$ename,
					$lid,
					array(
						"EMAIL_FROM" => "from@default.ru",
						"EMAIL_TO" => $u["EMAIL"],
						"SUBJECT" => "Уведомление от интернет-магазина",
						"FIO" => $u["FIO"],
						"ITEMS" => $items,
					),
					$duplicate,
					$message
				);
				//write to log
				if($e) CEventLog::Add(array(
					"SEVERITY" => "UNKNOWN",
					"AUDIT_TYPE_ID" => "CRON_SEND_ITEMS",
					"MODULE_ID" => "sale",
					"ITEM_ID" => $key,
					"SITE_ID" => $lid,
					"DESCRIPTION" => "Рассылка писем, находящихся товаров в корзине у пользователя {$u['FIO']}",
				));
			}
		}
	}
	
	
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>
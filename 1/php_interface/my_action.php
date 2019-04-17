<?
namespace My\Action;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Main;
use \Bitrix\Sale\Fuser;
use \Bitrix\Main\Context;
use \Bitrix\Sale\Basket;
use \Bitrix\Main\Application;
use \Bitrix\Sale\Discount;
use \Bitrix\Main\Loader;


class MyAction
{
	public static function arSorter($a,$b) {
		$by = 'PRICE';
		if ($a[$by] == $b[$by]) {
			return 0;
		}
		return ($a[$by] < $b[$by]) ? -1 : 1;
	}
	
	public static function getCurrentURL() {
		$request = Application::getInstance()->getContext()->getRequest();
		return $request->getRequestUri();
	}
	
	public static function getBasket() {
		$fuser = Fuser::getId();
		$siteId = Context::getCurrent()->getSite();
		return Basket::loadItemsForFUser($fuser, $siteId);
	}
	
	public static function saveBasket($basket) {
		$bsave = $basket->save();
		if (!$bsave->isSuccess())	{
			return false;
		}
		return true;
	}
	
	//not working getExistsItem
	public static function getBasketItem($basket,$module,$product_id) {
		foreach($basket as $b) {
			if($b->getField('MODULE') == $module && $b->getField('PRODUCT_ID') == $product_id)
				return $b;
		}
		return false;
	}
	
	public static function resetBasket($is_save=true,$basket=false) {
		if(!Loader::includeModule('sale')) return false;
		$arHelp = array();
		$arDel = array();
		$basket = ($basket&&is_object($basket))?$basket:self::getBasket();
		foreach ($basket as $item) {
			if ('' != $item->getField('PRODUCT_PROVIDER_CLASS') || '' != $item->getField("CALLBACK_FUNC")) {
				$id = $item->getField('PRODUCT_ID');
				if(array_key_exists($id,$arHelp)) {
					$arHelp[$id]['QUANTITY'] += floatval($item->getField('QUANTITY'));
					$module = $arHelp[$id]['MODULE'];
					$p_id = $arHelp[$id]['PRODUCT_ID'];
					if(!empty($item->getID())) {
						$item->delete();
						$module = $arHelp[$id]['MODULE'];
						$p_id = $arHelp[$id]['PRODUCT_ID'];
						$bitem = self::getBasketItem($basket,$module,$p_id);
						if(!empty($bitem)) {
							$bitem->setField('QUANTITY', $arHelp[$id]['QUANTITY']);
						}	
					}else{
						$item_id = $arHelp[$id]['ID'];
						$bitem = $basket->getItemById($item_id);
						if(!empty($bitem))
							$bitem->delete();
						$item->setField('CUSTOM_PRICE','N');
						$item->setField('IGNORE_CALLBACK_FUNC','N');
						$arHelp[$id]['ID'] = null;
					}
				}else{
					$item->setField('CUSTOM_PRICE', 'N');
					$item->setField('IGNORE_CALLBACK_FUNC', 'N');
					if(!empty($item->getID())) {
						$disc = Discount::loadByBasket($basket);
						$calc = $disc->calculate();
						$calc_data = $calc->getData();
						$price = $calc_data['BASKET_ITEMS'][$item->getID()]['PRICE'];
						$item->setField('PRICE', $calc_data['BASKET_ITEMS'][$item->getID()]['PRICE']);
					}	
					$arHelp[$id] = array(
						'ID' => (!empty($item->getField('ID'))?$item->getID():null),
						'MODULE' => $item->getField('MODULE'),
						'PRICE' => $item->getField('PRICE'),
						'BASE_PRICE' => $item->getField('BASE_PRICE'),
						'PRODUCT_ID' => $item->getField('PRODUCT_ID'),
						'CURRENCY' => $item->getField('CURRENCY'),
						'WEIGHT' => $item->getField('WEIGHT'),
						'QUANTITY' => floatval($item->getField('QUANTITY')),
						'LID' => $item->getField('LID'),
						'CAN_BUY' => $item->getField('CAN_BUY'),
						'NAME' => $item->getField('NAME'),
						'CALLBACK_FUNC' => $item->getField('CALLBACK_FUNC'),
						'DETAIL_PAGE_URL' => $item->getField('DETAIL_PAGE_URL'),
						'PRODUCT_PROVIDER_CLASS' => $item->getField('PRODUCT_PROVIDER_CLASS'),
						'MEASURE_NAME' => $item->getField('MEASURE_NAME'),
						'MEASURE_CODE' => $item->getField('MEASURE_CODE'),
						'CUSTOM_PRICE' => $item->getField('CUSTOM_PRICE'),
						'IGNORE_CALLBACK_FUNC' => $item->getField('IGNORE_CALLBACK_FUNC'),
					);
				}
			}
		}
		if($is_save) self::saveBasket($basket);
		return $arHelp;
	}
	
	public static function updateBasket($N=2,$X=1,$is_save=true,$basket=false) {
		$arHelp = self::resetBasket($is_save,$basket);
		$basket = ($basket&&is_object($basket))?$basket:self::getBasket();
		if($basket->count() != count($arHelp)) return false;
		uasort($arHelp,array('self','arSorter'));
		reset($arHelp);
		$cnt_items = array_sum($basket->getQuantityList());
		$cnt_discount = intval((int)$cnt_items/$N);
		foreach($arHelp as $a) {
			if($cnt_discount <= 0) break;
			$icnt = intval($a['QUANTITY']);
			if($cnt_discount >= $icnt) {
				$bitem = self::getBasketItem($basket,$a['MODULE'],$a['PRODUCT_ID']);
				if(!empty($bitem))
					$bitem->setFields(array(
						'CUSTOM_PRICE' => 'Y',
						'IGNORE_CALLBACK_FUNC' => 'Y',
						'PRICE' => $X,
					));
				$cnt_discount -= $icnt;
			}elseif($cnt_discount < $icnt) {
				if((int)$a['PRICE'] > $X) {	//action set most lowest price
					$bitem = self::getBasketItem($basket,$a['MODULE'],$a['PRODUCT_ID']);
					if(!empty($bitem))
						$bitem->setField('QUANTITY', ($icnt - $cnt_discount));
					$bitem_new = $basket->createItem($a['MODULE'], $a['PRODUCT_ID']);
					$arNew = $a;
					unset($arNew['ID']);
					unset($arNew['MODULE']);
					$arNew['QUANTITY'] = $cnt_discount;
					$arNew['PRICE'] = $X;
					$arNew['CUSTOM_PRICE'] = 'Y';
					$arNew['IGNORE_CALLBACK_FUNC'] = 'Y';
					$bitem_new->setFields($arNew);
				}
				$cnt_discount = 0;
			}
		}
		if($is_save) {
			if(!self::saveBasket($basket))	return false;
		}
		return true;
	}
	
	public static function __callStatic($name,$arg) {
		$ar = explode('__',substr($name,1));
		if(count($ar) >= 3) {
			$N = intval($ar[0]);
			$X = intval($ar[1]);
			if($ar[2] == 'updateBasket') {
				return self::updateBasket($N,$X);
			}
		}else return $ar;
	}
}

?>
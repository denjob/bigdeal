<?
use \Bitrix\Main\EventManager;
use \Bitrix\Main\EventResult;
use \Bitrix\Main\Event;
use \My\Action\MyAction;

require_once(__DIR__.'/my_action.php');

EventManager::getInstance()->addEventHandler(
    'sale',
    'OnSaleBasketBeforeSaved',
    'myAction'
);

function myAction(Event $event) {
	$N = 2; //each item
	$X = 1; //by price
	$start_date = '01.04.2019'; //start action
	$end_date = '31.05.2019'; //end action
	
	if(strtotime($start_date) < time() && strtotime($end_date) > time()) {
		$basket = $event->getParameter("ENTITY");
		$res = MyAction::updateBasket($N,$X,false,$basket);
		if(!$res) {
			return new EventResult(EventResult::ERROR);
		}	
	}
	
	return new EventResult(EventResult::SUCCESS);
}	

?>
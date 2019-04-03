<?
namespace My\Test;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Entity;

Loc::loadMessages(__FILE__);

/**
 * Class OrmTable
 * 
 * Fields:
 * <ul>
 * <li> id int mandatory
 * <li> name string mandatory
 * <li> date int mandatory
 * </ul>
 *
 * @package Bitrix\Orm
 **/

class OrmTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'mytest_orm';
	}
	
	public static function getUfId()
    {
        return 'MYTEST_ORM';
    }
	
	/*public static function getObjectClass()
    {
        return OrmTable::class;
    }*/

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			new Entity\IntegerField('id',array(
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('ORM_ENTITY_ID_FIELD'),
			)),
			new Entity\StringField('name',array(
				'required' => true,
				'title' => Loc::getMessage('ORM_ENTITY_NAME_FIELD'),
			)),
			new Entity\DateTimeField('date',array(
				'required' => true,
				'title' => Loc::getMessage('ORM_ENTITY_DATE_FIELD'),
				'save_data_modification' => function () {
					return array(
						function ($value) {
							return strtotime($value);
						}
					);
				},
				'fetch_data_modification' => function () {
					return array(
						function ($value) {
							return date('d.m.Y H:i:s',$value);
						}
					);
				}
			)),
		);
	}
}

?>
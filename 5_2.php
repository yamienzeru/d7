<?php
namespace Bitrix\Iblock;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class ElementTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'b_iblock_element';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'primary' => true, //первичный ключ
                'autocomplete' => true, //автоинкремент
                'title' => Loc::getMessage('IBLOCK_ELEMENT_ENTITY_ID_FIELD'), //заголовок
            )),
            new Entity\StringField('NAME', array(
                'required' => true, //обязательное заполнение
            )),
            new Entity\BooleanField('ACTIVE', array(
                'values' => array('N','Y'), //список для типа boolean
            )),
            new Entity\IntegerField('SORT', array(
                'default' => 500, //значение по-умолчанию
                'validation' => function() { //валидатор
                    return array(
                        ...
                    );
                }
            )),
            new Entity\EnumField('VARIANT', array(
                'values' => array('VALUE1', 'VALUE2', 'VALUE3') //список переменных
            )),
            new Entity\DateField('ACTIVE_FROM', array(
                'default_value' => function () {
                    $lastFriday = date('Y-m-d', strtotime('last friday'));
                    return new Type\Date($lastFriday, 'Y-m-d');
                }
            )),
            new Entity\IntegerField('IBLOCK_ID'),
            new Entity\ReferenceField(
                'IBLOCK', // 1 - имя поля
                'Bitrix\Iblock', //или 'Iblock' // 2 - название сущности-партнера, с которым формируется отношение
                array('=this.IBLOCK_ID' => 'ref.ID'), // 3 - описывает, по каким полям связаны сущности
                array('join_type' => 'LEFT') // LEFT JOIN b_iblock ON b_iblock_element.IBLOCK_ID = b_iblock.ID // 4 - тип подключения таблицы
            )),
        );
    }
}
?>
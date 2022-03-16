<?
require_once (__DIR__.'/crest.php');
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$postData = file_get_contents('php://input');
$data=json_decode($postData, true);
writeToLog($data,"DATA");

use Bitrix\Main\EventManager,
    Bitrix\Main\Diag\Debug,
    Bitrix\Main\Context;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\Loader;
use Bitrix\Main\IO,
    Bitrix\Main\Application;
use Bitrix\Main\Diag;


if(!empty($data['classData']['Тело']['ГлобИД']))
{


//Входящий пакет компания
if($data['classData']['Тип']=='Контрагент')
{

        if(isset($data['classData']['Тело']['КПП'])&&(!empty($data['classData']['Тело']['КПП'])))       $opf=50; else $opf=53;
        //поиск страны по коду с входящего пакета
        $country=search($data['classData']['Тело']['Страна'],'country');
        //поиск города по коду с входящего пакета
        $city=search($data['classData']['Тело']['Город'],'city');
        //перебор ЮрФизЛицо на сопоставимость с входящим пакетом
        switch ($data['classData']['Тело']['ЮрФизЛицо'])
                {
                        case "ЮрЛицо":
                        $fizur=34; //
                        break;
                        case "ИП":
                        $fizur=33; //
                        break;
                        case "Обособленное подразделение":
                        $fizur=286; //
                        break;
                        case "Самозанятый":
                        $fizur=1581; //
                        break;
                        case "Физ. Лицо без статуса":
                        $fizur=1584; //
                        break;
                }
        //Резидент или нет * обязательно
    if($data['classData']['Тело']['Страна']=='643')     $rez=140; else $rez=141;
        $i=0;$j=0; $phone=array(); $email=array();
        foreach($data['classData']['Тело']['КонтактнаяИнформация']['row'] as $info)
        {
                if($info['Вид']=='Электронная почта') {$email[$i]['VALUE']=$info['АдресЭП'];$email[$i]['VALUE_TYPE'] = "WORK"; $i++;}
                elseif($info['Вид']=='Телефон') {$phone[$j]['VALUE']=$info['НомерТелефона']; $phone[$j]['VALUE_TYPE'] = "WORK"; $j++;}
                elseif($info['Вид']=='Фактический адрес') $factadress=$info['Представление'];
                elseif($info['Вид']=='Юридический адрес') $yradress=$info['Представление'];
                elseif($info['Вид']=='Адрес для корреспонденции') $postadress=$info['Представление'];
        }
        //Поиск по GUID ID компании
        $id=search($data['classData']['Тело']['ГлобИД'],'company');

        if($id>0)
        {
                $result = CRest::call('crm.company.update',
                                [
                                        'id' => $id,
                                        'fields' => [
                                                                  'TITLE' => $data['classData']['Тело']['Наименование'],
                                                                  'UF_CRM_1490625536' => ['VALUE' => $opf],// ОПФ обязательное
                                                                  'UF_CRM_1489576733' => $data['classData']['Тело']['НаименованиеПолное'],
                                                                  'ORIGIN_ID' => $data['classData']['Тело']['ГлобИД'],
                                                                  'ORIGINATOR_ID' => substr($data['classData']['Тело']['ГлобИД'],0,3),
                                                                  'UF_CRM_1489563808' => $data['classData']['Тело']['ИНН'],
                                                                  'UF_CRM_1489576937' => $data['classData']['Тело']['КПП'],
                                                                  'REQUISITES' => $data['classData']['Тело']['ИНН'],
                                                                  'UF_CRM_1643698164119' => $data['classData']['Тело']['КредитныйЛимит'],
                                                                  'UF_CRM_1490626832' => 73, //прочее (источник компании)
                                                                  'UF_CRM_1499086058' => $country, // не правильно, должен быть список, или привязка к элементам справочника
                                                                  'UF_CRM_1517392716' => $city,
                                                                  'UF_CRM_1541271248' => ['VALUE' =>567], //направление деятельности, продукт (Клиент)
                                                                  'UF_CRM_58C94940538E4' => $fizur,
                                                                  'UF_CRM_1493022880' => $rez,
                                                                  'UF_CRM_1493024947' => $yradress,
                                                                  'UF_CRM_1493024967' => $factadress,
                                                                  'UF_CRM_1493024904' => $postadress,
                                                                  'ADDRESS' => $factadress,
                                                                  'COMMENTS' => $data['classData']['Тело']['Комментарий'],
                                                                  'PHONE'=> $phone,
                                                                  'EMAIL'=> $email,
                                                                  'ASSIGNED_BY_ID' => 2785
                                                                ],
                                        'params' => [ "REGISTER_SONET_EVENT" => "Y"]

                                ]);
                                search($id, 'products_delete');
                        foreach($data['classData']['Тело']['ОтветственныеПоКонтрагентам']['row'] as $product)
                {
                        $prdoc=search($product['Продукт'],"products", $id);

                        $el = new CIBlockElement;
                        $res=$el->Add(
                        [
                                'IBLOCK_ID' => $prdoc['IBLOCK_ID'],
                                'ACTIVE' => 'Y',
                                'NAME' => $prdoc['IBLOCK_ELEMENTS_ELEMENT_PRODUCTS_NAZVANIE_PRODUKTA_ITEM_VALUE'], // * обязательное
                                'PROPERTY_674' => $prdoc['IBLOCK_ELEMENTS_ELEMENT_PRODUCTS_NAZVANIE_PRODUKTA_ITEM_ID'], // * обязательное
                                'PROPERTY_559' => search($product['ОтветственныйЗаДЗ'],"user"),
                                'PROPERTY_556' => search($product['Бухгалтер'],"user"),
                                'PROPERTY_550' => search($product['KAM'],"user"),
                                'PROPERTY_547' => search($product['CS'],"user"),
                                'PROPERTY_544' => search($product['Sales'],"user")
                        ],true);

                }



                                writeToLog($result,"crm.company.update");
                echo "Обновляем";
        }
        else
        {
                $result = CRest::call('crm.company.add',
                                [
                                        'fields' => [
                                                                  'TITLE' => $data['classData']['Тело']['Наименование'],
                                                                  'UF_CRM_1490625536' => ['VALUE' => $opf],// ОПФ обязательное
                                                                  'UF_CRM_1489576733' => $data['classData']['Тело']['НаименованиеПолное'],
                                                                  'ORIGIN_ID' => $data['classData']['Тело']['ГлобИД'],
                                                                  'ORIGINATOR_ID' => substr($data['classData']['Тело']['ГлобИД'],0,3),
                                                                  'UF_CRM_1489563808' => $data['classData']['Тело']['ИНН'],
                                                                  'UF_CRM_1489576937' => $data['classData']['Тело']['КПП'],
                                                                 // 'REQUISITES' => $data['classData']['Тело']['ИНН'],
                                                                  'UF_CRM_1643698164119' => $data['classData']['Тело']['КредитныйЛимит'],
                                                                  'UF_CRM_1490626832' => 73, //прочее (источник компании)
                                                                  'UF_CRM_1499086058' => $country, // не правильно, должен быть список, или привязка к элементам справочника
                                                                  'UF_CRM_1517392716' => $city,
                                                                  'UF_CRM_1541271248' => ['VALUE' =>567], //направление деятельности, продукт (Клиент)
                                                                  'UF_CRM_58C94940538E4' => $fizur,
                                                                  'UF_CRM_1493022880' => $rez,
                                                                  'UF_CRM_1493024947' => $yradress,
                                                                  'UF_CRM_1493024967' => $factadress,
                                                                  'UF_CRM_1493024904' => $postadress,
                                                                  'ADDRESS' => $factadress,
                                                                  'COMMENTS' => $data['classData']['Тело']['Комментарий'],
                                                                  'PHONE'=> $phone,
                                                                  'EMAIL'=> $email,
                                                                  'ASSIGNED_BY_ID' => search($data['classData']['Тело']['Автор'],'user')                                
                                                                ],
                                        'params' => [ "REGISTER_SONET_EVENT" => "Y"]

                                ]);

                foreach($data['classData']['Тело']['ОтветственныеПоКонтрагентам']['row'] as $product)
                {
                        $prdoc=search($product['Продукт'],"products", search($product['Контрагент'],"company"));

                        $el = new CIBlockElement;
                        $res=$el->Add(
                        [
                                'IBLOCK_ID' => $prdoc['IBLOCK_ID'],
                                'ACTIVE' => 'Y',
                                'NAME' => $prdoc['IBLOCK_ELEMENTS_ELEMENT_PRODUCTS_NAZVANIE_PRODUKTA_ITEM_VALUE'], // * обязательное
                                'PROPERTY_674' => $prdoc['IBLOCK_ELEMENTS_ELEMENT_PRODUCTS_NAZVANIE_PRODUKTA_ITEM_ID'], // * обязательное
                                'PROPERTY_559' => search($product['ОтветственныйЗаДЗ'],"user"),
                                'PROPERTY_556' => search($product['Бухгалтер'],"user"),
                                'PROPERTY_550' => search($product['KAM'],"user"),
                                'PROPERTY_547' => search($product['CS'],"user"),
                                'PROPERTY_544' => search($product['Sales'],"user")
                        ],true);
                }

                writeToLog($result,"crm.company.add");
                dum($result);
                print_r($add);
                echo "create";
                echo "Создаем";
        }

}




//Входящий пакет договор
if($data['classData']['Тип']=='Договор')
{
                //обязательно учитывать обязательные поля в списке
                //перебор типа договора на сопоставимость с входящим пакетом
                switch ($data['classData']['Тело']['ТипДоговора'])
                {
                        case "СПокупателем":
                        $typedogovor=558;
                        break;
                        case "СПоставщиком":
                        $typedogovor=559;
                        break;
                }
                //перебор вида договора на сопоставимость с входящим пакетом
                switch ($data['classData']['Тело']['ВидДоговора'])
                {
                        case "ТО":
                        $viddogovor=562;
                        break;
                        case "ТЭО":
                        $viddogovor=563;
                        break;
                        case "Агентский":
                        $viddogovor=565;
                        break;
                        case "ТО_ТЭО":
                        $viddogovor=565;
                        break;
                        case "ДопСоглашение":
                        $viddogovor=565;
                        break;
                }
                //перебор формата договора на сопоставимость с входящим пакетом
        /*      switch ($data['classData']['Тело']['ТипДоговора']) //!!!!!!!!!!!->
                {
                        case "СКлиентом": //Типовой
                        $formdogovor=569;
                        break;
                        case "ТО_ТЭО": //Нетиповой
                        $formdogovor=570;
                        break;
                        case "ДопСоглашение": //ДопСоглашение
                        $formdogovor=571;
                        break;
                }
        */
                //перебор организаций договора на сопоставимость с входящим пакетом
                switch ($data['classData']['Тело']['Организация'])
                {
                        case "TMS0db012250b95430ba249ffedba53":
                        $org=543;
                        break;
                }

                //перебор направления деятельности на сопоставимость с входящим пакетом
        /*      switch ($data['classData']['Тело']['NapDeylti'])
                {
                        case "СКлиентом":
                        $typedogovor=558;
                        break;
                        default: $napdeytel=583;
                }       */

        //Проверка договора на создание или обновление
                $gets = \Bitrix\Iblock\Elements\ElementContractTable::getList([
                        'select' => ['ID','PRODUCTS_'=>'PRODUCTS', 'IBLOCK_ID'],
                        'filter' => ['=ACTIVE' => 'Y','XML_ID'=>$data['classData']['Тело']['ГлобИД']],
                ])->fetchall();
                dum($gets);
                        $el = new CIBlockElement;
                foreach($gets as $in=>$temp)
                {
                        $get['ID']=$temp['ID'];
                        $get['IBLOCK_ID'] = $temp['IBLOCK_ID'];
                        $delete[$in]=$temp['PRODUCTS_VALUE'];
                        //$res=$el->Delete($temp['PRODUCTS_VALUE']);
                }
dum($get['IBLOCK_ID']);
dum($get['ID']);

        //Проверка на создание или обновление по GUID
        if(isset($get['ID']))
        {
                        $buff=array();
                        $el = new CIBlockElement;
                        foreach($delete as $del)
                        {
                                $res=$el->Delete($del);
                                dum($res);
                        }
                foreach($data['classData']['Тело']['ПараметрыДоговора']['row'] as $in=>$param)
                {
                        $pardog=search($param['Продукт'],"products_for_docs");
                        //перебор типа договора на сопоставимость с входящим пакетом
                        switch ($data['classData']['Тело']['ТипДоговора'])
                        {
                                case "СПокупателем":
                                $typedogovor=558;
                                break;
                                case "СПоставщиком":
                                $typedogovor=559;
                                break;
                        }
                        //перебор вида договора на сопоставимость с входящим пакетом
                        switch ($data['classData']['Тело']['ВидДоговора'])
                        {
                                case "ТО":
                                $viddogovor=562;
                                break;
                                case "ТЭО":
                                $viddogovor=563;
                                break;
                                case "Агентский":
                                $viddogovor=565;
                                break;
                                case "ТО_ТЭО":
                                $viddogovor=565;
                                break;
                                case "ДопСоглашение":
                                $viddogovor=565;
                                break;
                        }
                        //перебор события отсрочки на сопоставимость с входящим пакетом
                        switch ($param['СобытиеНачалаОтсчетаОтсрочки'])
                        {
                                case "ОтДатыСчета":
                                $otsr=783;
                                break;
                                case "ОтДатыРеализации":
                                $otsr=786;
                                break;
                                case "ОтДатыПредоставленияДокументов":
                                $otsr=789;
                                break;
                                case "ОтДатыЗаявки":
                                $otsr=792;
                                break;
                        }
                        //перебор типа отсрочки на сопоставимость с входящим пакетом
                        switch ($param['ТипДнейОтсрочки'])
                        {
                                case "Рабочие":
                                $dayotsr=795;
                                break;
                                case "Календарные":
                                $dayotsr=798;
                                break;
                        }

                        $buff=array();
                        $PROP1 = array();
                        $PROP1[656] = $pardog['IBLOCK_ELEMENTS_ELEMENT_PRODUCTSFORDOCS_PRODUCT_ITEM_ID'];  //
                        $PROP1[579] = $otsr;
                        $PROP1[576] = $param['УстановленСрокОплатыПоДоговоруДней'];
                        $PROP1[582] = $dayotsr;

                        $el = new CIBlockElement;
                        $res1=$el->Add(
                        [
                                'IBLOCK_ID' => $pardog['IBLOCK_ID'],
                                'ACTIVE'=> 'Y',
                                'NAME' => $pardog['IBLOCK_ELEMENTS_ELEMENT_PRODUCTSFORDOCS_PRODUCT_ITEM_VALUE'],
                                'PROPERTY_VALUES' => $PROP1                             // * обязательное

                        ],true);

                        $buff[$in]=$res1;
                }

                        $PROP = array();
                        $PROP[481] = $typedogovor;  // свойству с кодом 12 присваиваем значение "Белый"
                        $PROP[482] = $viddogovor;
                        $PROP[483] = 571;
                        $PROP[492] = $data['classData']['Тело']['Номер'];
                        $PROP[468] = date("d.m.Y", strtotime($data['classData']['Тело']['ДатаСоздания'])); //преобразование даты и времени в дату
                        $PROP[470] = search($data['classData']['Тело']['Владелец'],'company'); // * обязательное
                        $PROP[475] = $org; // * обязательное
                        $PROP[477] = search($data['classData']['Тело']['ВалютаДоговора'],'valuta');
                        $PROP[478] = search($data['classData']['Тело']['ВалютаВзаиморасчетов'],'ras');
                        $PROP[480] = date("d.m.Y", strtotime($data['classData']['Тело']['ДатаОкончанияДействия']));
                        $PROP[487] = 577; // * обязательное
                        $PROP[488] = 579; // * обязательное
                        $PROP[495] = search($data['classData']['Тело']['Автор'],'user');
                        $PROP[505] = 583; // * обязательное
                        $PROP[591] = $buff;

                        $el = new CIBlockElement;
                        $res=$el->Update($get['ID'],
                        [
                                'IBLOCK_ID' =>$get['IBLOCK_ID'],
                                'ACTIVE'=> 'Y',
                                'NAME' => $data['classData']['Тело']['Наименование'], // * обязательное
                                'PROPERTY_VALUES' => $PROP
                        ],true);

        }
        else
        {
            //добавление договора;

                foreach($data['classData']['Тело']['ПараметрыДоговора']['row'] as $in=>$param)
                {
                        $pardog=search($param['Продукт'],"products_for_docs");
                        //перебор типа договора на сопоставимость с входящим пакетом
                        switch ($data['classData']['Тело']['ТипДоговора'])
                        {
                                case "СПокупателем":
                                $typedogovor=558;
                                break;
                                case "СПоставщиком":
                                $typedogovor=559;
                                break;
                        }
                        //перебор вида договора на сопоставимость с входящим пакетом
                        switch ($data['classData']['Тело']['ВидДоговора'])
                        {
                                case "ТО":
                                $viddogovor=562;
                                break;
                                case "ТЭО":
                                $viddogovor=563;
                                break;
                                case "Агентский":
                                $viddogovor=565;
                                break;
                                case "ТО_ТЭО":
                                $viddogovor=565;
                                break;
                                case "ДопСоглашение":
                                $viddogovor=565;
                                break;
                        }
                        //перебор события отсрочки на сопоставимость с входящим пакетом
                        switch ($param['СобытиеНачалаОтсчетаОтсрочки'])
                        {
                                case "ОтДатыСчета":
                                $otsr=783;
                                break;
                                case "ОтДатыРеализации":
                                $otsr=786;
                                break;
                                case "ОтДатыПредоставленияДокументов":
                                $otsr=789;
                                break;
                                case "ОтДатыЗаявки":
                                $otsr=792;
                                break;
                        }
                        //перебор типа отсрочки на сопоставимость с входящим пакетом
                        switch ($param['ТипДнейОтсрочки'])
                        {
                                case "Рабочие":
                                $dayotsr=795;
                                break;
                                case "Календарные":
                                $dayotsr=798;
                                break;
                        }

                        $buff=array();
                        $PROP1 = array();
                        $PROP1[656] = $pardog['IBLOCK_ELEMENTS_ELEMENT_PRODUCTSFORDOCS_PRODUCT_ITEM_ID'];  //
                        $PROP1[579] = $otsr;
                        $PROP1[576] = $param['УстановленСрокОплатыПоДоговоруДней'];
                        $PROP1[582] = $dayotsr;

                        $el = new CIBlockElement;
                        $res1=$el->Add(
                        [
                                'IBLOCK_ID' => $pardog['IBLOCK_ID'],
                                'ACTIVE'=> 'Y',
                                'NAME' => $pardog['IBLOCK_ELEMENTS_ELEMENT_PRODUCTSFORDOCS_PRODUCT_ITEM_VALUE'],
                                'PROPERTY_VALUES' => $PROP1                             // * обязательное

                        ],true);

                        $buff[$in]=$res1;
                }


                        $PROP = array();
                        $PROP[481] = $typedogovor;  // свойству с кодом 12 присваиваем значение "Белый"
                        $PROP[482] = $viddogovor;
                        $PROP[483] = 571;
                        $PROP[492] = $data['classData']['Тело']['Номер'];
                        $PROP[468] = date("d.m.Y", strtotime($data['classData']['Тело']['ДатаСоздания'])); //преобразование даты и времени в дату
                        $PROP[470] = search($data['classData']['Тело']['Владелец'],'company'); // * обязательное
                        $PROP[475] = $org; // * обязательное
                        $PROP[477] = search($data['classData']['Тело']['ВалютаДоговора'],'valuta');
                        $PROP[478] = search($data['classData']['Тело']['ВалютаВзаиморасчетов'],'ras');
                        $PROP[480] = date("d.m.Y", strtotime($data['classData']['Тело']['ДатаОкончанияДействия']));
                        $PROP[487] = 577; // * обязательное
                        $PROP[488] = 579; // * обязательное
                        $PROP[495] = search($data['classData']['Тело']['Автор'],'user');
                        $PROP[505] = 583; // * обязательное
                        $PROP[591] = $buff;

                        $el = new CIBlockElement;
                        $res2=$el->Add(
                        [
                                'IBLOCK_ID' => 106,
                                'ACTIVE'=> 'Y',
                                'NAME' => $data['classData']['Тело']['Наименование'], // * обязательное
                                'XML_ID' => $data['classData']['Тело']['ГлобИД'],
                                'PROPERTY_VALUES' => $PROP
                        ],true);


        }

}
}

function search($guid, $type, $company=false)
{
        switch ($type)
                {
                        case "company":
                                $arFilter = array("ORIGIN_ID" => $guid ,"CHECK_PERMISSIONS"=>"N");
                                $arSelect = array("ID");
                                $rsCompany = CCrmCompany::GetList(Array(),$arFilter,$arSelect);
                                if($arComp = $rsCompany->Fetch()){
                                  return $arComp['ID'];
                                }
                                else return 0;
                        break;
                        case "user":
                                $user = \Bitrix\Main\UserTable::getList(array(
                                        'filter' => array(
                                                'UF_GUID' => $guid,
                                        ),
                                        'limit'=>1,
                                        'select'=>array('ID'),
                                ))->fetch();
                                return $user['ID'];
                        break;
                        case "rekviz":
                                $client = new \Bitrix\socialservices\properties\Client;
                                $arRequisite = $client->getByInn($guid);
                                return $arRequisite;
                        break;
                        case "valuta":
                                $element = \Bitrix\Iblock\Elements\ElementContractTable::getList([
                                        'select' => ['ID', 'VALYUTA_'=>'VALYUTA'],
                                        'filter' => ['=ACTIVE' => 'Y', '=VALYUTA.ITEM.XML_ID'=>$guid],
                                ])->fetch();
                                return $element['VALYUTA_VALUE'];
                        break;
                        case "ras":
                                $element = \Bitrix\Iblock\Elements\ElementContractTable::getList([
                                        'select' => ['ID', 'VALYUTA_RASCHETOV_'=>'VALYUTA_RASCHETOV'],
                                        'filter' => ['=ACTIVE' => 'Y', '=VALYUTA_RASCHETOV.ITEM.XML_ID'=>$guid],
                                ])->fetch();
                                return $element['VALYUTA_RASCHETOV_VALUE'];
                        break;
                        case "country":
                                $element = \Bitrix\Iblock\Elements\ElementDogovorTable::getList([
                                        'select' => ['NAME'],
                                        'filter' => ['=ACTIVE' => 'Y', "CODE" => $guid],
                                ])->fetch();
                                return ucfirst(strtolower($element['NAME']));
                        break;
                        case "city":
                                $element = \Bitrix\Iblock\Elements\ElementDogovorTable::getList([
                                        'select' => ['NAME'],
                                        'filter' => ['=ACTIVE' => 'Y', "CODE" => $guid],
                                ])->fetch();
                                return $element['NAME'];
                        break;
                        case "products_delete":
                        $elements = \Bitrix\Iblock\Elements\ElementProductsTable::getList([
                                        'select' => ['ID','IBLOCK_ID'],
                                        'filter' => ['=ACTIVE' => 'Y',"=COMPANY.VALUE"=>$guid]
                                ])->fetchall();
                        $el = new CIBlockElement;
                        foreach($elements as $element)
                        {$res=$el->Delete($element['ID']);}
                        break;
                        case "products":
                        $element = \Bitrix\Iblock\Elements\ElementProductsTable::getList([
                                        'select' => ['ID','NAZVANIE_PRODUKTA.ITEM','IBLOCK_ID'],
                                        'filter' => ["=NAZVANIE_PRODUKTA.ITEM.XML_ID"=>$guid, "=COMPANY.VALUE"=>$company]
                                ])->fetch();
                                return $element;
                        break;
                        case "products_for_docs":
                        $element = \Bitrix\Iblock\Elements\ElementProductsfordocsTable::getList([
                                'select' => ['ID','PRODUCT.ITEM','IBLOCK_ID'],
                                'filter' => [ "=PRODUCT.ITEM.XML_ID"=>$guid ]
                        ])->fetch();
                                return $element;
                        break;
                }

}

function writeToLog($data, $title = '')
{
        if (!extractlog)
                return false;

        $log = "\n------------------------\n";
        $log .= date("Y.m.d G:i:s")."\n";
        $log .= (strlen($title) > 0 ? $title : 'DEBUG')."\n";
        $log .= print_r($data, 1);
        $log .= "\n------------------------\n";

        file_put_contents(__DIR__."/".extractlog, $log, FILE_APPEND);

        return true;
}

                ?>

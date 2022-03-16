<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
require_once (__DIR__.'/crest.php');

use Bitrix\Main\EventManager,
    Bitrix\Main\Diag\Debug,
    Bitrix\Main\Context;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\Loader;
use Bitrix\Main\IO,
    Bitrix\Main\Application;
use Bitrix\Main\Diag;


//Договора
//Создание договора
if($_REQUEST['event']=='DOGOVORADD')
{
        writeToLog($_REQUEST,"REQUEST");
                //обязательно учитывать обязательные поля в списке
                //перебор типа договора на сопоставимость с входящим пакетом
                switch ($_REQUEST['body']['PROPERTY_VALUES'][481])
                {
                        case "558":
                        $typedogovor="СПокупателем";
                        break;
                        case "559":
                        $typedogovor="СПоставщиком";
                        break;
                }
                //перебор вида договора на сопоставимость с входящим пакетом
                switch ($_REQUEST['body']['PROPERTY_VALUES'][482])
                {
                        case "562":
                        $viddogovor="TO";
                        break;
                        case "563":
                        $viddogovor="ТЭО";
                        break;
                        case "565":
                        $viddogovor="Агентский";
                        break;
                        case "567":
                        $viddogovor="Административный";
                        break;
                        case "568":
                        $viddogovor="ДопСоглашение";
                        break;
                }
                //перебор формата договора на сопоставимость с входящим пакетом
                switch ($_REQUEST['body']['PROPERTY_VALUES'][483]) //!!!!!!!!!!!->
                {
                        case "569": //Типовой
                        $formdogovor="Типовой";
                        break;
                        case "570": //Нетиповой
                        $formdogovor="Нетиповой";
                        break;
                        case "571": //ДопСоглашение
                        $formdogovor="ДопСоглашение";
                        break;
                }

                //перебор организаций договора на сопоставимость с входящим пакетом
                switch ($_REQUEST['body']['PROPERTY_VALUES'][475])
                {
                        case "543":
                        $org="TMS0db012250b95430ba249ffedba53";
                        break;
                }

                //дата создания
                foreach($_REQUEST['body']['DATE_CREATE'] as $data)
                {
                        $datacreate=date("Y-m-d\TH:i:s");
                }
                //срок окончания
                foreach($_REQUEST['body']['PROPERTY_VALUES'][480] as $data)
                {
                        $dataend=date("Y-m-d\TH:i:s", strtotime($data['VALUE']));
                }
                //номер договора        фактическая
                foreach($_REQUEST['body']['PROPERTY_VALUES'][492] as $data)
                {
                        $numberfact=$data['VALUE'];
                }
                //дата договора фактическая
                foreach($_REQUEST['body']['PROPERTY_VALUES'][468] as $data)
                {
                        $datafact=date("Y-m-d\TH:i:s", strtotime($data['VALUE']));
                }
                //компания
                foreach($_REQUEST['body']['PROPERTY_VALUES'][470] as $comp)
                {
                        $company=search($comp['VALUE'],'company');
                }

                //перебор направления деятельности на сопоставимость с входящим пакетом !!!!
                switch ($_REQUEST['body']['PROPERTY_VALUES'][505])
                {
                        case "СКлиентом":
                        $typedogovor=558;
                        break;
                        default: $napdeytel=583;
                }

                                writeToLog(search($_REQUEST['body']['PROPERTY_VALUES'][477],'valuta'),"valuta");
                        writeToLog(search($_REQUEST['body']['PROPERTY_VALUES'][478],'ras'),"ras");
            //Отправка договора
                $http = new \Bitrix\Main\Web\HttpClient();
                $http->setTimeout(5);
                $http->setStreamTimeout(50);
                $http->setHeader('Content-Type', 'application/json',true);
                //Проверка компании и получение данных к порталу
                $json = $http->post('http://217.73.57.52:8091/dogovor',json_encode(
                [
                  'classData' => [
                                                        'Тип' => 'Договор',
                                                        'Событие' => 'Создание',
                                                        'Тело' =>[
                                                                        'Владелец' => $company,
                                                                        'Наименование' => $_REQUEST['body']['NAME'],
                                                                        'АвтоматическиПролонгировать' => false,
                                                                        'Автор' => search($_REQUEST['body']['CREATED_BY'],'user'),
                                                                        'Активирован' => false,
                                                                        'Номер' => $numberfact,
                                                                        'ВалютаДоговора' => search($_REQUEST['body']['PROPERTY_VALUES'][477],'valuta'),
                                                                        'ВалютаВзаиморасчетов' => search($_REQUEST['body']['PROPERTY_VALUES'][478],'ras'),
                                                                        'Дата' => $datafact,
                                                                        'ДатаОкончанияДействия' => $dataend,
                                                                        'ДатаСоздания' => date("Y-m-d\TH:i:s"),
                                                                        'Организация' => $org,
                                                                        'ТипДоговора' => $typedogovor,
                                                                        'ВидДоговора' => $viddogovor,
                                                                        'ФорДоговора' => $formdogovor,
                                                                        'ГлобИД' => $_REQUEST['body']['XML_ID'],
                                                                        'Комментарий' => "тест Битрикс",
                                                                        'ПараметрыДоговора' =>search($_REQUEST['body']['ID'],"products_for_docs")
                                                                        ]
                                                 ],
                  ]));
                writeToLog($http->getContentType(),"ContentType");
                writeToLog($http->getHeaders(),"HEADERS");
                writeToLog(\Bitrix\Main\Web\Json::decode($http->getResult()),"RESULT");
                $result = json_decode($json);
                writeToLog($result,"result");
}

//Обновление договора
if($_REQUEST['event']=='DOGOVORUPDATE')
{
                //обязательно учитывать обязательные поля в списке
                //перебор типа договора на сопоставимость с входящим пакетом
                switch ($_REQUEST['body']['PROPERTY_VALUES'][481])
                {
                        case "558":
                        $typedogovor="СПокупателем";
                        break;
                        case "559":
                        $typedogovor="СПоставщиком";
                        break;
                }
                //перебор вида договора на сопоставимость с входящим пакетом
                switch ($_REQUEST['body']['PROPERTY_VALUES'][482])
                {
                        case "562":
                        $viddogovor="TO";
                        break;
                        case "563":
                        $viddogovor="ТЭО";
                        break;
                        case "565":
                        $viddogovor="Агентский";
                        break;
                        case "567":
                        $viddogovor="Административный";
                        break;
                        case "568":
                        $viddogovor="ДопСоглашение";
                        break;
                }
                //перебор формата договора на сопоставимость с входящим пакетом
                switch ($_REQUEST['body']['PROPERTY_VALUES'][483]) //!!!!!!!!!!!->
                {
                        case "569": //Типовой
                        $formdogovor="Типовой";
                        break;
                        case "570": //Нетиповой
                        $formdogovor="Нетиповой";
                        break;
                        case "571": //ДопСоглашение
                        $formdogovor="ДопСоглашение";
                        break;
                }

                //перебор организаций договора на сопоставимость с входящим пакетом
                switch ($_REQUEST['body']['PROPERTY_VALUES'][475])
                {
                        case "543":
                        $org="TMS0db012250b95430ba249ffedba53";
                        break;
                }
                //дата создания
                foreach($_REQUEST['body']['DATE_CREATE'] as $data)
                {
                        $datacreate=date("Y-m-d\TH:i:s");
                }
                //срок окончания
                foreach($_REQUEST['body']['PROPERTY_VALUES'][480] as $data)
                {
                        if(empty($data['VALUE'])) $dataend="";
                        else $dataend=date("Y-m-d\TH:i:s", strtotime($data['VALUE']));
                }
                //дата договора фактическая
                foreach($_REQUEST['body']['PROPERTY_VALUES'][468] as $data)
                {
                        $datafact=date("Y-m-d\TH:i:s", strtotime($data['VALUE']));
                }
                //номер договора        фактическая
                foreach($_REQUEST['body']['PROPERTY_VALUES'][492] as $data)
                {
                        $numberfact=$data['VALUE'];
                }
                //компания
                foreach($_REQUEST['body']['PROPERTY_VALUES'][470] as $comp)
                {
                        $company=search($comp['VALUE'],'company');
                }


                //перебор направления деятельности на сопоставимость с входящим пакетом  !!!!
                switch ($_REQUEST['body']['PROPERTY_VALUES'][505])
                {
                        case "СКлиентом":
                        $typedogovor=558;
                        break;
                        default: $napdeytel=583;
                }

            //Отправка договора
                $http = new \Bitrix\Main\Web\HttpClient();
                $http->setTimeout(5);
                $http->setStreamTimeout(50);
                $http->setHeader('Content-Type', 'application/json',true);
                //Проверка компании и получение данных к порталу
                $json = $http->post('http://217.73.57.52:8091/dogovor',json_encode(
                  [
                  'classData' => [
                                                        'Тип' => 'Договор',
                                                        'Событие' => 'Обновление',
                                                        'Тело' =>
                                                                        [
                                                                        'Владелец' => $company,
                                                                        'Наименование' => $_REQUEST['body']['NAME'],
                                                                        'АвтоматическиПролонгировать' => false,
                                                                        'Автор' => search($_REQUEST['body']['CREATED_BY'],'user'),
                                                                        'Активирован' => false,
                                                                        'Номер' => $numberfact,
                                                                        'ВалютаДоговора' => search($_REQUEST['body']['PROPERTY_VALUES'][477],'valuta'),
                                                                        'ВалютаВзаиморасчетов' => search($_REQUEST['body']['PROPERTY_VALUES'][478],'ras'),
                                                                        'Дата' => $datafact,
                                                                        'ДатаОкончанияДействия' => $dataend,
                                                                        'ДатаСоздания' => $datacreate,
                                                                        'Организация' => $org,
                                                                        'ТипДоговора' => $typedogovor,
                                                                        'ВидДоговора' => $viddogovor,
                                                                        'ФорДоговора' => $formdogovor,
                                                                        'ГлобИД' => $_REQUEST['body']['XML_ID'],
                                                                        'Комментарий' => "тест Битрикс",
                                                                        'ПараметрыДоговора' =>search($_REQUEST['body']['ID'],"products_for_docs")
                                                                        ]
                                                 ]
                  ]
                ));
                $result = json_decode($json);
                writeToLog($result,"result");
}

//Компании
if($_REQUEST['event']=='ONCRMCOMPANYADD')
{

        //перебор стран на сопоставимость с входящим пакетом
        switch ($_REQUEST['body']['UF_CRM_58C94940538E4'])
                {
                        case "34":
                        $fizur="ЮрЛицо"; //
                        break;
                        case "33":
                        $fizur="ИП"; //
                        break;
                        case "286":
                        $fizur="Обособленное подразделение"; //
                        break;
                        case "1581":
                        $fizur="Самозанятый"; //
                        break;
                        case "1584":
                        $fizur="Физ. Лицо без статуса"; //
                        break;
                }

        $i=0;
        foreach($_REQUEST['body']['FM']['PHONE'] as $info)
        {
                $phone['row'][$i]['phone']=$info['VALUE'];
                $i++;
        }
        $i=0;
        foreach($_REQUEST['body']['FM']['EMAIL'] as $info)
        {
                $email['row'][$i]['email']=$info['VALUE'];
                $i++;
        }
/*
 $buff['row'][0]['Контрагент'] = "TMS214ca7ec8e1e4cc2a5ba37f583b1554b";
 $buff['row'][0]['Продукт'] = "TMS08ed305907dc4234a31063cde4e5";
 $buff['row'][0]['CS'] = "TMS2d699995c0134a0cb98c8cd842916727";
 $buff['row'][0]['KAM'] = "TMS20512ebe11d24d9ba2c466a983bc3967";
 $buff['row'][0]['Sales'] = "TMScb9959e72d094fa2ac23728f89532c74";
 $buff['row'][0]['Бухгалтер'] = "TMS58705d45667d4d47a3f8011f27834414";
 $buff['row'][0]['ОтветственныйЗаДЗ'] => "TMS2d699995c0134a0cb98c8cd842916727";
 */

                //Отправка контрагента
                $http = new \Bitrix\Main\Web\HttpClient();
                $http->setTimeout(5);
                $http->setStreamTimeout(50);
                $http->setHeader('Content-Type', 'application/json',true);
                $json = $http->post('http://217.73.57.52:8091/kontragent',json_encode(
                                [
                                'classData' => [
                                                        'Тип' => 'Контрагент',
                                                        'Событие' => 'Создание',
                                                        'Тело' =>[
                                                                        'Родитель' =>$_REQUEST['body']['ORIGIN_ID'],
                                                                        'ЭтоГруппа' => false,
                                                                        'Наименование' => $_REQUEST['body']['TITLE'],
                                                                        'Страна' => search($_REQUEST['body']['UF_CRM_1499086058'], "country"),
                                                                        'Город' => search($_REQUEST['body']['UF_CRM_1517392716'], "city"),
                                                                        'ИНН' => $_REQUEST['body']['UF_CRM_1489563808'],
                                                                        'КПП' => $_REQUEST['body']['UF_CRM_1489576937'],
                                                                        'ЮрФизЛицо' => $fizur,
                                                                        'АнглийскоеНаименование' => code($_REQUEST['body']['TITLE']),
                                                                        'РусскоеНаименование' => $_REQUEST['body']['TITLE'],
                                                                        'НаименованиеПолное' => $_REQUEST['body']['UF_CRM_1489576733'],
                                                                        'НаименованиеПолноеАнглийское' => code($_REQUEST['body']['UF_CRM_1489576733']),
                                                                        'КредитныйЛимит' => $_REQUEST['body']['UF_CRM_1643698164119'],
                                                                        'ГоловнойКонтрагент' => $_REQUEST['body']['ORIGIN_ID'],
                                                                        'Комментарий' => $_REQUEST['body']['COMMENTS'],
                                                                        'Экспедитор' => '',
                                                                        'Автор' => search($_REQUEST['body']['ASSIGNED_BY_ID'],"user"),
                                                                        'ДатаСоздания' => date("Y-m-d\TH:i:s", time()),
                                                                        'СобственникТС' => '',
                                                                        'ГлобИД' => $_REQUEST['body']['ORIGIN_ID'],
                                                                        'ТекущаяДата' => date("Y-m-d\TH:i:s", time()),
                                                                        'PHONES' => $phone,
                                                                        'EMAILS' => $email,
                                                                        'Юридический адрес' => $_REQUEST['body']['UF_CRM_1493024947'],
                                                                        'Фактический адрес' => $_REQUEST['body']['UF_CRM_1493024967'],
                                                                        'Адрес для корреспонденции' => $_REQUEST['body']['UF_CRM_1493024904'],
                                                                        'ОтветственныеПоКонтрагентам'=>""//$buff//search($_REQUEST['body']['ID'],"products")
                                                                        ]
                                                                ]
                                ]));
                                $status=$http->getStatus();
                                writeToLog($status,"status");
                                if($http->getStatus()==200) writeToLog(\Bitrix\Main\Web\Json::decode($http->getResult()));
                                else writeToLog(\Bitrix\Main\Web\Json::decode($http->getError()));

                $result = json_decode($json);
                writeToLog($result,"result");
}

if($_REQUEST['event']=='ONCRMCOMPANYUPDATE')
{
        $result = CRest::call('crm.company.get',
                [
                        'id' => $_REQUEST['body']['ID']
                ]);
        $_REQUEST['body']=$result['result'];
        //перебор стран на сопоставимость с входящим пакетом
        switch ($_REQUEST['body']['UF_CRM_58C94940538E4'])
                {
                        case "34":
                        $fizur="ЮрЛицо"; //
                        break;
                        case "33":
                        $fizur="ИП"; //
                        break;
                        case "286":
                        $fizur="Обособленное подразделение"; //
                        break;
                        case "1581":
                        $fizur="Самозанятый"; //
                        break;
                        case "1584":
                        $fizur="Физ. Лицо без статуса"; //
                        break;
                }
        $i=0;
        foreach($_REQUEST['body']['FM']['PHONE'] as $info)
        {
                $phone[$i]=$info['VALUE'];
                $i++;
        }
        $i=0;
        foreach($_REQUEST['body']['FM']['EMAIL'] as $info)
        {
                $email[$i]=$info['VALUE'];
                $i++;
        }
/*
 $buff['row'][0]['Контрагент'] = "TMS214ca7ec8e1e4cc2a5ba37f583b1554b";
 $buff['row'][0]['Продукт'] = "TMS08ed305907dc4234a31063cde4e5";
 $buff['row'][0]['CS'] = "TMS2d699995c0134a0cb98c8cd842916727";
 $buff['row'][0]['KAM'] = "TMS20512ebe11d24d9ba2c466a983bc3967";
 $buff['row'][0]['Sales'] = "TMScb9959e72d094fa2ac23728f89532c74";
 $buff['row'][0]['Бухгалтер'] = "TMS58705d45667d4d47a3f8011f27834414";
 $buff['row'][0]['ОтветственныйЗаДЗ'] => "TMS2d699995c0134a0cb98c8cd842916727";
 */
                //Отправка контрагента
                $http = new \Bitrix\Main\Web\HttpClient();
                $http->setTimeout(5);
                $http->setStreamTimeout(50);
                $http->setHeader('Content-Type', 'application/json',true);
                $json = $http->post('http://217.73.57.52:8091/kontragent',json_encode(
                                [
                                'classData' => [
                                                        'Тип' => 'Контрагент',
                                                        'Событие' => 'Обновление',
                                                        'Тело' =>[
                                                                        'Родитель' =>"",
                                                                        'Наименование' => $_REQUEST['body']['TITLE'],
                                                                        'Страна' => search($_REQUEST['body']['UF_CRM_1499086058'], "country"),
                                                                        'Город' => search($_REQUEST['body']['UF_CRM_1517392716'], "city"),
                                                                        'ИНН' => $_REQUEST['body']['UF_CRM_1489563808'],
                                                                        'КПП' => $_REQUEST['body']['UF_CRM_1489576937'],
                                                                        'ЮрФизЛицо' => $fizur,
                                                                        //'АнглийскоеНаименование' => code($_REQUEST['body']['TITLE']),
                                                                        'РусскоеНаименование' => $_REQUEST['body']['TITLE'],
                                                                        'НаименованиеПолное' => $_REQUEST['body']['UF_CRM_1489576733'],
                                                                //      'НаименованиеПолноеАнглийское' => code($_REQUEST['body']['UF_CRM_1489576733']),
                                                                        'КредитныйЛимит' => $_REQUEST['body']['UF_CRM_1643698164119'],
                                                                        'ГоловнойКонтрагент' => $_REQUEST['body']['ORIGIN_ID'],
                                                                        'Комментарий' => $_REQUEST['body']['COMMENTS'],
                                                                        'Экспедитор' => '',
                                                                        'Автор' => search($_REQUEST['body']['ASSIGNED_BY_ID'],"user"),
                                                                        'ДатаСоздания' => date("Y-m-d\TH:i:s", time()),
                                                                        'СобственникТС' => '',
                                                                        'ГлобИД' => $_REQUEST['body']['ORIGIN_ID'],
                                                                        'ТекущаяДата' => date("Y-m-d\TH:i:s", time()),
                                                                        'PHONE' => $phone,
                                                                        'EMAIL' => $email,
                                                                        'Юридический адрес' => $_REQUEST['body']['UF_CRM_1493024947'],
                                                                        'Фактический адрес' => $_REQUEST['body']['UF_CRM_1493024967'],
                                                                        'Адрес для корреспонденции' => $_REQUEST['body']['UF_CRM_1493024904'],
                                                                        'ОтветственныеПоКонтрагентам'=>""//search($_REQUEST['body']['ID'],"products")
                                                                        ]
                                                                ]
                                ]));
                $result = json_decode($json);
                writeToLog($result,"result");
}

function search($id, $type)
{
        switch ($type)
                {
                        case "company":
                                $result = CRest::call('crm.company.list',
                                [
                                        'filter' => [ "ID" => $id ],
                                        'select' => [ "ORIGIN_ID"]

                                ]);
                                return $result['result'][0]['ORIGIN_ID'];
                        break;
                        case "user":
                                $user = \Bitrix\Main\UserTable::getList(array(
                                        'filter' => array(
                                                'ID' => $id,
                                        ),
                                        'limit'=>1,
                                        'select'=>array('UF_GUID'),
                                ))->fetch();
                                return $user['UF_GUID'];
                        break;
                        case "rekviz":
                                $client = new \Bitrix\socialservices\properties\Client;
                                $arRequisite = $client->getByInn($id);
                                return $arRequisite;
                        break;
                        case "valuta":
                                $element = \Bitrix\Iblock\Elements\ElementContractTable::getList([
                                        'select' => ['ID', 'VALYUTA.ITEM.XML_ID'],
                                        'filter' => ['=ACTIVE' => 'Y','VALYUTA.ITEM.ID'=>$id],
                                ])->fetch();
                                return $element['IBLOCK_ELEMENTS_ELEMENT_CONTRACT_VALYUTA_ITEM_XML_ID'];
                        break;
                        case "ras":
                                $element = \Bitrix\Iblock\Elements\ElementContractTable::getList([
                                        'select' => ['ID', 'VALYUTA_RASCHETOV.ITEM.XML_ID'],
                                        'filter' => ['=ACTIVE' => 'Y','VALYUTA_RASCHETOV.ITEM.ID'=>$id],
                                ])->fetch();
                                return $element['IBLOCK_ELEMENTS_ELEMENT_CONTRACT_VALYUTA_RASCHETOV_ITEM_XML_ID'];
                        break;
                        case "country":
                                $element = \Bitrix\Iblock\Elements\ElementDogovorTable::getList([
                                'select' => ['CODE'],
                                'filter' => ['=ACTIVE' => 'Y', "NAME" => strtoupper($id)],
                                ])->fetch();
                                return $element['CODE'];
                        break;
                        case "city":
                                $element = \Bitrix\Iblock\Elements\ElementDogovorTable::getList([
                                'select' => ['CODE'],
                                'filter' => ['=ACTIVE' => 'Y', "NAME" => ucfirst(strtolower($id))],
                                ])->fetch();
                                return $element['CODE'];
                        break;
                        case "products":
                        $element = \Bitrix\Iblock\Elements\ElementProductsTable::getList([
                                        'select' => ['ID','NAZVANIE_PRODUKTA.ITEM','IBLOCK_ID'],
                                        'filter' => ['=ACTIVE' => 'Y', "=COMPANY.VALUE"=>$id]
                                ])->fetchall();
                                return $element;
                        break;
                        case "products_for_docs":
                        $elements = \Bitrix\Iblock\Elements\ElementContractTable::getList([
                                'select' => ['PRODUCTS_'=>'PRODUCTS','KONTRAGENT_'=>'KONTRAGENT','XML_ID'],
                                'filter' => [ "=ID"=>$id]
                        ])->fetchall();
                        foreach($elements as $in=>$element)
                        {
                                $product = \Bitrix\Iblock\Elements\ElementProductsfordocsTable::getList([
                                        'select' => ['PRODUCT.ITEM.XML_ID','START_POINT.ITEM.ID','DUE_DATE','DEFERRAL_TYPE.ITEM.ID'],
                                        'filter' => [ "=ID"=>$element['PRODUCTS_VALUE']]
                                ])->fetch();
                                //перебор события отсрочки на сопоставимость с входящим пакетом
                                switch ($product['IBLOCK_ELEMENTS_ELEMENT_PRODUCTSFORDOCS_START_POINT_ITEM_ID'])
                                {
                                        case "783":
                                        $otsr="ОтДатыСчета";
                                        break;
                                        case "786":
                                        $otsr="ОтДатыРеализации";
                                        break;
                                        case "789":
                                        $otsr="ОтДатыПредоставленияДокументов";
                                        break;
                                        case "792":
                                        $otsr="ОтДатыЗаявки";
                                        break;
                                }
                                //перебор типа отсрочки на сопоставимость с входящим пакетом
                                switch ($product['IBLOCK_ELEMENTS_ELEMENT_PRODUCTSFORDOCS_DEFERRAL_TYPE_ITEM_ID'])
                                {
                                        case "795":
                                        $dayotsr="Рабочие";
                                        break;
                                        case "798":
                                        $dayotsr="Календарные";
                                        break;
                                }

                                $buf['row'][$in]['Контрагент'] = search($element['KONTRAGENT_VALUE'],"company"); //id  TMScc407c95a0304c3fb4802ca26f0ec4c8
                $buf['row'][$in]['Продукт'] = $product['IBLOCK_ELEMENTS_ELEMENT_PRODUCTSFORDOCS_PRODUCT_ITEM_XML_ID'];// TMS5f96b6c13f8149ba8ffdcc22552a
                $buf['row'][$in]['Договор'] = $element['XML_ID']; //TMS66740e4b9146467aa195d1f39de4167d
                $buf['row'][$in]['СобытиеНачалаОтсчетаОтсрочки'] = $otsr;
                $buf['row'][$in]['УстановленСрокОплатыПоДоговоруДней'] = $product['IBLOCK_ELEMENTS_ELEMENT_PRODUCTSFORDOCS_DUE_DATE_IBLOCK_GENERIC_VALUE'];
                $buf['row'][$in]['ТипДнейОтсрочки'] = $dayotsr;
                                //dum($product);
                        }
                                return $buf;
                        break;
                }

}
function writeToLog($data, $title = '')
{
        if (!DEBUG_FILE_NAME)
                return false;

        $log = "\n------------------------\n";
        $log .= date("Y.m.d G:i:s")."\n";
        $log .= (strlen($title) > 0 ? $title : 'DEBUG')."\n";
        $log .= print_r($data, 1);
        $log .= "\n------------------------\n";

        file_put_contents(__DIR__."/".DEBUG_FILE_NAME, $log, FILE_APPEND);

        return true;
}

                ?>

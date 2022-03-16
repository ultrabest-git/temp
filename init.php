<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
//константы Биотайма
//define("BIOTIME","http://mail.домен.com:9000/api/v1/");
//define("BIOTIMELOGIN","admin");
//define("BIOTIMEPASSWORD","Nawinia");

//показывает своё местоположение
use Bitrix\Main\Diag\ExceptionHandlerLog;
use \Bitrix\Main\Application,
    \Bitrix\Main\Web\Uri,
        \Bitrix\Main\UserTable,
    \Bitrix\Main\Web\HttpClient;
CModule::IncludeModule('crm');

if (!function_exists("prn_")) {
    function prn_($obj) {
        $dump = ""; //  50  -
        $dumpname = "/dump.php";
        $max_dump_size = 2;    //максимальный размер дампа (мб), после которого он удаляется и пишется заново
        if (filesize($_SERVER["DOCUMENT_ROOT"] . "$dumpname") > 1024 * 1024 * $max_dump_size) {
            unlink($_SERVER["DOCUMENT_ROOT"] . "$dumpname");
        } // ,
        if (!file_exists($_SERVER["DOCUMENT_ROOT"] . "$dumpname")) {
            $dump = '<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");global $USER; if(!($USER->IsAdmin() || isset($_REQUEST["dev"]))){die();} ?>' . "\n\r";
        } //
        $infoDebug = debug_backtrace();
        $infoDebug = $infoDebug[0];
        $dump .= ": " . $infoDebug["file"] .
                ":" . $infoDebug["line"] .
                ";\n\r"; //
        $dump .= "<pre style='font-size: 11px; font-family: tahoma;'>" . print_r($obj, true) .
                "</pre>" .
                "\n\r";
        $files = $_SERVER["DOCUMENT_ROOT"] . "$dumpname";
        $fp = fopen($files, "a+") or die("   $files"); //
        if (fwrite($fp, $dump) === false) {
            AddMessage2Log("      ($files)");
            exit;
        }
        fclose($fp);
    }
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/handler/add_lang_selector.php');

require_once ( $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/mpdf-8.0.17/vendor/autoload.php' );
require_once ( $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/dompdf/vendor/autoload.php' );



//Пользователи, присвоение GUID
$eventManager->addEventHandlerCompatible(
    'main',
    'OnBeforeUserAdd',
    function( &$arFields )
    {
        if(empty($arFields['UF_GUID'])) {$arFields['UF_GUID']=getGUID();}
        return true;
    }
);

//Отправка запроса на обработчик
$eventManager->addEventHandlerCompatible(
    'main',
    'OnAfterUserAdd',
    function( &$arFields )
    {
                post("USERADD",$arFields);
        return true;
    }
);




//Компании, присвоение GUID
$eventManager->addEventHandlerCompatible(
    'crm',
    'OnBeforeCrmCompanyAdd',
    function( &$arFields )
    {
                Bitrix\Main\Diag\Debug::writeToFile($arFields, 'OnBeforeCrmCompanyAdd', 'beforecrmcompanyadd.txt');
                        $company = CCrmCompany::GetList([], ["ORIGIN_ID" => $arFields['ORIGINATOR_ID'],"CHECK_PERMISSIONS"=>"N" ])->Fetch();
                        if(isset($company['ID'])&& !empty($arFields['ORIGIN_ID']))
                        {
                                $arFields['ORIGINATOR_ID']="B24";
                                $arFields['ORIGIN_ID']=getGUID();
                          Bitrix\Main\Diag\Debug::writeToFile($arComp, 'OnBeforeCrmCompanyAdd', 'beforecrmcompanyadd.txt');
                        }

        return true;
    }
);

//Отправка запроса на обработчик при добавлении
$eventManager->addEventHandlerCompatible(
    'crm',
    'OnAfterCrmCompanyAdd',
    function( &$arFields )
    {
                if($arFields['ORIGINATOR_ID']=="B24")
        {
                        Bitrix\Main\Diag\Debug::writeToFile($arFields, 'OnAfterCrmCompanyAdd', 'crmcompanyadd.txt');
                        post("ONCRMCOMPANYADD",$arFields);
                }
        return true;
    }
);

//Отправка запроса на обработчик при обновлении
$eventManager->addEventHandlerCompatible(
    'crm',
    'OnBeforeCrmCompanyUpdate',
    function( &$arFields )
    {
                if($arFields['ASSIGNED_BY_ID']==2785){$arFields['ASSIGNED_BY_ID']=$arFields['MODIFY_BY_ID'];} else $arFields['ORIGINATOR_ID']="B24";
                Bitrix\Main\Diag\Debug::writeToFile($arFields, 'OnBeforeCrmCompanyUpdate', 'crmcompanyupdate.txt');
    }
);

//Отправка запроса на обработчик при обновлении
$eventManager->addEventHandlerCompatible(
    'crm',
    'OnAfterCrmCompanyUpdate',
    function( &$arFields )
    {
                if($arFields['ORIGINATOR_ID']=="B24")
        {
                        Bitrix\Main\Diag\Debug::writeToFile($arFields, 'OnAfterCrmCompanyUPDATE', 'crmcompanyupdate.txt');
                        post("ONCRMCOMPANYUPDATE",$arFields);
                }

                                //post("ONCRMCOMPANYUPDATE",$arFields);
                return true;
    }
);


//Договора, присвоение GUID
$eventManager->addEventHandlerCompatible(
    'iblock',
    'OnBeforeIBlockElementAdd',
    function( &$arFields )
    {
Bitrix\Main\Diag\Debug::writeToFile($arFields, 'OnBeforeIBlockElementAdd', 'listsaddbefore.txt');
                if($arFields['IBLOCK_ID']==106)
                {
                        $element = \Bitrix\Iblock\Elements\ElementContractTable::getList([
                                'select' => ['ID'],
                                'filter' => ['=ACTIVE' => 'Y','XML_ID'=>$arFields['XML_ID']],
                        ])->fetch();
                //Проверка на создание или обновление по GUID
                Bitrix\Main\Diag\Debug::writeToFile($arFields, 'OnBeforeIBlockElementAdd', 'listsaddbefore.txt');
                        if(isset($element['ID'])||empty($arFields['XML_ID']))
                        {
                                $arFields['XML_ID']=getGUID();
                        }
                }
        return true;
    }
);

//Отправка запроса на обработчик при добавлении
$eventManager->addEventHandlerCompatible(
    'iblock',
    'OnAfterIBlockElementAdd',
    function( &$arFields )
    {
                if($arFields['IBLOCK_ID']==106 && $arFields['RESULT']>0)
                {

                        $number="NAW №".$arFields['ID'];
                        foreach($arFields['PROPERTY_VALUES'][492] as $ind=>$dogovor)
                        {
                                if(empty($dogovor['VALUE'])) {CIBlockElement::SetPropertyValuesEx($arFields['ID'], false, ['NOMER_DOGOVORA_FAKTICHESKIY'=>$number]); $arFields['PROPERTY_VALUES'][492][$ind]['VALUE']=$number;}

                        }
                        foreach($arFields['PROPERTY_VALUES'][468] as $ind=>$datadogovor)
                        {
                                if(empty($datadogovor['VALUE'])) {CIBlockElement::SetPropertyValuesEx($arFields['ID'], false, ['DATA_DOGOVORA_FAKTICHESKAYA'=>date("d.m.Y H:i:s", time())]); $arFields['PROPERTY_VALUES'][468][$ind]['VALUE']=date("d.m.Y H:i:s", time());}

                        }

                        if(substr($arFields['XML_ID'], 0, 3)=="B24"){
                                                Bitrix\Main\Diag\Debug::writeToFile($arFields, 'OnAfterIBlockElementADD', 'listsadd.txt');
                        post("DOGOVORADD",$arFields);   }
                }
                return $arFields;
    }
);

//Отправка запроса на обработчик при обновлении
$eventManager->addEventHandlerCompatible(
    'iblock',
    'OnAfterIBlockElementUpdate',
    function( &$arFields )
    {
                Bitrix\Main\Diag\Debug::writeToFile($arFields, 'OnAfterIBlockElementUpdate', 'listsupdate.txt');
                if($arFields['IBLOCK_ID']==106 && $arFields['PROPERTY_VALUES'][487] == 576 && $arFields['PROPERTY_VALUES'][488] == 578 && !empty($arFields['XML_ID']))
                {
                        Bitrix\Main\Diag\Debug::writeToFile($arFields, 'OnAfterIBlockElementUpdateif', 'listsupdate.txt');
                        post("DOGOVORUPDATE",$arFields);
                }
    }
);

function dum($array)
{
        echo "<pre>"; print_r($array); echo "</pre>";
        return true;
}

function fiotoid($fio)
{

        $fio=explode(" ",$fio);
$user = UserTable::getList([
    'select' => [
        'ID'
    ],
    'filter' => [
        'NAME'=>$fio[1],
                'LAST_NAME'=>$fio[0],
                "SECOND_NAME"=>$fio[2]
    ]
])->fetch();
        return $user['ID'];
}


function getGUID(){
    if (function_exists('com_create_guid')){
        return com_create_guid();
    }else{
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
      //  $hyphen = chr(45);//
        $uuid = "B24".substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12);// "}"
        return $uuid;
    }
}

function post($event, $body)
{
        $http = new \Bitrix\Main\Web\HttpClient();
    $http->setTimeout(5);
    $http->setStreamTimeout(50);
        $http->setHeader('Content-Type', 'application/x-www-form-urlencoded',true);
        $json = $http->post('https://домен/exchange/index.php',
                  [
                  'event' => $event,
                  'body' => $body
                  ]
           );
        return true;
}

//biotime получение токена
function auth()
{
$user="bitrix";
$pass="F9CEE3AD1FCF4F50A7348C10E22C5A33";
$login = BIOTIMELOGIN;
$password = BIOTIMEPASSWORD;

   $http = new \Bitrix\Main\Web\HttpClient();
   $http->setTimeout(5);
   $http->setStreamTimeout(50);
   $http->setAuthorization($user,$pass);
   $http->setHeader('Content-Type', 'application/x-www-form-urlencoded',true);
                //Проверка компании и получение данных к порталу
                $json = $http->post('http://домен:9000/oauth/token',
                [
                  'username' => $login,
                  'password' => $password,
                  'grant_type' => 'password'
                  ]);
           $result = \Bitrix\Main\Web\Json::decode($json);
           file_put_contents(__DIR__."/token.txt", $result['access_token']);
        return "auth();";
}

function status()
{
//получение индификаторов BIOTIME
                                $users = \Bitrix\Main\UserTable::getList(array(
                                        'filter' => array('=ACTIVE' => "Y", "!=UF_DEPARTMENT" =>""),
                                        'select'=>array('UF_BIOTIME'),
                                ))->fetchall();
return $users;
}
function code($name)
{
        $params = Array(
       "max_len" => "100", // обрезает символьный код до 100 символов
       "change_case" => "L", // буквы преобразуются к нижнему регистру
       "replace_space" => "_", // меняем пробелы на нижнее подчеркивание
       "replace_other" => "_", // меняем левые символы на нижнее подчеркивание
       "delete_repeat_replace" => "true", // удаляем повторяющиеся нижние подчеркивания
       "use_google" => "false", // отключаем использование google
    );


        $code = CUtil::translit($name, "ru" , $params);
        return $code;
}

//set_exception_handler(function(){
//    $arguments = func_get_args();
//    $arguments['trace'] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
//    \Bitrix\Main\Diag\Debug::dumpToFile($arguments, 'error_log', 'dump_exceptions.html');
//});

function biotime()
{
        require_once ($_SERVER["DOCUMENT_ROOT"].'/local/php/biotime/crest.php');
        $start = date('Y-m-d\TH:i:s', strtotime("-2 min"));
        $end = date("Y-m-d\TH:i:s");
                                $users = \Bitrix\Main\UserTable::getList(array(
                                        'filter' => array('=ACTIVE' => "Y", "!=UF_DEPARTMENT" =>"", "!=UF_BIOTIME" =>""),
                                        'select'=>array('ID','UF_BIOTIME'),
                                ))->fetchall();
                                foreach($users as $user)
                                {
                                /*      $statusbitrix=CRest::call('timeman.status',['USER_ID' =>$user['ID']]);
                                        if($statusbitrix['result']['STATUS']=="OPENED")
                                        {
                                                //$res=curlbot("POST","Events/Save",['item'=>['OwnerId' =>'5b0877b4-3894-30ac-e71b-3a006dbe9b1b',"CheckpointId"=>'7316f192-dbf8-11e5-f01d-3a01f0354203','ActivityType'=>'Logon' ,'EventTime' => date("Y-m-d\TH:i:s", time())]]);
                                                //dum($res);
                                                $user = new CUser;
                                                $user->Update($UserID, ["UF_REMOTE_STATUS_NOW" => 0]);
                                        }       */
                                        $mass[]=$user['UF_BIOTIME'];
                                }
                                $events=curl("POST","Events/GetForEmployeesInInterval",['start' =>$start,"end"=>$end, 'employeesId' => $mass]);
                                writeToLog1($events,"События");
                                foreach($events as $event)
                                {
                                        $UserID = biotobitrix($event['OwnerId']);
                                        $status=CRest::call('timeman.status',['USER_ID' =>$UserID]);
                                        writeToLog1($status,"События");
                                        if($event['EventType']==1)
                                                {
                                                        switch ($status['result']['STATUS'])
                                                        {
                                                                case "PAUSED":
                                                                        CRest::call('timeman.open',['USER_ID' =>$UserID]);
                                                                        break;
                                                                case "EXPIRED":
                                                                        CRest::call('timeman.close',['USER_ID' =>$UserID]);
                                                                        CRest::call('timeman.open',['USER_ID' =>$UserID]);
                                                                        break;
                                                                case "CLOSED":
                                                                        CRest::call('timeman.open',['USER_ID' =>$UserID]);
                                                                        break;
                                                        }

                                                        if($event['DeviceNumber']=='TelegramBot')
                                                        {
                                                                //обновление места работы сотрудника - удаленка
                                                                $user = new CUser;
                                                                $user->Update($UserID, ["UF_REMOTE_STATUS_NOW" => 0]);
                                                        }
                                                        else
                                                        {
                                                                //обновление места работы сотрудника - офис
                                                                $user = new CUser;
                                                                $user->Update($UserID, ["UF_REMOTE_STATUS_NOW" => 1]);

                                                        }
                                                }
                                        elseif($event['EventType']==2)
                                                {
                                                        switch ($status)
                                                        {
                                                                case "OPENED":
                                                                        CRest::call('timeman.pause',['USER_ID' =>$UserID]);
                                                                        break;
                                                        }
                                                        if($event['DeviceNumber']=='TelegramBot')
                                                        {
                                                                CRest::call('timeman.close',['USER_ID' =>$UserID]);
                                                        }
                                                }
                                }


   return "biotime();";
}

function curl($method, $url, $entityBody = null)
{
   $token=file_get_contents(__DIR__."/token.txt");
   $url=BIOTIME.$url;
  // dum($url);
   $http = new \Bitrix\Main\Web\HttpClient();
   $http->setTimeout(5);
   $http->setStreamTimeout(50);
   $http->setHeader('Authorization','Bearer '.$token, true);
 //  $http->setHeader('Content-Type', 'x-www-form-urlencoded',true);
   if($method=="GET")
   {
         if(!empty($entityBody)) $http->get($url."?".http_build_query($entityBody));
         else $http->get($url);
   }
   elseif($method=="POST")
        {
                $http->setHeader('Content-Type', 'application/json',true);
                $http->post($url."?".http_build_query($entityBody),true);
        }
//      dum(\Bitrix\Main\Web\Json::decode($http->getResult()));
//      dum(json_decode($http->getError()));
   if($http->getStatus()==200) return \Bitrix\Main\Web\Json::decode($http->getResult());
   else return \Bitrix\Main\Web\Json::decode($http->getError());
}

function logfile($textLog) {
$file = __DIR__.'/biotime.txt';
$text = '=======================<br>';
$text .= print_r($textLog,true);
$text .= '\n'. date('Y-m-d H:i:s') .'<br>'; //Добавим актуальную дату после текста или дампа массива
$fOpen = fopen($file,'a');
fwrite($fOpen, $text);
fclose($fOpen);
}

function biotobitrix($bio)
{
        $user = \Bitrix\Main\UserTable::getList(array(
                'filter' => array('=ACTIVE' => "Y", "UF_BIOTIME" =>$bio),
                'select'=>array('ID'),
        ))->fetch();
        return $user['ID'];
}

function htmltopdf($name)
{
                require_once($_SERVER['DOCUMENT_ROOT']. "/local/pdf/dompdf/dompdf_config.inc.php");
                //use Dompdf\Dompdf;
                $dompdf = new DOMPDF();
                $html = file_get_contents($_SERVER['DOCUMENT_ROOT'] . "/upload/pdf/".code($name).".html");
                $dompdf->load_html($html);
        //      $dompdf->setPaper('A4', 'portrait');
                //$dompdf->setPaper('A4', 'landscape');
                $dompdf->render();
                file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/upload/pdf/".$name.".pdf", $dompdf->output());
                //dum($dompdf->stream($_SERVER['DOCUMENT_ROOT'] . "/upload/pdf/".$name.".pdf"));

}

function writeToLog1($data, $title = '')
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

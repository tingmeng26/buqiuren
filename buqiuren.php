<?php

$response = getBuQiuRenData();
$sub = '%0D%0A';
$message = 'message='.$sub.'本日無不求人資料';
if(!empty($response)){
  $message = 'message='.$sub.'本日不求人資料如下';
  foreach($response as $key=>$row){
    $list = $key+1;
    $message.=$sub.$list.'：'.$row['ORG_NAME'];
    $message.=$sub.'職缺：'.$row['TITLE'];
    $message.=$sub.'位於：'.$row['WORK_PLACE_TYPE'];
    $message.=$sub.'詳情請點：'.$row['VIEW_URL'].$sub;
  }
}


$headers = [
  'Content-Type: application/x-www-form-urlencoded',
  'Authorization: Bearer Sv0HqJizpB1T267Qk20PKHsPBwE5c7VXrp39UI5um62'
];
// 備忘錄 Sv0HqJizpB1T267Qk20PKHsPBwE5c7VXrp39UI5um62
// 正式 Zl4QpMoFU2hPihfpDJNehMv1ARSuKWLcdYh7CVk8WYk
$result = postCurl("https://notify-api.line.me/api/notify", $message,$headers); 
echo $result;

function getBuQiuRenData(){
  $url = 'https://web3.dgpa.gov.tw/want03front/AP/WANTF00003.aspx';
  $data = [
    '__EVENTTARGET'=>'',
    '__EVENTARGUMENT'=>'',
    '__VIEWSTATE'=>'/ixr1Ll3gB7886sj5CoPohNIRPjWv+zHG5gtBBrtjW317Wx40v2UgMfnV5O7uUjZamtoKxv2hv0D5rwOtWHOZwl4xwxsfMeAGj6fmhrDjbRMMBDg7xtbF+pc5Ri3MXAbrV8DgesfImu/ziFOdCsFi2cvYcjL2VUPDFD1QGHDFA+iiD2y3wx+0h6DKfaMuRKstsb0USKJJoOcZEh45US4heXd7vA=',
    '__VIEWSTATEGENERATOR'=>'61F9EE84',
    '__VIEWSTATEENCRYPTED'=>'',
    'ctl00$ContentPlaceHolder1$btn_DownloadXML'=>'職缺 Open Data(XML)'
  ];
  $headers = [
    'content_type'=>'application/x-www-form-urlencoded',
    'user_agent'=>'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Safari/537.36',
    'referer'=>'https://web3.dgpa.gov.tw/want03front/AP/WANTF00003.aspx'
];
$response = postCurl($url,http_build_query($data),$headers);
$data = simplexml_load_string($response);
$data = $data->ROW;
$result = [];
foreach($data as $row){
  if(preg_match('/稅務/i',$row->TITLE)) {
    $row = (array)$row;
    array_push($result,$row);
  }
}
return $result;
}

function postCurl($url, $data, $headers = null, $debug = false, $CA = false, $CApem = "", $timeout = 30)
    {
        //網址,資料,header,返回錯誤訊息,https時驗證CA憑證,CA檔名,超時(秒)
        global $path_cacert;
        $result = "";
        $cacert = $path_cacert . $CApem;
        //CA根证书
        $SSL = substr($url, 0, 8) == "https://" ? true : false;
        if ($SSL && $CA && $CApem == "") {
            return "請指定CA檔名";
        }
        if ($headers == null) {
            $headers = [
                'Content-Type: application/x-www-form-urlencoded',
            ];
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        //允許執行的最長秒數
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout - 2);
        //連接前等待時間(0為無限)
        //$headers == '' ? '' : curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if ($SSL && $CA) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            // 驗證CA憑證
            curl_setopt($ch, CURLOPT_CAINFO, $cacert);
            // CA憑證檔案位置
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        } elseif ($SSL && !$CA) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            // 信任任何憑證
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $result = curl_exec($ch);
        if ($debug === true && curl_errno($ch)) {
            echo 'GCM error: ' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }

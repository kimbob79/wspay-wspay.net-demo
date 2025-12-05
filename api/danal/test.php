<?php
/* 
제작자 : 돌을든남자
설   명 : CURL 기반 병렬 소켓접속 처리. 
사용법 : $_result = curl_msocket(array('http://a.com/a.php', 'http://b.com/b.php'), array('file1' => 'aa.jpg', 'file2' => 'bb.jpg'), array('action' =>'upload'), "POST")
 
결과 : var_dump($_result);
*/
 
function curl_msocket($remote_url, $_files, $_arrays, $_type = "POST") {
 
  // 파일은 무조껀 POST 전송, 만약 배열타입이라면 다중파일이라 판단한다.
  if($_files){
 
    if(is_array($_files))
    {
      /* 멀티파일 업로드에 대하여 CURL 타입으로 변수를 재조합해 준다. */
      foreach($_files as $_fKey => $_fVal)
      {
        /* 실제 전송할 파일이 존재할 경우만 처리함. */
        if(file_exists($_fVal))
        {
          $postData[$_fKey]= "@".$_fVal;
        } // end of if
      }   // end of foreach
 
    } else {
 
      if(file_exists($_fVal))
      {
        $postData['upfile']= "@".$_files; 
      } // end of if
 
    }   // end of else
 
  } // 파일 변수가 존재하는지 확인함
 
 
 
  // 변수 넘김 처리.
  foreach($_arrays as $_pKey => $_pVal)
  {
    $postData[$_pKey] = $_pVal;
  }
 
  // 소켓 처리.
  $_minit   = curl_multi_init();
  $_resource  = array();                              // 반환값 저장
 
  /* foreach */
  foreach($remote_url as $_mkey => $_url)
  {
 
    // CURL 세션을 초기화함.     
    ${"init".$_mkey} = curl_init();
 
    curl_setopt(${"init".$_mkey}  , CURLOPT_HEADER      , 0);
    curl_setopt(${"init".$_mkey}  , CURLOPT_USERAGENT   , "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
    curl_setopt(${"init".$_mkey}  , CURLOPT_RETURNTRANSFER  , 1);       // 결과값 받을지 결정.
//    curl_setopt(${"init".$_mkey}  , CURLOPT_TIMEOUT     , 10);      // 타임아웃은 10 초로 설정 -- microtime 단위가 아님..
 
    /* 타입에 따른 데이터 처리 */
    switch(strtolower($_type))
    {
      case "post":
        curl_setopt(${"init".$_mkey}, CURLOPT_POST      , true);
        curl_setopt(${"init".$_mkey}, CURLOPT_URL     , $_url);
        curl_setopt(${"init".$_mkey}, CURLOPT_POSTFIELDS  , $postData);
      break;
 
      case "put":
        curl_setopt(${"init".$_mkey}, CURLOPT_PUT     , true);
        curl_setopt(${"init".$_mkey}, CURLOPT_URL     , $_url);
        curl_setopt(${"init".$_mkey}, CURLOPT_POSTFIELDS  , http_build_query($postData));
      break;
 
      case "get":
        curl_setopt(${"init".$_mkey}, CURLOPT_URL     , $_url."?".http_build_query($_arrays));
        curl_setopt(${"init".$_mkey}, CURLOPT_HTTPGET, TRUE);
      break;
 
      case "delete":
        curl_setopt(${"init".$_mkey}, CURLOPT_URL     , $_url);
        curl_setopt(${"init".$_mkey}, CURLOPT_POSTFIELDS  , $postData);
        curl_setopt(${"init".$_mkey}, CURLOPT_CUSTOMREQUEST , "DELETE");
      break;
    }
 
    // HTTPS 또는 HTTP에 따른 처리 
    if(substr(strtolower($_url), 0, 5) == "https"){
      curl_setopt(${"init".$_mkey}, CURLOPT_SSL_VERIFYPEER  , FALSE);
      curl_setopt(${"init".$_mkey}, CURLOPT_SSL_VERIFYHOST  , 0);
//        curl_setopt(${"init".$_mkey}, CURLOPT_SSLVERSION    , 3);         // SSL 버젼 (https 접속시에 필요)
    }
 
    curl_multi_add_handle($_minit , ${"init".$_mkey});                // CURL 핸들 넣기.
 
  }
 
 
  // 소켓 접속 실행.
  $running = NULL;                                  // 리턴값의 초기화
  do {
    usleep(1000000);  // 1초..
    curl_multi_exec($_minit, $running);
  } while ($running > 0);
 
  // 컨텐츠 펌질후 완료된 소켓은 종료함.
  foreach($remote_url as $_mkey => $_url)
  {
    $_resource[$_mkey] = curl_multi_getcontent(${"init".$_mkey});
    curl_multi_remove_handle($_minit  , ${"init".$_mkey});
  }
 
  curl_multi_close($_minit);    // 소켓종료..
  return $_resource;
 
}
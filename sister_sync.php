<?php
/**
 * Created by fmarzuki on 3 May 2018
 * Script untuk menjalankan sinkronisasi SISTER Universitas dengan SISTER Pusat, dijalankan melalui cron
 */

// Login API sister untuk mendapatkan Token untuk sync, informasi User dan Password didapatkan dari akun Admin PT sister
function login(){
$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "http://ipaddress-sister-univ/api.php/0.1/Login",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => "{\n  \"username\": \"isidenganusername\",\n  \"password\": \"isidenganpassword\",\n\t\"id_pengguna\" : \"isidenganidpengguna\"\n}",
  CURLOPT_COOKIE => "PHPSESSID=tgdlq1gonnt3sl1bf5b38ghsu4",
  CURLOPT_HTTPHEADER => array(
    "content-type: application/json"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
	$response = (array)json_decode($response, true);
	return $token = $response['data']['id_token'];
	}
}

// Log file
function logToFile($filename, $msg)
   { 
   $fd = fopen($filename, "a");
   $str = "[" . date("Y-m-d H:i:s", mktime()) . "] " . $msg; 
   fwrite($fd, $str . "\n");
   fclose($fd);
   } 
   
// Dapatkan login token sister melalui fungsi login
$token = login();
$data = json_encode(array(
			"id_token"  => $token
));

// Mulai proses sync dengan token yang sudah didapatkan
$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => "http://ipaddress-sister-univ/api.php/0.1/Sync",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 200,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => $data,
  CURLOPT_COOKIE => "PHPSESSID=tgdlq1gonnt3sl1bf5b38ghsu4",
  CURLOPT_HTTPHEADER => array(
      "accept: application/json",
      "content-type: application/json",
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  $response = (array)json_decode($response, true);
  // jika tidak ada error berarti sync sukses dan simpan log ke file
  if ($response['error_code'] == 0){ 
      logToFile("success_sister.log", "Sukses sync memakai token ". $token);
  }else {
      logToFile("error_sister.log", "Gagal sync memakai token ". $token);
  }
  
}

<?php
ob_start();
session_start();
header('Content-Type: text/html; charset=utf-8');
	$data= (($_SESSION['sendxml']));



     $sendxml = '<KuveytTurkVPosMessage xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">'
			.'<APIVersion>'.$data['APIVersion'].'</APIVersion>'
			.'<OkUrl>'.$data['OkUrl'].'</OkUrl>'
			.'<FailUrl>'.$data['FailUrl'].'</FailUrl>'
			.'<HashData>'.$data['HashData'].'</HashData>'
			.'<MerchantId>'.$data['MerchantId'].'</MerchantId>'
			.'<CustomerId>'.$data['CustomerId'].'</CustomerId>'
			.'<UserName>'.$data['UserName'].'</UserName>'
			.'<CardNumber>'.$data['CardNumber'].'</CardNumber>'
			.'<CardExpireDateYear>'.$data['CardExpireDateYear'].'</CardExpireDateYear>'
			.'<CardExpireDateMonth>'.$data['CardExpireDateMonth'].'</CardExpireDateMonth>'
			.'<CardCVV2>'.$data['CardCVV2'].'</CardCVV2>'
			.'<CardHolderName>'.$data['CardHolderName'].'</CardHolderName>'
			.'<CardType>'.$data['CardType'].'</CardType>'
			.'<BatchID>0</BatchID>'
			.'<TransactionType>'.$data['TransactionType'].'</TransactionType>'
			.'<InstallmentCount>0</InstallmentCount>'
			.'<Amount>'.$data['Amount'].'</Amount>'
			.'<DisplayAmount>'.$data['Amount'].'</DisplayAmount>'
			.'<CurrencyCode>'.$data['CurrencyCode'].'</CurrencyCode>'
			.'<MerchantOrderId>'.$data['MerchantOrderId'].'</MerchantOrderId>'
			.'<TransactionSecurity>3</TransactionSecurity>'
			.'</KuveytTurkVPosMessage>';
try {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POST, true); //POST Metodu kullanarak verileri gönder
	curl_setopt($ch, CURLOPT_HEADER, false); //Serverdan gelen Header bilgilerini önemseme.
	curl_setopt($ch, CURLOPT_URL,'https://boatest.kuveytturk.com.tr/boa.virtualpos.services/Home/ThreeDModelPayGate'); //Baglanacagi URL https://boa.kuveytturk.com.tr/sanalposservice/
	curl_setopt($ch, CURLOPT_POSTFIELDS,$sendxml);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //Transfer sonuçlarini al.
	echo $dataf = curl_exec($ch);
	curl_close($ch);
}
	catch (Exception $e) {
	echo 'Caught exception: ',  $e->getMessage(), "\n";
 }


	echo($data);
	error_reporting(E_ALL);
	ini_set("display_errors", 1);
?>
BANKAYA YÖNLENDİRİLİYORSUNUZ

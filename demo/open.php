<?php

require_once __DIR__ . "/../src/UnionPay.php";
require_once __DIR__ . "/../src/UnionPayDirect.php";
require_once __DIR__ . "/../src/HttpClient.php";
use zhangv\unionPay\UnionPay;
use zhangv\unionPay\UnionPayDirect;

list($mode,$config) = include './config-direct.php';
$unionPay = new UnionPayDirect($config,$mode);

$payOrderNo = date('YmdHis');
$amt = 1;
$desc = 'desc';
$testAcc = $config['testAcc'][0];
$accNo = $testAcc['accNo'];

$customerInfo = [
	'phoneNo' => $testAcc['phoneNo'], //手机号
	'certifTp' => $testAcc['certifTp'], //证件类型，01-身份证
	'certifId' => $testAcc['certifId'], //证件号，15位身份证不校验尾号，18位会校验尾号，请务必在前端写好校验代码
	'customerNm' => $testAcc['customerNm'], //姓名
];
$form = $unionPay->frontOpen($payOrderNo,$accNo,$customerInfo);
echo $form;
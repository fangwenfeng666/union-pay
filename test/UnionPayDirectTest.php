<?php
/**
 * Created by PhpStorm.
 * User: derekzhangv
 * Date: 2018/4/10
 * Time: 10:52
 */
require_once __DIR__ . '/../src/UnionPayDirect.php';
use zhangv\unionpay\UnionPayDirect;
use PHPUnit\Framework\TestCase;

class UnionPayDirectTest extends TestCase{
	/** @var  UnionPayDirect */
	private $unionPay;
	private $config;
	public function setUp(){
		list($mode,$this->config) = include_once __DIR__ .'/../demo/config-direct.php';
		$this->unionPay = new UnionPayDirect($this->config,$mode);
	}

	/**
	 * 测试商户号仅支持前台开通，后台开通：无此交易权限。（需要用真实商户号测试）
	 * @test
	 * @expectedException Exception
	 */
	public function backOpen(){
		$orderId = date('YmdHis');
		$testAcc = $this->config['testAcc'][1];
		$accNo = $testAcc['accNo'];
		$customerInfo = array(
			'phoneNo' => $testAcc['phoneNo'], //手机号
			'cvn2' => $testAcc['cvn2'], //cvn2
			'expired' => $testAcc['expired'], //有效期，YYMM格式，持卡人卡面印的是MMYY的，请注意代码设置倒一下
			'smsCode' => '111111', //短信验证码
		);
		$r = $this->unionPay->backOpen($orderId,$accNo,$customerInfo);
	}

	/**
	 * @test
	 */
	public function queryOpen(){
		$orderId = date('YmdHis');
		$testAcc = $this->config['testAcc'][0];
		$accNo = $testAcc['accNo'];
		$r = $this->unionPay->queryOpen($orderId,$accNo);
		$this->assertEquals('1',$r['activateStatus']);
	}
	/**
	 * @test
	 */
	public function sms(){
		$orderId = date('YmdHis');
		$testAcc = $this->config['testAcc'][0];
		$accNo = $testAcc['accNo'];
		$customerInfo = array(
			'phoneNo' => $testAcc['phoneNo'],
		);
		$r = $this->unionPay->sms($orderId,$accNo,$customerInfo);
		$this->assertEquals('00',$r['respCode']);
	}

	/**
	 * @test
	 */
	public function pay(){
		$orderId = date('YmdHis');
		$testAcc = $this->config['testAcc'][2];
		$accNo = $testAcc['accNo'];
		$ext = [
			'accNo' => $accNo,
			'customerInfo' => ['smsCode' => '111111']
		];
		$r = $this->unionPay->pay($orderId,1000,'','',$ext);
		$this->assertEquals('00',$r['respCode']);
	}

	/**
	 * @test
	 */
	public function encryptData(){
		$accNo = '6226388000000095';
		$r1 = $this->unionPay->encryptData($accNo);
		$r2 = $this->unionPay->encryptData($accNo);
		$this->assertNotEquals($r1,$r2);
	}

	/**
	 * 无此交易权限
	 * @test
	 * @expectedException Exception
	 * @expectedExceptionMessageRegExp  /无此交易权限/
	 */
	public function payByInstallment(){
		$orderId = date('YmdHis');
		$testAcc = $this->config['testAcc'][1];
		$ext = [
			'accNo' => $testAcc['accNo'],
			'customerInfo' => ['smsCode' => '111111'],
			'instalTransInfo' => $testAcc['instalTransInfo']
		];
		$this->unionPay->payByInstallment($orderId,100,'','',$ext);
	}

	/**
	 * @test
	 */
	public function testVerifySignature(){
		$vstr = "accNo=cjsqgmTALZk1Rcb/l0GL+WKoExXkdZPv+kEezrB0+qpw0qQNNXjCDnV65peho8RyqPchKR3uX22Ov9A5mkUsoUtQD8Z9p1dBxv/s0C+fZOLHJz3LkJJL8xDgfAS7OGghS7gKRJt05S5WDnC5SBoIvb5+PFCB9gjOEJrOBYE3YgwBqQ/UQbPpVsk5FnOKlYQyHC5Z/BBz5YhUbarjAKwBN8aY3aLpD+PN0ii535XuMV2ZTnnkKvVtiWNHHZf5HOD5qgUOR83QSAQSEw6/5inRqI6miWCbAVeidk0JbOIqbElXUeiPDwFvGx6DmBWsydqKI4iQsfYBIrdScevzZnGvHg==&accessType=0&bizType=000301&currencyCode=156&encoding=utf-8&merId=777290058158470&orderId=20180414025538&queryId=121804140255385818028&respCode=00&respMsg=成功[0000000]&signMethod=01&signPubKeyCert=-----BEGIN CERTIFICATE-----
MIIEQzCCAyugAwIBAgIFEBJJZVgwDQYJKoZIhvcNAQEFBQAwWDELMAkGA1UEBhMC
Q04xMDAuBgNVBAoTJ0NoaW5hIEZpbmFuY2lhbCBDZXJ0aWZpY2F0aW9uIEF1dGhv
cml0eTEXMBUGA1UEAxMOQ0ZDQSBURVNUIE9DQTEwHhcNMTcxMTAxMDcyNDA4WhcN
MjAxMTAxMDcyNDA4WjB3MQswCQYDVQQGEwJjbjESMBAGA1UEChMJQ0ZDQSBPQ0Ex
MQ4wDAYDVQQLEwVDVVBSQTEUMBIGA1UECxMLRW50ZXJwcmlzZXMxLjAsBgNVBAMU
JTA0MUBaMjAxNy0xMS0xQDAwMDQwMDAwOlNJR05AMDAwMDAwMDEwggEiMA0GCSqG
SIb3DQEBAQUAA4IBDwAwggEKAoIBAQDDIWO6AESrg+34HgbU9mSpgef0sl6avr1d
bD/IjjZYM63SoQi3CZHZUyoyzBKodRzowJrwXmd+hCmdcIfavdvfwi6x+ptJNp9d
EtpfEAnJk+4quriQFj1dNiv6uP8ARgn07UMhgdYB7D8aA1j77Yk1ROx7+LFeo7rZ
Ddde2U1opPxjIqOPqiPno78JMXpFn7LiGPXu75bwY2rYIGEEImnypgiYuW1vo9UO
G47NMWTnsIdy68FquPSw5FKp5foL825GNX3oJSZui8d2UDkMLBasf06Jz0JKz5AV
blaI+s24/iCfo8r+6WaCs8e6BDkaijJkR/bvRCQeQpbX3V8WoTLVAgMBAAGjgfQw
gfEwHwYDVR0jBBgwFoAUz3CdYeudfC6498sCQPcJnf4zdIAwSAYDVR0gBEEwPzA9
BghggRyG7yoBATAxMC8GCCsGAQUFBwIBFiNodHRwOi8vd3d3LmNmY2EuY29tLmNu
L3VzL3VzLTE0Lmh0bTA5BgNVHR8EMjAwMC6gLKAqhihodHRwOi8vdWNybC5jZmNh
LmNvbS5jbi9SU0EvY3JsMjQ4NzIuY3JsMAsGA1UdDwQEAwID6DAdBgNVHQ4EFgQU
mQQLyuqYjES7qKO+zOkzEbvdFwgwHQYDVR0lBBYwFAYIKwYBBQUHAwIGCCsGAQUF
BwMEMA0GCSqGSIb3DQEBBQUAA4IBAQAujhBuOcuxA+VzoUH84uoFt5aaBM3vGlpW
KVMz6BUsLbIpp1ho5h+LaMnxMs6jdXXDh/du8X5SKMaIddiLw7ujZy1LibKy2jYi
YYfs3tbZ0ffCKQtv78vCgC+IxUUurALY4w58fRLLdu8u8p9jyRFHsQEwSq+W5+bP
MTh2w7cDd9h+6KoCN6AMI1Ly7MxRIhCbNBL9bzaxF9B5GK86ARY7ixkuDCEl4XCF
JGxeoye9R46NqZ6AA/k97mJun//gmUjStmb9PUXA59fR5suAB5o/5lBySZ8UXkrI
pp/iLT8vIl1hNgLh0Ghs7DBSx99I+S3VuUzjHNxL6fGRhlix7Rb8
-----END CERTIFICATE-----&txnAmt=1000&txnSubType=01&txnTime=20180414025538&txnType=01&version=5.1.0";
		//line separator!!!!
		$vstr = preg_replace("/\r\n|\r|\n/m", "\r\n", $vstr);
		var_dump($vstr);
		$sha256 = hash('sha256', $vstr);
		$this->assertEquals("ba3adf0b7276ac823d063aefa672d045a0647ba2f248ddfbe3c4054e5d6d95d5", $sha256);
		$sig = "f5Gz5srn7RvdF2qtAHcakoiwVbSO8cOf9CVX9AJ3oCyjxsdTTXQmx+JQZ8Aw1y2ON+dvFxWC5Z4X/lOmQRSXs3fUZWaErWkgTqBO9Wrl5x3f6FgnB3sGuCXSPs/fm/mXhzv3LVrsmx2EmAxgsuDc7U+eRej/kfwSqI3E2wgHdteQW9jVhG8hxllO7yu9OTfcoPlo87quisMtggeXrfprpuBWKRTPRqsWUypP3+cskVZmc65XL7AGsz74HhS5kwZ9Sc2LejrQKC73Q4wzREdwKUwiPAnoL96ryDqca5+RT1WYq9u3YtxjQUzFTTXypMtZlH92P++MK+rppE9ck5rpyg==";

		$sig = base64_decode($sig);
		$signPubKeyCert = "-----BEGIN CERTIFICATE-----
MIIEQzCCAyugAwIBAgIFEBJJZVgwDQYJKoZIhvcNAQEFBQAwWDELMAkGA1UEBhMC
Q04xMDAuBgNVBAoTJ0NoaW5hIEZpbmFuY2lhbCBDZXJ0aWZpY2F0aW9uIEF1dGhv
cml0eTEXMBUGA1UEAxMOQ0ZDQSBURVNUIE9DQTEwHhcNMTcxMTAxMDcyNDA4WhcN
MjAxMTAxMDcyNDA4WjB3MQswCQYDVQQGEwJjbjESMBAGA1UEChMJQ0ZDQSBPQ0Ex
MQ4wDAYDVQQLEwVDVVBSQTEUMBIGA1UECxMLRW50ZXJwcmlzZXMxLjAsBgNVBAMU
JTA0MUBaMjAxNy0xMS0xQDAwMDQwMDAwOlNJR05AMDAwMDAwMDEwggEiMA0GCSqG
SIb3DQEBAQUAA4IBDwAwggEKAoIBAQDDIWO6AESrg+34HgbU9mSpgef0sl6avr1d
bD/IjjZYM63SoQi3CZHZUyoyzBKodRzowJrwXmd+hCmdcIfavdvfwi6x+ptJNp9d
EtpfEAnJk+4quriQFj1dNiv6uP8ARgn07UMhgdYB7D8aA1j77Yk1ROx7+LFeo7rZ
Ddde2U1opPxjIqOPqiPno78JMXpFn7LiGPXu75bwY2rYIGEEImnypgiYuW1vo9UO
G47NMWTnsIdy68FquPSw5FKp5foL825GNX3oJSZui8d2UDkMLBasf06Jz0JKz5AV
blaI+s24/iCfo8r+6WaCs8e6BDkaijJkR/bvRCQeQpbX3V8WoTLVAgMBAAGjgfQw
gfEwHwYDVR0jBBgwFoAUz3CdYeudfC6498sCQPcJnf4zdIAwSAYDVR0gBEEwPzA9
BghggRyG7yoBATAxMC8GCCsGAQUFBwIBFiNodHRwOi8vd3d3LmNmY2EuY29tLmNu
L3VzL3VzLTE0Lmh0bTA5BgNVHR8EMjAwMC6gLKAqhihodHRwOi8vdWNybC5jZmNh
LmNvbS5jbi9SU0EvY3JsMjQ4NzIuY3JsMAsGA1UdDwQEAwID6DAdBgNVHQ4EFgQU
mQQLyuqYjES7qKO+zOkzEbvdFwgwHQYDVR0lBBYwFAYIKwYBBQUHAwIGCCsGAQUF
BwMEMA0GCSqGSIb3DQEBBQUAA4IBAQAujhBuOcuxA+VzoUH84uoFt5aaBM3vGlpW
KVMz6BUsLbIpp1ho5h+LaMnxMs6jdXXDh/du8X5SKMaIddiLw7ujZy1LibKy2jYi
YYfs3tbZ0ffCKQtv78vCgC+IxUUurALY4w58fRLLdu8u8p9jyRFHsQEwSq+W5+bP
MTh2w7cDd9h+6KoCN6AMI1Ly7MxRIhCbNBL9bzaxF9B5GK86ARY7ixkuDCEl4XCF
JGxeoye9R46NqZ6AA/k97mJun//gmUjStmb9PUXA59fR5suAB5o/5lBySZ8UXkrI
pp/iLT8vIl1hNgLh0Ghs7DBSx99I+S3VuUzjHNxL6fGRhlix7Rb8
-----END CERTIFICATE-----";
		$result = openssl_verify($sha256, $sig, $signPubKeyCert, "sha256");
		$this->assertEquals(1,$result);
	}

	/**
	 * @test
	 */
	public function temp(){
		$vstr = "accNo=rpgqsHCR39+nQ1KhFvMGB7XDo4EYV2CVoM64Xf6QjMCwbPU6RAfi6awoi9I21Tp7fil9qr5G+dXw492duUFEiN3ctv1HYUe2ar+1TtET4meITiTEYn11gKVhfBX1X5BLPEVe+3uPw5Gp3OmXHnQmgENyK4xjWuvbaOf/9BJVmaFhMFlVmS0FSOgHaeeSDB0UCXvRfTmrWfoPQOveCec6Lvy8w5huuf4vbfrZwlniYISVvTvarafP+ZOHKKxmuFpooSQ1WJX8rJZ6E7qw42YZaGSD8Wl81PS/0eBRP5instVoaSAyyTX8k/5roQqgB7LcSo9FIus6Q54vVpBqRB4wEw==&accessType=0&bizType=000301&currencyCode=156&encoding=utf-8&merId=777290058158470&orderId=20180415035146&queryId=151804150351466461028&respCode=00&respMsg=成功[0000000]&signMethod=01&txnAmt=1000&txnSubType=01&txnTime=20180415035146&txnType=01&version=5.1.0&signPubKeyCert=-----BEGIN CERTIFICATE-----
MIIEQzCCAyugAwIBAgIFEBJJZVgwDQYJKoZIhvcNAQEFBQAwWDELMAkGA1UEBhMC
Q04xMDAuBgNVBAoTJ0NoaW5hIEZpbmFuY2lhbCBDZXJ0aWZpY2F0aW9uIEF1dGhv
cml0eTEXMBUGA1UEAxMOQ0ZDQSBURVNUIE9DQTEwHhcNMTcxMTAxMDcyNDA4WhcN
MjAxMTAxMDcyNDA4WjB3MQswCQYDVQQGEwJjbjESMBAGA1UEChMJQ0ZDQSBPQ0Ex
MQ4wDAYDVQQLEwVDVVBSQTEUMBIGA1UECxMLRW50ZXJwcmlzZXMxLjAsBgNVBAMU
JTA0MUBaMjAxNy0xMS0xQDAwMDQwMDAwOlNJR05AMDAwMDAwMDEwggEiMA0GCSqG
SIb3DQEBAQUAA4IBDwAwggEKAoIBAQDDIWO6AESrg+34HgbU9mSpgef0sl6avr1d
bD/IjjZYM63SoQi3CZHZUyoyzBKodRzowJrwXmd+hCmdcIfavdvfwi6x+ptJNp9d
EtpfEAnJk+4quriQFj1dNiv6uP8ARgn07UMhgdYB7D8aA1j77Yk1ROx7+LFeo7rZ
Ddde2U1opPxjIqOPqiPno78JMXpFn7LiGPXu75bwY2rYIGEEImnypgiYuW1vo9UO
G47NMWTnsIdy68FquPSw5FKp5foL825GNX3oJSZui8d2UDkMLBasf06Jz0JKz5AV
blaI+s24/iCfo8r+6WaCs8e6BDkaijJkR/bvRCQeQpbX3V8WoTLVAgMBAAGjgfQw
gfEwHwYDVR0jBBgwFoAUz3CdYeudfC6498sCQPcJnf4zdIAwSAYDVR0gBEEwPzA9
BghggRyG7yoBATAxMC8GCCsGAQUFBwIBFiNodHRwOi8vd3d3LmNmY2EuY29tLmNu
L3VzL3VzLTE0Lmh0bTA5BgNVHR8EMjAwMC6gLKAqhihodHRwOi8vdWNybC5jZmNh
LmNvbS5jbi9SU0EvY3JsMjQ4NzIuY3JsMAsGA1UdDwQEAwID6DAdBgNVHQ4EFgQU
mQQLyuqYjES7qKO+zOkzEbvdFwgwHQYDVR0lBBYwFAYIKwYBBQUHAwIGCCsGAQUF
BwMEMA0GCSqGSIb3DQEBBQUAA4IBAQAujhBuOcuxA+VzoUH84uoFt5aaBM3vGlpW
KVMz6BUsLbIpp1ho5h+LaMnxMs6jdXXDh/du8X5SKMaIddiLw7ujZy1LibKy2jYi
YYfs3tbZ0ffCKQtv78vCgC+IxUUurALY4w58fRLLdu8u8p9jyRFHsQEwSq+W5+bP
MTh2w7cDd9h+6KoCN6AMI1Ly7MxRIhCbNBL9bzaxF9B5GK86ARY7ixkuDCEl4XCF
JGxeoye9R46NqZ6AA/k97mJun//gmUjStmb9PUXA59fR5suAB5o/5lBySZ8UXkrI
pp/iLT8vIl1hNgLh0Ghs7DBSx99I+S3VuUzjHNxL6fGRhlix7Rb8
-----END CERTIFICATE-----";
		//line separator!!!!
//		$vstr = preg_replace("/\r\n|\r|\n/m", "\r\n", $vstr);
		var_dump($vstr);
		$sha256 = hash('sha256', $vstr);
		$this->assertEquals("fa354403cc1ffbdff607da87c7350ceb9c9e3115f45b5f689eee6060fbf5ffeb", $sha256);
		$sig = "sCCp/WQAqNKCUrI8Zx40ZV0TYnYZdpi4zqGIgoxB9rvRKHLwrjWWDDpMOC3BcuIRzYo/z33P1ZmU30EIqr73Q8CSCN1G7Lb2na5CG64beO6kgFDPdcAzszJwOnNP+U720rVS7MBKZhBEXlXnpLv23zolNfvzHmoIUT3wVbKiK70yfwwS4cGXX+BdLGI4i3uEsDU3oz+94jk8w/fb0240ZBANeP9sRmi8ZpQv0ryN78OKwsYLSAZj6J/ghsydJe384STxxMLlQzIIKG6qo6b4K8bQHhvoHEjl07HvCtOYqlPDCRmBxqavosEV963l6NIf8sq61hV3Yig00qpMfILDeQ==";

		$sig = base64_decode($sig);
		var_dump($sig);
		$signPubKeyCert = "-----BEGIN CERTIFICATE-----
MIIEQzCCAyugAwIBAgIFEBJJZVgwDQYJKoZIhvcNAQEFBQAwWDELMAkGA1UEBhMC
Q04xMDAuBgNVBAoTJ0NoaW5hIEZpbmFuY2lhbCBDZXJ0aWZpY2F0aW9uIEF1dGhv
cml0eTEXMBUGA1UEAxMOQ0ZDQSBURVNUIE9DQTEwHhcNMTcxMTAxMDcyNDA4WhcN
MjAxMTAxMDcyNDA4WjB3MQswCQYDVQQGEwJjbjESMBAGA1UEChMJQ0ZDQSBPQ0Ex
MQ4wDAYDVQQLEwVDVVBSQTEUMBIGA1UECxMLRW50ZXJwcmlzZXMxLjAsBgNVBAMU
JTA0MUBaMjAxNy0xMS0xQDAwMDQwMDAwOlNJR05AMDAwMDAwMDEwggEiMA0GCSqG
SIb3DQEBAQUAA4IBDwAwggEKAoIBAQDDIWO6AESrg+34HgbU9mSpgef0sl6avr1d
bD/IjjZYM63SoQi3CZHZUyoyzBKodRzowJrwXmd+hCmdcIfavdvfwi6x+ptJNp9d
EtpfEAnJk+4quriQFj1dNiv6uP8ARgn07UMhgdYB7D8aA1j77Yk1ROx7+LFeo7rZ
Ddde2U1opPxjIqOPqiPno78JMXpFn7LiGPXu75bwY2rYIGEEImnypgiYuW1vo9UO
G47NMWTnsIdy68FquPSw5FKp5foL825GNX3oJSZui8d2UDkMLBasf06Jz0JKz5AV
blaI+s24/iCfo8r+6WaCs8e6BDkaijJkR/bvRCQeQpbX3V8WoTLVAgMBAAGjgfQw
gfEwHwYDVR0jBBgwFoAUz3CdYeudfC6498sCQPcJnf4zdIAwSAYDVR0gBEEwPzA9
BghggRyG7yoBATAxMC8GCCsGAQUFBwIBFiNodHRwOi8vd3d3LmNmY2EuY29tLmNu
L3VzL3VzLTE0Lmh0bTA5BgNVHR8EMjAwMC6gLKAqhihodHRwOi8vdWNybC5jZmNh
LmNvbS5jbi9SU0EvY3JsMjQ4NzIuY3JsMAsGA1UdDwQEAwID6DAdBgNVHQ4EFgQU
mQQLyuqYjES7qKO+zOkzEbvdFwgwHQYDVR0lBBYwFAYIKwYBBQUHAwIGCCsGAQUF
BwMEMA0GCSqGSIb3DQEBBQUAA4IBAQAujhBuOcuxA+VzoUH84uoFt5aaBM3vGlpW
KVMz6BUsLbIpp1ho5h+LaMnxMs6jdXXDh/du8X5SKMaIddiLw7ujZy1LibKy2jYi
YYfs3tbZ0ffCKQtv78vCgC+IxUUurALY4w58fRLLdu8u8p9jyRFHsQEwSq+W5+bP
MTh2w7cDd9h+6KoCN6AMI1Ly7MxRIhCbNBL9bzaxF9B5GK86ARY7ixkuDCEl4XCF
JGxeoye9R46NqZ6AA/k97mJun//gmUjStmb9PUXA59fR5suAB5o/5lBySZ8UXkrI
pp/iLT8vIl1hNgLh0Ghs7DBSx99I+S3VuUzjHNxL6fGRhlix7Rb8
-----END CERTIFICATE-----";
		$result = openssl_verify($sha256, $sig, $signPubKeyCert, "sha256");
		$this->assertEquals(1,$result);
	}
}

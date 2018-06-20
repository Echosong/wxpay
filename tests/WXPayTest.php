<?php
require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/Config.php';

use WXPay\WXPay;

/**
 * 测试查询订单
 */
function test_orderQuery() {
    $useSandbox = true;
    $wxpay = new WXPay(
        Config::WXPAY_APPID,
        Config::WXPAY_MCHID,
        Config::WXPAY_KEY,
        Config::WXPAY_CERTPEMPATH,
        Config::WXPAY_KEYPEMPATH,
        6000,   // 网络超时时间，单位是毫秒
        \WXPay\WXPayConstants::SIGN_TYPE_MD5,
        $useSandbox);

    var_dump( $wxpay->orderQuery(array('out_trade_no' => '201610265257070987061763')) );

}

// test_orderQuery();

/**
 * 测试退款
 */
function test_refund() {
    $reqData = array(
        'out_trade_no' => '201610265257070987061763',
        'out_refund_no' => '201610265257070987061763',
        'total_fee' => 1,
        'refund_fee' => 1,
        'op_user_id' => '100'
    );
    $wxpay = new WXPay(
        Config::WXPAY_APPID,
        Config::WXPAY_MCHID,
        Config::WXPAY_KEY,
        Config::WXPAY_CERTPEMPATH,
        Config::WXPAY_KEYPEMPATH,
        6000); // 网络超时时间，单位是毫秒

    var_dump($wxpay->refund($reqData));
}

// test_refund();


/**
 * 测试下载对账单
 */
function test_downloadBill() {
    $reqData = array(
        'bill_date' => '20161102',
        'bill_type' => 'ALL'
    );

    $wxpay = new WXPay(
        Config::WXPAY_APPID,
        Config::WXPAY_MCHID,
        Config::WXPAY_KEY,
        Config::WXPAY_CERTPEMPATH,
        Config::WXPAY_KEYPEMPATH,
        6000);  // 网络超时时间，单位是毫秒

    var_dump( $wxpay->downloadBill($reqData, 10000) );  // 第 2 个参数是超时时间，单位毫秒。这里设置了10000，6000就用不到了。
}

// test_downloadBill();

function test_fillRequestData() {
    $data = array(
        'bill_date' => '20140603',
        'bill_type' => 'ALL',
        'tar_type' => 'GZIP'
    );
    $wxpay = new WXPay(
        Config::WXPAY_APPID,
        Config::WXPAY_MCHID,
        Config::WXPAY_KEY,
        Config::WXPAY_CERTPEMPATH,
        Config::WXPAY_KEYPEMPATH,
        6000);
    $data = $wxpay->fillRequestData($data);
    var_dump($data);
    echo "\n";
    echo \WXPay\WXPayUtil::array2xml($data);
}

// test_fillRequestData();


function test_isPayResultNotifySignatureValid() {
    $wxpay = new WXPay(
        Config::WXPAY_APPID,
        Config::WXPAY_MCHID,
        "192006250b4c09247ec02edce69f6a2d",
        Config::WXPAY_CERTPEMPATH,
        Config::WXPAY_KEYPEMPATH,
        6000);
    $xml = "<xml> 
        <appid>wxd930ea5d5a258f4f</appid> 
        <mch_id>10000100</mch_id> 
        <device_info>1000</device_info> 
        <body>test</body> 
        <nonce_str>ibuaiVcKdpRxkhJA</nonce_str> 
        <sign>9A0A8659F005D6984697E2CA0A9CF3B7</sign> 
        </xml>";
    $data = \WXPay\WXPayUtil::xml2array($xml);
    var_dump($wxpay->isPayResultNotifySignatureValid($data));
}
test_isPayResultNotifySignatureValid();
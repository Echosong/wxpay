<?php

require __DIR__.'/../vendor/autoload.php';

use WXPay\WXPayUtil;


function test_xml2array()
{
    $xmlArray = WXPayUtil::xml2array('<xml><name>xiaoming</name><age>10</age></xml>');
    var_dump($xmlArray);

    $xmlArray = WXPayUtil::xml2array('<xml><name>xiaoming</name><data2><![CDATA[hi ]]></data2> <age>10</age></xml>');
    var_dump($xmlArray);

    $xmlArray = WXPayUtil::xml2array('<root><name>xiaoming</name><data2><![CDATA[hi ]]></data2> <age>10</age></root>');
    var_dump($xmlArray);

    $xmlArray = WXPayUtil::xml2array('<?xml version="1.0"?><root><name>xiaoming&lt;</name><data2><![CDATA[hi ]]></data2> <age>10</age></root>');
    var_dump($xmlArray);

    echo '有一个字段为空';
    $xmlArray = WXPayUtil::xml2array('<?xml version="1.0"?><root><name></name><data2><![CDATA[hi ]]></data2> <age>10</age></root>');
    var_dump($xmlArray);

    echo '有一个字段为空字符串';
    $xmlArray = WXPayUtil::xml2array('<?xml version="1.0"?><root><name> </name><data2><![CDATA[hi ]]></data2> <age>10</age></root>');
    var_dump($xmlArray);

    echo '字段重复';
    $xmlArray = WXPayUtil::xml2array('<xml><name>xiaoming</name> <name>xiaoming2 </name> <data2><![CDATA[hi]]></data2> <age>10</age></xml>');
    var_dump($xmlArray);
}

// test_xml2array();

function test_array2xml()
{
    echo '--1';
    $data = array(
        'name'=>'xiaoming<',
        'age' => 20
    );
    echo WXPayUtil::array2xml($data);

    echo '--2';
    $data = array(
        'name'=>'xiaoming<',
        'name'=>'xiaoming2>',
        'age' => 20
    );
    echo WXPayUtil::array2xml($data);

    echo '--3';
    $data = array(
        'name'=>'',
        'age' => 20
    );
    echo WXPayUtil::array2xml($data);

    echo '--4';
    $data = array(
        'name'=>' ',
        'age' => 20
    );
    echo WXPayUtil::array2xml($data);

    echo '--5';
    $data = array(
        'student'=>array(
            'name' => 'xiaoming'
        ),
        'age' => 20
    );
    echo WXPayUtil::array2xml($data);
}

// test_array2xml();

function test_generateSignature()
{
    $data = array('name'=>'xiaoming', 'age'=>20);
    echo WXPayUtil::generateSignature($data, '123');
    echo "\n";

    $data = array(
        'appid' => 'wxd930ea5d5a258f4f',
        'mch_id' => '10000100',
        'nonce_str' => 'ibuaiVcKdpRxkhJA',
        'device_info' => 1000,
        'body' => 'test',
        'ooo' => '',
        'sign' => '9A0A8659F005D6984697E2CA0A9CF3B7'
    );
    echo WXPayUtil::generateSignature($data, '192006250b4c09247ec02edce69f6a2d');
    echo "\n";
    echo WXPayUtil::generateSignature($data, '192006250b4c09247ec02edce69f6a2d', \WXPay\WXPayConstants::SIGN_TYPE_HMACSHA256);
}

// test_generateSignature();

function test_generateSignedXml() {
    $data = array(
        'appid' => 'wxd930ea5d5a258f4f',
        'mch_id' => '10000100',
        'nonce_str' => 'ibuaiVcKdpRxkhJA',
        'device_info' => 1000,
        'body' => 'test',
        'ooo' => '',
    );
    echo WXPayUtil::generateSignedXml($data, '192006250b4c09247ec02edce69f6a2d');
}

// test_generateSignedXml();

function test_generateNonceStr() {
    echo WXPayUtil::generateNonceStr() . "\n";
    echo WXPayUtil::generateNonceStr() . "\n";
    echo WXPayUtil::generateNonceStr() . "\n";
    echo WXPayUtil::generateNonceStr() . "\n";
}

// test_generateNonceStr();
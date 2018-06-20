<?php
/**
 * 拷贝到 Config.php， WXPayTest.php 会用到
 * cp Config_example.php Config.php
 * 并将类名修改为 Config
 */


class Config_example
{
    const WXPAY_APPID = 'wx888888888';
    const WXPAY_MCHID = '22222222';
    const WXPAY_KEY = '123456781234567812345678';
    const WXPAY_CERTPEMPATH = '/path/to/apiclient_cert.pem';
    const WXPAY_KEYPEMPATH = '/path/to/apiclient_key.pem';
}
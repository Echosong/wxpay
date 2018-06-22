<?php

namespace Echosong\WXPay;

class WXPayUtil
{
    /**
     * 将array转换为XML格式的字符串
     * @param array $data
     * @return string
     * @throws \Exception
     */
    public static function array2xml($data) {
        $xml = new \SimpleXMLElement('<xml/>');
        foreach($data as $k => $v ) {
            if (is_string($k) && (is_numeric($v) || is_string($v))) {
                $xml->addChild("$k",htmlspecialchars("$v"));
            }
            else {
                throw new \Exception('Invalid array, will not be converted to xml');
            }
        }
        return $xml->asXML();
    }

    /**
     * 将XML格式字符串转换为array
     * 参考： http://php.net/manual/zh/book.simplexml.php
     * @param string $str XML格式字符串
     * @return array
     * @throws \Exception
     */
    public static function xml2array($str) {
        $xml = simplexml_load_string($str, 'SimpleXMLElement', LIBXML_NOCDATA);
        $json = json_encode($xml);
        $result = array();
        $bad_result = json_decode($json,TRUE);  // value，一个字段多次出现，结果中的value是数组
        // return $bad_result;
        foreach ($bad_result as $k => $v) {
            if (is_array($v)) {
                if (count($v) == 0) {
                    $result[$k] = '';
                }
                else if (count($v) == 1) {
                    $result[$k] = $v[0];
                }
                else {
                    throw new \Exception('Duplicate elements in XML. ' . $str);
                }
            }
            else {
                $result[$k] = $v;
            }
        }
        return $result;
    }


    /**
     * 生成签名。注意$data中若有sign_type字段，必须和参数$signType的值一致。这里不做检查。
     * @param array $data
     * @param string $wxpayKey API密钥
     * @param string $signType
     * @return string
     * @throws \Exception
     */
    public static function generateSignature($data, $wxpayKey, $signType=WXPayConstants::SIGN_TYPE_MD5) {
        $combineStr = '';
        $keys = array_keys($data);
        asort($keys);  // 排序
        foreach($keys as $k) {
            $v = $data[$k];
            if ($k == WXPayConstants::FIELD_SIGN) {
                continue;
            }
            elseif ((is_string($v) && strlen($v) > 0) || is_numeric($v) ) {
                $combineStr = "${combineStr}${k}=${v}&";
            }
            elseif (is_string($v)  && strlen($v) == 0) {
                continue;
            }
            else {
                throw new \Exception('Invalid data, cannot generate signature: ' . json_encode($data));
            }
        }
        $combineStr = "${combineStr}key=${wxpayKey}";
        if ($signType === WXPayConstants::SIGN_TYPE_MD5) {
            return self::MD5($combineStr);
        }
        elseif ($signType === WXPayConstants::SIGN_TYPE_HMACSHA256) {
            return self::HMACSHA256($combineStr, $wxpayKey);
        }
        else {
            throw new \Exception('Invalid sign_type: ' . $signType);
        }
    }

    /**
     * 验证签名是否合法
     * @param array $data
     * @param string $wxpayKey API密钥
     * @param string $signType
     * @return bool
     */
    public static function isSignatureValid($data, $wxpayKey, $signType=WXPayConstants::SIGN_TYPE_MD5) {
        if ( !array_key_exists(WXPayConstants::FIELD_SIGN, $data) ) {
            return false;
        }
        $sign = $data[WXPayConstants::FIELD_SIGN];
        try {
            $generatedSign = WXPayUtil::generateSignature($data, $wxpayKey, $signType);
            // echo "签名: ${generatedSign} \n";
            if ($sign === $generatedSign) {
                return true;
            }
            else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 生成含有签名数据的XML格式字符串
     * @param array $data
     * @param string $wxpayKey API密钥
     * @param string $signType
     * @return string
     */
    public static function generateSignedXml($data, $wxpayKey, $signType=WXPayConstants::SIGN_TYPE_MD5) {
        // clone一份
        $newData = array();
        foreach ($data as $k => $v) {
            $newData[$k] = $v;
        }
        $sign = WXPayUtil::generateSignature($data, $wxpayKey, $signType);
        $newData[WXPayConstants::FIELD_SIGN] = $sign;
        return WXPayUtil::array2xml($newData);
    }

    /**
     * 生成 nonce str
     * 参考: http://php.net/manual/zh/function.uniqid.php
     * @return string
     */
    public static function generateNonceStr() {
        return sprintf('%04x%04x%04x%04x%04x%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * 获取 MD5 结果
     * @param string $data
     * @return string
     */
    public static function MD5($data) {
        return strtoupper(md5($data));
    }

    /**
     * 获取 HMAC-SHA256 签名结果
     * @param string $data
     * @param string $wxpayKey
     * @return string
     */
    public static function HMACSHA256($data, $wxpayKey) {
        return strtoupper(hash_hmac('sha256', $data, $wxpayKey));
    }
}
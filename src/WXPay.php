<?php

namespace WXPay;

class WXPay
{
    /**
     * WXPayApi constructor.
     * @param string $appId 公众帐号ID
     * @param string $mchId 商户号
     * @param string $key API密钥
     * @param string $certPemPath 商户pem格式证书文件路径
     * @param string $keyPemPath 商户pem格式证书密钥文件路径
     * @param float $timeout 网络超时时间，单位毫秒，默认8000ms
     * @param string $signType 使用的签名算法，默认为MD5
     * @param boolean $useSandbox 使用沙箱环境，默认为false
     */
    function __construct($appId, $mchId, $key, $certPemPath, $keyPemPath, $timeout=WXPayConstants::DEFAULT_TIMEOUT_MS, $signType=WXPayConstants::SIGN_TYPE_MD5, $useSandbox=false) {
        $this->appId = $appId;
        $this->mchId = $mchId;
        $this->key = $key;
        $this->certPemPath = $certPemPath;
        $this->keyPemPath = $keyPemPath;
        $this->timeout = $timeout;
        $this->signType = $signType;
        $this->useSandbox = $useSandbox;
    }

    /**
     * 处理去wxpay请求后的返回数据
     * @param string $xml
     * @return array
     * @throws \Exception
     */
    public function processResponseXml($xml) {
        $RETURN_CODE = "return_code";
        $FAIL = "FAIL";
        $SUCCESS = "SUCCESS";
        $data = WXPayUtil::xml2array($xml);

        if (array_key_exists($RETURN_CODE, $data)) {
            $return_code = $data[$RETURN_CODE];
        }
        else {
            throw new \Exception("Invalid XML. There is no `return_code`. ${xml}");
        }

        if ($return_code === $FAIL) {
            return $data;
        }
        elseif ($return_code === $SUCCESS) {
            if ($this->isResponseSignatureValid($data)) {
                return $data;
            }
            else {
                throw new \Exception("Invalid signature in XML. ${xml}");
            }
        }
        else {
            throw new \Exception("Invalid XML. `return_code` value ${return_code} is invalid");
        }
    }

    /**
     * 向关联数据中添加 appid、mch_id、nonce_str、sign_type、sign 字段
     * @param array $data
     * @return array
     */
    public function fillRequestData($data) {
        // clone一份新数据
        $newData = array();
        foreach ($data as $k => $v) {
            $newData[$k] = $v;
        }
        // 填充
        $newData['appid'] = $this->appId;
        $newData['mch_id'] = $this->mchId;
        $newData['nonce_str'] = WXPayUtil::generateNonceStr();
        $newData['sign_type'] = $this->signType;
        $sign = WXPayUtil::generateSignature($newData, $this->key, $this->signType);
        $newData['sign'] = $sign;
        return $newData;
    }

    /**
     * 判断 $data 的 sign 是否有效，必须包含sign字段，否则返回false。
     * @param array $data
     * @return bool
     */
    public function isResponseSignatureValid($data) {
        return WXPayUtil::isSignatureValid($data, $this->key, $this->signType);
    }

    /**
     * 判断支付结果通知中的sign是否有效。必须有sign字段
     *
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    public function isPayResultNotifySignatureValid($data) {
        if ( !array_key_exists(WXPayConstants::FIELD_SIGN_TYPE, $data) ) {
            $signType = WXPayConstants::SIGN_TYPE_MD5;
        }
        else {
            $signTypeInData = $data['sign_type'];
        }

        if (!isset($signTypeInData) || $signTypeInData == null) {
            $signType = WXPayConstants::SIGN_TYPE_MD5;
        }
        else {
            $signTypeInData = trim($signTypeInData);
            if (strlen($signTypeInData) == 0) {
                $signType = WXPayConstants::SIGN_TYPE_MD5;
            }
            elseif (WXPayConstants::SIGN_TYPE_MD5 == $signTypeInData) {
                $signType = WXPayConstants::SIGN_TYPE_MD5;
            }
            elseif (WXPayConstants::SIGN_TYPE_HMACSHA256 == $signTypeInData) {
                $signType = WXPayConstants::SIGN_TYPE_HMACSHA256;
            }
            else {
                throw new \Exception("Unsupported sign_type: ${signType}");
            }
        }
        return WXPayUtil::isSignatureValid($data, $this->key, $signType);
    }

    /**
     * Https请求，不带证书
     * @param string $url URL
     * @param array $reqData 请求数据
     * @param null|float $timeout 网络超时时间，单位是毫秒
     * @return string 返回的xml数据
     * @throws \Exception
     */
    public function requestWithoutCert($url, $reqData, $timeout=null) {
        if ($timeout == null) {
            $timeout = $this->timeout;
        }
        $reqXml = WXPayUtil::array2xml($reqData);
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, ((float)$timeout)/1000.0);

        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);  //严格校验
        curl_setopt($ch, CURLOPT_HEADER, FALSE);    //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);   // POST请求
        curl_setopt($ch, CURLOPT_POSTFIELDS, $reqXml);
        $output = curl_exec($ch);
        if($output){
            curl_close($ch);
            return $output;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new \Exception("curl出错，错误码: ${error}");
        }
    }

    /**
     * Https请求，带证书
     * @param string $url URL
     * @param array $reqData 请求数据
     * @param null|float $timeout 网络超时时间，单位是毫秒
     * @return string 返回的xml数据
     * @throws \Exception
     */
    public function requestWithCert($url, $reqData, $timeout=null) {
        if ($timeout == null) {
            $timeout = $this->timeout;
        }
        $reqXml = WXPayUtil::array2xml($reqData);
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, ((float)$timeout)/1000.0);

        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);  //严格校验
        curl_setopt($ch, CURLOPT_HEADER, FALSE);    //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        // 设置证书
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLCERT, $this->certPemPath);
        curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
        curl_setopt($ch,CURLOPT_SSLKEY, $this->keyPemPath);

        curl_setopt($ch, CURLOPT_POST, TRUE);  // POST请求
        curl_setopt($ch, CURLOPT_POSTFIELDS, $reqXml);
        $output = curl_exec($ch);
        if($output){
            curl_close($ch);
            return $output;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            throw new \Exception("curl出错，错误码: ${error}");
        }
    }

    /**
     * 提交刷卡支付
     * @param array $reqData 请求数据
     * @param null|float $timeout 网络超时时间，单位是毫秒
     * @return array wxpay返回数据
     */
    public function microPay($reqData, $timeout=null) {
        if ($this->useSandbox) {
            $url = WXPayConstants::SANDBOX_MICROPAY_URL;
        }
        else {
            $url = WXPayConstants::MICROPAY_URL;
        }
        return $this->processResponseXml($this->requestWithoutCert($url, $this->fillRequestData($reqData), $timeout));
    }

    /**
     * 统一下单
     * @param array $reqData 请求数据
     * @param null|float $timeout 网络超时时间，单位是毫秒
     * @return array wxpay返回数据
     */
    public function unifiedOrder($reqData, $timeout=null) {
        if ($this->useSandbox) {
            $url = WXPayConstants::SANDBOX_UNIFIEDORDER_URL;
        }
        else {
            $url = WXPayConstants::UNIFIEDORDER_URL;
        }
        return $this->processResponseXml($this->requestWithoutCert($url, $this->fillRequestData($reqData), $timeout));
    }

    /**
     * 订单查询
     * @param array $reqData 请求数据
     * @param null|float $timeout 网络超时时间，单位是毫秒
     * @return array wxpay返回数据
     */
    public function orderQuery($reqData, $timeout=null) {
        if ($this->useSandbox) {
            $url = WXPayConstants::SANDBOX_ORDERQUERY_URL;
        }
        else {
            $url = WXPayConstants::ORDERQUERY_URL;
        }
        return $this->processResponseXml($this->requestWithoutCert($url, $this->fillRequestData($reqData), $timeout));
    }

    /**
     * 撤销订单（用于刷卡支付）
     * @param array $reqData 请求数据
     * @param null|float $timeout 网络超时时间，单位是毫秒
     * @return array wxpay返回数据
     */
    public function reverse($reqData, $timeout=null) {
        if ($this->useSandbox) {
            $url = WXPayConstants::SANDBOX_REVERSE_URL;
        }
        else {
            $url = WXPayConstants::REVERSE_URL;
        }
        return $this->processResponseXml($this->requestWithCert($url, $this->fillRequestData($reqData), $timeout));
    }

    /**
     * 关闭订单
     * @param array $reqData 请求数据
     * @param null|float $timeout 网络超时时间，单位是毫秒
     * @return array wxpay返回数据
     */
    public function closeOrder($reqData, $timeout=null) {
        if ($this->useSandbox) {
            $url = WXPayConstants::SANDBOX_CLOSEORDER_URL;
        }
        else {
            $url = WXPayConstants::CLOSEORDER_URL;
        }
        return $this->processResponseXml($this->requestWithoutCert($url, $this->fillRequestData($reqData), $timeout));
    }

    /**
     * 申请退款
     * @param array $reqData 请求数据
     * @param null|float $timeout 网络超时时间，单位是毫秒
     * @return array wxpay返回数据
     */
    public function refund($reqData, $timeout=null) {
        if ($this->useSandbox) {
            $url = WXPayConstants::SANDBOX_REFUND_URL;
        }
        else {
            $url = WXPayConstants::REFUND_URL;
        }
        return $this->processResponseXml($this->requestWithCert($url, $this->fillRequestData($reqData), $timeout));
    }

    /**
     * 退款查询
     * @param array $reqData 请求数据
     * @param null|float $timeout 网络超时时间，单位是毫秒
     * @return array wxpay返回数据
     */
    public function refundQuery($reqData, $timeout=null) {
        if ($this->useSandbox) {
            $url = WXPayConstants::SANDBOX_REFUNDQUERY_URL;
        }
        else {
            $url = WXPayConstants::REFUNDQUERY_URL;
        }
        return $this->processResponseXml($this->requestWithoutCert($url, $this->fillRequestData($reqData), $timeout));
    }


    /**
     * 现金红包
     * @param array $reqData 请求数据
     * @param null|float $timeout 网络超时时间，单位是毫秒
     * @return array wxpay返回数据
     */
    public function bouns($reqData, $timeout =null){
        if ($this->useSandbox) {
            $url = WXPayConstants::SANDBOX_BOUNS_URL;
        }
        else {
            $url = WXPayConstants::BOUNS_URL;
        }
        return $this->processResponseXml($this->requestWithoutCert($url, $this->fillRequestData($reqData), $timeout));
    }

    /**
     * 裂变红包
     * @param array $reqData 请求数据
     * @param null|float $timeout 网络超时时间，单位是毫秒
     * @return array wxpay返回数据
     */
    public  function fissionBouns($reqData, $timeout= null){
        if ($this->useSandbox) {
            $url = WXPayConstants::SANDBOX_FISSIONBOUNS_URL;
        }
        else {
            $url = WXPayConstants::FISSIONBOUNS_URL;
        }
        return $this->processResponseXml($this->requestWithoutCert($url, $this->fillRequestData($reqData), $timeout));
    }

    /**
     * 企业直接付款到零钱
     * @param array $reqData 请求数据
     * @param null|float $timeout 网wxpay返回数据络超时时间，单位是毫秒
     * @return array wxpay返回数据
     */
    public function transfers($reqData, $timeout= null){
        if ($this->useSandbox) {
            $url = WXPayConstants::SANDBOX_TRANSFERS_URL;
        }
        else {
            $url = WXPayConstants::TRANSFERS_URL;
        }
        return $this->processResponseXml($this->requestWithoutCert($url, $this->fillRequestData($reqData), $timeout));
    }

    /**
     * 下载对账单
     * @param array $reqData 请求数据
     * @param null|float $timeout 网络超时时间，单位是毫秒
     * @return array wxpay返回数据  注意，若下载成功，wxpay只会返回对账单数据，非XML。该函数对此做了封装，加上了return_code和return_msg
     * @throws \Exception
     */
    public function downloadBill($reqData, $timeout=null) {
        if ($this->useSandbox) {
            $url = WXPayConstants::SANDBOX_DOWNLOADBILL_URL;
        }
        else {
            $url = WXPayConstants::DOWNLOADBILL_URL;
        }
        $respContent = $this->requestWithoutCert($url, $this->fillRequestData($reqData), $timeout);
        $respContent = trim($respContent);
        if (strlen($respContent) === 0) {
            throw new \Exception('HTTP response is empty!');
        }
        if (strlen($respContent) > 0 && substr( $respContent, 0, 1 ) === "<") {  // xml
            return WXPayUtil::xml2array($respContent);
        }
        else {  // 对账单数据
            return array(
                'return_code' => 'SUCCESS',
                'return_msg' => 'OK',
                'data' => $respContent
                );
        }
    }

    /**
     * 交易保障
     * @param array $reqData 请求数据
     * @param null|float $timeout 网络超时时间，单位是毫秒
     * @return array wxpay返回数据
     */
    public function report($reqData, $timeout=null) {
        if ($this->useSandbox) {
            $url = WXPayConstants::SANDBOX_REPORT_URL;
        }
        else {
            $url = WXPayConstants::REPORT_URL;
        }
        $respXml = $this->requestWithoutCert($url, $this->fillRequestData($reqData), $timeout);
        return WXPayUtil::xml2array($respXml);
    }

    /**
     * 转换短链接
     * @param array $reqData 请求数据
     * @param null|float $timeout 网络超时时间，单位是毫秒
     * @return array wxpay返回数据
     */
    public function shortUrl($reqData, $timeout=null) {
        if ($this->useSandbox) {
            $url = WXPayConstants::SANDBOX_SHORTURL_URL;
        }
        else {
            $url = WXPayConstants::SHORTURL_URL;
        }
        return $this->processResponseXml($this->requestWithoutCert($url, $this->fillRequestData($reqData), $timeout));
    }

    /**
     * 授权码查询OPENID接口
     * @param array $reqData 请求数据
     * @param null|float $timeout 网络超时时间，单位是毫秒
     * @return array wxpay返回数据
     */
    public function authCodeToOpenid($reqData, $timeout=null) {
        if ($this->useSandbox) {
            $url = WXPayConstants::SANDBOX_AUTHCODETOOPENID_URL;
        }
        else {
            $url = WXPayConstants::AUTHCODETOOPENID_URL;
        }
        return $this->processResponseXml($this->requestWithoutCert($url, $this->fillRequestData($reqData), $timeout));
    }
}
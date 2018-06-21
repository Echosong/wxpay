<?php

namespace WXPay;

class WXPayConstants
{
    const FAIL     = "FAIL";
    const SUCCESS  = "SUCCESS";

    const SIGN_TYPE_MD5 = "MD5";
    const SIGN_TYPE_HMACSHA256 = "HMAC-SHA256";

    const FIELD_SIGN = "sign";
    const FIELD_SIGN_TYPE = "sign_type";

    const DEFAULT_TIMEOUT_MS  = 8000.0; // ms

    const MICROPAY_URL     = 'https://api.mch.weixin.qq.com/pay/micropay';
    const UNIFIEDORDER_URL = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
    const ORDERQUERY_URL   = 'https://api.mch.weixin.qq.com/pay/orderquery';
    const REVERSE_URL      = 'https://api.mch.weixin.qq.com/secapi/pay/reverse';
    const CLOSEORDER_URL   = 'https://api.mch.weixin.qq.com/pay/closeorder';
    const REFUND_URL       = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
    const REFUNDQUERY_URL  = 'https://api.mch.weixin.qq.com/pay/refundquery';
    const DOWNLOADBILL_URL = 'https://api.mch.weixin.qq.com/pay/downloadbill';
    const REPORT_URL       = 'https://api.mch.weixin.qq.com/payitil/report';
    const SHORTURL_URL     = 'https://api.mch.weixin.qq.com/tools/shorturl';
    const AUTHCODETOOPENID_URL = 'https://api.mch.weixin.qq.com/tools/authcodetoopenid';
    const BOUNS_URL = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';
    const TRANSFERS_URL = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
    const FISSIONBOUNS_URL = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendgroupredpack';

    // sandbox

    const SANDBOX_MICROPAY_URL     = "https://api.mch.weixin.qq.com/sandboxnew/pay/micropay";
    const SANDBOX_UNIFIEDORDER_URL = "https://api.mch.weixin.qq.com/sandboxnew/pay/unifiedorder";
    const SANDBOX_ORDERQUERY_URL   = "https://api.mch.weixin.qq.com/sandboxnew/pay/orderquery";
    const SANDBOX_REVERSE_URL      = "https://api.mch.weixin.qq.com/sandboxnew/secapi/pay/reverse";
    const SANDBOX_CLOSEORDER_URL   = "https://api.mch.weixin.qq.com/sandboxnew/pay/closeorder";
    const SANDBOX_REFUND_URL       = "https://api.mch.weixin.qq.com/sandboxnew/secapi/pay/refund";
    const SANDBOX_REFUNDQUERY_URL  = "https://api.mch.weixin.qq.com/sandboxnew/pay/refundquery";
    const SANDBOX_DOWNLOADBILL_URL = "https://api.mch.weixin.qq.com/sandboxnew/pay/downloadbill";
    const SANDBOX_REPORT_URL       = "https://api.mch.weixin.qq.com/sandboxnew/payitil/report";
    const SANDBOX_SHORTURL_URL     = "https://api.mch.weixin.qq.com/sandboxnew/tools/shorturl";
    const SANDBOX_AUTHCODETOOPENID_URL = "https://api.mch.weixin.qq.com/sandboxnew/tools/authcodetoopenid";
    const SANDBOX_BOUNS_URL = 'https://api.mch.weixin.qq.com/sandboxnew/mmpaymkttransfers/sendredpack';
    const SANDBOX_TRANSFERS_URL = 'https://api.mch.weixin.qq.com/sandboxnew/mmpaymkttransfers/promotion/transfers';
    const SANDBOX_FISSIONBOUNS_URL = 'https://api.mch.weixin.qq.com/sandboxnew/mmpaymkttransfers/sendgroupredpack';
}
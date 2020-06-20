<?php
$aliwapPayconfig = array (

        //'user_id' => "2088421787198094,2088421787198094,2088421787198094",  //配置多个时
        'user_id' => "2088421787198094",

		//应用ID,您的APPID。
		'app_id' => "2016083101828224",

		//商户私钥，您的原始格式RSA私钥
		'merchant_private_key' => "MIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQCQSlRo5D6lQBFgElBLliyUUKSNL7DUmMIBjVXhmUaSFu4Po2T0uwp7qe7CaoGgFqvgfucLkZCbveOqtbYOlhx04hCskpgCt9b6H18SK3sGkUqYB2oeLGoPjRVix2Xuc31Y2Dkp7SPQTZRNcjsq62ws8PyI6XDvWbQm0ryUOvyOW9eMcbl+ivt3aNxj4d0NdKRGXDWsxdAE8hdegrtKe03AUCSEcMkglhKJKynHJsXjJdbCegKVfZh8LC/IP5VUNEISC5jTviUcPVWRwjC6iHZjIlQvY1zmHeqB+iQjCXHwyT9tN0U160B2Q3hnUqhcLFgajkOoEPZ85CaCE15V1O9HAgMBAAECggEAK6ghs/7jKXKiDzRbURNl95YSw5kbYqe5g2i1BGYw4QDJFLg2UonJGTOIRxCcmchiRrF+zJRFcanZmYwTQoSOpZrEusI79g6Tn+ggBspbFdid/jO4GtsLWBsyzklQgP42fcwmN5ZFkL/4UBpw5oKGn25mqjIfdnvosxpt7leOlK+mrhAqHwgKLF8X68b9TRxATh+lI6TC5x1W+2Gncssva9uHAm4nZxaCOvm4RAJYLx9ulfgFoF1jlm3gKKmJnB/y2YbL+pTdygXdwS3zhDw/Kazy4fy+uq3Ecsz3c29wsoXwl1fQ2IrZt2y/gULdsBYv2FVneKr0009BpoRE0+e96QKBgQDJ0g+AF/cOxGHpI7NdlHhBC7NzvwG5edRcxPP89W27oK8NR9swg/bOR0KhC3JI0iBJfPBW2hzU1A1jEvXCyKXi1v/HURJc4TWiv217mu+y7JtS8k8BfiQY4CK9tsQdbbAdVbU9bs23YLp0YxvTmStMhlI4x+plTplBsKZJpV8NawKBgQC3Bo6U+LVY0Yfr0OUxF6W6TgnQfOrnDDeCe2QGdO74bMT7vPNADm2l3dp/VoS7BHOxr8hKmiV+SqSlm8tV/XhcMMl3JVFq5yFDkn4qq4kSgFnJFEmaZ0JmP8tqf1dCzK3b/BtaBnUnqKfsjuSKfSkYGGu1y5QyGvbzdhyrpe5glQKBgQCWQe/KZY1KEEn5QtwyyuwxOV30yfultJ+4JealqbB2Je7Oi3YUi9t/vqxLrHL85nylWgCyGReoGOySm7YfvDVNStcJ9UEfp4jAT5dalILrip8lxUOvD9QeNRmId39Rja22WW5je5Bre/e12WgJRRtokQS8Q5Mus7MEpllXsWiwWwKBgQCWnnnGFOrAfiaaJR4ICYrkSAaBodt6aq5f3gWR3rcuj+yHspaIV5dakbmXY271rRM83gk5g6NpTCo084IhcOeVDr0tJPPcwvq37h2QJfw20pORC/YKcAHPvZ48NTFtkp1dVRp5Oqk9CumunmVrptajsq0pPbDmjxQ9hSzRAUusBQKBgQC6XxGom0QRlGj7VpJHr3NWMXySBrFOEE+nEWI8717SH9klrS4hpNXUuZ8Wj39EhSbygfHwYuO5tRxiA7ZKciR/jK2c2xCt34GcAWHZUbHHH65upOowYGxbupCinlWAZzzaHsZDfF1N2zAog0zrhbdwXztDyebImFCZVFUW2zdQXw==",
		//异步通知地址
		'notify_url' => "http://sdk-test-pay.intranet.com/alipayRecharge",
		
		//同步跳转
		'return_url' => "http://sdk-test-pay.intranet.com/alipayRecharge",

        'quit_url' => 'https://www.baidu.com',

		//编码格式
		'charset' => "UTF-8",

		//签名方式
		'sign_type'=>"RSA2",

		//支付宝网关
		'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

		//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEApVajeuUpxiRMKtl0Bk21T1TNGMpsuq1C40dg9WCDeZWe/mCQTRr+1SWMnBjIehWucE8XBbDvY3oYzLHM08plSPnSGteiDCcumczoZaMxKJtSyukMj/S3x90s29fShwW4/dIrdgiFaNXeYawAugY9nKnDmQsaMsNTU45d710aFUrFQFtwVKAP+yJ3dEcN7mnLPhtn44k/yndIJDcgyEoYOFgYMTIKm9PA+3WbZvDVzmCBKNWAq9Wws0YlODJppeLN+u7HeC41qe/k3/x3Sf4wItjvnA8uZGQWJJrQQSLdXP40nSC2rl3kb8nLHqDGl3uYhtY1SgbuSkHGE6u531256wIDAQAB"
);

return $aliwapPayconfig;
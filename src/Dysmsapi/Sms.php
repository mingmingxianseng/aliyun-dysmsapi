<?php
/**
 * Created by PhpStorm.
 * User: webid
 * Date: 17-12-25
 * Time: 下午5:29
 */

namespace mmxs\Dysmsapi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Log\LoggerInterface;

class Sms
{
    private $options = [
        'access_key_id'     => '',
        'access_key_secret' => '',
        'domain'            => 'dysmsapi.aliyuncs.com',
        'secret'            => false,
        'sign_name'         => '',
        "region_id"         => "cn-hangzhou",
        "action"            => "SendSms",
        "version"           => "2017-05-25",
    ];
    private $params = [
        "SignatureMethod"  => "HMAC-SHA1",
        "SignatureVersion" => "1.0",
        "Format"           => "JSON",
    ];
    /** @var Client */
    private $client;
    /** @var  LoggerInterface $logger */
    private $logger;

    public function __construct(array $options, LoggerInterface $logger)
    {
        $this->options = array_replace($this->options, $options);
        $this->setParam('AccessKeyId', $this->options['access_key_id'])
            ->setParam('SignName', $this->options['sign_name'])
            ->setParam('RegionId', $this->options['region_id'])
            ->setParam('Action', $this->options['action'])
            ->setParam('Version', $this->options['version']);
        $this->client = new Client(['verify' => $this->options['secret'] == true]);
        $this->logger = $logger;
        $this->logger->info('dysmsapi options', ['options' => $this->options]);
    }

    /**
     * send 发送短信
     *
     * @author chenmingming
     *
     * @param string $phone          手机号码
     * @param string $templateCode   短信模板编码
     * @param array  $templateParams 短信模板变量数组
     *
     * @return bool
     */
    public function send($phone, $templateCode, $templateParams = [])
    {
        $this->setParam('SignatureNonce', uniqid(mt_rand(0, 0xffff), true))
            ->setParam('Timestamp', gmdate("Y-m-d\TH:i:s\Z"))
            ->setParam('TemplateCode', $templateCode)
            ->setParam('PhoneNumbers', $phone);
        if ($templateParams) {
            $this->setParam('TemplateParam', json_encode($templateParams, JSON_UNESCAPED_UNICODE));
        }
        $sortedQueryStringTmp = $this->getQueryStr();
        $signature            = $this->getSignature($sortedQueryStringTmp);
        $url                  = ($this->options['secret'] ? 'https' : 'http')
            . "://{$this->options['domain']}/?Signature="
            . $signature . $sortedQueryStringTmp;

        $this->logger->debug('dysmsapi sendSms', ['params' => $this->params]);
        try {
            $response = $this->client->get($url);
            $json     = $response->getBody()->getContents();
            $this->logger->debug('request success', ['response' => $json]);
            $data = json_decode($json, true);
            if ($data['Code'] === 'OK') {
                $this->logger->debug('result success');

                return true;
            } else {
                $this->logger->error('result failed', ['response' => $json]);

                return false;
            }

        } catch (ClientException $e) {
            $this->logger->error($e, ['response' => $e->getResponse()->getBody()->getContents()]);

            return false;
        } catch (\Exception $e) {
            $this->logger->error($e, ['params' => $this->params, 'options' => $this->options]);

            return false;
        }
    }

    /**
     * getSignature
     *
     * @author chenmingming
     *
     * @param $str
     *
     * @return string
     */
    private function getSignature($str)
    {
        $stringToSign = "GET&%2F&" . $this->encode(substr($str, 1));

        $sign = base64_encode(hash_hmac("sha1", $stringToSign, $this->options['access_key_secret'] . "&", true));

        return $this->encode($sign);
    }

    /**
     * getQueryStr
     *
     * @author chenmingming
     * @return string
     */
    private function getQueryStr()
    {
        $params = $this->params;
        ksort($params);
        $sortedQueryStringTmp = "";
        foreach ($params as $key => $value) {
            $sortedQueryStringTmp .= "&" . $this->encode($key) . "=" . $this->encode($value);
        }

        return $sortedQueryStringTmp;
    }

    public function setParam($key, $value)
    {
        $this->params[$key] = $value;

        return $this;
    }

    public function getParams()
    {
        return $this->params;
    }

    private function encode($str)
    {
        $res = urlencode($str);
        $res = preg_replace("/\+/", "%20", $res);
        $res = preg_replace("/\*/", "%2A", $res);
        $res = preg_replace("/%7E/", "~", $res);

        return $res;
    }
}
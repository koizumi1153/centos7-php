<?php
namespace App\Controller\Component;

use Cake\Controller\Component;

/**
 * Amazon APIに関するコンポーネント
 *
 * Class AmazonComponent
 * @package App\Controller\Component
 */
class Amazon extends Component
{
  public function setRequest($searchIndex='', $keywords='', $sort="-orig-rel-date"){
    $baseurl = "https://aws.amazonaws.jp/onca/xml";

// リクエストのパラメータ作成
    $params = array();
    $params["Service"]          = "AWSECommerceService";
    $params["AWSAccessKeyId"]   = ACCESS_KEY_ID;
    $params["Version"]          = "2006-09-13";
    $params["Operation"]        = "ItemSearch";
    $params["SearchIndex"]      = $searchIndex;
    $params["Keywords"]         = $keywords;
    $params["AssociateTag"]     = ASSOCIATE_ID;
#    $params["ResponseGroup"]    = "ItemAttributes,Offers, Images";
    $params["MinimumPrice"]     = "100";
    $params["ItemPage"]         = "1";
    $params["Sort"]             = $sort;


    $base_request = "";
    foreach ($params as $k => $v) { $base_request .= "&" . $k . "=" . $v; }
    $base_request = $baseurl . "?" . substr($base_request, 1);

    $params["Timestamp"] = date("Y-m-d\TH:i:s.000Z");
    $base_request .= "&Timestamp=" . $params['Timestamp'];

    $base_request = "";
    foreach ($params as $k => $v) {
      $base_request .= '&' . $k . '=' . rawurlencode($v);
      $params[$k] = rawurlencode($v);
    }
    $base_request = $baseurl . "?" . substr($base_request, 1);

    $base_request = preg_replace("/.*\?/", "", $base_request);
    $base_request = str_replace("&", "\n", $base_request);

    ksort($params);
    $base_request = "";
    foreach ($params as $k => $v) { $base_request .= "&" . $k . "=" . $v; }
    $base_request = substr($base_request, 1);
    $base_request = str_replace("&", "\n", $base_request);

    $base_request = str_replace("\n", "&", $base_request);

    $parsed_url = parse_url($baseurl);
    $base_request = "GET\n" . $parsed_url['host'] . "\n" . $parsed_url['path'] . "\n" . $base_request;

    $signature = base64_encode(hash_hmac('sha256', $base_request, ACCESS_SECRET_KEY, true));
    $signature = rawurlencode($signature);

    $base_request = "";
    foreach ($params as $k => $v) { $base_request .= "&" . $k . "=" . $v; }
    $base_request = $baseurl . "?" . substr($base_request, 1) . "&Signature=" . $signature;

    return $base_request;
  }
}
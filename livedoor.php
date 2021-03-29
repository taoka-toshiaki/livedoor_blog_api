<?php
class livedoor{
    public static $livedoor_id = "livedoor_id";
    public static $livedoor_apikey = "livedoor_apikey";
    public static $blog_name ="blog_name";
    public static $nonce = null;
    public static $digest = null;
    public static $wsse = null;

    public function blog_post($title="",$content="text",$category=array()){
        $date = new DateTime();
        $time = $date->format("Y-m-d\TH:i:s\Z");
        self::$nonce = sha1(time() . rand() . getmypid(), true);
        self::$digest = base64_encode(sha1(self::$nonce . $time . self::$livedoor_apikey, true)); 
        self::$wsse =
        sprintf(
            "UsernameToken Username=\"%s\",PasswordDigest=\"%s\",Nonce=\"%s\",Created=\"%s\"",
            self::$livedoor_id,
            self::$digest,
            base64_encode(self::$nonce),
            $time
        );
        $headers  = array(
            "Authorization: WSSE profile=\"UsernameToken\"",
            "X-WSSE: ".self::$wsse,
            "Accept: application/xml"
        );


        $POST_DATA = '<?xml version="1.0" encoding="UTF-8"?>
        <entry xmlns="http://www.w3.org/2007/app" xmlns:atom="http://www.w3.org/2005/Atom">
        <title type="text/html" mode="escaped">'.$title.'</title>
        <content type="application/xhtml+xml">'.$content.'</content>
        <category term="'.$category[0].'"/>
        </entry>';

        $curl = curl_init("https://livedoor.blogcms.jp/atompub/". self::$blog_name . "/article");
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $POST_DATA);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        if ($response === false) {
            echo 'Curl error: ' . curl_error($curl);
        }
        curl_close($curl);
        return $response;
    }
}

$title = "test";
$content = "テストですよ";
$category[0] = "blog";

$response = livedoor::blog_post($title,$content,$category);
print $response?"投稿しました":"投稿に失敗しました";

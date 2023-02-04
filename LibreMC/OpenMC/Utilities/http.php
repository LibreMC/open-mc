<?php namespace LibreMC\OpenMC\Utilities;
function http_get(string $url)
{
    $ch = curl_init($url);

    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_HEADER => FALSE,
        CURLOPT_SSL_VERIFYPEER => FALSE,
        CURLOPT_SSL_VERIFYHOST => FALSE,
        CURLOPT_FOLLOWLOCATION => TRUE,
        CURLOPT_HTTPHEADER => array(
            'User-Agent' => 'PHP cURL (open-mc)'
        )
    ));

    $result = '';
    if (!($result = curl_exec($ch))) {
        trigger_error(curl_error($ch));
    }

    $res = new HttpResponse($result, (int) curl_getinfo($ch, CURLINFO_HTTP_CODE));

    curl_close($ch);
    return $res;
}

class HttpResponse {
    public $text;
    public $code;
    
    public function __construct(string $text, int $code)
    {
        $this->text = $text;
        $this->code = $code;
    }
}
<?php
class HttpResponse
{
    public string $text;
    public int $code;

    public function __construct(string $text, int $responseCode)
    {
        $this->text = $text;
        $this->code = $responseCode;
    }
}

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
            'User-Agent' => 'Mozilla/5.0 (open-mc)'
        )
    ));

    $result = '';
    if (!($result = curl_exec($ch))) {
        trigger_error(curl_error($ch));
    }

    $res = new HttpResponse($result, (int)curl_getinfo($ch, CURLINFO_HTTP_CODE));

    curl_close($ch);
    return $res;
}

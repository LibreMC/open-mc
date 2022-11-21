<?php
class HttpResponse
{
    public $text;
    public $code;
    
    public function __construct(string $text, int $responseCode)
    {
        $this->text = $text;
        $this->code = $responseCode;
    }
}
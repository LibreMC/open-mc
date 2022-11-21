<?php
class HttpResponse
{
    public $text;
    public $code;
    
    public function __construct(string $text, int $code)
    {
        $this->text = $text;
        $this->code = $code;
    }
}
<?php
require_once ('common.php');

class Profile
{
    public $uuid = '';
    public $fullUuid = '';
    public $name = '';
    public $history = array();
    public $ofCape = FALSE;

    public function __construct(string $uuid, string $name, array $history)
    {
        $this->uuid = $uuid;
        $this->fullUuid = preg_replace('/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/', '$1-$2-$3-$4-$5', $uuid);
        $this->name = $name;
        $this->history = $history;
        $this->ofCape = http_get("http://s.optifine.net/capes/{$name}.png")->code === 200;
    }
}
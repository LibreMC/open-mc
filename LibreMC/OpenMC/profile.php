<?php namespace LibreMC\OpenMC;
require_once ('Utilities/http.php');

use function LibreMC\OpenMC\Utilities\http_get;

class Profile {
    public $id = '';
    public $fullId = '';
    public $username = '';
    public $usernameHistory = array();
    public $hasOptiFineCape = FALSE;

    function __construct(string $id, string $username) {
        $this->id = $id;
        $this->fullId = preg_replace('/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/', '$1-$2-$3-$4-$5', $id);
        $this->username = $username;

        $this->getUsernameHistory();
        
        $this->hasOptiFineCape = http_get("http://s.optifine.net/capes/{$this->username}.png")->code === 200;
    }

    private function sort_username_history($e1, $e2) {
        if (!isset($e1->{'changedToAt'}) || !isset($e2->{'changedToAt'})) return 1;
    
        if ($e1->{'changedToAt'} < $e2->{'changedToAt'}) {
            return 1;
        }
    
        return -1;
    }

    private function getUsernameHistory() {
        $res = http_get("https://api.mojang.com/user/profiles/{$this->id}/names");
        if ($res->code !== 200) {
            return;
        }
        
        $json = json_decode($res->text);
        if (!$json || isset($json->{'errorMessage'})) {
            return;
        }
        
        usort($json, array($this, 'sort_username_history'));
        $this->usernameHistory = $json;
    }
}
<?php

class HttpResponse {
    public $text;
    public $code;
}

function curl_get($url) {
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

    $res = new HttpResponse();
    $res->text = $result;
    $res->code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);
    return $res;
}

function sort_name_history($e1, $e2) {
    if (!isset($e1->{'changedToAt'})) {
        return 1;
    } else if ($e1->{'changedToAt'} < $e2->{'changedToAt'}) {
        return 1;
    }

    return -1;
}

class Profile {
    public $uuid = '';
    public $fullUuid = '';
    public $name = '';
    public $history = array();
    public $mojangCapes = array();
    public $ofCape = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['q'])) {
        $query = $_POST['q'];

        if ($query !== '') {   
            $uuid = NULL;

            if (strlen($query) < 17) { // Username Lookup  
                // Valid usernames contain A-Z, 0-9, or '_'
                if (!preg_match('/[^\w]/', $query)) {
                    // Username -> UUID resolve
                    $res = curl_get("https://api.mojang.com/users/profiles/minecraft/$query");
                    
                    if ($res->code === 200) {
                        $json = json_decode($res->text);

                        if ($json) {
                            if (isset($json->{'errorMessage'})) {
                                die(json_encode(array(
                                    'error' => $json->{'errorMessage'}
                                )));
                            } else {
                                $uuid = $json->{'id'};
                            }
                        }
                    } else {
                        die(json_encode(array(
                            'error' => "Profile '$query' was not found."
                        )));
                    }
                } else {
                    die(json_encode(array(
                        'error' => 'Illegal username characters.'
                    )));
                }
            } else if (strlen($query) === 32 || strlen($query) === 36) { // UUID validation
                // Valid UUIDs contain A-F, 0-9, and '-'
                if (!preg_match('/[^a-f0-9-]/i', $query)) {
                    $uuid = str_replace('-', '', $query);
                } else {
                    die(json_encode(array(
                        'error' => 'Illegal UUID characters.'
                    )));
                }
            } else {
                die(json_encode(array(
                    'error' => 'Invalid query format.'
                )));
            }

            if ($uuid !== NULL) {
                $res = curl_get("https://api.mojang.com/user/profiles/$uuid/names");

                if ($res->code === 200) {
                    $json = json_decode($res->text);

                    if($json) {
                        if (isset($json->{'errorMessage'})) {
                            die( json_decode(array(
                                'error' => $json->{'errorMessage'}
                            )));
                        } else {
                            usort($json, 'sort_name_history');
                            
                            $profile = new Profile();
                            $profile->uuid = $uuid;
                            $profile->fullUuid = preg_replace('/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/', '$1-$2-$3-$4-$5', $uuid);
                            $profile->name = $json[0]->name;
                            $profile->history = $json;

                            $profile->ofCape = curl_get("http://s.optifine.net/capes/{$profile->name}.png")->code === 200;

                            echo json_encode($profile);
                        }
                        
                    }
                } else {
                    die(json_encode(array(
                        'error' => "UUID '$query' was not found."
                    )));
                }
            } else {
                die(json_encode(array(
                    'error' => "Invalid UUID."
                )));
            }
        } else {
            die(json_encode(array(
                'error' => "No query provided."
            )));
        }
    }
}
?>
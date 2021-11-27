<?php
require_once('common.php');
require_once('profile.php');

function sort_name_history($e1, $e2)
{
    if (!isset($e1->{'changedToAt'})) {
        return 1;
    } else if ($e1->{'changedToAt'} < $e2->{'changedToAt'}) {
        return 1;
    }

    return -1;
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
                    $res = http_get("https://api.mojang.com/users/profiles/minecraft/$query");

                    if ($res->code === 200) {
                        $json = json_decode($res->text);

                        if ($json) {
                            if (isset($json->{'errorMessage'})) {
                                die(json_encode(array('error' => $json->{'errorMessage'})));
                            } else {
                                $uuid = $json->{'id'};
                            }
                        }
                    } else {
                        die(json_encode(array('error' => "Profile '$query' was not found.")));
                    }
                } else {
                    die(json_encode(array('error' => 'Illegal characters in username.')));
                }
            } else if (strlen($query) === 32 || strlen($query) === 36) { // UUID validation
                // Valid UUIDs contain A-F, 0-9, and '-'
                if (!preg_match('/[^a-f0-9-]/i', $query)) {
                    $uuid = str_replace('-', '', $query);
                } else {
                    die(json_encode(array('error' => 'Illegal characters in UUID.')));
                }
            } else {
                die(json_encode(array('error' => 'Illegal query format, please use either a valid username or UUID.')));
            }

            if ($uuid !== NULL) {
                $res = http_get("https://api.mojang.com/user/profiles/$uuid/names");

                if ($res->code === 200) {
                    $json = json_decode($res->text);

                    if ($json) {
                        if (isset($json->{'errorMessage'})) {
                            die(json_encode(array('error' => $json->{'errorMessage'})));
                        } else {
                            usort($json, 'sort_name_history');
                            $profile = new Profile($uuid, $json[0]->name, $json, array());

                            echo json_encode($profile);
                        }
                    }
                } else {
                    die(json_encode(array('error' => "UUID '$query' was not found.")));
                }
            } else {
                die(json_encode(array('error' => 'Invalid UUID.')));
            }
        } else {
            die(json_encode(array('error' => 'No query provided.')));
        }
    }
}

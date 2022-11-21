<?php
require_once('common.php');
require_once('Profile.php');

function sort_name_history($e1, $e2)
{
    if (!isset($e1->{'changedToAt'}) || !isset($e2->{'changedToAt'})) return 1;

    if ($e1->{'changedToAt'} < $e2->{'changedToAt'}) {
        return 1;
    }

    return -1;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') return;
if (!isset($_GET['q'])) return;

$query = $_GET['q'];
if ($query === '') {
    die(json_encode(array('error' => 'No query provided.')));
}

$uuid = NULL;
if (strlen($query) < 17) { // Username
    // Valid usernames contain A-Z, 0-9, or '_'
    if (preg_match('/[^\w]/i', $query)) {
        die(json_encode(array('error' => 'Illegal characters in username.')));
    }

    // Username -> UUID resolve
    $res = http_get("https://api.mojang.com/users/profiles/minecraft/$query");
    if ($res->code !== 200) {
        die(json_encode(array('error' => "Profile '$query' was not found.")));
    }

    $json = json_decode($res->text);
    if (!$json) return;

    if (isset($json->{'errorMessage'})) {
        die(json_encode(array('error' => $json->{'errorMessage'})));
    } else {
        $uuid = $json->{'id'};
    }
} else if (strlen($query) === 32 || strlen($query) === 36) { // UUID
    // Valid UUIDs contain A-F, 0-9, and '-'
    if (preg_match('/[^a-f0-9-]/i', $query)) {
        die(json_encode(array('error' => 'Illegal characters in UUID.')));
    }

    $uuid = str_replace('-', '', $query);
} else {
    die(json_encode(array('error' => 'Illegal query format, please use either a valid username or UUID.')));
}

if ($uuid === NULL) {
    die(json_encode(array('error' => 'Invalid UUID.')));
}

$res = http_get("https://api.mojang.com/user/profiles/$uuid/names");
if ($res->code !== 200) {
    die(json_encode(array('error' => "UUID '$uuid' was not found.")));
}

$json = json_decode($res->text);
if (!$json) return;

if (isset($json->{'errorMessage'})) {
    die(json_encode(array('error' => $json->{'errorMessage'})));
} else {
    usort($json, 'sort_name_history');
    $profile = new profile($uuid, $json[0]->name, $json, array());

    echo json_encode($profile);
}

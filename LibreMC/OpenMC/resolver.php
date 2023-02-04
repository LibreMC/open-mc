<?php namespace LibreMC\OpenMC;
require_once('Utilities/http.php');
require_once('profile.php');

use function LibreMC\OpenMC\Utilities\http_get;

function get_profile_from_username(string $username) {
    // Valid usernames must contain A-Z, 0-9, or '_' (there are SOME exceptions, but we won't accommodate for them.)
    if (preg_match('/[^\w]/i', $iusernamenput)) {
        die(json_encode(array('error' => 'Illegal characters in username.')));
    }

    // Request lookup by username
    $res = http_get("https://api.mojang.com/users/profiles/minecraft/{$username}");
    if ($res->code !== 200) {
        die(json_encode(array('error' => "No profile with username '{$username}'.")));
    }

    $json = json_decode($res->text);
    if (!$json) return;

    if (isset($json->{'errorMessage'})) {
        die(json_encode(array('error' => $json->{'errorMessage'})));
    } else {
        return new Profile($json->{'id'}, $json->{'name'});
    }
}

function get_profile_from_uuid(string $uuid) {
    // Valid UUIDs only contain A-F, 0-9, and '-'
    if (preg_match('/[^a-f0-9-]/i', $uuid)) {
        die(json_encode(array('error' => 'Illegal characters in UUID.')));
    }

    $uuid = str_replace('-', '', $uuid);

    // Request lookup by UUID
    $res = http_get("https://sessionserver.mojang.com/session/minecraft/profile/{$uuid}");
    if ($res->code !== 200) {
        die(json_encode(array('error' => "No profile with UUID '{$uuid}'.")));
    }

    $json = json_decode($res->text);
    if (!$json) return;

    if (isset($json->{'errorMessage'})) {
        die(json_encode(array('error' => $json->{'errorMessage'})));
    } else {
        return new Profile($json->{'id'}, $json->{'name'});
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') return;
if (!isset($_GET['q'])) return;

$query = $_GET['q'];
if ($query === '') {
    die(json_encode(array('error' => 'No query provided.')));
}

$profile = NULL;
if (strlen($query) < 17) {
    $profile = get_profile_from_username($query);
} else if (strlen($query) === 32 || strlen($query) === 36) {
    $profile = get_profile_from_uuid($query);
} else {
    die(json_encode(array('error' => 'Illegal query format, please use either a valid username or UUID.')));
}

if ($profile === NULL) {
    die(json_encode(array('error' => 'No profile was found.')));
}

echo json_encode($profile);
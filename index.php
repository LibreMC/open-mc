<?php
$profile = NULL;

function curl_get($url, $query = array()) {
    $ch = curl_init($url);

    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_HEADER => FALSE,
        CURLOPT_SSL_VERIFYPEER => FALSE
    ));

    $result = '';
    if (!($result = curl_exec($ch))) {
        trigger_error(curl_error($ch));
    }

    curl_close($ch);
    return $result;
}

function sort_name_history($e1, $e2) {
    if (!$e1->{'changedToAt'}) {
        return 1;
    } else if ($e1->{'changedToAt'} < $e2->{'changedToAt'}) {
        return 1;
    }

    return -1;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['q'])) {
        $mcUser = $_GET['q'];
        if ($mcUser !== '' && !preg_match('/[^a-zA-Z0-9_-]/', $mcUser)) {
            $uuid = '';

            if (strlen($mcUser) < 16) {
                // Username -> UUID resolve
                $res = curl_get("https://api.mojang.com/users/profiles/minecraft/$mcUser");
                if ($res) {
                    $json = json_decode($res);
                    if ($json) {
                        if ($json->{'errorMessage'}) {
                            echo 'Mojang Error @ Profile Fetch: ' . $json->{'errorMessage'};
                        } else {
                            $uuid = $json->{'id'};
                        }
                    }
                } else {
                    echo nl2br("Profile '$mcUser' not found!\n");
                }
            } else {
                $uuid = str_replace('-', '', $mcUser);
            }

            if (strlen($uuid) == 32) {
                $uuid = preg_replace('/(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})/', '$1-$2-$3-$4-$5', $uuid);
                $res = curl_get("https://api.mojang.com/user/profiles/$uuid/names");

                if ($res) {
                    $json = json_decode($res);

                    if($json) {
                        if ($json->{'errorMessage'}) {
                            echo 'Mojang Error @ History Fetch: ' . $json->{'errorMessage'};
                        } else {
                            usort($json, 'sort_name_history');
                            $profile = $json;

                            echo nl2br($json[0]->{'name'} . " (current)\n\n");
    
                            foreach ($json as $entry) {
                                echo $entry->{'name'};
                                if (!$entry->{'changedToAt'}) {
                                    echo nl2br(" (original)\n");
                                } else {
                                    echo nl2br(' (' . gmdate('d / m / Y H:i:s A', $entry->{'changedToAt'} / 1000) . ")\n");
                                }
                            }
                        }
                    }
                }
            } else {
                echo "UUID '$uuid' length was not 32? (real = " . strlen($uuid) . ')';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title> <?php echo $profile != NULL ? $profile[0]->{'name'} . ' | ' : '' ?>Open-MC</title>
    <form method="GET">
        <input type="text" name="q" placeholder="Username / UUID" maxlength="36" />
    </form>
</head>
<body>
</body>
</html>
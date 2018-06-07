<?

function get_apk_permissions($file) {
    if (!is_file($file))
        return false;
    $aapt = load_config('aapt_executable_path');
    $cmd = "${aapt} d badging '${file}'";
    $stdout = shell_exec($cmd);
    if (!preg_match_all("/uses-permission:'(.+?)'/", $stdout, $matches))
        return false;
    return $matches[1];
}


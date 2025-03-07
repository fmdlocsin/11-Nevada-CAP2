<?php
function getAreaManagerFilter() {
    // Check if the logged-in user is an area manager.
    if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'area-manager') {
        // Since area_code is not stored in users_accounts, we rely on a default or manually set value.
        // In your login code, you might already set a default like:
        // $_SESSION['area_code'] = '1014';
        $area_code = isset($_SESSION['area_code']) ? $_SESSION['area_code'] : '1014';
        return ["clause" => "AND area_code = ?", "param" => $area_code];
    }
    return ["clause" => "", "param" => null];
}
?>


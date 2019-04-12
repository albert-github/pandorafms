<?php

// Pandora FMS - http://pandorafms.com
// ==================================================
// Copyright (c) 2005-2019 Artica Soluciones Tecnologicas
// Please see http://pandorafms.org for full contribution list
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
require_once '../../include/config.php';

// Set root on homedir, as defined in setup.
chdir($config['homedir']);

ob_start();
// Enterprise support.
if (file_exists(ENTERPRISE_DIR.'/load_enterprise.php')) {
    include_once ENTERPRISE_DIR.'/load_enterprise.php';
}

if (file_exists(ENTERPRISE_DIR.'/include/functions_login.php')) {
    include_once ENTERPRISE_DIR.'/include/functions_login.php';
}

require_once $config['homedir'].'/vendor/autoload.php';

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n";
echo '<html xmlns="http://www.w3.org/1999/xhtml">'."\n";
echo '<head>';

global $vc_public_view;
$vc_public_view = true;
$config['public_view'] = true;

// This starts the page head. In the call back function,
// things from $page['head'] array will be processed into the head.
ob_start('ui_process_page_head');
// Enterprise main.
enterprise_include('index.php');

require_once 'include/functions_visual_map.php';

$hash = (string) get_parameter('hash');
$visualConsoleId = (int) get_parameter('id_layout');
$config['id_user'] = (string) get_parameter('id_user');
$refr = (int) get_parameter('refr', $config['refr']);

if (!isset($config['pure'])) {
    $config['pure'] = 0;
}

$myhash = md5($config['dbpass'].$visualConsoleId.$config['id_user']);

// Check input hash.
if ($myhash != $hash) {
    exit;
}

// Load Visual Console.
use Models\VisualConsole\Container as VisualConsole;
$visualConsole = null;
try {
    $visualConsole = VisualConsole::fromDB(['id' => $visualConsoleId]);
} catch (Throwable $e) {
    db_pandora_audit(
        'ACL Violation',
        'Trying to access visual console without Id'
    );
    include $config['homedir'].'/general/noaccess.php';
    exit;
}

$visualConsoleData = $visualConsole->toArray();
$visualConsoleName = $visualConsoleData['name'];

echo '<div id="visual-console-container"></div>';

// Floating menu - Start.
echo '<div id="vc-controls" style="z-index:300;">';

echo '<div id="menu_tab">';
echo '<ul class="mn">';

// QR code.
echo '<li class="nomn">';
echo '<a href="javascript: show_dialog_qrcode();">';
echo '<img class="vc-qr" src="../../images/qrcode_icon_2.jpg"/>';
echo '</a>';
echo '</li>';

// Console name.
echo '<li class="nomn">';
echo '<div class="vc-title">'.$visualConsoleName.'</div>';
echo '</li>';

echo '</ul>';
echo '</div>';

echo '</div>';

// QR code dialog.
echo '<div style="display: none;" id="qrcode_container" title="'.__('QR code of the page').'">';
echo '<div id="qrcode_container_image"></div>';
echo '</div>';

// Check groups can access user.
$aclUserGroups = [];
if (!users_can_manage_group_all('AR')) {
    $aclUserGroups = array_keys(users_get_groups(false, 'AR'));
}

// Load Visual Console Items.
$visualConsoleItems = VisualConsole::getItemsFromDB(
    $visualConsoleId,
    $aclUserGroups
);

ui_require_javascript_file('pandora_visual_console');
visual_map_load_client_resources();
?>

<style type="text/css">
    body {
        background-color: <?php echo $visualConsoleData['backgroundColor']; ?>;
    }
</style>

<script type="text/javascript">
    var container = document.getElementById("visual-console-container");
    var props = <?php echo (string) $visualConsole; ?>;
    var items = <?php echo '['.implode($visualConsoleItems, ',').']'; ?>;
    var baseUrl = "<?php echo $config['homeurl']; ?>";
    var handleUpdate = function (prevProps, newProps) {
        if (!newProps) return;

        // Change the background color when the fullscreen mode is enabled.
        if (prevProps
            && prevProps.backgroundColor != newProps.backgroundColor
        ) {
            var body = document.querySelector("body");
            if (body !== null) {
                body.style.backgroundColor = newProps.backgroundColor
            }
        }

        // Change the title.
        if (prevProps && prevProps.name != newProps.name) {
            var title = document.querySelector("div.vc-title");
            if (title !== null) {
                title.textContent = newProps.name;
            }
        }

        // Change the links.
        if (prevProps && prevProps.id !== newProps.id) {
            var regex = /(id=|id_visual_console=|id_layout=)\d+(&?)/gi;
            var replacement = '$1' + newProps.id + '$2';

            // Tab links.
            var menuLinks = document.querySelectorAll("div#menu_tab a");
            if (menuLinks !== null) {
                menuLinks.forEach(function (menuLink) {
                    menuLink.href = menuLink.href.replace(regex, replacement);
                });
            }

            // Change the URL (if the browser has support).
            if ("history" in window) {
                var href = window.location.href.replace(regex, replacement);
                window.history.replaceState({}, document.title, href);
            }
        }
    }
    var visualConsole = createVisualConsole(
        container,
        props,
        items,
        baseUrl,
        <?php echo ($refr * 1000); ?>,
        handleUpdate
    );

    $(document).ready(function () {
        var controls = document.getElementById('vc-controls');
        if (controls) autoHideElement(controls, 1000);
    });
</script>

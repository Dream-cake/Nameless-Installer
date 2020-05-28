<?php
/*
 *  Made by Aberdeener
 *  https://github.com/NamelessMC/Nameless-Installer/
 *  Nameless-Installer version 1.0.0-rc1
 * 
 *  NamelessMC by Samerton
 *  https://github.com/NamelessMC/Nameless/
 *
 *  License: MIT
 */

// Dont allow rerunning if Nameless is currently installed
if (file_exists('./core/config.php')) {
    header('Location: http://' . $_SERVER['HTTP_HOST'] . '/');
}

// Display errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$version = $_GET['ver'] ?? 'null';
$step = $_GET['step'] ?? 'welcome';

$zip_url = '';
$zip_file = 'namelessmc-' . $version . '.zip';
// These will need to be updated with each NMC release
$zip_subdir = ($version == 'v1' ? 'Nameless-1.0.21' : 'Nameless-2.0.0-pr7');

// Recursively copy a directory to another location. Used after extraction of the zip file
function moveDirectory($source, $dest)
{
    $result = false;

    if (is_file($source)) {
        if ($dest[strlen($dest) - 1] == '/') {
            if (!file_exists($dest)) {
                cmfcDirectory::makeAll($dest, 0755, true);
            }
            $__dest = $dest . "/" . basename($source);
        } else $__dest = $dest;
        $result = copy($source, $__dest);
        chmod($__dest, 0755);
    } elseif (is_dir($source)) {
        if ($dest[strlen($dest) - 1] == '/') {
            if ($source[strlen($source) - 1] != '/') {
                // Change parent itself and its contents
                $dest = $dest . basename($source);
                @mkdir($dest);
            }
        } else @mkdir($dest, 0755);
        $dirHandle = opendir($source);
        while ($file = readdir($dirHandle)) {
            if ($file != "." && $file != "..") {
                $__dest = $dest . "/" . $file;
                $result = moveDirectory($source . "/" . $file, $__dest);
            }
        }
        closedir($dirHandle);
    } else $result = false;

    return $result;
}

// Used to delete the original extracted zip dir
function deleteDirectory($dir)
{
    if (!file_exists($dir))  return true;

    if (!is_dir($dir)) return unlink($dir);

    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item))  return false;
    }

    return rmdir($dir);
}

// Used to display errors
function showError($message)
{
?>
    <p style="color: red;">[ERROR]: <?php echo $message ?></p>
    <p>If this continues to happen, contact support in our <a href=" https://discord.gg/QWdS9CB" target="_blank">Discord</a>.</p>
    <a href="?step=select">Click here to try again.</a>
<?php
}

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Easy Install • NamelessMC</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <link rel="icon" href="https://namelessmc.com/favicon.ico">
</head>

<body style="background-color: #F3F6FA">

    <style>
        .btn-version,
        .btn-version:hover {
            color: white;
            border-color: #90C2E7;
        }

        .btn-version:hover {
            border-color: white;
            outline: 5px;
        }
    </style>

    <div class="container" align="center">
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-8">

                <br />
                <br />
                <div>
                    <h1>Easy Install • NamelessMC</h1>
                    <h3>Step: <?php echo ucfirst($step) ?></h3>
                    <?php if ($version != 'null') { ?>
                        <h3>Version: <?php echo $version ?></h3>
                    <?php } ?>
                    <hr>
                </div>

                <?php
                switch ($step) {
                    case 'welcome': {
                ?>
                            <p><i>Welcome to NamelessMC!</i></p>
                            <p>This script will download and extract NamelessMC for you.</p>
                            <p>In the next step we will choose which version of NamelessMC to install.</p>
                            <a class="btn btn-primary" style="color: white;" href="?step=select">Continue »</a>
                        <?php
                            break;
                        }
                    case 'select': {
                        ?>
                            <p><i>Now you must choose which version of NamelessMC you want to install.</i></p>
                            <p>NamelessMC has two versions: <b>v1 (1.0.21)</b> and <b>v2 (pr7)</b>.</p>
                            <p><b>v2</b> is recommended by NamelessMC developers as it is a complete rewrite and provides many more functionalities - such as modules, widgets and beautiful templates.</p>
                            <br />
                            <div align="center">
                                <div class="row">
                                    <div class="card mx-auto" style="width: 18rem;">
                                        <div class="card-body rounded" style="background-color: #2185D0">
                                            <h5 class="card-title" style="color: white">Legacy</h5>
                                            <img src="https://namelessmc.com/custom/templates/Nameless-Semantic/img/v1-homepage.jpg" class="card-img" alt="NamelessMC v1.0.21">
                                            <hr style="background-color: white">
                                            <a href="?step=verify&ver=v1" class="btn btn-outline btn-version">v1.0.21</a>
                                        </div>
                                    </div>
                                    <div class="card mx-auto" style="width: 18rem;">
                                        <div class="card-body rounded" style="background-color: #21BA45">
                                            <h5 class="card-title" style="color: white">Recommended</h5>
                                            <img src="https://namelessmc.com/custom/templates/Nameless-Semantic/img/v2-homepage.jpg" class="card-img" alt="NamelessMC v2.0.0-pr7">
                                            <hr style="background-color: white">
                                            <a href="?step=verify&ver=v2" class="btn btn-outline btn-version">v2.0.0-pr7</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php
                            break;
                        }
                    case 'verify': {
                        ?>
                            <p><i>NamelessMC <?php echo $version ?> will now download and extract itself.</i></p>
                            <p>It will automatically refresh, so please do not reload the page.</p>
                            <p>Click <a href="?step=download&ver=<?php echo $version ?>" onclick="statusUpdate()">here</a> to proceed.</p>
                            <div id="status" align="center"></div>
                            <h4 id="no-reload" style="color: red; display: none"><b>DO NOT RELOAD</b></h4>
                            <?php
                            break;
                        }
                    case 'download': {
                            if ($version == 'v1') {
                                $zip_url = 'https://github.com/NamelessMC/Nameless/archive/v1.0.21.zip';
                            } else if ($version == 'v2') {
                                $zip_url = 'https://github.com/NamelessMC/Nameless/archive/v2.0.0-pr7.zip';
                            } else {
                                header('Location: http://' . $_SERVER['HTTP_HOST'] . '/easy-install.php?step=select');
                                break;
                            }
                            if (copy($zip_url, $zip_file)) { ?>
                                <p style="color: green;">[DEBUG]: NamelessMC (<?php echo $zip_file ?>) downloaded...</p>
                            <?php } else {
                                showError("NamelessMC could not be downloaded.");
                                break;
                            }

                            // Unzip, move files and cleanup
                            $zip = new ZipArchive;
                            if ($zip->open($zip_file) === true) {
                                $zip->extractTo('./');
                                $zip->close(); ?>
                                <p style="color: green;">[DEBUG]: Success extracting zip file...</p>
                                <?php if (moveDirectory($zip_subdir, '.')) { ?>
                                    <p style="color: green;">[DEBUG]: Success copying files from zip to root directory...</p>
                                    <?php if (deleteDirectory($zip_subdir)) { ?>
                                        <p style="color: green;">[DEBUG]: Success deleting original folder...</p>
                                    <?php } else { ?>
                                        <p style="color: goldenrod;">[WARNING]: NamelessMC extracted folder could not be deleted, but it safe to continue.</p>
                                    <?php }
                                    if (unlink($zip_file)) { ?>
                                        <p style="color: green;">[DEBUG]: Success deleting zip file...</p>
                                    <?php } else { ?>
                                        <p style="color: goldenrod;">[WARNING]: NamelessMC zip file could not be deleted, but it is safe to continue.</p>
                                    <?php } ?>
                                    <p>Attempting to redirect you... <a href="http://<?php echo $_SERVER['HTTP_HOST'] ?>">Click here</a> if nothing happens.</p>
                                    <script type="text/javascript">
                                        window.location.href = 'http://<?php echo $_SERVER['HTTP_HOST'] ?>'
                                    </script>
                    <?php } else {
                                    showError("NamelessMC could not be moved from the extracted folder.");
                                }
                            } else {
                                showError("NamelessMC could not be extracted.");
                            }
                            break;
                        }
                    default:
                        header('Location: http://' . $_SERVER['HTTP_HOST']);
                }
                if ($step != 'welcome') { ?>
                    <hr>
                    <div align="center">
                        <button onclick="history.back();" class="btn btn-sm btn-secondary">« Back</button>
                    </div>
                <?php } ?>
                <div align="right">
                    <p>Nameless-Installer | Version: 1.0.0-rc1</p>
                </div>
            </div>
            <div class="col-md-2"></div>
        </div>
    </div>

    <script>
        const status = document.getElementById("status");
        status.innerHTML = "STANDBY"
        status.style.color = "Orange"
        status.style.fontSize = "large"
        status.style.fontWeight = "bold"

        let installing = false;

        function statusUpdate() {
            status.innerHTML = "WORKING"
            status.style.color = "Green"
            installing = true;
            document.getElementById("no-reload").style.display = "block"
        }

        // This seems to only work in Firefox & Chrome, in Safari nothing changes from "STANDBY"
        let dotCount = 0
        var dots = window.setInterval(function() {
            if (installing) {
                if (dotCount < 3) {
                    status.innerHTML += "."
                        ++dotCount
                } else {
                    status.innerHTML = "WORKING"
                    dotCount = 0
                }
            }
        }, 450);
    </script>

</body>

</html>
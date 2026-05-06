<?php
session_start();

/* =========================
   KONFIGURASI LOGIN ADMIN
========================= */
$adminUser = 'admin';
$adminPass = '123456';

$configFile = __DIR__ . '/config.json';
$message = '';

/* =========================
   DEFAULT CONFIG
========================= */
$config = [
    'url' => 'https://google.com',
    'camera' => true,

    'remote_button' => [
        'enabled' => false,
        'text' => 'Menu',
        'target_url' => '',
        'show_time' => '',
        'confirm_text' => 'Yakin?'
    ],

    'popup' => [
        'enabled' => false,
        'title' => 'PESAN PENGAWAS',
        'message' => '',
        'force' => true
    ],
    
    'force_exit' => [
    'enabled' => false,
    'message' => 'Ujian telah selesai. Aplikasi akan ditutup.'
],
];

/* =========================
   LOAD CONFIG JSON
========================= */
if (file_exists($configFile)) {
    $json = json_decode(file_get_contents($configFile), true);
    if ($json) {
        $config = array_replace_recursive($config, $json);
    }
}

/* =========================
   LOGIN
========================= */
if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === $adminUser && $password === $adminPass) {
        $_SESSION['admin_login'] = true;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $message = 'Username atau password salah.';
    }
}

/* =========================
   LOGOUT
========================= */
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

/* =========================
   SIMPAN DASHBOARD
========================= */
if (isset($_SESSION['admin_login']) && $_SESSION['admin_login'] === true && $_SERVER['REQUEST_METHOD'] === 'POST') {

    /* URL */
    if (isset($_POST['save_url'])) {
        if (filter_var($_POST['url'], FILTER_VALIDATE_URL)) {
            $config['url'] = $_POST['url'];
            $message = 'URL berhasil disimpan.';
        } else {
            $message = 'URL tidak valid.';
        }
    }

    /* CAMERA */
    if (isset($_POST['toggle_camera'])) {
        $config['camera'] = ($_POST['toggle_camera'] === 'on');
        $message = 'Status kamera diperbarui.';
    }

    /* REMOTE BUTTON */
    if (isset($_POST['save_remote'])) {
        $config['remote_button'] = [
            'enabled' => isset($_POST['enabled']),
            'text' => $_POST['text'] ?? 'Menu',
            'target_url' => $_POST['target_url'] ?? '',
            'show_time' => $_POST['show_time'] ?? '',
            'confirm_text' => $_POST['confirm_text'] ?? 'Yakin?'
        ];
        $message = 'Remote button disimpan.';
    }

    /* POPUP ADMIN */
    if (isset($_POST['save_popup'])) {
        $config['popup'] = [
            'enabled' => isset($_POST['popup_enabled']),
            'title' => $_POST['popup_title'] ?? 'PESAN PENGAWAS',
            'message' => $_POST['popup_message'] ?? '',
            'force' => isset($_POST['popup_force']),
             'id' => (string) time()
        ];
        $message = 'Popup broadcast berhasil dikirim.';
    }

    /* MATIKAN POPUP */
    if (isset($_POST['disable_popup'])) {
        $config['popup']['enabled'] = false;
        $config['popup']['message'] = '';
        $message = 'Popup dimatikan.';
    }
    
  /* =========================
   FORCE EXIT SEKALI KLIK
   Saat tombol ditekan:
   false -> true -> simpan -> kembali false
========================= */
if (isset($_POST['force_exit_once'])) {

    // ambil pesan dari form
    $forceMessage = trim($_POST['force_exit_message'] ?? '');

    if ($forceMessage === '') {
        $forceMessage = 'Ujian telah selesai. Aplikasi ditutup oleh admin.';
    }

    /* =========================
       STEP 1 : AKTIFKAN
    ========================= */
    $config['force_exit'] = [
        'enabled' => true,
        'message' => $forceMessage
    ];

    file_put_contents(
        $configFile,
        json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );

    /* =========================
       STEP 2 : TUNGGU
       Beri waktu aplikasi siswa membaca
    ========================= */
    usleep(2000000); // 2 detik

    /* =========================
       STEP 3 : MATIKAN LAGI
    ========================= */
    $config['force_exit']['enabled'] = false;

    file_put_contents(
        $configFile,
        json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );

    $message = 'Force Exit sekali kirim berhasil.';
}

    file_put_contents(
        $configFile,
        json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin CBT</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f3f4f6;
    padding: 24px;
}
.card {
    max-width: 900px;
    margin: auto;
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,.08);
}
input, textarea {
    width: 100%;
    padding: 12px;
    border-radius: 10px;
    border: 1px solid #ccc;
    margin-top: 8px;
    margin-bottom: 15px;
    box-sizing: border-box;
}
textarea {
    min-height: 120px;
    resize: vertical;
}
button {
    width: 100%;
    padding: 12px;
    margin-top: 10px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-weight: bold;
}
.btn-main { background:#1f2937; color:white; }
.btn-on { background:green; color:white; }
.btn-off { background:red; color:white; }
.btn-blue { background:#2563eb; color:white; }
.btn-orange { background:#ea580c; color:white; }

.status {
    margin: 15px 0;
    font-weight: bold;
}
.logout {
    text-decoration:none;
    display:inline-block;
    margin-bottom:15px;
    color:red;
    font-weight:bold;
}
hr {
    margin: 30px 0;
}
h2, h3 {
    margin-bottom: 15px;
}
label {
    font-weight: bold;
}
.preview {
    background:#111827;
    color:#22c55e;
    padding:15px;
    border-radius:12px;
    overflow:auto;
    font-size:14px;
}
</style>
</head>
<body>

<div class="card">

<?php if (!isset($_SESSION['admin_login'])): ?>

    <h2>Login Admin CBT</h2>

    <?php if ($message): ?>
        <div><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button name="login" class="btn-main">Login</button>
    </form>

<?php else: ?>

    <a class="logout" href="?logout=1">Logout</a>

    <h2>Dashboard Admin CBT</h2>

    <?php if ($message): ?>
        <div><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- STATUS -->
    <div class="status">
        Kamera:
        <span style="color:<?= $config['camera'] ? 'green' : 'red' ?>">
            <?= $config['camera'] ? 'AKTIF' : 'NONAKTIF' ?>
        </span>
    </div>

    <!-- CAMERA -->
    <form method="POST">
        <button name="toggle_camera" value="on" class="btn-on">Hidupkan Kamera</button>
        <button name="toggle_camera" value="off" class="btn-off">Matikan Kamera</button>
    </form>

    <hr>

    <!-- URL -->
    <form method="POST">
        <label>URL Website CBT</label>
        <input type="url" name="url"
               value="<?= htmlspecialchars($config['url']) ?>" required>

        <button name="save_url" class="btn-main">Simpan URL</button>
    </form>

    <hr>

    <!-- REMOTE BUTTON -->
    <h3>Remote Button</h3>

    <form method="POST">

        <label>
            <input type="checkbox" name="enabled"
                <?= $config['remote_button']['enabled'] ? 'checked' : '' ?>>
            Aktifkan Tombol
        </label><br><br>

        <label>Text Tombol</label>
        <input type="text" name="text"
            value="<?= htmlspecialchars($config['remote_button']['text']) ?>">

        <label>URL Tujuan</label>
        <input type="url" name="target_url"
            value="<?= htmlspecialchars($config['remote_button']['target_url']) ?>">

        <label>Pesan Konfirmasi</label>
        <input type="text" name="confirm_text"
            value="<?= htmlspecialchars($config['remote_button']['confirm_text']) ?>">

        <label>Jam Muncul (HH:mm)</label>
        <input type="time" name="show_time"
            value="<?= htmlspecialchars($config['remote_button']['show_time']) ?>">

        <button name="save_remote" class="btn-main">Simpan Remote Button</button>
    </form>

    <hr>

    <!-- POPUP BROADCAST -->
    <h3>Broadcast Popup ke Semua Siswa</h3>

    <form method="POST">

        <label>
            <input type="checkbox" name="popup_enabled"
                <?= $config['popup']['enabled'] ? 'checked' : '' ?>>
            Aktifkan Popup
        </label><br><br>

        <label>Judul Popup</label>
        <input type="text" name="popup_title"
            value="<?= htmlspecialchars($config['popup']['title']) ?>">

        <label>Isi Pesan</label>
        <textarea name="popup_message"><?= htmlspecialchars($config['popup']['message']) ?></textarea>

        <label>
            <input type="checkbox" name="popup_force"
                <?= $config['popup']['force'] ? 'checked' : '' ?>>
            Paksa tampil (hanya tombol OK)
        </label>

        <button name="save_popup" class="btn-blue">Kirim Popup Sekarang</button>
        <button name="disable_popup" class="btn-orange">Matikan Popup</button>

    </form>


  <hr>

<!-- FORCE EXIT SEKALI -->
<h3>Force Exit Sekali Klik</h3>

<form method="POST"
      onsubmit="return confirm('Yakin ingin mengeluarkan semua siswa sekarang?');">

    <label>Pesan Force Exit</label>
    <textarea name="force_exit_message"><?= htmlspecialchars(
        $config['force_exit']['message'] ?? 'Ujian telah selesai. Aplikasi akan ditutup.'
    ) ?></textarea>

    <button name="force_exit_once" class="btn-off">
        KELUARKAN SEMUA SISWA SEKARANG
    </button>

</form>

    <!-- PREVIEW JSON -->
    <h3>Preview config.json</h3>
    <pre class="preview"><?= htmlspecialchars(
json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
) ?></pre>

<?php endif; ?>

</div>

</body>
</html>
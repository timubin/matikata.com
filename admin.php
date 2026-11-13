<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function is_logged_in(): bool
{
    return !empty($_SESSION['admin_logged_in']);
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: admin.php');
    exit;
}

$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !is_logged_in()) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (hash_equals(ADMIN_USERNAME, $username) && hash_equals(ADMIN_PASSWORD, $password)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_user'] = $username;
        header('Location: admin.php');
        exit;
    }
    $loginError = 'Invalid ID or password.';
}

$submissions = [];
$dbError = '';
if (is_logged_in()) {
    try {
        $pdo = get_db();
        $submissions = $pdo->query('SELECT * FROM mason_applications ORDER BY created_at DESC')->fetchAll();
    } catch (Throwable $e) {
        $dbError = 'Database connection error. Please check MySQL and config.php.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel | Submissions</title>
  <style>
    :root{
      --bg:#f5f7fb;
      --card:#ffffff;
      --ink:#1c2742;
      --muted:#6b7384;
      --accent:#d10000;
      --border:#e4e8f1;
    }
    *{box-sizing:border-box;}
    body{
      margin:0;
      font-family: "Segoe UI", system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
      background:var(--bg);
      color:var(--ink);
    }
    a{color:var(--accent);text-decoration:none;}
    .container{
      max-width:1200px;
      margin:40px auto;
      padding:0 16px;
    }
    .card{
      background:var(--card);
      border:1px solid var(--border);
      border-radius:14px;
      box-shadow:0 10px 30px rgba(0,0,0,0.05);
      padding:22px;
    }
    h1,h2,h3{
      margin:0 0 10px;
    }
    .muted{color:var(--muted);}
    .login-card{
      max-width:420px;
      margin:80px auto;
    }
    form label{
      display:block;
      font-weight:600;
      margin-bottom:6px;
    }
    form input{
      width:100%;
      padding:10px 12px;
      border-radius:10px;
      border:1px solid var(--border);
      font-size:15px;
      margin-bottom:14px;
      outline:none;
    }
    form input:focus{border-color:var(--accent);}
    button{
      padding:11px 14px;
      border:none;
      background:var(--accent);
      color:#fff;
      border-radius:10px;
      font-weight:700;
      cursor:pointer;
    }
    .error{
      padding:10px 12px;
      background:#fdecea;
      color:#8a1f2b;
      border:1px solid #f5c2c7;
      border-radius:10px;
      margin-bottom:12px;
    }
    .topbar{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap:12px;
      margin-bottom:18px;
    }
    table{
      width:100%;
      border-collapse:collapse;
      font-size:14px;
    }
    th,td{
      padding:10px 8px;
      text-align:left;
      border-bottom:1px solid var(--border);
      vertical-align:top;
    }
    th{
      background:#f8fafc;
      font-weight:700;
    }
    .pill{
      display:inline-block;
      padding:3px 9px;
      border-radius:999px;
      background:#eef2ff;
      color:#27346a;
      font-weight:700;
      font-size:12px;
      border:1px solid #dfe6ff;
    }
    .table-wrap{
      overflow:auto;
      max-height:70vh;
    }
    .actions a{
      display:inline-flex;
      align-items:center;
      gap:6px;
      font-weight:600;
    }
  </style>
</head>
<body>
<?php if (!is_logged_in()): ?>
  <div class="container login-card">
    <div class="card">
      <h2>Admin Login</h2>
      <p class="muted">Enter your ID and password to view submissions.</p>
      <?php if ($loginError): ?>
        <div class="error"><?php echo e($loginError); ?></div>
      <?php endif; ?>
      <form method="post" action="admin.php">
        <label for="username">Admin ID</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Login</button>
      </form>
    </div>
  </div>
<?php else: ?>
  <div class="container">
    <div class="card">
      <div class="topbar">
        <div>
          <h2>Service Provider Submissions</h2>
          <div class="muted">Total entries: <strong><?php echo count($submissions); ?></strong></div>
        </div>
        <div class="actions">
          <a href="logout.php">Logout</a>
        </div>
      </div>
      <?php if ($dbError): ?>
        <div class="error"><?php echo e($dbError); ?></div>
      <?php endif; ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Mobile</th>
              <th>Father</th>
              <th>Mother</th>
              <th>NID</th>
              <th>Gender</th>
              <th>Marital</th>
              <th>Spouse Name</th>
              <th>Spouse NID</th>
              <th>Blood</th>
              <th>Permanent Address</th>
              <th>Present Address</th>
              <th>Sons</th>
              <th>Daughters</th>
              <th>Note</th>
              <th>Submitted</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($submissions)): ?>
              <tr><td colspan="16">No submissions yet.</td></tr>
            <?php else: ?>
              <?php foreach ($submissions as $row): ?>
                <tr>
                  <td><?php echo e($row['mason_name'] ?? ''); ?></td>
                  <td><?php echo e($row['mobile_number'] ?? ''); ?></td>
                  <td><?php echo e($row['father_name'] ?? ''); ?></td>
                  <td><?php echo e($row['mother_name'] ?? ''); ?></td>
                  <td><?php echo e($row['nid_number'] ?? ''); ?></td>
                  <td><?php echo e($row['gender'] ?? ''); ?></td>
                  <td><?php echo e($row['marital_status'] ?? ''); ?></td>
                  <td><?php echo e($row['spouse_name'] ?? ''); ?></td>
                  <td><?php echo e($row['spouse_nid'] ?? ''); ?></td>
                  <td><span class="pill"><?php echo e($row['blood_group'] ?? ''); ?></span></td>
                  <td><?php echo nl2br(e($row['permanent_address'] ?? '')); ?></td>
                  <td><?php echo nl2br(e($row['present_address'] ?? '')); ?></td>
                  <td><?php echo (int)($row['sons'] ?? 0); ?></td>
                  <td><?php echo (int)($row['daughters'] ?? 0); ?></td>
                  <td><?php echo nl2br(e($row['note'] ?? '')); ?></td>
                  <td><?php echo e($row['created_at'] ?? ''); ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php endif; ?>
</body>
</html>

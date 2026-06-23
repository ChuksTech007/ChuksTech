<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/repo.php';
requireLogin();

$repo        = new ProjectRepo();
$message     = '';
$messageType = '';

// --- Handle POST actions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ADD PROJECT
    if ($action === 'add') {
        $title       = trim($_POST['title']       ?? '');
        $category    = trim($_POST['category']    ?? '');
        $description = trim($_POST['description'] ?? '');
        $live_url    = trim($_POST['live_url']    ?? '');
        $github_url  = trim($_POST['github_url']  ?? '');
        $tags        = trim($_POST['tags']        ?? '');
        $is_visible  = isset($_POST['is_visible']);
        $sort_order  = (int)($_POST['sort_order'] ?? 0);
        $image_path  = '';

        if ($title && $category) {
            if (!empty($_FILES['image']['name'])) {
                $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $ftype   = mime_content_type($_FILES['image']['tmp_name']);
                if (in_array($ftype, $allowed, true)) {
                    $result = uploadToCloudinary($_FILES['image']['tmp_name']);
                    if (isset($result['url'])) {
                        $image_path = $result['url'];
                    } else {
                        $message = $result['error'] ?? 'Cloudinary upload failed.';
                        $messageType = 'error';
                    }
                } else {
                    $message = 'Invalid file type. Only JPG, PNG, GIF, WebP allowed.';
                    $messageType = 'error';
                }
            } else {
                $image_path = trim($_POST['image_url'] ?? '');
            }

            if (!$message) {
                $repo->insert([
                    'title'       => $title,
                    'category'    => $category,
                    'description' => $description,
                    'image'       => $image_path,
                    'live_url'    => $live_url,
                    'github_url'  => $github_url,
                    'tags'        => $tags,
                    'is_visible'  => $is_visible,
                    'sort_order'  => $sort_order,
                ]);
                $message = "Project \"$title\" added successfully.";
                $messageType = 'success';
            }
        } else {
            $message = 'Title and category are required.';
            $messageType = 'error';
        }
    }

    // UPDATE PROJECT
    if ($action === 'update') {
        $id          = trim($_POST['id']          ?? '');
        $title       = trim($_POST['title']       ?? '');
        $category    = trim($_POST['category']    ?? '');
        $description = trim($_POST['description'] ?? '');
        $live_url    = trim($_POST['live_url']    ?? '');
        $github_url  = trim($_POST['github_url']  ?? '');
        $tags        = trim($_POST['tags']        ?? '');
        $is_visible  = isset($_POST['is_visible']);
        $sort_order  = (int)($_POST['sort_order'] ?? 0);

        if ($id && $title && $category) {
            $data = compact('title','category','description','live_url','github_url','tags','is_visible','sort_order');

            if (!empty($_FILES['image']['name'])) {
                $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
                $ftype   = mime_content_type($_FILES['image']['tmp_name']);
                if (in_array($ftype, $allowed, true)) {
                    $result = uploadToCloudinary($_FILES['image']['tmp_name']);
                    if (isset($result['url'])) {
                        $data['image'] = $result['url'];
                    } else {
                        $message = $result['error'] ?? 'Cloudinary upload failed.';
                        $messageType = 'error';
                    }
                } else {
                    $message = 'Invalid file type. Only JPG, PNG, GIF, WebP allowed.';
                    $messageType = 'error';
                }
            } elseif (!empty($_POST['image_url'])) {
                $data['image'] = trim($_POST['image_url']);
            }
            // No new image = keep existing (don't set 'image' key)

            if (!$message) {
                $repo->update($id, $data);
                $message = "Project \"{$title}\" updated.";
                $messageType = 'success';
            }
        } else {
            $message = 'Title and category are required.';
            $messageType = 'error';
        }
    }

    // DELETE PROJECT
    if ($action === 'delete') {
        $id = trim($_POST['id'] ?? '');
        if ($id !== '') {
            $img = $repo->getImage($id);
            if ($img && str_contains($img, UPLOAD_URL)) {
                $localFile = UPLOAD_DIR . basename($img);
                if (file_exists($localFile)) unlink($localFile);
            }
            $repo->delete($id);
            $message = 'Project deleted.';
            $messageType = 'success';
        }
    }

    // TOGGLE VISIBILITY
    if ($action === 'toggle') {
        $id = trim($_POST['id'] ?? '');
        if ($id !== '') {
            $repo->toggle($id);
            $message = 'Visibility updated.';
            $messageType = 'success';
        }
    }

    // UPDATE SORT ORDER
    if ($action === 'reorder') {
        foreach ($_POST['order'] ?? [] as $id => $pos) {
            $repo->reorder((string)$id, (int)$pos);
        }
        $message = 'Order saved.';
        $messageType = 'success';
    }
}

$categories = ['landing-page', 'full-stack', 'front-end', 'back-end', 'app'];

// Pagination
$per_page    = 10;
$page        = max(1, (int)($_GET['page'] ?? 1));
$total_rows  = $repo->totalCount();
$total_pages = (int)ceil($total_rows / $per_page);
$page        = min($page, max(1, $total_pages));
$offset      = ($page - 1) * $per_page;

$projects = $repo->findPage($per_page, $offset);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard, CCC. Portfolio</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#050b18;--bg2:#0a1628;--card:#0d1f3c;--border:#1e3a5f;
  --accent:#38bdf8;--accent2:#818cf8;--text:#e2e8f0;--muted:#94a3b8;
  --danger:#f87171;--success:#34d399;--warn:#fbbf24;
  --grad:linear-gradient(135deg,#38bdf8,#818cf8)
}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh}

/* Sidebar */
.sidebar{position:fixed;top:0;left:0;width:220px;height:100vh;background:var(--bg2);border-right:1px solid var(--border);padding:28px 20px;display:flex;flex-direction:column;z-index:100;overflow-y:auto}
.brand{font-family:'Space Grotesk',sans-serif;font-size:24px;font-weight:700;background:var(--grad);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:4px}
.brand-sub{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:32px}
.nav-link{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;color:var(--muted);font-size:14px;font-weight:500;text-decoration:none;margin-bottom:4px;transition:all .2s}
.nav-link:hover,.nav-link.active{background:rgba(56,189,248,.08);color:var(--accent)}
.nav-link i{font-size:16px}
.sidebar-bottom{margin-top:auto}
.logout-btn{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;color:var(--danger);font-size:14px;font-weight:500;text-decoration:none;transition:all .2s}
.logout-btn:hover{background:rgba(248,113,113,.08)}
.user-chip{display:flex;align-items:center;gap:10px;padding:10px 12px;border-top:1px solid var(--border);margin-bottom:8px}
.user-avatar{width:32px;height:32px;border-radius:50%;background:var(--grad);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px}
.user-name{font-size:13px;font-weight:600;color:var(--text)}

/* Main content */
.main{margin-left:220px;padding:32px 36px;min-height:100vh}
.page-header{margin-bottom:32px}
.page-title{font-family:'Space Grotesk',sans-serif;font-size:26px;font-weight:700;color:var(--text)}
.page-subtitle{font-size:14px;color:var(--muted);margin-top:4px}
.stats-row{display:flex;gap:16px;margin-bottom:32px}
.stat-card{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:20px 24px;flex:1}
.stat-num{font-family:'Space Grotesk',sans-serif;font-size:28px;font-weight:700;color:var(--accent)}
.stat-label{font-size:12px;color:var(--muted);margin-top:2px;text-transform:uppercase;letter-spacing:.06em}

/* Alert */
.alert{padding:12px 18px;border-radius:10px;margin-bottom:24px;font-size:14px;font-weight:500}
.alert-success{background:rgba(52,211,153,.1);border:1px solid rgba(52,211,153,.3);color:var(--success)}
.alert-error{background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.3);color:var(--danger)}

/* Card */
.card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:28px;margin-bottom:28px}
.card-title{font-size:16px;font-weight:700;color:var(--text);margin-bottom:20px;display:flex;align-items:center;gap:8px}
.card-title i{color:var(--accent)}

/* Form */
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-group{display:flex;flex-direction:column;gap:6px}
.form-group.full{grid-column:1/-1}
label{font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.07em}
input[type=text],input[type=url],input[type=number],select,textarea{background:var(--bg2);border:1px solid var(--border);border-radius:8px;padding:10px 14px;color:var(--text);font-family:inherit;font-size:14px;outline:none;transition:border-color .2s;width:100%}
input:focus,select:focus,textarea:focus{border-color:var(--accent)}
select option{background:var(--bg2)}
textarea{resize:vertical;min-height:90px}
.file-input-wrap{position:relative}
.file-input-wrap input[type=file]{opacity:0;position:absolute;inset:0;cursor:pointer;width:100%}
.file-label{display:flex;align-items:center;gap:8px;background:var(--bg2);border:1px dashed var(--border);border-radius:8px;padding:10px 14px;font-size:13px;color:var(--muted);cursor:pointer;transition:border-color .2s}
.file-label:hover{border-color:var(--accent)}
.check-group{display:flex;align-items:center;gap:8px}
.check-group input{width:16px;height:16px;accent-color:var(--accent)}
.check-group label{text-transform:none;font-size:14px;letter-spacing:0;color:var(--text)}
.hint{font-size:11px;color:var(--muted);margin-top:3px}
.btn{padding:10px 20px;border-radius:8px;font-family:inherit;font-size:14px;font-weight:600;cursor:pointer;border:none;transition:all .2s;display:inline-flex;align-items:center;gap:6px}
.btn-primary{background:var(--grad);color:#fff}
.btn-primary:hover{opacity:.85;transform:translateY(-1px)}
.btn-sm{padding:6px 12px;font-size:12px}
.btn-danger{background:rgba(248,113,113,.15);color:var(--danger);border:1px solid rgba(248,113,113,.3)}
.btn-danger:hover{background:rgba(248,113,113,.25)}
.btn-ghost{background:rgba(56,189,248,.08);color:var(--accent);border:1px solid rgba(56,189,248,.2)}
.btn-ghost:hover{background:rgba(56,189,248,.15)}
.btn-warn{background:rgba(251,191,36,.1);color:var(--warn);border:1px solid rgba(251,191,36,.2)}
.btn-warn:hover{background:rgba(251,191,36,.2)}
.or-divider{font-size:11px;color:var(--muted);text-align:center;margin:6px 0}

/* Table */
.table-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse;font-size:14px}
th{text-align:left;padding:10px 12px;border-bottom:1px solid var(--border);color:var(--muted);font-size:11px;text-transform:uppercase;letter-spacing:.07em;font-weight:600}
td{padding:12px;border-bottom:1px solid rgba(30,58,95,.5);vertical-align:middle}
tr:last-child td{border-bottom:none}
tr:hover td{background:rgba(56,189,248,.03)}
.thumb{width:56px;height:40px;object-fit:cover;border-radius:6px;border:1px solid var(--border)}
.thumb-placeholder{width:56px;height:40px;border-radius:6px;border:1px dashed var(--border);background:var(--bg2);display:flex;align-items:center;justify-content:center;color:var(--border)}
.badge{display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600}
.badge-landing{background:rgba(56,189,248,.12);color:var(--accent)}
.badge-full{background:rgba(129,140,248,.12);color:var(--accent2)}
.badge-front{background:rgba(52,211,153,.12);color:#34d399}
.badge-back{background:rgba(251,191,36,.1);color:var(--warn)}
.badge-app{background:rgba(251,113,133,.12);color:#fb7185}
.badge-hidden{background:rgba(148,163,184,.1);color:var(--muted)}
.tags-wrap{display:flex;flex-wrap:wrap;gap:4px}
.tag{padding:2px 7px;border-radius:4px;font-size:10px;background:rgba(56,189,248,.07);color:var(--muted);border:1px solid rgba(56,189,248,.15)}
.actions-cell{display:flex;gap:6px;align-items:center}
.id-col{color:var(--muted);font-size:12px}

/* Mobile header */
.mobile-header{display:none;position:fixed;top:0;left:0;right:0;height:60px;background:var(--bg2);border-bottom:1px solid var(--border);align-items:center;justify-content:space-between;padding:0 20px;z-index:200}
.mobile-brand{font-family:'Space Grotesk',sans-serif;font-size:22px;font-weight:700;background:var(--grad);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hamburger{background:none;border:1px solid var(--border);border-radius:8px;padding:7px 10px;cursor:pointer;color:var(--text);font-size:18px;line-height:1;transition:border-color .2s}
.hamburger:hover{border-color:var(--accent)}

/* Overlay */
.sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:150;backdrop-filter:blur(2px)}
.sidebar-overlay.open{display:block}

/* Responsive tweaks */
@media(max-width:768px){
  .mobile-header{display:flex}
  .sidebar{
    transform:translateX(-100%);
    transition:transform .28s cubic-bezier(.4,0,.2,1);
    top:60px;
    height:calc(100vh - 60px);
    height:calc(100svh - 60px);
    overflow-y:auto;
    z-index:160
  }
  .sidebar.open{transform:translateX(0)}
  .sidebar-overlay{top:60px}
  .main{margin-left:0;padding:80px 16px 32px}
  .form-grid{grid-template-columns:1fr}
  .stats-row{flex-wrap:wrap;gap:10px}
  .stat-card{min-width:calc(50% - 5px);flex:1 1 calc(50% - 5px)}
  .page-title{font-size:20px}
  .page-subtitle{font-size:13px}
  table{font-size:13px}
  td,th{padding:8px}
  .actions-cell{flex-wrap:wrap;gap:4px}
  .card{padding:18px}
}
@media(max-width:480px){
  .stat-card{min-width:calc(50% - 5px)}
  .mobile-header{padding:0 14px}
}

/* Edit modal */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:500;align-items:center;justify-content:center;padding:20px;backdrop-filter:blur(3px)}
.modal-overlay.open{display:flex}
.modal-box{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:28px;width:100%;max-width:720px;max-height:90vh;overflow-y:auto}
.modal-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px}
.modal-title{font-size:16px;font-weight:700;color:var(--text)}
.modal-close{background:none;border:none;color:var(--muted);font-size:22px;cursor:pointer;line-height:1;padding:0}
.modal-close:hover{color:var(--text)}
.img-preview{width:100%;max-height:140px;object-fit:cover;border-radius:8px;border:1px solid var(--border);margin-bottom:8px}
</style>
</head>
<body>

<!-- Mobile header -->
<header class="mobile-header">
  <div class="mobile-brand">CCC.</div>
  <button class="hamburger" id="menuToggle" aria-label="Open menu">&#9776;</button>
</header>

<!-- Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
  <div>
    <div class="brand">CCC.</div>
    <div class="brand-sub">Admin Panel</div>
    <a href="/admin/index.php" class="nav-link active">
      <i class="bi bi-grid-fill"></i><span>Projects</span>
    </a>
    <a href="/" target="_blank" class="nav-link">
      <i class="bi bi-eye"></i><span>View Portfolio</span>
    </a>
  </div>
  <div class="sidebar-bottom">
    <div class="user-chip">
      <div class="user-avatar">CC</div>
      <span class="user-name">Admin</span>
    </div>
    <a href="/admin/logout.php" class="logout-btn">
      <i class="bi bi-box-arrow-left"></i><span>Logout</span>
    </a>
  </div>
</aside>

<!-- Main -->
<main class="main">
  <div class="page-header">
    <div class="page-title">Projects</div>
    <div class="page-subtitle">Manage your portfolio projects, add, edit visibility, reorder, and delete.</div>
  </div>

  <?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?>"><?= htmlspecialchars($message) ?></div>
  <?php endif ?>

  <!-- Stats row, counts across ALL projects, not just current page -->
  <?php
    $stats      = $repo->stats();
    $stat_total   = $stats['total'];
    $stat_visible = (int)$stats['visible'];
    $stat_hidden  = $stat_total - $stat_visible;
    $stat_cats    = $stats['cats'];
  ?>
  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-num"><?= $stat_total ?></div>
      <div class="stat-label">Total Projects</div>
    </div>
    <div class="stat-card">
      <div class="stat-num" style="color:var(--success)"><?= $stat_visible ?></div>
      <div class="stat-label">Visible</div>
    </div>
    <div class="stat-card">
      <div class="stat-num" style="color:var(--muted)"><?= $stat_hidden ?></div>
      <div class="stat-label">Hidden</div>
    </div>
    <div class="stat-card">
      <div class="stat-num" style="color:var(--accent2)"><?= $stat_cats ?></div>
      <div class="stat-label">Categories</div>
    </div>
  </div>

  <!-- Add Project Form -->
  <div class="card">
    <div class="card-title"><i class="bi bi-plus-circle-fill"></i> Add New Project</div>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="add">
      <div class="form-grid">
        <div class="form-group">
          <label>Project Title *</label>
          <input type="text" name="title" placeholder="e.g. Pumeco Industries" required>
        </div>
        <div class="form-group">
          <label>Category *</label>
          <select name="category" required>
            <option value="">Select category…</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat ?>"><?= ucwords(str_replace('-', ' ', $cat)) ?></option>
            <?php endforeach ?>
          </select>
        </div>
        <div class="form-group full">
          <label>Description</label>
          <textarea name="description" placeholder="Describe what the project does, what problems it solves, and your role…"></textarea>
        </div>
        <div class="form-group">
          <label>Project Image</label>
          <div class="file-input-wrap">
            <div class="file-label"><i class="bi bi-upload"></i> Choose screenshot / image…</div>
            <input type="file" name="image" accept="image/*" onchange="document.querySelector('.file-label').textContent = this.files[0]?.name || 'Choose screenshot / image…'">
          </div>
          <div class="or-divider">— or paste a path/URL instead,</div>
          <input type="text" name="image_url" placeholder="assets/img/portfolio/Screenshot (xxx).png">
          <span class="hint">Upload takes priority if both are provided.</span>
        </div>
        <div class="form-group">
          <label>Sort Order</label>
          <input type="number" name="sort_order" value="0" min="0">
          <span class="hint">Lower numbers appear first.</span>
        </div>
        <div class="form-group">
          <label>Live URL</label>
          <input type="url" name="live_url" placeholder="https://yourproject.com">
        </div>
        <div class="form-group">
          <label>GitHub URL</label>
          <input type="url" name="github_url" placeholder="https://github.com/ChuksTech007/…">
        </div>
        <div class="form-group full">
          <label>Tech Tags</label>
          <input type="text" name="tags" placeholder="Node.js,Express.js,MongoDB,REST API (comma separated)">
        </div>
        <div class="form-group full">
          <div class="check-group">
            <input type="checkbox" name="is_visible" id="is_visible" checked>
            <label for="is_visible">Visible on portfolio</label>
          </div>
        </div>
      </div>
      <button type="submit" class="btn btn-primary" style="margin-top:20px">
        <i class="bi bi-plus-lg"></i> Add Project
      </button>
    </form>
  </div>

  <!-- Projects Table -->
  <div class="card">
    <div class="card-title"><i class="bi bi-list-ul"></i> All Projects (<?= $stat_total ?>) <span style="font-size:12px;font-weight:400;color:var(--muted)">— page <?= $page ?> of <?= max(1,$total_pages) ?></span></div>
    <?php if (empty($projects)): ?>
      <p style="color:var(--muted);font-size:14px">No projects yet. Add one above.</p>
    <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Thumbnail</th>
            <th>Title</th>
            <th>Category</th>
            <th>Tags</th>
            <th>Links</th>
            <th>Order</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($projects as $p): ?>
          <?php
            $catClass = match($p['category']) {
              'landing-page' => 'badge-landing',
              'full-stack'   => 'badge-full',
              'front-end'    => 'badge-front',
              'back-end'     => 'badge-back',
              'app'          => 'badge-app',
              default        => ''
            };
            $tags = array_filter(array_map('trim', explode(',', $p['tags'])));
          ?>
          <tr>
            <td class="id-col"><?= $p['id'] ?></td>
            <td>
              <?php if ($p['image']): ?>
                <img src="<?= htmlspecialchars(str_starts_with($p['image'], 'http') ? $p['image'] : '/' . $p['image']) ?>" class="thumb" alt="">
              <?php else: ?>
                <div class="thumb-placeholder"><i class="bi bi-image" style="font-size:18px"></i></div>
              <?php endif ?>
            </td>
            <td style="font-weight:600;max-width:180px"><?= htmlspecialchars($p['title']) ?></td>
            <td><span class="badge <?= $catClass ?>"><?= htmlspecialchars($p['category']) ?></span></td>
            <td style="max-width:180px">
              <div class="tags-wrap">
                <?php foreach ($tags as $t): ?>
                  <span class="tag"><?= htmlspecialchars($t) ?></span>
                <?php endforeach ?>
              </div>
            </td>
            <td>
              <?php if ($p['live_url']): ?>
                <a href="<?= htmlspecialchars($p['live_url']) ?>" target="_blank" style="color:var(--accent);font-size:18px" title="Live site"><i class="bi bi-box-arrow-up-right"></i></a>
              <?php endif ?>
              <?php if ($p['github_url']): ?>
                <a href="<?= htmlspecialchars($p['github_url']) ?>" target="_blank" style="color:var(--muted);font-size:18px;margin-left:8px" title="GitHub"><i class="bi bi-github"></i></a>
              <?php endif ?>
            </td>
            <td>
              <form method="POST" style="display:inline">
                <input type="hidden" name="action" value="reorder">
                <input type="number" name="order[<?= $p['id'] ?>]" value="<?= $p['sort_order'] ?>" min="0" style="width:56px;padding:4px 8px;font-size:13px" onchange="this.form.submit()">
              </form>
            </td>
            <td>
              <?php if ($p['is_visible']): ?>
                <span class="badge" style="background:rgba(52,211,153,.12);color:var(--success)">Visible</span>
              <?php else: ?>
                <span class="badge badge-hidden">Hidden</span>
              <?php endif ?>
            </td>
            <td>
              <div class="actions-cell">
                <!-- Edit -->
                <button type="button" class="btn btn-sm btn-warn" title="Edit"
                  onclick="openEdit(<?= htmlspecialchars(json_encode([
                    'id'          => $p['id'],
                    'title'       => $p['title'],
                    'category'    => $p['category'],
                    'description' => $p['description'] ?? '',
                    'image'       => $p['image'] ?? '',
                    'live_url'    => $p['live_url'] ?? '',
                    'github_url'  => $p['github_url'] ?? '',
                    'tags'        => $p['tags'] ?? '',
                    'sort_order'  => $p['sort_order'] ?? 0,
                    'is_visible'  => (bool)$p['is_visible'],
                  ]), ENT_QUOTES) ?>)">
                  <i class="bi bi-pencil"></i>
                </button>
                <!-- Toggle visibility -->
                <form method="POST" style="display:inline">
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="id" value="<?= $p['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-ghost" title="<?= $p['is_visible'] ? 'Hide' : 'Show' ?>">
                    <i class="bi bi-<?= $p['is_visible'] ? 'eye-slash' : 'eye' ?>"></i>
                  </button>
                </form>
                <!-- Delete -->
                <form method="POST" style="display:inline" onsubmit="return confirm('Delete \'<?= addslashes($p['title']) ?>\'? This cannot be undone.')">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?= $p['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                    <i class="bi bi-trash3"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach ?>
        </tbody>
      </table>
    </div>

    <?php if ($total_pages > 1): ?>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-top:20px;flex-wrap:wrap;gap:12px">
      <span style="font-size:13px;color:var(--muted)">
        Showing <?= $offset + 1 ?>–<?= min($offset + $per_page, $stat_total) ?> of <?= $stat_total ?> projects
      </span>
      <div style="display:flex;gap:6px;align-items:center">
        <?php if ($page > 1): ?>
          <a href="?page=<?= $page - 1 ?>" class="btn btn-sm btn-ghost"><i class="bi bi-chevron-left"></i> Prev</a>
        <?php endif ?>
        <?php
          $start = max(1, $page - 2);
          $end   = min($total_pages, $page + 2);
          for ($p = $start; $p <= $end; $p++):
        ?>
          <a href="?page=<?= $p ?>" class="btn btn-sm <?= $p === $page ? 'btn-primary' : 'btn-ghost' ?>"><?= $p ?></a>
        <?php endfor ?>
        <?php if ($page < $total_pages): ?>
          <a href="?page=<?= $page + 1 ?>" class="btn btn-sm btn-ghost">Next <i class="bi bi-chevron-right"></i></a>
        <?php endif ?>
      </div>
    </div>
    <?php endif ?>

    <?php endif ?>
  </div>
</main>

<script>
(function(){
  var toggle  = document.getElementById('menuToggle');
  var sidebar = document.getElementById('sidebar');
  var overlay = document.getElementById('sidebarOverlay');
  function open()  { sidebar.classList.add('open'); overlay.classList.add('open'); toggle.innerHTML='&#10005;'; }
  function close() { sidebar.classList.remove('open'); overlay.classList.remove('open'); toggle.innerHTML='&#9776;'; }
  toggle.addEventListener('click', function(){ sidebar.classList.contains('open') ? close() : open(); });
  overlay.addEventListener('click', close);
})();

var _editOverlay = document.getElementById('editOverlay');

function openEdit(p) {
  document.getElementById('edit_id').value          = p.id;
  document.getElementById('edit_title').value       = p.title;
  document.getElementById('edit_category').value    = p.category;
  document.getElementById('edit_description').value = p.description;
  document.getElementById('edit_live_url').value    = p.live_url;
  document.getElementById('edit_github_url').value  = p.github_url;
  document.getElementById('edit_tags').value        = p.tags;
  document.getElementById('edit_sort_order').value  = p.sort_order;
  document.getElementById('edit_is_visible').checked = p.is_visible;
  document.getElementById('edit_image_url').value   = '';
  var preview = document.getElementById('edit_img_preview');
  if (p.image) {
    preview.src = p.image.startsWith('http') ? p.image : '/' + p.image;
    preview.style.display = 'block';
  } else {
    preview.style.display = 'none';
  }
  document.getElementById('edit_file_label').textContent = 'Choose new image to replace…';
  _editOverlay.classList.add('open');
}

function closeEdit(e) {
  if (!e || e.target === _editOverlay) _editOverlay.classList.remove('open');
}
</script>

<!-- Edit Modal -->
<div class="modal-overlay" id="editOverlay" onclick="closeEdit(event)">
  <div class="modal-box">
    <div class="modal-header">
      <div class="modal-title"><i class="bi bi-pencil-square" style="color:var(--warn);margin-right:6px"></i>Edit Project</div>
      <button class="modal-close" onclick="closeEdit()">&times;</button>
    </div>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id" id="edit_id">
      <div class="form-grid">
        <div class="form-group">
          <label>Project Title *</label>
          <input type="text" name="title" id="edit_title" required>
        </div>
        <div class="form-group">
          <label>Category *</label>
          <select name="category" id="edit_category" required>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat ?>"><?= ucwords(str_replace('-', ' ', $cat)) ?></option>
            <?php endforeach ?>
          </select>
        </div>
        <div class="form-group full">
          <label>Description</label>
          <textarea name="description" id="edit_description"></textarea>
        </div>
        <div class="form-group full">
          <label>Project Image</label>
          <img id="edit_img_preview" class="img-preview" src="" alt="" style="display:none">
          <div class="file-input-wrap">
            <div class="file-label" id="edit_file_label"><i class="bi bi-upload"></i> Choose new image to replace…</div>
            <input type="file" name="image" accept="image/*"
              onchange="document.getElementById('edit_file_label').textContent = this.files[0]?.name || 'Choose new image to replace…'">
          </div>
          <div class="or-divider">— or paste a URL instead</div>
          <input type="text" name="image_url" id="edit_image_url" placeholder="https://… (leave blank to keep current image)">
          <span class="hint">Leave both empty to keep the existing image.</span>
        </div>
        <div class="form-group">
          <label>Live URL</label>
          <input type="url" name="live_url" id="edit_live_url">
        </div>
        <div class="form-group">
          <label>GitHub URL</label>
          <input type="url" name="github_url" id="edit_github_url">
        </div>
        <div class="form-group">
          <label>Tech Tags</label>
          <input type="text" name="tags" id="edit_tags" placeholder="Node.js,Express.js,MongoDB (comma separated)">
        </div>
        <div class="form-group">
          <label>Sort Order</label>
          <input type="number" name="sort_order" id="edit_sort_order" min="0">
        </div>
        <div class="form-group full">
          <div class="check-group">
            <input type="checkbox" name="is_visible" id="edit_is_visible">
            <label for="edit_is_visible">Visible on portfolio</label>
          </div>
        </div>
      </div>
      <div style="display:flex;gap:10px;margin-top:20px">
        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Changes</button>
        <button type="button" class="btn btn-ghost" onclick="closeEdit()">Cancel</button>
      </div>
    </form>
  </div>
</div>

</body>
</html>

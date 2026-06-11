<?php
// ── Load .env ─────────────────────────────────────────────────────────────────
$__envFile = __DIR__ . '/.env';
if (file_exists($__envFile)) {
    foreach (file($__envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $__line) {
        $__line = trim($__line);
        if ($__line === '' || $__line[0] === '#') continue;
        [$__k, $__v] = array_map('trim', explode('=', $__line, 2)) + [1 => ''];
        if ($__k && getenv($__k) === false) { putenv("$__k=$__v"); $_ENV[$__k] = $__v; }
    }
}

// ── Load projects (MongoDB → MySQL → empty) ────────────────────────────────────
$projects_db = [];
try {
    $__mongoUri = getenv('MONGODB_URI');
    if ($__mongoUri && class_exists('\MongoDB\Client')) {
        // MongoDB Atlas (production)
        $__client  = new \MongoDB\Client($__mongoUri);
        $__cursor  = $__client->iportfolio_db->projects->find(
            ['is_visible' => true],
            [
                'sort'    => ['sort_order' => 1, '_id' => 1],
                'typeMap' => ['root' => 'array', 'document' => 'array'],
            ]
        );
        foreach ($__cursor as $__doc) {
            $__doc['id'] = (string)$__doc['_id'];
            $projects_db[] = $__doc;
        }
    } else {
        // MySQL (local XAMPP)
        $__pdo = new PDO('mysql:host=localhost;dbname=iportfolio_db;charset=utf8mb4', 'root', '', [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $projects_db = $__pdo->query(
            "SELECT * FROM projects WHERE is_visible = 1 ORDER BY sort_order ASC, id ASC"
        )->fetchAll();
    }
} catch (Exception $__e) {
    $projects_db = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Charles-Chukwudi | Full-Stack Developer & Backend Engineer</title>
  <meta content="Full-Stack Developer, Backend Engineer, Tech Educator based in Nigeria. Specializing in Node.js, Laravel, React, and scalable web systems." name="description">
  <meta content="Full-Stack Developer, Backend Engineer, Node.js, Laravel, React, Nigeria, Tech Educator" name="keywords">

  <!-- Open Graph -->
  <meta property="og:title" content="Charles-Chukwudi | Full-Stack Developer">
  <meta property="og:description" content="Backend Engineer & Tech Educator building scalable production systems in Nigeria.">
  <meta property="og:image" content="assets/img/Charles.jpg">
  <meta property="og:type" content="website">

  <!-- Favicon -->
  <link href="assets/img/Charles.jpg" rel="icon">
  <link href="assets/img/Charles.jpg" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Vendor CSS -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">

  <style>
    /* ===== VARIABLES ===== */
    :root {
      --bg:      #050b18;
      --bg2:     #0a1628;
      --card:    #0d1f3c;
      --border:  #1e3a5f;
      --accent:  #38bdf8;
      --accent2: #818cf8;
      --grad:    linear-gradient(135deg, #38bdf8, #818cf8);
      --text:    #e2e8f0;
      --muted:   #94a3b8;
      --fmain:   'Inter', sans-serif;
      --fhead:   'Space Grotesk', sans-serif;
    }

    /* ===== BASE ===== */
    *, *::before, *::after { box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    body {
      font-family: var(--fmain);
      background: var(--bg);
      color: var(--text);
      line-height: 1.7;
      overflow-x: hidden;
    }
    a { text-decoration: none; color: var(--accent); }
    a:hover { color: #7dd3fc; }
    ul { margin: 0; padding: 0; list-style: none; }
    img { max-width: 100%; display: block; }

    ::-webkit-scrollbar { width: 5px; }
    ::-webkit-scrollbar-track { background: var(--bg); }
    ::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 3px; }

    /* ===== NAVBAR ===== */
    .navbar {
      background: rgba(5,11,24,0.8);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      border-bottom: 1px solid rgba(56,189,248,0.08);
      padding: 16px 0;
      transition: padding 0.3s, background 0.3s;
      z-index: 1000;
    }
    .navbar.scrolled {
      padding: 10px 0;
      background: rgba(5,11,24,0.97);
      border-bottom-color: var(--border);
    }
    .navbar-brand {
      font-family: var(--fhead);
      font-size: 26px;
      font-weight: 700;
      background: var(--grad);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    .nav-link {
      color: var(--muted) !important;
      font-size: 14px;
      font-weight: 500;
      padding: 8px 14px !important;
      position: relative;
      transition: color 0.2s;
    }
    .nav-link::after {
      content: '';
      position: absolute;
      bottom: 2px; left: 14px; right: 14px;
      height: 2px;
      background: var(--grad);
      transform: scaleX(0);
      transition: transform 0.25s;
      border-radius: 2px;
    }
    .nav-link:hover, .nav-link.active-link { color: var(--text) !important; }
    .nav-link:hover::after, .nav-link.active-link::after { transform: scaleX(1); }
    .nav-hire {
      background: var(--grad);
      color: #050b18 !important;
      font-weight: 700 !important;
      border-radius: 8px;
      padding: 10px 22px !important;
      -webkit-text-fill-color: #050b18 !important;
      transition: opacity 0.2s, transform 0.2s !important;
    }
    .nav-hire::after { display: none !important; }
    .nav-hire:hover { opacity: 0.88; transform: translateY(-1px); }
    .navbar-toggler { border: 1px solid var(--border); padding: 6px 10px; }
    .navbar-toggler-icon { filter: invert(0.8); }

    /* ===== BUTTONS ===== */
    .btn-grad {
      background: var(--grad);
      color: #050b18; font-weight: 700;
      padding: 13px 28px; border-radius: 8px;
      border: none; font-size: 14px;
      display: inline-flex; align-items: center; gap: 8px;
      transition: opacity 0.2s, transform 0.2s;
      cursor: pointer;
    }
    .btn-grad:hover { opacity: 0.88; transform: translateY(-2px); color: #050b18; }
    .btn-out {
      background: transparent; color: var(--text);
      font-weight: 600; padding: 12px 28px;
      border-radius: 8px; border: 1.5px solid var(--border);
      font-size: 14px; display: inline-flex; align-items: center; gap: 8px;
      transition: border-color 0.2s, color 0.2s, transform 0.2s;
    }
    .btn-out:hover { border-color: var(--accent); color: var(--accent); transform: translateY(-2px); }

    /* ===== SECTION BASE ===== */
    section { padding: 90px 0; overflow: hidden; }
    .sec-tag {
      display: inline-block;
      font-size: 11px; font-weight: 700; letter-spacing: 2.5px;
      text-transform: uppercase; color: var(--accent);
      padding: 4px 14px; border: 1px solid rgba(56,189,248,0.3);
      border-radius: 20px; margin-bottom: 14px;
    }
    .sec-title {
      font-family: var(--fhead);
      font-size: clamp(30px, 5vw, 46px);
      font-weight: 700; color: var(--text); line-height: 1.2; margin-bottom: 12px;
    }
    .sec-title span { background: var(--grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    .sec-desc { color: var(--muted); font-size: 15px; max-width: 500px; margin-top: 8px; }

    /* ===== HERO ===== */
    #hero {
      min-height: 100vh;
      display: flex; align-items: center;
      padding: 110px 0 70px;
      position: relative; overflow: hidden;
    }
    /* Dot-grid background */
    #hero::before {
      content: '';
      position: absolute; inset: 0;
      background-image: radial-gradient(rgba(56,189,248,0.18) 1px, transparent 1px);
      background-size: 32px 32px;
      pointer-events: none;
      mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 40%, transparent 100%);
      -webkit-mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 40%, transparent 100%);
    }
    /* Glow orbs */
    .hero-orb1 {
      position: absolute; top: -120px; right: -120px;
      width: 500px; height: 500px;
      background: radial-gradient(circle, rgba(56,189,248,0.12) 0%, transparent 70%);
      pointer-events: none;
    }
    .hero-orb2 {
      position: absolute; bottom: -80px; left: -80px;
      width: 360px; height: 360px;
      background: radial-gradient(circle, rgba(129,140,248,0.1) 0%, transparent 70%);
      pointer-events: none;
    }
    .avail-badge {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(52,211,153,0.1); border: 1px solid rgba(52,211,153,0.3);
      border-radius: 20px; padding: 6px 14px;
      font-size: 12px; font-weight: 600; color: #34d399;
      margin-bottom: 20px;
    }
    .avail-dot {
      width: 8px; height: 8px; background: #34d399;
      border-radius: 50%;
      animation: pulse-dot 1.8s ease-in-out infinite;
    }
    @keyframes pulse-dot {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: 0.4; transform: scale(0.7); }
    }
    .hero-name {
      font-family: var(--fhead);
      font-size: clamp(40px, 7vw, 76px);
      font-weight: 700; line-height: 1.1; margin-bottom: 18px;
      background: var(--grad);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }
    .hero-sub { font-size: clamp(16px, 2.5vw, 22px); color: var(--muted); margin-bottom: 38px; }
    .hero-sub .typed { color: var(--text); font-weight: 600; }
    .hero-btns { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 44px; }
    .hero-socials { display: flex; gap: 10px; }
    .hero-socials a {
      width: 42px; height: 42px;
      border: 1.5px solid var(--border); border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      color: var(--muted); font-size: 17px;
      transition: all 0.2s;
    }
    .hero-socials a:hover { border-color: var(--accent); color: var(--accent); transform: translateY(-2px); }
    .hero-img-wrap {
      position: relative;
      width: 380px; max-width: 100%;
      border-radius: 22px;
      padding: 4px;
      background: var(--grad);
      box-shadow: 0 0 48px rgba(56,189,248,0.28), 0 0 96px rgba(129,140,248,0.14);
      flex-shrink: 0;
    }
    .hero-img-glow { display: none; }
    .hero-photo {
      width: 100%; height: 430px;
      object-fit: cover; object-position: top center;
      border-radius: 18px; display: block;
    }
    .hero-badge {
      position: absolute; bottom: -18px; left: -18px;
      background: var(--card); border: 1.5px solid var(--border);
      border-radius: 12px; padding: 14px 18px; z-index: 2;
      display: flex; align-items: center; gap: 12px;
      box-shadow: 0 10px 36px rgba(0,0,0,0.5);
    }
    .hero-badge i { font-size: 26px; color: var(--accent); }
    .hero-badge strong { display: block; font-size: 20px; font-weight: 700; color: var(--text); line-height: 1.1; }
    .hero-badge span { font-size: 11px; color: var(--muted); }
    .hero-stat-wrap {
      position: absolute; top: 32px; right: -20px;
      background: var(--card); border: 1.5px solid var(--border);
      border-radius: 12px; padding: 14px 18px; z-index: 2;
      box-shadow: 0 10px 36px rgba(0,0,0,0.5);
      text-align: center;
    }
    .hero-stat-wrap strong { display: block; font-size: 22px; font-weight: 700; background: var(--grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; line-height: 1; }
    .hero-stat-wrap span { font-size: 11px; color: var(--muted); }

    /* ===== ABOUT ===== */
    #about { background: var(--bg2); }
    .about-photo {
      width: 100%; max-width: 440px; height: 500px;
      object-fit: cover; border-radius: 16px; border: 1px solid var(--border);
      filter: brightness(0.88) contrast(1.05);
    }
    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin: 24px 0 30px; }
    .info-cell {
      background: var(--card); border: 1px solid var(--border);
      border-radius: 10px; padding: 14px 16px;
    }
    .info-cell label {
      font-size: 10px; font-weight: 700; text-transform: uppercase;
      letter-spacing: 1.5px; color: var(--muted); display: block; margin-bottom: 4px;
    }
    .info-cell span { font-size: 13px; font-weight: 500; color: var(--text); }

    /* ===== SKILLS ===== */
    #skills { background: var(--bg); }
    .skill-card {
      background: var(--card); border: 1px solid var(--border);
      border-radius: 14px; padding: 28px; height: 100%;
      transition: border-color 0.25s, transform 0.25s;
    }
    .skill-card:hover { border-color: var(--accent); transform: translateY(-4px); }
    .skill-card-head {
      font-size: 11px; font-weight: 700; text-transform: uppercase;
      letter-spacing: 2px; color: var(--accent); margin-bottom: 18px;
      display: flex; align-items: center; gap: 8px;
    }
    .tags { display: flex; flex-wrap: wrap; gap: 8px; }
    .tag {
      padding: 5px 14px; border: 1.5px solid var(--border);
      border-radius: 20px; font-size: 13px; color: var(--muted);
      transition: all 0.2s; cursor: default;
    }
    .tag:hover { border-color: var(--accent); color: var(--text); background: rgba(56,189,248,0.07); }

    /* ===== STATS ===== */
    #stats { background: var(--bg2); padding: 60px 0; overflow: hidden; }
    .stat-box {
      background: var(--card); border: 1px solid var(--border);
      border-radius: 14px; padding: 32px 20px; text-align: center;
      transition: border-color 0.2s, transform 0.2s;
    }
    .stat-box:hover { border-color: var(--accent); transform: translateY(-4px); }
    .stat-num {
      font-family: var(--fhead); font-size: 50px; font-weight: 700;
      background: var(--grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
      line-height: 1; margin-bottom: 8px;
    }
    .stat-lbl { font-size: 13px; color: var(--muted); font-weight: 500; }

    /* ===== RESUME ===== */
    #resume { background: var(--bg); }
    .col-head {
      font-size: 11px; font-weight: 700; text-transform: uppercase;
      letter-spacing: 2px; color: var(--muted); margin-bottom: 28px;
      display: flex; align-items: center; gap: 8px;
    }
    .col-head i { color: var(--accent); font-size: 14px; }
    .timeline { position: relative; padding-left: 28px; }
    .timeline::before {
      content: '';
      position: absolute; left: 6px; top: 6px; bottom: 0;
      width: 2px;
      background: linear-gradient(to bottom, var(--accent), var(--accent2), transparent);
    }
    .tl-item { position: relative; margin-bottom: 24px; }
    .tl-item::before {
      content: '';
      position: absolute; left: -26px; top: 8px;
      width: 10px; height: 10px; border-radius: 50%;
      background: var(--accent); box-shadow: 0 0 0 3px rgba(56,189,248,0.2);
    }
    .tl-card {
      background: var(--card); border: 1px solid var(--border);
      border-radius: 12px; padding: 22px 24px; transition: border-color 0.2s;
    }
    .tl-card:hover { border-color: var(--accent); }
    .tl-top {
      display: flex; justify-content: space-between;
      align-items: flex-start; gap: 10px; flex-wrap: wrap; margin-bottom: 4px;
    }
    .tl-role { font-family: var(--fhead); font-size: 16px; font-weight: 600; color: var(--text); }
    .tl-period {
      font-size: 11px; font-weight: 700; padding: 3px 10px;
      border-radius: 20px; background: rgba(56,189,248,0.1);
      color: var(--accent); white-space: nowrap;
    }
    .tl-company { font-size: 13px; color: var(--accent); margin-bottom: 10px; }
    .tl-list { font-size: 13px; color: var(--muted); line-height: 1.8; padding-left: 16px; }
    .tl-list li { margin-bottom: 3px; }

    .edu-card {
      background: var(--card); border: 1px solid var(--border);
      border-radius: 12px; padding: 20px 22px;
      display: flex; gap: 16px; align-items: flex-start;
      margin-bottom: 14px; transition: border-color 0.2s;
    }
    .edu-card:hover { border-color: var(--accent); }
    .edu-card i { font-size: 26px; color: var(--accent); flex-shrink: 0; margin-top: 2px; }
    .edu-name { font-size: 15px; font-weight: 600; color: var(--text); margin-bottom: 3px; }
    .edu-school { font-size: 13px; color: var(--accent); margin-bottom: 3px; }
    .edu-year { font-size: 12px; color: var(--muted); }

    /* ===== PORTFOLIO ===== */
    #portfolio { background: var(--bg2); }
    .pf-filters { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 36px; }
    .pf-filters li {
      padding: 6px 18px; border-radius: 20px; font-size: 13px; font-weight: 500;
      border: 1.5px solid var(--border); color: var(--muted);
      cursor: pointer; transition: all 0.2s;
    }
    .pf-filters li:hover, .pf-filters li.filter-active {
      border-color: var(--accent); color: var(--accent); background: rgba(56,189,248,0.08);
    }
    .proj-card {
      background: var(--card); border: 1px solid var(--border);
      border-radius: 14px; overflow: hidden; height: 100%;
      transition: border-color 0.25s, transform 0.25s;
    }
    .proj-card:hover { border-color: var(--accent); transform: translateY(-6px); }
    .proj-thumb { position: relative; overflow: hidden; height: 200px; }
    .proj-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease; }
    .proj-card:hover .proj-thumb img { transform: scale(1.06); }
    .proj-overlay {
      position: absolute; inset: 0;
      background: rgba(5,11,24,0.72);
      display: flex; align-items: center; justify-content: center; gap: 12px;
      opacity: 0; transition: opacity 0.3s;
    }
    .proj-card:hover .proj-overlay { opacity: 1; }
    .proj-overlay a {
      width: 42px; height: 42px; background: var(--accent);
      border-radius: 8px; display: flex; align-items: center; justify-content: center;
      color: #050b18; font-size: 17px; transition: transform 0.2s;
    }
    .proj-overlay a:hover { transform: scale(1.1); color: #050b18; }
    .proj-body { padding: 18px 20px; }
    .proj-cat { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: var(--accent); margin-bottom: 6px; }
    .proj-title { font-size: 15px; font-weight: 600; color: var(--text); margin-bottom: 10px; }
    .proj-link {
      display: inline-flex; align-items: center; gap: 6px;
      font-size: 13px; color: var(--accent); font-weight: 500;
      transition: gap 0.2s, color 0.2s;
    }
    .proj-link:hover { gap: 10px; color: #7dd3fc; }

    /* Portfolio pagination */
    .proj-paged-hidden { display: none !important; }
    .btn-outline-pf {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 11px 28px; border-radius: 10px; border: 1.5px solid var(--border);
      background: transparent; color: var(--text); font-family: var(--fmain);
      font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.25s;
    }
    .btn-outline-pf:hover { border-color: var(--accent); color: var(--accent); background: rgba(56,189,248,0.06); }

    /* Project card extras */
    .proj-tags { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 12px; }
    .proj-tag {
      padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 500;
      background: rgba(56,189,248,0.08); color: var(--muted);
      border: 1px solid rgba(56,189,248,0.15);
    }
    .proj-tag-more { color: var(--accent2); border-color: rgba(129,140,248,0.25); background: rgba(129,140,248,0.08); }
    .proj-footer {
      display: flex; align-items: center; gap: 8px; margin-top: 4px;
    }
    .proj-detail-btn {
      display: inline-flex; align-items: center; gap: 5px;
      background: none; border: 1px solid rgba(56,189,248,0.3); border-radius: 6px;
      padding: 5px 12px; font-size: 12px; font-weight: 600; color: var(--accent);
      cursor: pointer; transition: all 0.2s; font-family: var(--fmain);
    }
    .proj-detail-btn:hover { background: rgba(56,189,248,0.08); border-color: var(--accent); }
    .proj-live-btn {
      display: inline-flex; align-items: center; justify-content: center;
      width: 30px; height: 30px; border-radius: 6px;
      background: rgba(56,189,248,0.07); border: 1px solid rgba(56,189,248,0.2);
      color: var(--muted); font-size: 14px; transition: all 0.2s;
    }
    .proj-live-btn:hover { color: var(--accent); border-color: var(--accent); background: rgba(56,189,248,0.12); }

    /* Project Detail Modal */
    .proj-modal-bg {
      display: none; position: fixed; inset: 0; z-index: 9999;
      background: rgba(5,11,24,0.88); backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      align-items: center; justify-content: center; padding: 20px;
    }
    .proj-modal-bg.open { display: flex; }
    .proj-modal {
      background: var(--card); border: 1px solid var(--border);
      border-radius: 20px; width: 100%; max-width: 760px;
      max-height: 90vh; overflow-y: auto;
      position: relative; animation: modalIn 0.25s ease;
    }
    @keyframes modalIn { from { opacity:0; transform:translateY(20px) scale(.97); } to { opacity:1; transform:none; } }
    .proj-modal-close {
      position: absolute; top: 16px; right: 16px; z-index: 10;
      background: rgba(5,11,24,0.7); border: 1px solid var(--border);
      border-radius: 50%; width: 36px; height: 36px;
      display: flex; align-items: center; justify-content: center;
      color: var(--muted); cursor: pointer; font-size: 16px;
      transition: all 0.2s;
    }
    .proj-modal-close:hover { color: var(--text); border-color: var(--accent); }
    .proj-modal-img-wrap { height: 260px; overflow: hidden; border-radius: 18px 18px 0 0; }
    .proj-modal-img-wrap img { width: 100%; height: 100%; object-fit: cover; }
    .proj-modal-body { padding: 28px 32px 32px; }
    .proj-modal-cat { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: var(--accent); }
    .proj-modal-title { font-family: var(--fhead); font-size: 22px; font-weight: 700; color: var(--text); margin: 8px 0 14px; }
    .proj-modal-desc { font-size: 14px; color: var(--muted); line-height: 1.85; margin-bottom: 18px; }
    .proj-modal-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 24px; }
    .proj-modal-tags .proj-tag { font-size: 11px; padding: 3px 10px; }
    .proj-modal-actions { display: flex; gap: 12px; flex-wrap: wrap; }
    .btn-outline {
      padding: 10px 22px; border-radius: 8px; font-size: 14px; font-weight: 600;
      color: var(--text); border: 1px solid var(--border);
      background: transparent; transition: all 0.2s;
    }
    .btn-outline:hover { border-color: var(--accent); color: var(--accent); background: rgba(56,189,248,0.06); }

    /* ===== SERVICES ===== */
    #services { background: var(--bg); }
    .svc-card {
      background: var(--card); border: 1px solid var(--border);
      border-radius: 14px; padding: 30px 26px; height: 100%;
      position: relative; overflow: hidden;
      transition: border-color 0.25s, transform 0.25s;
    }
    .svc-card::before {
      content: '';
      position: absolute; top: 0; left: 0; right: 0;
      height: 3px; background: var(--grad);
      transform: scaleX(0); transition: transform 0.3s; transform-origin: left;
    }
    .svc-card:hover { border-color: var(--accent); transform: translateY(-4px); }
    .svc-card:hover::before { transform: scaleX(1); }
    .svc-icon {
      width: 52px; height: 52px; background: rgba(56,189,248,0.1);
      border-radius: 12px; display: flex; align-items: center; justify-content: center;
      font-size: 22px; color: var(--accent); margin-bottom: 18px;
    }
    .svc-title { font-family: var(--fhead); font-size: 16px; font-weight: 600; color: var(--text); margin-bottom: 10px; }
    .svc-desc { font-size: 13px; color: var(--muted); line-height: 1.85; }

    /* ===== TESTIMONIALS ===== */
    #testimonials { background: var(--bg2); }
    .testi-card {
      background: var(--card); border: 1px solid var(--border);
      border-radius: 16px; padding: 30px 26px; height: 100%;
      position: relative; transition: border-color 0.25s, transform 0.25s;
    }
    .testi-card:hover { border-color: var(--accent); transform: translateY(-4px); }
    .testi-quote {
      font-size: 48px; line-height: 1; color: var(--accent); opacity: 0.35;
      font-family: Georgia, serif; margin-bottom: 12px;
    }
    .testi-text { font-size: 14px; color: var(--muted); line-height: 1.85; margin-bottom: 22px; font-style: italic; }
    .testi-author { display: flex; align-items: center; gap: 14px; }
    .testi-avatar {
      width: 46px; height: 46px; border-radius: 50%;
      background: var(--grad); display: flex; align-items: center; justify-content: center;
      font-size: 18px; font-weight: 700; color: #050b18; font-family: var(--fhead);
      flex-shrink: 0;
    }
    .testi-name { font-size: 14px; font-weight: 600; color: var(--text); }
    .testi-role { font-size: 12px; color: var(--muted); }
    .testi-stars { color: #fbbf24; font-size: 13px; margin-bottom: 4px; }

    /* ===== CONTACT ===== */
    #contact { background: var(--bg); }
    .ct-item { display: flex; align-items: flex-start; gap: 16px; margin-bottom: 24px; }
    .ct-icon {
      width: 46px; height: 46px; background: rgba(56,189,248,0.1);
      border: 1px solid rgba(56,189,248,0.2); border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px; color: var(--accent); flex-shrink: 0;
    }
    .ct-lbl { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; color: var(--muted); margin-bottom: 3px; }
    .ct-val { font-size: 14px; color: var(--text); font-weight: 500; }
    .copy-btn {
      background: none; border: none; padding: 0; margin-left: 8px;
      color: var(--muted); cursor: pointer; font-size: 14px; transition: color 0.2s;
    }
    .copy-btn:hover { color: var(--accent); }
    .copy-toast {
      display: inline-block; font-size: 11px; color: #34d399;
      margin-left: 6px; opacity: 0; transition: opacity 0.3s;
    }
    .form-box {
      background: var(--card); border: 1px solid var(--border);
      border-radius: 16px; padding: 36px 32px;
    }
    .form-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--muted); margin-bottom: 8px; }
    .form-control {
      background: var(--bg2) !important; border: 1.5px solid var(--border) !important;
      border-radius: 8px !important; color: var(--text) !important;
      padding: 12px 16px !important; font-size: 14px;
      transition: border-color 0.2s !important;
    }
    .form-control::placeholder { color: var(--muted) !important; opacity: 1; }
    .form-control:focus {
      border-color: var(--accent) !important;
      box-shadow: 0 0 0 3px rgba(56,189,248,0.1) !important;
      outline: none;
    }
    .btn-send {
      width: 100%; background: var(--grad); color: #050b18;
      font-weight: 700; padding: 14px; border-radius: 8px;
      border: none; font-size: 15px; cursor: pointer;
      transition: opacity 0.2s, transform 0.2s;
      display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .btn-send:hover { opacity: 0.88; transform: translateY(-2px); }
    .loading, .error-message, .sent-message { display: none; font-size: 14px; text-align: center; margin-top: 10px; }
    .loading { color: var(--muted); }
    .error-message { color: #f87171; }
    .sent-message { color: #34d399; }

    /* ===== FOOTER ===== */
    footer {
      background: #020810; padding: 28px 0;
      border-top: 1px solid var(--border);
    }
    .ft-logo {
      font-family: var(--fhead); font-size: 20px; font-weight: 700;
      background: var(--grad); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
    }
    .ft-sub { font-size: 12px; color: var(--muted); margin-top: 3px; }
    .ft-socials { display: flex; gap: 10px; justify-content: flex-end; align-items: center; }
    .ft-socials a {
      width: 36px; height: 36px; border: 1px solid var(--border);
      border-radius: 7px; display: flex; align-items: center; justify-content: center;
      color: var(--muted); font-size: 15px; transition: all 0.2s;
    }
    .ft-socials a:hover { border-color: var(--accent); color: var(--accent); }
    .ft-copy {
      text-align: center; font-size: 12px; color: var(--muted);
      margin-top: 20px; padding-top: 18px; border-top: 1px solid var(--border);
    }

    /* ===== SCROLL TOP ===== */
    #scroll-top {
      position: fixed; bottom: 80px; right: 26px;
      width: 44px; height: 44px; background: var(--grad);
      border-radius: 10px; display: flex; align-items: center; justify-content: center;
      color: #050b18; font-size: 22px; z-index: 998;
      opacity: 0; transform: translateY(16px);
      transition: all 0.3s; pointer-events: none;
    }
    #scroll-top.show { opacity: 1; transform: translateY(0); pointer-events: all; }
    #scroll-top:hover { color: #050b18; transform: translateY(-2px); }

    /* ===== WHATSAPP FLOAT ===== */
    .wa-float {
      position: fixed; bottom: 26px; right: 26px; z-index: 999;
      width: 54px; height: 54px; background: #25d366;
      border-radius: 50%; display: flex; align-items: center; justify-content: center;
      box-shadow: 0 6px 24px rgba(37,211,102,0.45);
      transition: transform 0.2s, box-shadow 0.2s;
      animation: wa-bounce 2s ease-in-out 3s 2;
    }
    .wa-float:hover { transform: scale(1.1); box-shadow: 0 10px 32px rgba(37,211,102,0.6); }
    .wa-float i { font-size: 26px; color: #fff; }
    .wa-float .wa-tip {
      position: absolute; right: 64px; top: 50%; transform: translateY(-50%);
      background: var(--card); border: 1px solid var(--border);
      color: var(--text); font-size: 13px; font-weight: 600;
      padding: 6px 14px; border-radius: 8px; white-space: nowrap;
      opacity: 0; pointer-events: none; transition: opacity 0.2s;
    }
    .wa-float:hover .wa-tip { opacity: 1; }
    @keyframes wa-bounce {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.12); }
    }

    /* ===== PRELOADER ===== */
    #preloader {
      position: fixed; inset: 0; background: var(--bg);
      display: flex; align-items: center; justify-content: center;
      z-index: 9999; transition: opacity 0.5s;
    }
    .spin {
      width: 38px; height: 38px;
      border: 3px solid var(--border); border-top-color: var(--accent);
      border-radius: 50%; animation: spin 0.7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 991px) {
      #hero { padding-top: 120px; }
      .hero-img-wrap { margin-top: 48px; }
      .hero-photo { max-width: 300px; height: 340px; }
      .hero-badge { bottom: -14px; left: 0; }
      .hero-stat-wrap { top: 20px; right: -10px; }
      .ft-socials { justify-content: flex-start; margin-top: 16px; }
      .info-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 576px) {
      section { padding: 70px 0; }
      .form-box { padding: 24px 18px; }
      .hero-stat-wrap { display: none; }
    }
  </style>
</head>

<body>

  <!-- Preloader -->
  <div id="preloader"><div class="spin"></div></div>

  <!-- WhatsApp Float -->
  <a class="wa-float" href="https://wa.me/2347060691695?text=Hi%20Charles!%20I%20saw%20your%20portfolio%20and%20I'd%20like%20to%20discuss%20a%20project." target="_blank" title="Chat on WhatsApp">
    <i class="bi bi-whatsapp"></i>
    <span class="wa-tip">Let's chat on WhatsApp</span>
  </a>

  <!-- Scroll Top -->
  <a href="#hero" id="scroll-top"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
      <a class="navbar-brand" href="#hero">CC.</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-end" id="navMenu">
        <ul class="navbar-nav align-items-lg-center gap-lg-1 mt-3 mt-lg-0">
          <li class="nav-item"><a class="nav-link active-link" href="#hero">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
          <li class="nav-item"><a class="nav-link" href="#skills">Skills</a></li>
          <li class="nav-item"><a class="nav-link" href="#resume">Resume</a></li>
          <li class="nav-item"><a class="nav-link" href="#portfolio">Projects</a></li>
          <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
          <li class="nav-item ms-lg-2 mt-2 mt-lg-0"><a class="nav-link nav-hire" href="#contact">Hire Me</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- ===== HERO ===== -->
  <section id="hero">
    <div class="hero-orb1"></div>
    <div class="hero-orb2"></div>
    <div class="container">
      <div class="row align-items-center gy-5">
        <div class="col-lg-7" data-aos="fade-right" data-aos-duration="800">
          <div class="avail-badge">
            <span class="avail-dot"></span>
            Available for new projects
          </div>
          <h1 class="hero-name">Charles-Chukwudi<br>Chukwudi</h1>
          <p class="hero-sub">
            I'm a <span class="typed" data-typed-items="Full-Stack Developer,Backend Engineer,Tech Educator,Node.js Developer,Laravel Developer">Full-Stack Developer</span><span class="typed-cursor typed-cursor--blink" aria-hidden="true"></span>
          </p>
          <div class="hero-btns">
            <a href="#portfolio" class="btn-grad"><i class="bi bi-grid-3x3-gap-fill"></i> View My Work</a>
            <a href="FE_CV.pdf" download class="btn-out"><i class="bi bi-download"></i> Download CV</a>
          </div>
          <div class="hero-socials">
            <a href="http://www.twitter.com/ChuksTech_" target="_blank" title="Twitter/X"><i class="bi bi-twitter-x"></i></a>
            <a href="https://www.linkedin.com/in/prince-charles-2a3b1a226/" target="_blank" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
            <a href="https://github.com/ChuksTech007" target="_blank" title="GitHub"><i class="bi bi-github"></i></a>
            <a href="https://www.instagram.com/chuksjnr_/" target="_blank" title="Instagram"><i class="bi bi-instagram"></i></a>
            <a href="https://web.facebook.com/charleschukwudi.chukwudi.7" target="_blank" title="Facebook"><i class="bi bi-facebook"></i></a>
          </div>
        </div>
        <div class="col-lg-5" data-aos="fade-left" data-aos-duration="800" data-aos-delay="200">
          <div class="hero-img-wrap">
            <div class="hero-img-glow"></div>
            <img src="assets/img/Charles.jpg" class="hero-photo" alt="Charles-Chukwudi Chukwudi">
            <div class="hero-badge">
              <i class="bi bi-code-slash"></i>
              <div>
                <strong>4+</strong>
                <span>Years Coding</span>
              </div>
            </div>
            <div class="hero-stat-wrap">
              <strong>25+</strong>
              
              <span>Students<br>Mentored</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== ABOUT ===== -->
  <section id="about">
    <div class="container">
      <div class="row align-items-center gy-5">
        <div class="col-lg-5" data-aos="fade-right">
          <img src="assets/img/Charles.jpg" class="about-photo" alt="Charles-Chukwudi Chukwudi">
        </div>
        <div class="col-lg-7" data-aos="fade-left" data-aos-delay="100">
          <span class="sec-tag">About Me</span>
          <h2 class="sec-title">Full-Stack Developer &amp;<br><span>Backend Engineer</span></h2>
          <p style="color:var(--muted);font-size:15px;line-height:1.9;margin-bottom:16px;">
            Results-driven Full-Stack Developer and Backend Engineer with hands-on experience building production-grade systems across food delivery, fleet management, and education platforms. Proficient in Node.js, Laravel, PHP, Python, React, and Next.js, with strong database expertise across MySQL, MongoDB, and PostgreSQL.
          </p>
          <p style="color:var(--muted);font-size:15px;line-height:1.9;">
            I'm also passionate about teaching — having mentored 20+ students and delivered institutional courses in PHP, Python, and modern frontend development. I combine technical depth with operational adaptability.
          </p>
          <div class="info-grid">
            <div class="info-cell">
              <label>Location</label>
              <span>Umuahia, Abia, Nigeria</span>
            </div>
            <div class="info-cell">
              <label>Phone</label>
              <span>+234 706 069 1695</span>
            </div>
            <div class="info-cell">
              <label>Email</label>
              <span style="font-size:12px;">charleschukwudichukwudi@gmail.com</span>
            </div>
            <div class="info-cell">
              <label>Status</label>
              <span style="color:#34d399;font-weight:600;">&#10003; Open to Work</span>
            </div>
          </div>
          <a href="#contact" class="btn-grad">Let's Work Together <i class="bi bi-arrow-right"></i></a>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== SKILLS ===== -->
  <section id="skills">
    <div class="container">
      <div class="text-center mb-5" data-aos="fade-up">
        <span class="sec-tag">What I Know</span>
        <h2 class="sec-title">My <span>Tech Stack</span></h2>
        <p class="sec-desc mx-auto">Technologies and tools I use to build production-ready, scalable systems.</p>
      </div>
      <div class="row gy-4">
        <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
          <div class="skill-card">
            <div class="skill-card-head"><i class="bi bi-server"></i> Backend Development</div>
            <div class="tags">
              <span class="tag">Node.js</span><span class="tag">Express.js</span><span class="tag">NestJS</span>
              <span class="tag">Laravel</span><span class="tag">PHP</span><span class="tag">Python</span>
              <span class="tag">REST API Design</span><span class="tag">Microservices</span>
              <span class="tag">Socket.io</span><span class="tag">JWT Auth</span><span class="tag">Livewire</span>
            </div>
          </div>
        </div>
        <div class="col-lg-6" data-aos="fade-up" data-aos-delay="150">
          <div class="skill-card">
            <div class="skill-card-head"><i class="bi bi-layout-text-window"></i> Frontend Development</div>
            <div class="tags">
              <span class="tag">React</span><span class="tag">Next.js</span><span class="tag">TypeScript</span>
              <span class="tag">JavaScript (ES6+)</span><span class="tag">Tailwind CSS</span>
              <span class="tag">Bootstrap 5</span><span class="tag">HTML5</span><span class="tag">CSS3</span>
            </div>
          </div>
        </div>
        <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
          <div class="skill-card">
            <div class="skill-card-head"><i class="bi bi-database"></i> Databases</div>
            <div class="tags">
              <span class="tag">MongoDB</span><span class="tag">MySQL</span><span class="tag">PostgreSQL</span>
              <span class="tag">Supabase</span><span class="tag">Mongoose</span><span class="tag">Geospatial Queries</span>
            </div>
          </div>
        </div>
        <div class="col-lg-6" data-aos="fade-up" data-aos-delay="250">
          <div class="skill-card">
            <div class="skill-card-head"><i class="bi bi-tools"></i> DevOps &amp; Tools</div>
            <div class="tags">
              <span class="tag">Docker</span><span class="tag">Git / GitHub</span><span class="tag">Linux</span>
              <span class="tag">Paystack</span><span class="tag">Firebase / FCM</span>
              <span class="tag">Cloudinary</span><span class="tag">Google Maps API</span>
              <span class="tag">Postman</span><span class="tag">Swagger</span><span class="tag">Kudi SMS</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== STATS ===== -->
  <section id="stats">
    <div class="container">
      <div class="row gy-4">
        <div class="col-lg-3 col-6" data-aos="fade-up" data-aos-delay="100">
          <div class="stat-box">
            <div class="stat-num"><span data-purecounter-start="0" data-purecounter-end="20" data-purecounter-duration="1" class="purecounter"></span>+</div>
            <div class="stat-lbl">Students Mentored</div>
          </div>
        </div>
        <div class="col-lg-3 col-6" data-aos="fade-up" data-aos-delay="150">
          <div class="stat-box">
            <div class="stat-num"><span data-purecounter-start="0" data-purecounter-end="10" data-purecounter-duration="1" class="purecounter"></span>+</div>
            <div class="stat-lbl">Projects Delivered</div>
          </div>
        </div>
        <div class="col-lg-3 col-6" data-aos="fade-up" data-aos-delay="200">
          <div class="stat-box">
            <div class="stat-num"><span data-purecounter-start="0" data-purecounter-end="6" data-purecounter-duration="1" class="purecounter"></span></div>
            <div class="stat-lbl">Professional Roles</div>
          </div>
        </div>
        <div class="col-lg-3 col-6" data-aos="fade-up" data-aos-delay="250">
          <div class="stat-box">
            <div class="stat-num"><span data-purecounter-start="0" data-purecounter-end="2" data-purecounter-duration="1" class="purecounter"></span>+</div>
            <div class="stat-lbl">Years Experience</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== RESUME ===== -->
  <section id="resume">
    <div class="container">
      <div class="text-center mb-5" data-aos="fade-up">
        <span class="sec-tag">My Journey</span>
        <h2 class="sec-title">Experience &amp; <span>Education</span></h2>
      </div>
      <div class="row gy-5">
        <div class="col-lg-7" data-aos="fade-right">
          <div class="col-head"><i class="bi bi-briefcase"></i> Work Experience</div>
          <div class="timeline">

            <div class="tl-item">
              <div class="tl-card">
                <div class="tl-top">
                  <span class="tl-role">Backend Engineer</span>
                  <span class="tl-period">Oct 2025 – Present</span>
                </div>
                <div class="tl-company">OunjeFood – Food Delivery &amp; Logistics · Remote</div>
                <ul class="tl-list">
                  <li>Built scalable backend services (Node.js, Express, MongoDB) for a multi-vendor platform.</li>
                  <li>Microservices with real-time order tracking via Socket.io and geospatial rider dispatch.</li>
                  <li>Integrated Paystack for multi-party payouts, DVAs, and idempotent webhook handling.</li>
                  <li>Google Maps routing, OTP verification, and Firebase/Kudi SMS notifications.</li>
                </ul>
              </div>
            </div>

            <div class="tl-item">
              <div class="tl-card">
                <div class="tl-top">
                  <span class="tl-role">Backend Tutor (PHP &amp; Python)</span>
                  <span class="tl-period">Nov 2025 – Feb 2026</span>
                </div>
                <div class="tl-company">Aptech Umuahia · Umuahia, Abia State</div>
                <ul class="tl-list">
                  <li>Delivered structured classroom instruction in PHP and Python at varying skill levels.</li>
                  <li>Designed lesson plans covering OOP, server-side scripting, databases, and API fundamentals.</li>
                </ul>
              </div>
            </div>

            <div class="tl-item">
              <div class="tl-card">
                <div class="tl-top">
                  <span class="tl-role">Frontend Tutor &amp; Community Manager</span>
                  <span class="tl-period">Mar 2025 – Jan 2026</span>
                </div>
                <div class="tl-company">ScaleFort · Remote</div>
                <ul class="tl-list">
                  <li>Mentored 20+ students in HTML, CSS, JavaScript, and Bootstrap.</li>
                  <li>Led an AI-powered website-building class and managed a large Tech Challenge community.</li>
                </ul>
              </div>
            </div>

            <div class="tl-item">
              <div class="tl-card">
                <div class="tl-top">
                  <span class="tl-role">Laravel Developer (Intern)</span>
                  <span class="tl-period">Sep 2024 – Dec 2024</span>
                </div>
                <div class="tl-company">ECR Technology Services · Umuahia, Abia State</div>
                <ul class="tl-list">
                  <li>Developed dynamic admin interfaces and integrated payment &amp; data sync APIs.</li>
                  <li>Collaborated in a multi-functional team integrating frontend, backend, and AI features.</li>
                </ul>
              </div>
            </div>

            <div class="tl-item">
              <div class="tl-card">
                <div class="tl-top">
                  <span class="tl-role">Web Developer</span>
                  <span class="tl-period">Jun 2024 – Aug 2024</span>
                </div>
                <div class="tl-company">Pumeco Industry Nigeria Limited · Umuahia, Abia State</div>
                <ul class="tl-list">
                  <li>Built a fleet and fuel management system (Laravel + RBAC) for a road construction firm.</li>
                  <li>Implemented secure auth via Laravel Breeze, custom middleware, and audit logging.</li>
                </ul>
              </div>
            </div>

          </div>
        </div>

        <div class="col-lg-5" data-aos="fade-left">
          <div class="col-head"><i class="bi bi-mortarboard"></i> Education &amp; Certifications</div>

          <div class="edu-card">
            <i class="bi bi-building"></i>
            <div>
              <div class="edu-name">B.Eng – Electrical &amp; Electronics Engineering</div>
              <div class="edu-school">Federal University of Technology Owerri (FUTO)</div>
              <div class="edu-year">2020 – 2025 &nbsp;·&nbsp; Owerri, Imo State, Nigeria</div>
            </div>
          </div>

          <div class="edu-card">
            <i class="bi bi-patch-check"></i>
            <div>
              <div class="edu-name">Full-Stack Web Developer Certification</div>
              <div class="edu-school">ECR Technological Service</div>
              <div class="edu-year">Umuahia, Abia State</div>
            </div>
          </div>

          <div class="skill-card mt-3">
            <div class="skill-card-head"><i class="bi bi-heart"></i> Interests &amp; Activities</div>
            <div class="tags">
              <span class="tag">Teaching &amp; Mentoring</span>
              <span class="tag">Technology &amp; Innovation</span>
              <span class="tag">Coding Challenges</span>
              <span class="tag">Forex Trading</span>
              <span class="tag">Crypto</span>
              <span class="tag">Reading</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== PORTFOLIO ===== -->
  <section id="portfolio">
    <div class="container">
      <div class="text-center mb-5" data-aos="fade-up">
        <span class="sec-tag">My Work</span>
        <h2 class="sec-title">Featured <span>Projects</span></h2>
        <p class="sec-desc mx-auto">Real-world systems and interfaces built across food delivery, fleet management, and web platforms.</p>
      </div>

      <div class="isotope-layout" data-default-filter="*" data-layout="masonry" data-sort="original-order">
        <ul class="pf-filters isotope-filters" data-aos="fade-up">
          <li data-filter="*" class="filter-active">All</li>
          <li data-filter=".landing-page">Landing Page</li>
          <li data-filter=".full-stack">Full Stack</li>
          <li data-filter=".front-end">Front End</li>
          <li data-filter=".back-end">Back End</li>
          <li data-filter=".app">App</li>
        </ul>

        <div class="row gy-4 isotope-container" data-aos="fade-up" data-aos-delay="100">

          <?php if (empty($projects_db)): ?>
            <div class="col-12 text-center py-5">
              <p style="color:var(--muted)">No projects yet. <a href="setup.php">Run setup</a> to load projects from the database.</p>
            </div>
          <?php else: ?>
          <?php foreach ($projects_db as $p):
            $catLabel = match($p['category']) {
              'landing-page' => 'Landing Page',
              'full-stack'   => 'Full Stack',
              'front-end'    => 'Front End',
              'back-end'     => 'Back End',
              default        => ucwords(str_replace('-', ' ', $p['category']))
            };
            $imgSrc = $p['image']
              ? (str_starts_with($p['image'], 'http') ? $p['image'] : htmlspecialchars($p['image']))
              : 'assets/img/portfolio/placeholder.png';
            $tags = array_filter(array_map('trim', explode(',', $p['tags'] ?? '')));
            $desc = htmlspecialchars($p['description'] ?? '');
            $title = htmlspecialchars($p['title']);
            $liveUrl = htmlspecialchars($p['live_url'] ?? '');
            $ghUrl   = htmlspecialchars($p['github_url'] ?? '');
          ?>
          <div class="col-lg-4 col-md-6 portfolio-item isotope-item <?= htmlspecialchars($p['category']) ?>"
               data-title="<?= $title ?>"
               data-desc="<?= $desc ?>"
               data-cat="<?= htmlspecialchars($catLabel) ?>"
               data-img="<?= $imgSrc ?>"
               data-live="<?= $liveUrl ?>"
               data-github="<?= $ghUrl ?>"
               data-tags="<?= htmlspecialchars($p['tags'] ?? '') ?>">
            <div class="proj-card">
              <div class="proj-thumb">
                <img src="<?= $imgSrc ?>" alt="<?= $title ?>" loading="lazy">
                <div class="proj-overlay">
                  <?php if ($p['image']): ?>
                  <a href="<?= $imgSrc ?>" class="glightbox" data-gallery="portfolio-gallery" title="Preview"><i class="bi bi-zoom-in"></i></a>
                  <?php endif ?>
                  <?php if ($liveUrl): ?>
                  <a href="<?= $liveUrl ?>" target="_blank" rel="noopener" title="Visit Site"><i class="bi bi-box-arrow-up-right"></i></a>
                  <?php endif ?>
                </div>
              </div>
              <div class="proj-body">
                <div class="proj-cat"><?= $catLabel ?></div>
                <div class="proj-title"><?= $title ?></div>
                <?php if (!empty($tags)): ?>
                <div class="proj-tags">
                  <?php foreach (array_slice($tags, 0, 3) as $t): ?>
                    <span class="proj-tag"><?= htmlspecialchars($t) ?></span>
                  <?php endforeach ?>
                  <?php if (count($tags) > 3): ?>
                    <span class="proj-tag proj-tag-more">+<?= count($tags) - 3 ?></span>
                  <?php endif ?>
                </div>
                <?php endif ?>
                <div class="proj-footer">
                  <button class="proj-detail-btn" onclick="openProjectModal(this.closest('.portfolio-item'))">Details <i class="bi bi-arrow-right"></i></button>
                  <?php if ($liveUrl): ?>
                  <a href="<?= $liveUrl ?>" target="_blank" rel="noopener" class="proj-live-btn" title="Live Site"><i class="bi bi-box-arrow-up-right"></i></a>
                  <?php endif ?>
                  <?php if ($ghUrl): ?>
                  <a href="<?= $ghUrl ?>" target="_blank" rel="noopener" class="proj-live-btn" title="GitHub"><i class="bi bi-github"></i></a>
                  <?php endif ?>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach ?>
          <?php endif ?>

        </div>

        <!-- Load More -->
        <div class="text-center mt-5" id="loadMoreWrap" data-aos="fade-up">
          <button id="loadMoreBtn" class="btn-outline-pf">
            <i class="bi bi-plus-circle"></i> Load More Projects
          </button>
          <p id="projCountLabel" style="font-size:12px;color:var(--muted);margin-top:10px"></p>
        </div>

      </div>
    </div>
  </section>

  <!-- ===== PROJECT DETAIL MODAL ===== -->
  <div id="projModal" class="proj-modal-bg" onclick="if(event.target===this)closeProjModal()">
    <div class="proj-modal">
      <button class="proj-modal-close" onclick="closeProjModal()"><i class="bi bi-x-lg"></i></button>
      <div class="proj-modal-img-wrap">
        <img id="pmImg" src="" alt="">
      </div>
      <div class="proj-modal-body">
        <span id="pmCat" class="proj-modal-cat"></span>
        <h3 id="pmTitle" class="proj-modal-title"></h3>
        <p id="pmDesc" class="proj-modal-desc"></p>
        <div id="pmTags" class="proj-modal-tags"></div>
        <div class="proj-modal-actions">
          <a id="pmLive" href="#" target="_blank" rel="noopener" class="btn-grad d-inline-flex align-items-center gap-2"><i class="bi bi-box-arrow-up-right"></i> View Live</a>
          <a id="pmGithub" href="#" target="_blank" rel="noopener" class="btn-outline d-inline-flex align-items-center gap-2"><i class="bi bi-github"></i> Source Code</a>
        </div>
      </div>
    </div>
  </div>

  <!-- ===== SERVICES ===== -->
  <section id="services">
    <div class="container">
      <div class="text-center mb-5" data-aos="fade-up">
        <span class="sec-tag">What I Offer</span>
        <h2 class="sec-title">My <span>Services</span></h2>
        <p class="sec-desc mx-auto">From backend systems to frontend interfaces to tech education — here's how I can help.</p>
      </div>
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
          <div class="svc-card">
            <div class="svc-icon"><i class="bi bi-server"></i></div>
            <h4 class="svc-title">Backend Development</h4>
            <p class="svc-desc">Scalable backend systems using Node.js, Express, NestJS, Laravel, and PHP. REST APIs, microservices, auth flows, and real-time features with Socket.io.</p>
          </div>
        </div>
        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="150">
          <div class="svc-card">
            <div class="svc-icon"><i class="bi bi-laptop"></i></div>
            <h4 class="svc-title">Full-Stack Web Development</h4>
            <p class="svc-desc">End-to-end development of dynamic web apps — from database architecture to intuitive UI using React, Next.js, Laravel, and modern CSS frameworks.</p>
          </div>
        </div>
        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
          <div class="svc-card">
            <div class="svc-icon"><i class="bi bi-plug"></i></div>
            <h4 class="svc-title">API Development &amp; Integration</h4>
            <p class="svc-desc">Designing and integrating REST APIs, payment gateways (Paystack), notification services (Firebase, SMS), and cloud storage — reliable and well-documented.</p>
          </div>
        </div>
        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
          <div class="svc-card">
            <div class="svc-icon"><i class="bi bi-palette"></i></div>
            <h4 class="svc-title">Frontend Design &amp; UX</h4>
            <p class="svc-desc">Intuitive, mobile-first interfaces using React, Tailwind CSS, and Bootstrap. Clean layouts, smooth interactions, and seamless cross-browser compatibility.</p>
          </div>
        </div>
        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="150">
          <div class="svc-card">
            <div class="svc-icon"><i class="bi bi-mortarboard"></i></div>
            <h4 class="svc-title">Tech Education &amp; Mentoring</h4>
            <p class="svc-desc">Structured training in web development, PHP, Python, and JavaScript. Mentored 20+ students and delivered institutional courses from fundamentals to real-world projects.</p>
          </div>
        </div>
        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
          <div class="svc-card">
            <div class="svc-icon"><i class="bi bi-lightbulb"></i></div>
            <h4 class="svc-title">Technical Consulting</h4>
            <p class="svc-desc">Advisory on system architecture, tech stack selection, API design, and project planning for startups and businesses building their digital infrastructure.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== TESTIMONIALS ===== -->
  <section id="testimonials">
    <div class="container">
      <div class="text-center mb-5" data-aos="fade-up">
        <span class="sec-tag">Kind Words</span>
        <h2 class="sec-title">What People <span>Say</span></h2>
        <p class="sec-desc mx-auto">Feedback from colleagues, clients, and students I've had the pleasure of working with.</p>
      </div>
      <div class="row gy-4">

        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
          <div class="testi-card">
            <div class="testi-quote">&ldquo;</div>
            <p class="testi-text">Charles built our company's fleet and fuel management system from scratch. His understanding of Laravel, RBAC, and clean code architecture was impressive. The system has been running flawlessly since delivery.</p>
            <div class="testi-author">
              <div class="testi-avatar">P</div>
              <div>
                <div class="testi-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                <div class="testi-name">Pumeco IT Team</div>
                <div class="testi-role">Pumeco Industry Nigeria Limited</div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="150">
          <div class="testi-card">
            <div class="testi-quote">&ldquo;</div>
            <p class="testi-text">As a student in his class, Charles made backend development feel approachable. His patience, structured lesson plans, and real-world examples helped me land my first dev role within 3 months of graduating.</p>
            <div class="testi-author">
              <div class="testi-avatar">A</div>
              <div>
                <div class="testi-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                <div class="testi-name">Aptech Student</div>
                <div class="testi-role">Backend Development Trainee</div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
          <div class="testi-card">
            <div class="testi-quote">&ldquo;</div>
            <p class="testi-text">Charles joined OunjeFood and immediately hit the ground running. His microservices architecture and Paystack integration work have been central to our platform's reliability. A solid team player and sharp engineer.</p>
            <div class="testi-author">
              <div class="testi-avatar">O</div>
              <div>
                <div class="testi-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
                <div class="testi-name">OunjeFood Team Lead</div>
                <div class="testi-role">OunjeFood Platform · Remote</div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- ===== CONTACT ===== -->
  <section id="contact">
    <div class="container">
      <div class="text-center mb-5" data-aos="fade-up">
        <span class="sec-tag">Get In Touch</span>
        <h2 class="sec-title">Let's <span>Work Together</span></h2>
        <p class="sec-desc mx-auto">Have a project in mind? Send a message and let's talk.</p>
      </div>
      <div class="row gy-5 align-items-start">
        <div class="col-lg-5" data-aos="fade-right">
          <div class="ct-item">
            <div class="ct-icon"><i class="bi bi-geo-alt"></i></div>
            <div>
              <div class="ct-lbl">Location</div>
              <div class="ct-val">143 Ozuitem Street, Umuahia, Abia, Nigeria</div>
            </div>
          </div>
          <div class="ct-item">
            <div class="ct-icon"><i class="bi bi-telephone"></i></div>
            <div>
              <div class="ct-lbl">Phone</div>
              <div class="ct-val">+234 706 069 1695</div>
            </div>
          </div>
          <div class="ct-item">
            <div class="ct-icon"><i class="bi bi-envelope"></i></div>
            <div>
              <div class="ct-lbl">Email</div>
              <div class="ct-val">
                charleschukwudichukwudi@gmail.com
                <button class="copy-btn" onclick="copyEmail(this)" title="Copy email"><i class="bi bi-clipboard"></i></button>
                <span class="copy-toast">Copied!</span>
              </div>
            </div>
          </div>
          <div class="ct-item">
            <div class="ct-icon"><i class="bi bi-github"></i></div>
            <div>
              <div class="ct-lbl">GitHub</div>
              <div class="ct-val"><a href="https://github.com/ChuksTech007" target="_blank">github.com/ChuksTech007</a></div>
            </div>
          </div>
          <div class="ct-item">
            <div class="ct-icon"><i class="bi bi-linkedin"></i></div>
            <div>
              <div class="ct-lbl">LinkedIn</div>
              <div class="ct-val"><a href="https://www.linkedin.com/in/prince-charles-2a3b1a226/" target="_blank">linkedin.com/in/prince-charles</a></div>
            </div>
          </div>
          <div class="ct-item">
            <div class="ct-icon" style="background:rgba(37,211,102,0.1);border-color:rgba(37,211,102,0.25);color:#25d366;"><i class="bi bi-whatsapp"></i></div>
            <div>
              <div class="ct-lbl">WhatsApp</div>
              <div class="ct-val"><a href="https://wa.me/2347060691695" target="_blank" style="color:#25d366;">+234 706 069 1695</a></div>
            </div>
          </div>
        </div>

        <div class="col-lg-7" data-aos="fade-left">
          <div class="form-box">
            <form id="contactForm" action="contact.php" method="post" class="php-email-form">
              <div class="row gy-4">
                <div class="col-md-6">
                  <label class="form-label">Your Name</label>
                  <input type="text" name="name" class="form-control" placeholder="John Doe" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label">Your Email</label>
                  <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
                </div>
                <div class="col-12">
                  <label class="form-label">Subject</label>
                  <input type="text" name="subject" class="form-control" placeholder="Project Inquiry" required>
                </div>
                <div class="col-12">
                  <label class="form-label">Message</label>
                  <textarea name="message" rows="6" class="form-control" placeholder="Tell me about your project..." required></textarea>
                </div>
                <div class="col-12">
                  <p id="contactMsg" style="display:none;font-size:14px;margin-bottom:12px;font-weight:500"></p>
                  <button type="submit" class="btn-send">Send Message <i class="bi bi-send"></i></button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ===== FOOTER ===== -->
  <footer>
    <div class="container">
      <div class="row align-items-center">
        <div class="col-md-6">
          <div class="ft-logo">Charles-Chukwudi.</div>
          <div class="ft-sub">Full-Stack Developer &nbsp;·&nbsp; Backend Engineer &nbsp;·&nbsp; Tech Educator</div>
        </div>
        <div class="col-md-6">
          <div class="ft-socials">
            <a href="http://www.twitter.com/ChuksTech_" target="_blank" title="Twitter/X"><i class="bi bi-twitter-x"></i></a>
            <a href="https://web.facebook.com/charleschukwudi.chukwudi.7" target="_blank" title="Facebook"><i class="bi bi-facebook"></i></a>
            <a href="https://www.instagram.com/chuksjnr_/" target="_blank" title="Instagram"><i class="bi bi-instagram"></i></a>
            <a href="https://www.linkedin.com/in/prince-charles-2a3b1a226/" target="_blank" title="LinkedIn"><i class="bi bi-linkedin"></i></a>
            <a href="https://github.com/ChuksTech007" target="_blank" title="GitHub"><i class="bi bi-github"></i></a>
          </div>
        </div>
      </div>
      <div class="ft-copy">
        &copy; 2026 Charles-Chukwudi Chukwudi. All Rights Reserved. &nbsp;·&nbsp; Designed by <a href="https://chukstech.onrender.com" target="_blank">Chukwudi</a>
      </div>
    </div>
  </footer>

  <!-- Vendor JS -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/typed.js/typed.umd.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/waypoints/noframework.waypoints.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/imagesloaded/imagesloaded.pkgd.min.js"></script>
  <script src="assets/vendor/isotope-layout/isotope.pkgd.min.js"></script>

  <script>
    // ── AOS
    AOS.init({ duration: 700, easing: 'ease-in-out', once: true, offset: 60 });

    // ── Typed.js
    const typedEl = document.querySelector('.typed');
    if (typedEl) {
      new Typed('.typed', {
        strings: typedEl.getAttribute('data-typed-items').split(','),
        typeSpeed: 60, backSpeed: 30, backDelay: 2200, loop: true
      });
    }

    // ── PureCounter
    new PureCounter();

    // ── GLightbox
    GLightbox({ selector: '.glightbox' });

    // ── Isotope
    document.addEventListener('DOMContentLoaded', function () {
      imagesLoaded('.isotope-container', function () {
        const iso = new Isotope('.isotope-container', { itemSelector: '.isotope-item', layoutMode: 'masonry' });
        document.querySelectorAll('.isotope-filters li').forEach(function (f) {
          f.addEventListener('click', function () {
            document.querySelectorAll('.isotope-filters li').forEach(x => x.classList.remove('filter-active'));
            this.classList.add('filter-active');
            iso.arrange({ filter: this.getAttribute('data-filter') });
          });
        });
      });
    });

    // ── Scroll events
    const navbar   = document.querySelector('.navbar');
    const scrollBtn = document.getElementById('scroll-top');
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');

    window.addEventListener('scroll', function () {
      const y = window.scrollY;
      navbar.classList.toggle('scrolled', y > 50);
      scrollBtn.classList.toggle('show', y > 300);

      let current = '';
      sections.forEach(s => { if (y >= s.offsetTop - 100) current = s.getAttribute('id'); });
      navLinks.forEach(l => {
        l.classList.remove('active-link');
        if (l.getAttribute('href') === '#' + current) l.classList.add('active-link');
      });
    });

    // ── Mobile: close nav on link click
    document.querySelectorAll('.navbar-nav .nav-link').forEach(function (link) {
      link.addEventListener('click', function () {
        const navMenu = document.getElementById('navMenu');
        if (navMenu.classList.contains('show')) {
          document.querySelector('.navbar-toggler').click();
        }
      });
    });

    // ── Copy email
    function copyEmail(btn) {
      navigator.clipboard.writeText('charleschukwudichukwudi@gmail.com').then(function () {
        const toast = btn.nextElementSibling;
        toast.style.opacity = '1';
        btn.innerHTML = '<i class="bi bi-clipboard-check"></i>';
        setTimeout(function () {
          toast.style.opacity = '0';
          btn.innerHTML = '<i class="bi bi-clipboard"></i>';
        }, 2500);
      });
    }

    // ── Preloader
    window.addEventListener('load', function () {
      const pre = document.getElementById('preloader');
      if (pre) { pre.style.opacity = '0'; setTimeout(() => pre.remove(), 500); }
    });

    // ── Project Detail Modal
    function openProjectModal(item) {
      const m = document.getElementById('projModal');
      document.getElementById('pmImg').src       = item.dataset.img    || '';
      document.getElementById('pmCat').textContent   = item.dataset.cat    || '';
      document.getElementById('pmTitle').textContent = item.dataset.title  || '';
      document.getElementById('pmDesc').textContent  = item.dataset.desc   || '';

      const tagsWrap = document.getElementById('pmTags');
      tagsWrap.innerHTML = '';
      (item.dataset.tags || '').split(',').filter(t => t.trim()).forEach(t => {
        const s = document.createElement('span');
        s.className = 'proj-tag'; s.textContent = t.trim();
        tagsWrap.appendChild(s);
      });

      const liveEl = document.getElementById('pmLive');
      const ghEl   = document.getElementById('pmGithub');
      if (item.dataset.live) { liveEl.href = item.dataset.live; liveEl.style.display = ''; }
      else { liveEl.style.display = 'none'; }
      if (item.dataset.github) { ghEl.href = item.dataset.github; ghEl.style.display = ''; }
      else { ghEl.style.display = 'none'; }

      m.classList.add('open');
      document.body.style.overflow = 'hidden';
    }

    function closeProjModal() {
      document.getElementById('projModal').classList.remove('open');
      document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') closeProjModal();
    });

    // ── Contact form AJAX
    (function () {
      const form = document.getElementById('contactForm');
      if (!form) return;
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        const btn = form.querySelector('button[type=submit]');
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Sending…';
        btn.disabled = true;

        fetch('contact.php', { method: 'POST', body: new FormData(form) })
          .then(r => r.json())
          .then(data => {
            btn.innerHTML = orig; btn.disabled = false;
            const msg = document.getElementById('contactMsg');
            if (msg) {
              msg.textContent = data.message;
              msg.style.color = data.success ? 'var(--accent)' : '#f87171';
              msg.style.display = 'block';
              if (data.success) form.reset();
              setTimeout(() => { msg.style.display = 'none'; }, 5000);
            }
          })
          .catch(() => { btn.innerHTML = orig; btn.disabled = false; });
      });
    })();
  </script>

</body>
</html>

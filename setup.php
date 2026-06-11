<?php
/**
 * Portfolio Database Setup
 * Run once: http://localhost/iPortfolio/setup.php
 * Supports both MySQL (local) and MongoDB Atlas (production).
 * Delete this file after setup is complete.
 */

// ── Load .env ──────────────────────────────────────────────────────────────────
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;
        [$k, $v] = array_map('trim', explode('=', $line, 2)) + [1 => ''];
        if ($k && getenv($k) === false) { putenv("$k=$v"); $_ENV[$k] = $v; }
    }
}

$seed = [
    ['Sharpness Of Excellence',          'landing-page', 'A modern, responsive landing page showcasing excellence in design. Built with clean HTML, CSS and JavaScript with smooth animations and strong call-to-actions.',                                        'assets/img/portfolio/Screenshot (224).png', 'https://chukstech007.github.io/Bobbytech/',    '', 'HTML5,CSS3,JavaScript,Bootstrap',         true, 1],
    ['Oluebube A. Chukwu – OAC',         'full-stack',   'A professional personal portfolio website for a client, featuring a clean layout, biography, projects showcase, and contact form. Built as a full-stack solution with Laravel and MySQL.',          'assets/img/portfolio/Screenshot (233).png', 'https://oluebubechukwu.com/',                  '', 'Laravel,MySQL,Bootstrap,JavaScript,PHP',  true, 2],
    ['Swift Guide',                       'front-end',    'A responsive frontend guide/educational platform with intuitive navigation, categorized content, and a modern reading experience.',                                                                 'assets/img/portfolio/Screenshot (254).png', 'https://chukstech007.github.io/SwiftGuide/',   '', 'HTML5,CSS3,JavaScript,Bootstrap',         true, 3],
    ['CRUD NodeJS App',                   'back-end',     'A full-featured CRUD application built with Node.js and Express, demonstrating REST API design patterns, MongoDB integration, and clean backend architecture.',                                     'assets/img/portfolio/Screenshot (221).png', 'https://chuks-tech.onrender.com',              '', 'Node.js,Express.js,MongoDB,REST API',     true, 4],
    ['Camp Tools',                        'landing-page', 'A clean, conversion-focused landing page for a camping equipment brand. Features product highlights, testimonials, and a clear CTA section.',                                                      'assets/img/portfolio/Screenshot (240).png', 'https://chukstech007.github.io/CampTools/',    '', 'HTML5,CSS3,JavaScript,Responsive Design', true, 5],
    ['Pumeco Industries Nigeria Limited', 'full-stack',   'A fleet and fuel management system for a road construction firm. Built with Laravel and RBAC, featuring multi-branch operations, project tracking, gallery management, secure auth, and audit logging.', 'assets/img/portfolio/Screenshot (227).png', 'https://pumeco.com.ng/',                       '', 'Laravel,PHP,MySQL,Bootstrap,RBAC',        true, 6],
    ['NASS',                              'front-end',    'A frontend web application for legislative content, featuring a structured layout for announcements, news, and documents.',                                                                         'assets/img/portfolio/Screenshot (211).png', 'https://chukstech007.github.io/NASS/',         '', 'HTML5,CSS3,JavaScript,Bootstrap',         true, 7],
    ['Oscilloscope',                      'back-end',     'A backend-driven web application for signal visualization and analysis. Features real-time data rendering and a clean API layer.',                                                                  'assets/img/portfolio/Screenshot (242).png', 'https://oscilloscope.onrender.com',            '', 'Node.js,Express.js,JavaScript,REST API',  true, 8],
    ['Expense Tracker',                   'front-end',    'A personal finance tracking application with category management, expense logging, and visual budget summaries. Built with vanilla JavaScript and local state management.',                          'assets/img/portfolio/Screenshot (389).png', 'https://chukstech007.github.io/Expense-Tracker/', '', 'HTML5,CSS3,JavaScript,LocalStorage',   true, 9],
    ['Chat Application',                  'back-end',     'A real-time chat application built with Node.js and Socket.io, supporting multiple rooms and live message delivery.',                                                                              'assets/img/portfolio/Screenshot (420).png', 'https://chuks-tech.onrender.com',              '', 'Node.js,Socket.io,Express.js,MongoDB',    true, 10],
];

$mongoUri = getenv('MONGODB_URI');
$useMongo = $mongoUri && class_exists('\MongoDB\Client');

ob_start();

if ($useMongo) {
    // ── MongoDB Atlas ──────────────────────────────────────────────────────────
    try {
        $client = new \MongoDB\Client($mongoUri);
        $col    = $client->iportfolio_db->projects;

        $count = $col->countDocuments();
        if ($count === 0) {
            $docs = [];
            foreach ($seed as [$title, $cat, $desc, $img, $live, $gh, $tags, $vis, $order]) {
                $docs[] = [
                    'title'       => $title,
                    'category'    => $cat,
                    'description' => $desc,
                    'image'       => $img,
                    'live_url'    => $live,
                    'github_url'  => $gh,
                    'tags'        => $tags,
                    'is_visible'  => $vis,
                    'sort_order'  => $order,
                    'created_at'  => new \MongoDB\BSON\UTCDateTime(),
                ];
            }
            $col->insertMany($docs);
            echo "<p style='color:#34d399'>&#10003; Seeded " . count($docs) . " projects into MongoDB.</p>";
        } else {
            echo "<p style='color:#fbbf24'>&#9888; MongoDB collection already has data ($count documents) — skipping seed.</p>";
        }
        echo "<p style='color:#34d399'>&#10003; MongoDB database <strong>iportfolio_db</strong> is ready.</p>";
        echo "<p style='color:#34d399'>&#10003; Collection <strong>projects</strong> is ready.</p>";
        echo "<p style='color:#818cf8;font-size:12px'>Driver: MongoDB Atlas</p>";

    } catch (\Exception $e) {
        echo "<p style='color:#f87171'>&#10007; MongoDB Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p style='color:#94a3b8;font-size:13px'>Check your MONGODB_URI environment variable and ensure your Atlas cluster is running and Network Access allows 0.0.0.0/0.</p>";
    }

} else {
    // ── MySQL (local XAMPP) ────────────────────────────────────────────────────
    try {
        $pdo = new PDO("mysql:host=localhost;charset=utf8mb4", 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `iportfolio_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `iportfolio_db`");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `projects` (
                `id`          INT AUTO_INCREMENT PRIMARY KEY,
                `title`       VARCHAR(255) NOT NULL,
                `category`    VARCHAR(100) NOT NULL,
                `description` TEXT,
                `image`       VARCHAR(600),
                `live_url`    VARCHAR(600),
                `github_url`  VARCHAR(600),
                `tags`        VARCHAR(500),
                `is_visible`  TINYINT(1) NOT NULL DEFAULT 1,
                `sort_order`  INT NOT NULL DEFAULT 0,
                `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");

        $count = $pdo->query("SELECT COUNT(*) FROM projects")->fetchColumn();
        if ($count == 0) {
            $stmt = $pdo->prepare("
                INSERT INTO projects (title, category, description, image, live_url, github_url, tags, is_visible, sort_order)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            foreach ($seed as [$title, $cat, $desc, $img, $live, $gh, $tags, $vis, $order]) {
                $stmt->execute([$title, $cat, $desc, $img, $live, $gh, $tags, $vis ? 1 : 0, $order]);
            }
            echo "<p style='color:#34d399'>&#10003; Seeded " . count($seed) . " projects into MySQL.</p>";
        } else {
            echo "<p style='color:#fbbf24'>&#9888; MySQL table already has data — skipping seed.</p>";
        }
        echo "<p style='color:#34d399'>&#10003; MySQL database <strong>iportfolio_db</strong> is ready.</p>";
        echo "<p style='color:#34d399'>&#10003; Table <strong>projects</strong> is ready.</p>";
        echo "<p style='color:#818cf8;font-size:12px'>Driver: MySQL (local)</p>";

    } catch (PDOException $e) {
        echo "<p style='color:#f87171'>&#10007; MySQL Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p style='color:#94a3b8;font-size:13px'>Make sure XAMPP MySQL is running.</p>";
    }
}

echo "<p style='color:#38bdf8;margin-top:12px'>&#8594; <a href='admin/login.php' style='color:#38bdf8'>Go to Admin Panel</a></p>";
echo "<p style='color:#f87171;font-size:12px;margin-top:16px'>&#9888; <strong>Delete setup.php after setup is complete.</strong></p>";

$output = ob_get_clean();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Portfolio Setup</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Inter',sans-serif;background:#050b18;color:#e2e8f0;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px}
  .box{background:#0d1f3c;border:1px solid #1e3a5f;border-radius:16px;padding:40px 44px;max-width:560px;width:100%}
  h1{font-size:22px;font-weight:700;color:#38bdf8;margin-bottom:6px}
  .driver-hint{font-size:12px;color:#94a3b8;margin-bottom:24px}
  p{margin:8px 0;font-size:14px;line-height:1.7}
  a{color:#38bdf8}
</style>
</head>
<body>
  <div class="box">
    <h1>Portfolio Database Setup</h1>
    <p class="driver-hint">
      <?= $useMongo
            ? '&#9654; Using <strong>MongoDB Atlas</strong> (MONGODB_URI detected in environment)'
            : '&#9654; Using <strong>MySQL</strong> (no MONGODB_URI in environment &mdash; <a href="https://dashboard.render.com" target="_blank" style="color:#f87171">add it in Render Dashboard → Environment</a>)' ?>
    </p>
    <?= $output ?>
  </div>
</body>
</html>

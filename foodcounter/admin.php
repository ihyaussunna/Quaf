<?php
/**
 * QUAF 2026 - Food Counter Admin & Session Management
 * URL: https://ihyaussunna.in/foodcounter/admin.php
 */
declare(strict_types=1);
require_once __DIR__.'/db.php';

$message = '';
$error = '';

// Handle Admin Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['admin_action'] ?? '';

    if ($act === 'create_session') {
        $title = trim($_POST['title'] ?? '');
        $date = trim($_POST['meal_date'] ?? date('Y-m-d'));
        $startTime = ! empty($_POST['start_time']) ? trim($_POST['start_time']).':00' : null;
        $endTime = ! empty($_POST['end_time']) ? trim($_POST['end_time']).':00' : null;
        $type = trim($_POST['meal_type'] ?? 'lunch');
        $makeActive = ! empty($_POST['make_active']) ? 1 : 0;

        if (! empty($title)) {
            if ($makeActive) {
                $pdo->query('UPDATE meal_sessions SET is_active = 0');
            }

            $stmt = $pdo->prepare('INSERT INTO meal_sessions (title, meal_date, meal_type, start_time, end_time, is_active) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$title, $date, $type, $startTime, $endTime, $makeActive]);
            $message = 'പുതിയ മീൽ സെഷൻ വിജയകരമായി ചേർത്തു: '.htmlspecialchars($title);
        } else {
            $error = 'സെഷൻ പേര് നൽകുക.';
        }
    } elseif ($act === 'set_active') {
        $sessionId = (int) ($_POST['session_id'] ?? 0);
        $pdo->query('UPDATE meal_sessions SET is_active = 0');
        $stmt = $pdo->prepare('UPDATE meal_sessions SET is_active = 1 WHERE id = ?');
        $stmt->execute([$sessionId]);
        $message = 'ആക്ടീവ് സെഷൻ മാറ്റി സജ്ജീകരിച്ചു. കൗണ്ടർ പേജിൽ ഇപ്പോൾ ഈ സെഷൻ മാത്രമാണ് ലഭ്യമാവുക.';
    } elseif ($act === 'delete_session') {
        $sessionId = (int) ($_POST['session_id'] ?? 0);
        if ($sessionId > 0) {
            $stmt = $pdo->prepare('DELETE FROM food_logs WHERE meal_session_id = ?');
            $stmt->execute([$sessionId]);
            $stmt = $pdo->prepare('DELETE FROM meal_sessions WHERE id = ?');
            $stmt->execute([$sessionId]);
            $message = 'മീൽ സെഷനും അതിന്റെ സ്കാൻ വിവരങ്ങളും പൂർണ്ണമായി നീക്കം ചെയ്തു.';
        }
    } elseif ($act === 'update_times') {
        $sessionId = (int) ($_POST['session_id'] ?? 0);
        $date = trim($_POST['meal_date'] ?? date('Y-m-d'));
        $startTime = ! empty($_POST['start_time']) ? trim($_POST['start_time']).':00' : null;
        $endTime = ! empty($_POST['end_time']) ? trim($_POST['end_time']).':00' : null;

        $stmt = $pdo->prepare('UPDATE meal_sessions SET meal_date = ?, start_time = ?, end_time = ? WHERE id = ?');
        $stmt->execute([$date, $startTime, $endTime, $sessionId]);
        $message = 'സെഷൻ സമയ ക്രമീകരണം പുതുക്കി.';
    } elseif ($act === 'extend_30') {
        $sessionId = (int) ($_POST['session_id'] ?? 0);
        $stmt = $pdo->prepare("
            UPDATE meal_sessions 
            SET end_time = ADDTIME(IFNULL(end_time, CURTIME()), '00:30:00') 
            WHERE id = ?
        ");
        $stmt->execute([$sessionId]);
        $message = 'ഭക്ഷണ വിതരണ സമയം 30 മിനിറ്റ് കൂടി നീട്ടി നൽകി (+30 Mins).';
    } elseif ($act === 'reset_session') {
        $sessionId = (int) ($_POST['session_id'] ?? 0);
        $stmt = $pdo->prepare('DELETE FROM food_logs WHERE meal_session_id = ?');
        $stmt->execute([$sessionId]);
        $message = 'ഈ സെഷനിലെ എല്ലാ സ്കാൻ വിവരങ്ങളും റീസെറ്റ് ചെയ്തു.';
    }
}

// Handle Export CSV
if (isset($_GET['export_csv'])) {
    $sessionId = (int) $_GET['export_csv'];
    $stmt = $pdo->prepare('SELECT * FROM meal_sessions WHERE id = ?');
    $stmt->execute([$sessionId]);
    $session = $stmt->fetch();
    $sessionTitle = $session ? $session['title'] : 'all';

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="food-attendance-'.preg_replace('/[^a-zA-Z0-9_-]/', '_', $sessionTitle).'-'.date('Ymd-His').'.csv"');

    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM
    fputcsv($out, ['Chest Number', 'Student Name', 'Team', 'Category', 'Class', 'Food Status', 'Scanned Time (IST)', 'Counter']);

    $stmt = $pdo->prepare("
        SELECT s.chest_number, s.name, s.group_name, s.category, s.class_level,
               fl.scanned_at, fl.counter_name
        FROM students s
        LEFT JOIN food_logs fl ON fl.student_chest = s.chest_number AND fl.meal_session_id = ?
        WHERE s.group_name != 'General'
        ORDER BY s.chest_number ASC
    ");
    $stmt->execute([$sessionId]);

    while ($row = $stmt->fetch()) {
        $formattedDate = $row['scanned_at'] ? date('d-m-Y h:i:s A', strtotime($row['scanned_at'])) : '-';
        fputcsv($out, [
            $row['chest_number'],
            $row['name'],
            $row['group_name'],
            $row['category'],
            $row['class_level'],
            $row['scanned_at'] ? 'COLLECTED' : 'NOT COLLECTED',
            $formattedDate,
            $row['counter_name'] ?? '-',
        ]);
    }

    fclose($out);
    exit;
}

$sessions = $pdo->query('
    SELECT ms.*, COUNT(fl.id) as served_count
    FROM meal_sessions ms
    LEFT JOIN food_logs fl ON fl.meal_session_id = ms.id
    GROUP BY ms.id
    ORDER BY ms.meal_date DESC, ms.id DESC
')->fetchAll();

$totalStudents = (int) $pdo->query("SELECT COUNT(*) FROM students WHERE group_name != 'General'")->fetchColumn();
$nowTime = date('H:i:s');
$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QUAF 2026 | Food Counter Administration</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon.png">
    <link rel="apple-touch-icon" href="favicon.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Anek+Malayalam:wght@400;600;700;800&family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sora', sans-serif; }
        .font-anek { font-family: 'Anek Malayalam', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen p-3 sm:p-6 antialiased pb-16">
    <div class="max-w-5xl mx-auto space-y-5">

        <!-- Top Header -->
        <div class="flex items-center justify-between border-b border-slate-200 pb-4 bg-white p-4 rounded-2xl shadow-xs">
            <div class="flex items-center gap-3">
                <a href="index.php">
                    <img src="logo.svg" onerror="this.onerror=null; this.src='logo.png';" alt="QUAF Logo" class="h-9 sm:h-10 w-auto object-contain">
                </a>
                <div class="border-l border-slate-200 pl-3">
                    <h1 class="text-base sm:text-lg font-black text-slate-900 flex items-center gap-2">
                        <span>Food Counter Admin</span>
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-mono font-bold"><?= $totalStudents ?> Students</span>
                    </h1>
                    <p class="text-[11px] text-slate-500 font-anek">മീൽ സെഷൻ കൺട്രോളും സമയ ക്രമീകരണവും</p>
                </div>
            </div>
            <a href="index.php" class="px-4 py-2.5 rounded-xl bg-[#be1e2d] hover:bg-red-700 text-white text-xs font-bold shadow-sm transition flex items-center gap-1.5">
                <span>Open Counter ↗</span>
            </a>
        </div>

        <?php if (! empty($message)) { ?>
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-900 text-xs font-bold shadow-xs">
                ✓ <?= htmlspecialchars($message) ?>
            </div>
        <?php } ?>

        <?php if (! empty($error)) { ?>
            <div class="p-4 rounded-xl bg-red-50 border border-red-300 text-red-900 text-xs font-bold shadow-xs">
                ✕ <?= htmlspecialchars($error) ?>
            </div>
        <?php } ?>

        <!-- Add New Session Card -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm">
            <div class="flex items-center gap-2 mb-3">
                <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">+</span>
                <h2 class="text-sm font-bold text-slate-900">Add New Meal Session (പുതിയ സെഷൻ ചേർക്കുക)</h2>
            </div>
            
            <form method="POST" class="space-y-3">
                <input type="hidden" name="admin_action" value="create_session">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    <div class="lg:col-span-2">
                        <label class="block text-slate-600 font-semibold text-xs mb-1">Session Title</label>
                        <input type="text" name="title" placeholder="e.g. Day 1 - Dinner (അത്താഴം)" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-xs font-semibold focus:bg-white focus:outline-none focus:border-[#be1e2d]">
                    </div>
                    <div>
                        <label class="block text-slate-600 font-semibold text-xs mb-1">Meal Date</label>
                        <input type="date" name="meal_date" value="<?= date('Y-m-d') ?>" required class="w-full py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-xs font-semibold focus:bg-white focus:outline-none focus:border-[#be1e2d]">
                    </div>
                    <div>
                        <label class="block text-slate-600 font-semibold text-xs mb-1">Start Time (തുടങ്ങുന്നത്)</label>
                        <input type="time" name="start_time" value="12:30" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-xs font-semibold focus:bg-white focus:outline-none focus:border-[#be1e2d]">
                    </div>
                    <div>
                        <label class="block text-slate-600 font-semibold text-xs mb-1">End Time (അവസാനിക്കുന്നത്)</label>
                        <input type="time" name="end_time" value="14:30" class="w-full py-2.5 px-3 bg-slate-50 border border-slate-300 rounded-xl text-slate-900 text-xs font-semibold focus:bg-white focus:outline-none focus:border-[#be1e2d]">
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-slate-100">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                        <input type="checkbox" name="make_active" value="1" class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                        <span>Make Active Immediately (ഇപ്പോൾ തന്നെ കൗണ്ടറിൽ ആക്റ്റീവ് ആക്കുക)</span>
                    </label>

                    <button type="submit" class="py-2.5 px-5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition cursor-pointer">
                        Save New Session
                    </button>
                </div>
            </form>
        </div>

        <!-- Sessions List & Operations Table -->
        <div class="bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-bold text-slate-900">Manage Meal Sessions</h2>
                <span class="text-xs text-slate-500">Current Server Time: <strong class="text-slate-800 font-mono"><?= date('h:i A') ?> (IST)</strong></span>
            </div>
            
            <div class="overflow-x-auto -mx-4 sm:mx-0">
                <table class="w-full text-left text-xs min-w-[700px]">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold text-[11px]">
                            <th class="py-3 px-3">Session Title</th>
                            <th class="py-3 px-3">Date</th>
                            <th class="py-3 px-3">Time Window</th>
                            <th class="py-3 px-3">Counter Status</th>
                            <th class="py-3 px-3">Served / Total</th>
                            <th class="py-3 px-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        <?php foreach ($sessions as $s) { ?>
                            <?php
                                $pct = $totalStudents > 0 ? round(($s['served_count'] / $totalStudents) * 100, 1) : 0;
                            $startFmt = ! empty($s['start_time']) ? date('h:i A', strtotime($s['start_time'])) : null;
                            $endFmt = ! empty($s['end_time']) ? date('h:i A', strtotime($s['end_time'])) : null;
                            $isExpired = (! empty($s['end_time']) && $s['meal_date'] === $today && $nowTime > $s['end_time']);
                            $isUpcoming = (! empty($s['start_time']) && $s['meal_date'] === $today && $nowTime < $s['start_time']);
                            ?>
                            <tr class="hover:bg-slate-50 transition <?= $s['is_active'] ? 'bg-emerald-50/40' : '' ?>">
                                <td class="py-3 px-3">
                                    <div class="font-bold text-slate-900 text-xs sm:text-sm">
                                        <?= htmlspecialchars($s['title']) ?>
                                    </div>
                                    <?php if ($s['is_active']) { ?>
                                        <span class="mt-0.5 inline-block text-[10px] px-2 py-0.5 rounded-full bg-emerald-600 text-white font-extrabold uppercase">Active on Counter</span>
                                    <?php } ?>
                                </td>
                                <td class="py-3 px-3 text-slate-600 font-mono font-semibold">
                                    <?= date('d M Y', strtotime($s['meal_date'])) ?>
                                </td>
                                <td class="py-3 px-3 font-mono">
                                    <?php if ($startFmt || $endFmt) { ?>
                                        <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-slate-800 font-bold text-[11px]">
                                            <?= $startFmt ?: 'Start' ?> – <?= $endFmt ?: 'No Limit' ?>
                                        </span>
                                    <?php } else { ?>
                                        <span class="text-slate-400">No time limit</span>
                                    <?php } ?>
                                </td>
                                <td class="py-3 px-3">
                                    <?php if ($s['is_active']) { ?>
                                        <?php if ($isExpired) { ?>
                                            <span class="px-2 py-0.5 rounded-full bg-red-100 text-red-800 font-extrabold text-[10px]">Auto-Closed (സമയം കഴിഞ്ഞു)</span>
                                        <?php } elseif ($isUpcoming) { ?>
                                            <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-extrabold text-[10px]">Upcoming (തുടങ്ങിയിട്ടില്ല)</span>
                                        <?php } else { ?>
                                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-extrabold text-[10px] animate-pulse">Open (സ്കാനിംഗ് നടക്കുന്നു)</span>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="admin_action" value="set_active">
                                            <input type="hidden" name="session_id" value="<?= $s['id'] ?>">
                                            <button type="submit" class="px-2 py-1 rounded bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-[10px] cursor-pointer">
                                                Activate
                                            </button>
                                        </form>
                                    <?php } ?>
                                </td>
                                <td class="py-3 px-3 font-mono">
                                    <span class="text-emerald-700 font-black text-sm"><?= $s['served_count'] ?></span>
                                    <span class="text-slate-400">/ <?= $totalStudents ?> (<?= $pct ?>%)</span>
                                </td>
                                <td class="py-3 px-3 text-right space-x-1 whitespace-nowrap">
                                    <!-- Quick +30 Mins -->
                                    <form method="POST" class="inline" title="Extend serving time by 30 minutes">
                                        <input type="hidden" name="admin_action" value="extend_30">
                                        <input type="hidden" name="session_id" value="<?= $s['id'] ?>">
                                        <button type="submit" class="px-2 py-1 rounded bg-amber-100 hover:bg-amber-200 text-amber-900 border border-amber-300 text-[10px] font-bold cursor-pointer">
                                            +30 Mins
                                        </button>
                                    </form>

                                    <!-- Export CSV -->
                                    <a href="admin.php?export_csv=<?= $s['id'] ?>" class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-300 text-[10px] font-bold shadow-2xs">
                                        CSV
                                    </a>

                                    <!-- Delete Section -->
                                    <form method="POST" class="inline" onsubmit="return confirm('ശ്രദ്ധിക്കുക: <?= htmlspecialchars(addslashes($s['title'])) ?> എന്ന സെഷൻ പൂർണ്ണമായി ഡിലീറ്റ് ചെയ്യണോ?');">
                                        <input type="hidden" name="admin_action" value="delete_session">
                                        <input type="hidden" name="session_id" value="<?= $s['id'] ?>">
                                        <button type="submit" class="px-2 py-1 rounded bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-[10px] font-bold cursor-pointer">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</body>
</html>

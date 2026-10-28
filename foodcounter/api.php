<?php

/**
 * QUAF 2026 - Food Counter Management API
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

require_once __DIR__.'/db.php';

$action = $_GET['action'] ?? $_POST['action'] ?? 'stats';

function getActiveSession(PDO $pdo): ?array
{
    $stmt = $pdo->query('SELECT * FROM meal_sessions WHERE is_active = 1 ORDER BY id DESC LIMIT 1');
    $session = $stmt->fetch();
    if (! $session) {
        $stmt = $pdo->query('SELECT * FROM meal_sessions ORDER BY id ASC LIMIT 1');
        $session = $stmt->fetch();
    }

    return $session ?: null;
}

function checkSessionStatus(?array $session): array
{
    if (! $session) {
        return [
            'is_open' => false,
            'status' => 'no_session',
            'badge_text' => 'സെഷൻ ലഭ്യമല്ല',
            'time_range' => '',
            'message' => 'ആക്ടീവ് ആയ മീൽ സെഷൻ ലഭ്യമല്ല. അഡ്മിൻ പാനലിൽ നിന്ന് സെഷൻ ആക്റ്റിവേറ്റ് ചെയ്യുക.',
        ];
    }

    $now = date('H:i:s');
    $today = date('Y-m-d');
    $startFormatted = ! empty($session['start_time']) ? date('h:i A', strtotime($session['start_time'])) : null;
    $endFormatted = ! empty($session['end_time']) ? date('h:i A', strtotime($session['end_time'])) : null;

    $timeRangeText = '';
    if ($startFormatted && $endFormatted) {
        $timeRangeText = "{$startFormatted} – {$endFormatted}";
    } elseif ($startFormatted) {
        $timeRangeText = "Starts {$startFormatted}";
    } elseif ($endFormatted) {
        $timeRangeText = "Ends {$endFormatted}";
    }

    // Check if meal_date is in future
    if (! empty($session['meal_date']) && $session['meal_date'] > $today) {
        $dateFormatted = date('d-m-Y', strtotime($session['meal_date']));

        return [
            'is_open' => false,
            'status' => 'upcoming_date',
            'badge_text' => 'തീയതി ആയിട്ടില്ല',
            'time_range' => $timeRangeText,
            'message' => "ഈ മീൽ സെഷൻ നിശ്ചയിച്ചിരിക്കുന്നത് {$dateFormatted}-നാണ്. ഇന്ന് ഈ സെഷൻ ആരംഭിച്ചിട്ടില്ല.",
        ];
    }

    // Check if start_time not reached
    if (! empty($session['start_time']) && $now < $session['start_time']) {
        return [
            'is_open' => false,
            'status' => 'not_started',
            'badge_text' => "തുടങ്ങുന്ന സമയം: {$startFormatted}",
            'time_range' => $timeRangeText,
            'message' => "ഭക്ഷണ വിതരണം ആരംഭിച്ചിട്ടില്ല! ({$session['title']} ആരംഭിക്കുന്നത് {$startFormatted}-ന് ആണ്).",
        ];
    }

    // Check if end_time passed
    if (! empty($session['end_time']) && $now > $session['end_time']) {
        return [
            'is_open' => false,
            'status' => 'expired',
            'badge_text' => "സമയം കഴിഞ്ഞു ({$endFormatted})",
            'time_range' => $timeRangeText,
            'message' => "ഭക്ഷണ വിതരണ സമയം അവസാനിച്ചു! ({$session['title']} സമയം {$endFormatted}-ന് കഴിഞ്ഞു). അഡ്മിൻ സമയം നീട്ടിയാൽ മാത്രമേ ഇനി ഭക്ഷണം നൽകാൻ സാധിക്കൂ.",
        ];
    }

    return [
        'is_open' => true,
        'status' => 'active',
        'badge_text' => $endFormatted ? "Ends at {$endFormatted}" : 'Open',
        'time_range' => $timeRangeText,
        'message' => 'Active',
    ];
}

function cleanChestNumber(string $raw): string
{
    $raw = trim($raw);
    if (empty($raw)) {
        return '';
    }

    // Handle full URL: https://quaf.ihyaussunna.in/verify/student/QF1001
    if (str_contains($raw, '/')) {
        $path = parse_url($raw, PHP_URL_PATH);
        $segments = explode('/', trim((string) $path, '/'));
        $raw = end($segments);
    }

    $raw = strtoupper(trim($raw));

    // If only numbers e.g. "1001", try prepending "QF"
    if (ctype_digit($raw)) {
        $raw = 'QF'.$raw;
    }

    return $raw;
}

try {
    switch ($action) {
        case 'scan':
            $rawChest = $_POST['chest'] ?? $_GET['chest'] ?? '';
            $counterName = trim($_POST['counter'] ?? $_GET['counter'] ?? 'Counter 1');
            $customSessionId = ! empty($_POST['session_id']) ? (int) $_POST['session_id'] : null;

            if (empty($rawChest)) {
                echo json_encode(['success' => false, 'status' => 'empty', 'message' => 'ചെസ്റ്റ് നമ്പർ നൽകുക']);
                exit;
            }

            $chest = cleanChestNumber($rawChest);

            // 1. Get Admin Active Session Only
            $session = getActiveSession($pdo);

            if (! $session) {
                echo json_encode(['success' => false, 'status' => 'no_session', 'message' => 'ആക്ടീവ് ആയ മീൽ സെഷൻ ലഭ്യമല്ല. അഡ്മിൻ പാനലിൽ നിന്ന് സെഷൻ ആക്റ്റിവേറ്റ് ചെയ്യുക.']);
                exit;
            }

            // Check Time Window (Auto-close after end_time, block before start_time)
            $statusInfo = checkSessionStatus($session);
            if (! $statusInfo['is_open']) {
                echo json_encode([
                    'success' => false,
                    'status' => $statusInfo['status'],
                    'message' => $statusInfo['message'],
                    'session' => $session,
                    'session_status' => $statusInfo,
                ]);
                exit;
            }

            // 2. Lookup Student
            $stmt = $pdo->prepare('SELECT * FROM students WHERE chest_number = ? LIMIT 1');
            $stmt->execute([$chest]);
            $student = $stmt->fetch();

            if (! $student) {
                echo json_encode([
                    'success' => false,
                    'status' => 'not_found',
                    'chest' => $chest,
                    'message' => "വിദ്യാർത്ഥിയെ കണ്ടെത്തിയില്ല! ({$chest}) രജിസ്റ്റർ ചെയ്ത ചെസ്റ്റ് നമ്പർ അല്ല.",
                ]);
                exit;
            }

            // 3. Check Duplicate in current session
            $stmt = $pdo->prepare('SELECT * FROM food_logs WHERE student_chest = ? AND meal_session_id = ? LIMIT 1');
            $stmt->execute([$chest, $session['id']]);
            $existing = $stmt->fetch();

            if ($existing) {
                $servedTime = date('h:i:s A', strtotime($existing['scanned_at']));
                echo json_encode([
                    'success' => false,
                    'status' => 'already_taken',
                    'message' => 'ഭക്ഷണം ഇതിനകം നൽകിയതാണ്!',
                    'served_at' => $servedTime,
                    'counter' => $existing['counter_name'],
                    'student' => $student,
                    'session' => $session,
                ]);
                exit;
            }

            // 4. Issue Food (Insert Log)
            $stmt = $pdo->prepare('INSERT INTO food_logs (student_chest, meal_session_id, counter_name, scanned_by) VALUES (?, ?, ?, ?)');
            $stmt->execute([$chest, $session['id'], $counterName, 'Volunteer']);
            $logId = (int) $pdo->lastInsertId();

            echo json_encode([
                'success' => true,
                'status' => 'issued',
                'message' => 'ഭക്ഷണം നൽകാം (Food Issued)',
                'time' => date('h:i A'),
                'log_id' => $logId,
                'student' => $student,
                'session' => $session,
            ]);
            exit;

        case 'stats':
            $session = getActiveSession($pdo);
            $sessionId = $session ? (int) $session['id'] : 0;
            $statusInfo = checkSessionStatus($session);

            // Total students (5 Official Teams only)
            $totalStudents = (int) $pdo->query("SELECT COUNT(*) FROM students WHERE group_name != 'General'")->fetchColumn();

            // Total served in active session
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM food_logs fl JOIN students s ON s.chest_number = fl.student_chest WHERE fl.meal_session_id = ? AND s.group_name != 'General'");
            $stmt->execute([$sessionId]);
            $totalServed = (int) $stmt->fetchColumn();

            $percentage = $totalStudents > 0 ? round(($totalServed / $totalStudents) * 100, 1) : 0;

            // Team-wise breakdown (5 Official Teams)
            $stmt = $pdo->prepare("
                SELECT s.group_name, s.group_code, s.group_color,
                       COUNT(fl.id) as served_count,
                       (SELECT COUNT(*) FROM students sub WHERE sub.group_code = s.group_code) as total_group_count
                FROM students s
                LEFT JOIN food_logs fl ON fl.student_chest = s.chest_number AND fl.meal_session_id = ?
                WHERE s.group_name != 'General' AND s.group_code != 'GEN'
                GROUP BY s.group_name, s.group_code, s.group_color
                ORDER BY s.group_name ASC
            ");
            $stmt->execute([$sessionId]);
            $teams = $stmt->fetchAll();

            echo json_encode([
                'success' => true,
                'active_session' => $session,
                'session_status' => $statusInfo,
                'total_students' => $totalStudents,
                'total_served' => $totalServed,
                'pending' => max(0, $totalStudents - $totalServed),
                'percentage' => $percentage,
                'teams' => $teams,
            ]);
            exit;

        case 'recent':
            $session = getActiveSession($pdo);
            $sessionId = $session ? (int) $session['id'] : 0;

            $stmt = $pdo->prepare("
                SELECT fl.id as log_id, fl.student_chest, fl.counter_name, fl.scanned_at,
                       DATE_FORMAT(fl.scanned_at, '%h:%i:%s %p') as formatted_time,
                       DATE_FORMAT(fl.scanned_at, '%h:%i %p') as formatted_time_short,
                       s.name, s.group_name, s.group_code, s.group_color, s.category, s.class_level
                FROM food_logs fl
                JOIN students s ON s.chest_number = fl.student_chest
                WHERE fl.meal_session_id = ?
                ORDER BY fl.id DESC
                LIMIT 20
            ");
            $stmt->execute([$sessionId]);
            $recent = array_map(function ($r) {
                if (empty($r['formatted_time']) && ! empty($r['scanned_at'])) {
                    $r['formatted_time'] = date('h:i:s A', strtotime($r['scanned_at']));
                }

                return $r;
            }, $stmt->fetchAll());

            echo json_encode([
                'success' => true,
                'recent' => $recent,
            ]);
            exit;

        case 'undo':
            $logId = (int) ($_POST['log_id'] ?? $_GET['log_id'] ?? 0);
            if ($logId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid log ID']);
                exit;
            }

            $stmt = $pdo->prepare('DELETE FROM food_logs WHERE id = ?');
            $stmt->execute([$logId]);

            echo json_encode(['success' => true, 'message' => 'എൻട്രി വിജയകരമായി പിൻവലിച്ചു (Cancelled)']);
            exit;

        case 'set_session':
            $sessionId = (int) ($_POST['session_id'] ?? 0);
            if ($sessionId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid session ID']);
                exit;
            }

            $pdo->query('UPDATE meal_sessions SET is_active = 0');
            $stmt = $pdo->prepare('UPDATE meal_sessions SET is_active = 1 WHERE id = ?');
            $stmt->execute([$sessionId]);

            echo json_encode(['success' => true, 'message' => 'മീൽ സെഷൻ മാറ്റി സജ്ജീകരിച്ചു']);
            exit;

        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action']);
            exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: '.$e->getMessage()]);
}

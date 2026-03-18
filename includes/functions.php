<?php
function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function slug(string $str): string {
    $str = strtolower(trim($str));
    $str = preg_replace('/[^a-z0-9\-]/', '-', $str);
    $str = preg_replace('/-+/', '-', $str);
    return trim($str, '-');
}

function randomKey(int $length = 32): string {
    return bin2hex(random_bytes($length / 2));
}

function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', strtotime($datetime));
}

function formatDate(string $datetime, string $format = 'M j, Y'): string {
    return date($format, strtotime($datetime));
}

function statusBadge(string $status): string {
    $map = [
        'new'          => ['bg-blue-100 text-blue-700', 'New'],
        'under_review' => ['bg-yellow-100 text-yellow-700', 'Under Review'],
        'planned'      => ['bg-indigo-100 text-indigo-700', 'Planned'],
        'in_progress'  => ['bg-orange-100 text-orange-700', 'In Progress'],
        'done'         => ['bg-green-100 text-green-700', 'Done'],
        'declined'     => ['bg-red-100 text-red-700', 'Declined'],
        'duplicate'    => ['bg-gray-100 text-gray-600', 'Duplicate'],
    ];
    [$cls, $label] = $map[$status] ?? ['bg-gray-100 text-gray-600', ucfirst($status)];
    return "<span class=\"inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium $cls\">$label</span>";
}

function priorityBadge(string $priority): string {
    $map = [
        'critical' => ['bg-red-100 text-red-700', '🔴 Critical'],
        'high'     => ['bg-orange-100 text-orange-700', '🟠 High'],
        'medium'   => ['bg-yellow-100 text-yellow-700', '🟡 Medium'],
        'low'      => ['bg-gray-100 text-gray-600', '⚪ Low'],
    ];
    [$cls, $label] = $map[$priority] ?? ['bg-gray-100 text-gray-600', ucfirst($priority)];
    return "<span class=\"inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium $cls\">$label</span>";
}

function sentimentBadge(?string $sentiment): string {
    if (!$sentiment) return '';
    $map = [
        'positive' => ['bg-green-100 text-green-700', '😊 Positive'],
        'neutral'  => ['bg-gray-100 text-gray-600', '😐 Neutral'],
        'negative' => ['bg-red-100 text-red-700', '😟 Negative'],
    ];
    [$cls, $label] = $map[$sentiment] ?? ['bg-gray-100 text-gray-600', $sentiment];
    return "<span class=\"inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium $cls\">$label</span>";
}

function redirect(string $url): never {
    header("Location: $url");
    exit;
}

function flash(string $type, string $msg): void {
    Auth::start();
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): array {
    Auth::start();
    $msgs = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $msgs;
}

function csrf(): string {
    Auth::start();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = randomKey(32);
    }
    return $_SESSION['csrf'];
}

function verifyCsrf(): bool {
    Auth::start();
    $token = $_POST['_csrf'] ?? '';
    return hash_equals($_SESSION['csrf'] ?? '', $token);
}

function rateLimit(string $action, int $limit = 10, int $windowSeconds = 60): bool {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $row = DB::fetch("SELECT * FROM ff_rate_limits WHERE ip = ? AND action = ?", [$ip, $action]);
    if ($row) {
        $windowStart = strtotime($row['window_start']);
        if (time() - $windowStart > $windowSeconds) {
            DB::query("UPDATE ff_rate_limits SET count = 1, window_start = NOW() WHERE ip = ? AND action = ?", [$ip, $action]);
            return true;
        }
        if ($row['count'] >= $limit) {
            return false;
        }
        DB::query("UPDATE ff_rate_limits SET count = count + 1 WHERE ip = ? AND action = ?", [$ip, $action]);
    } else {
        DB::query("INSERT INTO ff_rate_limits (ip, action) VALUES (?, ?) ON DUPLICATE KEY UPDATE count = count + 1, window_start = IF(TIMESTAMPDIFF(SECOND, window_start, NOW()) > $windowSeconds, NOW(), window_start)", [$ip, $action]);
    }
    return true;
}

function logActivity(int $projectId, ?int $userId, ?int $feedbackId, string $action, array $meta = []): void {
    DB::insert('ff_activity', [
        'project_id'  => $projectId,
        'user_id'     => $userId,
        'feedback_id' => $feedbackId,
        'action'      => $action,
        'meta'        => $meta ? json_encode($meta) : null,
    ]);
}

function getUserProjects(int $userId): array {
    return DB::fetchAll(
        "SELECT p.*, 
            (SELECT COUNT(*) FROM ff_feedback WHERE project_id = p.id) as feedback_count,
            (SELECT COUNT(*) FROM ff_feedback WHERE project_id = p.id AND status = 'new') as new_count
         FROM ff_projects p
         LEFT JOIN ff_project_members pm ON pm.project_id = p.id AND pm.user_id = ?
         WHERE p.owner_id = ? OR pm.user_id = ?
         GROUP BY p.id
         ORDER BY p.created_at DESC",
        [$userId, $userId, $userId]
    );
}

function getProject(int $projectId, int $userId): ?array {
    return DB::fetch(
        "SELECT p.* FROM ff_projects p
         LEFT JOIN ff_project_members pm ON pm.project_id = p.id AND pm.user_id = ?
         WHERE p.id = ? AND (p.owner_id = ? OR pm.user_id = ?)",
        [$userId, $projectId, $userId, $userId]
    );
}

function sanitize(string $input): string {
    return trim(strip_tags($input));
}

function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function uploadFile(array $file, string $subdir = 'attachments'): array|false {
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size'] > MAX_FILE_SIZE) return false;
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) return false;
    $filename = randomKey(20) . '.' . $ext;
    $dir = UPLOAD_DIR . $subdir . '/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) return false;
    return [
        'filename'      => $filename,
        'original_name' => $file['name'],
        'mime_type'     => $file['type'],
        'file_size'     => $file['size'],
        'url'           => UPLOAD_URL . $subdir . '/' . $filename,
    ];
}

function getNotificationCount(int $userId): int {
    return DB::count("SELECT COUNT(*) FROM ff_notifications WHERE user_id = ? AND is_read = 0", [$userId]);
}

function sendEmail(string $to, string $subject, string $htmlBody): bool {
    if (empty(SMTP_HOST)) {
        // Fallback to PHP mail()
        $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\nFrom: " . SMTP_FROM_NAME . " <" . SMTP_FROM . ">";
        return mail($to, $subject, $htmlBody, $headers);
    }
    // PHPMailer would go here — use composer if available
    // For now, use PHP mail() as fallback
    $headers = "MIME-Version: 1.0\r\nContent-type: text/html; charset=UTF-8\r\nFrom: " . SMTP_FROM_NAME . " <" . SMTP_FROM . ">";
    return mail($to, $subject, $htmlBody, $headers);
}

function aiAnalyzeFeedback(array $feedback): ?array {
    if (!AI_ENABLED) return null;
    $prompt = "Analyze this product feedback:\nTitle: {$feedback['title']}\nDescription: {$feedback['description']}\n\nReturn a JSON object with:\n- sentiment: positive|neutral|negative\n- sentiment_score: 0.0-1.0\n- summary: one sentence summary\n- priority_score: 0-10\n- suggested_tags: array of 1-3 tags\n- suggested_reply: a brief empathetic reply draft";
    $response = openAiCall($prompt);
    if ($response) {
        return json_decode($response, true);
    }
    return null;
}

function openAiCall(string $prompt, string $model = null): ?string {
    if (!AI_ENABLED) return null;
    $model = $model ?? OPENAI_MODEL;
    $data = json_encode([
        'model'    => $model,
        'messages' => [['role' => 'user', 'content' => $prompt]],
        'response_format' => ['type' => 'json_object'],
    ]);
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $data,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . OPENAI_API_KEY,
        ],
        CURLOPT_TIMEOUT        => 30,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    if (!$response) return null;
    $result = json_decode($response, true);
    return $result['choices'][0]['message']['content'] ?? null;
}

// ─────────────────────────────────────────────────────────────────────────────
// SMTP Mailer — no external dependencies required
// ─────────────────────────────────────────────────────────────────────────────
class SMTPMailer {
    private string $host;
    private int    $port;
    private string $user;
    private string $pass;
    private string $secure; // 'tls', 'ssl', or ''
    private        $socket = null;
    public  string $lastError = '';

    public function __construct(string $host, int $port, string $user, string $pass, string $secure = 'tls') {
        $this->host   = $host;
        $this->port   = $port;
        $this->user   = $user;
        $this->pass   = $pass;
        $this->secure = strtolower($secure);
    }

    public function send(string $toEmail, string $toName, string $fromEmail, string $fromName, string $subject, string $htmlBody): bool {
        try {
            if (!$this->connect()) return false;
            if (!$this->auth())    { $this->disconnect(); return false; }

            $boundary = '----=_Part_' . md5(uniqid());
            $plain    = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</div>'], "\n", $htmlBody));
            $plain    = html_entity_decode($plain, ENT_QUOTES | ENT_HTML5, 'UTF-8');

            $headers = "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromEmail}>\r\n"
                     . "To: =?UTF-8?B?" . base64_encode($toName ?: $toEmail) . "?= <{$toEmail}>\r\n"
                     . "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n"
                     . "MIME-Version: 1.0\r\n"
                     . "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n"
                     . "X-Mailer: FeedbackFlow\r\n";

            $body = "--{$boundary}\r\n"
                  . "Content-Type: text/plain; charset=UTF-8\r\n"
                  . "Content-Transfer-Encoding: base64\r\n\r\n"
                  . chunk_split(base64_encode($plain)) . "\r\n"
                  . "--{$boundary}\r\n"
                  . "Content-Type: text/html; charset=UTF-8\r\n"
                  . "Content-Transfer-Encoding: base64\r\n\r\n"
                  . chunk_split(base64_encode($htmlBody)) . "\r\n"
                  . "--{$boundary}--\r\n";

            if (!$this->cmd("MAIL FROM:<{$fromEmail}>", 250)) { $this->disconnect(); return false; }
            if (!$this->cmd("RCPT TO:<{$toEmail}>",    250)) { $this->disconnect(); return false; }
            if (!$this->cmd("DATA", 354))                    { $this->disconnect(); return false; }

            fwrite($this->socket, $headers . "\r\n" . $body . "\r\n.\r\n");
            $resp = $this->read();
            if ((int)$resp !== 250) {
                $this->lastError = "DATA rejected: {$resp}";
                $this->disconnect();
                return false;
            }

            $this->cmd("QUIT", 221);
            $this->disconnect();
            return true;
        } catch (\Throwable $e) {
            $this->lastError = $e->getMessage();
            $this->disconnect();
            return false;
        }
    }

    private function connect(): bool {
        $prefix = $this->secure === 'ssl' ? 'ssl://' : '';
        $errno = $errstr = null;
        $this->socket = @fsockopen($prefix . $this->host, $this->port, $errno, $errstr, 15);
        if (!$this->socket) {
            $this->lastError = "Cannot connect to {$this->host}:{$this->port} — {$errstr} ({$errno})";
            return false;
        }
        stream_set_timeout($this->socket, 15);
        $this->read(); // greeting

        if (!$this->cmd("EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost'), 250)) return false;

        if ($this->secure === 'tls') {
            if (!$this->cmd("STARTTLS", 220)) return false;
            if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                $this->lastError = "STARTTLS failed — TLS handshake error";
                return false;
            }
            if (!$this->cmd("EHLO " . ($_SERVER['SERVER_NAME'] ?? 'localhost'), 250)) return false;
        }
        return true;
    }

    private function auth(): bool {
        if (!$this->cmd("AUTH LOGIN", 334)) return false;
        if (!$this->cmd(base64_encode($this->user), 334)) {
            $this->lastError = "SMTP authentication failed — wrong username";
            return false;
        }
        if (!$this->cmd(base64_encode($this->pass), 235)) {
            $this->lastError = "SMTP authentication failed — wrong password";
            return false;
        }
        return true;
    }

    private function cmd(string $cmd, int $expectedCode): bool {
        fwrite($this->socket, $cmd . "\r\n");
        $resp = $this->read();
        if ((int)$resp !== $expectedCode) {
            $this->lastError = "CMD [{$cmd}] expected {$expectedCode}, got: {$resp}";
            return false;
        }
        return true;
    }

    private function read(): string {
        $response = '';
        while ($line = fgets($this->socket, 515)) {
            $response .= $line;
            if ($line[3] === ' ') break; // last line of multi-line response
        }
        return trim(substr($response, 0, 3));
    }

    private function disconnect(): void {
        if ($this->socket) { @fclose($this->socket); $this->socket = null; }
    }
}

/**
 * Send an email using configured SMTP or PHP mail() as fallback.
 * Returns true on success, false on failure.
 * On failure, $error is populated.
 */
function ffSendMail(string $toEmail, string $toName, string $subject, string $htmlBody, string &$error = ''): bool {
    $host   = defined('SMTP_HOST')   ? SMTP_HOST   : '';
    $port   = defined('SMTP_PORT')   ? SMTP_PORT   : 587;
    $user   = defined('SMTP_USER')   ? SMTP_USER   : '';
    $pass   = defined('SMTP_PASS')   ? SMTP_PASS   : '';
    $secure = defined('SMTP_SECURE') ? SMTP_SECURE : 'tls';
    $from   = defined('SMTP_FROM')   ? SMTP_FROM   : 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $fname  = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : (defined('APP_NAME') ? APP_NAME : 'FeedbackFlow');

    if ($host && $user) {
        // Use SMTP
        $mailer = new SMTPMailer($host, (int)$port, $user, $pass, $secure);
        $ok = $mailer->send($toEmail, $toName, $from, $fname, $subject, $htmlBody);
        if (!$ok) $error = $mailer->lastError;
        return $ok;
    }

    // Fallback: PHP mail()
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$fname} <{$from}>\r\n";
    $to = $toName ? ('"' . addslashes($toName) . '" <' . $toEmail . '>') : $toEmail;
    $ok = @mail($to, $subject, $htmlBody, $headers);
    if (!$ok) $error = 'PHP mail() failed. Configure SMTP in config.php for reliable delivery.';
    return $ok;
}

function triggerWebhooks(int $projectId, string $event, array $payload): void {
    $webhooks = DB::fetchAll(
        "SELECT * FROM ff_webhooks WHERE project_id = ? AND is_active = 1 AND events LIKE ?",
        [$projectId, "%$event%"]
    );
    foreach ($webhooks as $wh) {
        $body = json_encode(['event' => $event, 'data' => $payload, 'timestamp' => time()]);
        $sig = $wh['secret'] ? hash_hmac('sha256', $body, $wh['secret']) : '';
        $ch = curl_init($wh['url']);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'X-FeedbackFlow-Signature: ' . $sig,
            ],
            CURLOPT_TIMEOUT        => 10,
        ]);
        curl_exec($ch);
        curl_close($ch);
        DB::query("UPDATE ff_webhooks SET last_triggered = NOW() WHERE id = ?", [$wh['id']]);
    }
}

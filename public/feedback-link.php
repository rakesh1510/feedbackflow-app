<?php
/**
 * Universal Feedback Page
 * Used by: Email campaigns, QR codes, WhatsApp links, SMS links, direct links
 */
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

// Resolve project + link context
$token      = sanitize($_GET['token'] ?? '');
$slug       = sanitize($_GET['slug'] ?? '');
$source     = sanitize($_GET['source'] ?? 'direct');
$preRating  = (int)($_GET['r'] ?? 0); // pre-selected star rating from email buttons

$link = null;
$project = null;
$campaign = null;
$recipient = null;

if ($token) {
    // Could be a campaign recipient token or a feedback link token
    $recipient = DB::fetch("SELECT * FROM ff_campaign_recipients WHERE token = ?", [$token]);
    if ($recipient) {
        $campaign = DB::fetch("SELECT * FROM ff_email_campaigns WHERE id = ?", [$recipient['campaign_id']]);
        if ($campaign) {
            $project = DB::fetch("SELECT * FROM ff_projects WHERE id = ?", [$campaign['project_id']]);
            $source  = 'email';
            $preRating = $preRating ?: (int)$recipient['pre_rating'];
            // Mark as opened
            if (!$recipient['opened_at']) {
                DB::update('ff_campaign_recipients', ['opened_at' => date('Y-m-d H:i:s')], 'id = ?', [$recipient['id']]);
                DB::update('ff_email_campaigns', ['open_count' => DB::fetchColumn("SELECT open_count+1 FROM ff_email_campaigns WHERE id=?",[$campaign['id']])], 'id = ?', [$campaign['id']]);
            }
        }
    } else {
        $link = DB::fetch("SELECT * FROM ff_feedback_links WHERE token = ? AND is_active = 1", [$token]);
        if ($link) {
            $project = DB::fetch("SELECT * FROM ff_projects WHERE id = ?", [$link['project_id']]);
            $source  = $link['source'];
            DB::update('ff_feedback_links', ['click_count' => $link['click_count'] + 1], 'id = ?', [$link['id']]);
        }
    }
} elseif ($slug) {
    $project = DB::fetch("SELECT * FROM ff_projects WHERE slug = ? AND is_public = 1", [$slug]);
}

if (!$project) {
    http_response_code(404);
    die('<h1 style="font-family:sans-serif;text-align:center;margin-top:100px">Feedback link not found or expired.</h1>');
}

$categories = DB::fetchAll("SELECT * FROM ff_categories WHERE project_id = ? ORDER BY sort_order", [$project['id']]);
$ratingQuestion = $campaign['rating_question'] ?? $link['rating_question'] ?? 'How was your experience?';
$submitted = false;
$error     = '';

// Handle submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title    = sanitize($_POST['title'] ?? '');
    $desc     = sanitize($_POST['description'] ?? '');
    $catId    = (int)($_POST['category_id'] ?? 0);
    $name     = sanitize($_POST['submitter_name'] ?? '');
    $email    = sanitize($_POST['submitter_email'] ?? '');
    $rating   = (int)($_POST['rating'] ?? 0);

    if (empty($title) && empty($desc)) {
        $error = 'Please write your feedback before submitting.';
    } else {
        $title = $title ?: mb_substr($desc, 0, 80);

        $fid = DB::insert('ff_feedback', [
            'project_id'      => $project['id'],
            'source'          => $source,
            'rating'          => $rating ?: null,
            'campaign_id'     => $campaign['id'] ?? null,
            'category_id'     => $catId ?: null,
            'title'           => $title,
            'description'     => $desc,
            'submitter_name'  => $name ?: ($recipient['name'] ?? null),
            'submitter_email' => $email ?: ($recipient['email'] ?? null),
            'status'          => 'new',
            'priority'        => 'medium',
            'is_public'       => 1,
        ]);

        // Update recipient record
        if ($recipient) {
            DB::update('ff_campaign_recipients', ['submitted_at' => date('Y-m-d H:i:s'), 'feedback_id' => $fid, 'pre_rating' => $rating ?: $preRating], 'id = ?', [$recipient['id']]);
            DB::update('ff_email_campaigns', ['submit_count' => $campaign['submit_count'] + 1], 'id = ?', [$campaign['id']]);
        }
        if ($link) {
            DB::update('ff_feedback_links', ['submit_count' => $link['submit_count'] + 1], 'id = ?', [$link['id']]);
        }

        $submitted = true;
    }
}

$color = $project['widget_color'] ?? '#6366f1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<title>Feedback – <?= h($project['name']) ?></title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background: #f8fafc;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    padding: 20px 16px 40px;
    color: #1e293b;
  }
  .card {
    background: #fff;
    border-radius: 24px;
    box-shadow: 0 4px 40px rgba(0,0,0,.08);
    width: 100%;
    max-width: 520px;
    overflow: hidden;
    margin-top: 20px;
  }
  .card-header {
    padding: 28px 32px 24px;
    text-align: center;
    border-bottom: 1px solid #f1f5f9;
  }
  .project-logo {
    width: 56px; height: 56px;
    border-radius: 18px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px; font-weight: 800; color: #fff;
    margin: 0 auto 14px;
    background: linear-gradient(135deg, <?= $color ?>, <?= $color ?>99);
  }
  .card-header h1 { font-size: 20px; font-weight: 700; color: #0f172a; }
  .card-header p  { font-size: 14px; color: #64748b; margin-top: 4px; }

  /* Source badge */
  .source-badge {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 11px; font-weight: 600; padding: 3px 10px;
    border-radius: 99px; margin-top: 10px;
    background: #f1f5f9; color: #64748b;
    text-transform: uppercase; letter-spacing: .5px;
  }

  /* Star rating */
  .stars-section { padding: 28px 32px 0; text-align: center; }
  .stars-label { font-size: 16px; font-weight: 600; color: #0f172a; margin-bottom: 16px; }
  .stars { display: flex; justify-content: center; gap: 8px; }
  .stars input { display: none; }
  .stars label {
    font-size: 36px; cursor: pointer; line-height: 1;
    filter: grayscale(1) opacity(.3);
    transition: filter .15s, transform .1s;
  }
  .stars input:checked ~ label,
  .stars label:hover,
  .stars label:hover ~ label { filter: none !important; }
  .stars:hover label { filter: none; }
  .stars label:hover ~ label { filter: grayscale(1) opacity(.3) !important; }
  .stars input:checked ~ label { filter: none; }

  /* Star rating RTL trick */
  .stars { flex-direction: row-reverse; }
  .stars label:hover,
  .stars label:hover ~ label { filter: none; }
  .stars input:checked ~ label { filter: none; }

  /* Form */
  .form-body { padding: 24px 32px 32px; }
  .form-group { margin-bottom: 18px; }
  label.field-label {
    display: block; font-size: 12px; font-weight: 600;
    color: #475569; text-transform: uppercase; letter-spacing: .5px;
    margin-bottom: 6px;
  }
  input[type=text], input[type=email], textarea, select {
    width: 100%;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 14px;
    font-size: 14px;
    font-family: inherit;
    color: #0f172a;
    background: #f8fafc;
    outline: none;
    transition: border-color .15s, background .15s;
    resize: vertical;
  }
  input:focus, textarea:focus, select:focus {
    border-color: <?= $color ?>;
    background: #fff;
    box-shadow: 0 0 0 3px <?= $color ?>22;
  }
  textarea { min-height: 100px; }

  .submit-btn {
    width: 100%;
    padding: 14px;
    border: none;
    border-radius: 14px;
    font-size: 15px;
    font-weight: 700;
    color: #fff;
    cursor: pointer;
    background: linear-gradient(135deg, <?= $color ?>, <?= $color ?>cc);
    transition: opacity .15s, transform .1s;
    margin-top: 4px;
  }
  .submit-btn:hover { opacity: .92; }
  .submit-btn:active { transform: scale(.98); }

  .error-msg {
    background: #fef2f2; border: 1px solid #fecaca;
    color: #dc2626; border-radius: 12px;
    padding: 12px 16px; font-size: 13px; margin-bottom: 16px;
  }

  /* Success state */
  .success-card {
    text-align: center;
    padding: 48px 32px;
  }
  .success-icon {
    width: 72px; height: 72px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 32px; margin: 0 auto 20px;
    background: linear-gradient(135deg, #10b981, #059669);
  }
  .success-card h2 { font-size: 22px; font-weight: 700; color: #0f172a; }
  .success-card p  { color: #64748b; margin-top: 8px; font-size: 14px; }

  .powered-by {
    text-align: center; margin-top: 20px;
    font-size: 11px; color: #94a3b8;
  }
  .powered-by a { color: #94a3b8; text-decoration: none; }

  @media (max-width: 480px) {
    .card-header, .stars-section, .form-body { padding-left: 20px; padding-right: 20px; }
    .stars label { font-size: 30px; }
  }
</style>
</head>
<body>

<div class="card">
  <!-- Header -->
  <div class="card-header">
    <div class="project-logo"><?= strtoupper(substr($project['name'], 0, 1)) ?></div>
    <h1><?= h($project['name']) ?></h1>
    <p>We value your feedback</p>
    <?php
    $sourceLabels = ['email'=>'📧 Email','qr'=>'📷 QR Code','sms'=>'💬 SMS','whatsapp'=>'💚 WhatsApp','embedded'=>'🖥 Website','widget'=>'💬 Widget','direct'=>'🔗 Link'];
    $sl = $sourceLabels[$source] ?? '🔗 Link';
    ?>
    <div class="source-badge"><?= $sl ?></div>
  </div>

  <?php if ($submitted): ?>
  <!-- Success -->
  <div class="success-card">
    <div class="success-icon">✓</div>
    <h2>Thank you!</h2>
    <p>Your feedback has been received.<br>We appreciate you taking the time to share your thoughts.</p>
    <?php if ($project['website']): ?>
      <a href="<?= h($project['website']) ?>" style="display:inline-block;margin-top:20px;padding:10px 24px;border-radius:99px;font-size:13px;font-weight:600;color:#fff;text-decoration:none;background:<?= $color ?>">
        ← Back to <?= h($project['name']) ?>
      </a>
    <?php endif; ?>
  </div>

  <?php else: ?>
  <!-- Star Rating -->
  <div class="stars-section">
    <div class="stars-label"><?= h($ratingQuestion) ?></div>
    <div class="stars">
      <?php for ($i = 5; $i >= 1; $i--): ?>
        <input type="radio" name="star_display" id="sd<?= $i ?>" <?= $preRating == $i ? 'checked' : '' ?>>
        <label for="sd<?= $i ?>">★</label>
      <?php endfor; ?>
    </div>
  </div>

  <!-- Form -->
  <form method="POST" class="form-body">
    <input type="hidden" name="rating" id="ratingInput" value="<?= $preRating ?>">

    <?php if ($error): ?>
      <div class="error-msg"><?= h($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($categories)): ?>
    <div class="form-group">
      <label class="field-label">Category</label>
      <select name="category_id">
        <option value="">— Select a category —</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>"><?= h($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>

    <div class="form-group">
      <label class="field-label">Your feedback <span style="color:#ef4444">*</span></label>
      <textarea name="description" placeholder="Tell us what you think, what could be better, or report an issue..." rows="4" required></textarea>
    </div>

    <div class="form-group">
      <label class="field-label">Summary (optional)</label>
      <input type="text" name="title" placeholder="Short title for your feedback">
    </div>

    <?php if (!$recipient): ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px" class="form-group">
      <div>
        <label class="field-label">Your name</label>
        <input type="text" name="submitter_name" placeholder="John Doe">
      </div>
      <div>
        <label class="field-label">Email</label>
        <input type="email" name="submitter_email" placeholder="you@example.com">
      </div>
    </div>
    <?php else: ?>
      <input type="hidden" name="submitter_name" value="<?= h($recipient['name'] ?? '') ?>">
      <input type="hidden" name="submitter_email" value="<?= h($recipient['email']) ?>">
    <?php endif; ?>

    <button type="submit" class="submit-btn">Submit Feedback →</button>
  </form>
  <?php endif; ?>
</div>

<p class="powered-by">Powered by <a href="<?= APP_URL ?>">FeedbackFlow</a></p>

<script>
// Sync star rating display to hidden input
const stars = document.querySelectorAll('.stars input');
const ratingInput = document.getElementById('ratingInput');
stars.forEach(star => {
  star.addEventListener('change', function() {
    if (ratingInput) ratingInput.value = this.id.replace('sd','');
  });
});
</script>
</body>
</html>

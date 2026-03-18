<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

Auth::start();

$action = $_GET['action'] ?? $_GET['page'] ?? '';
$errors = [];

// Handle logout
if ($action === 'logout') {
    Auth::logout();
    redirect(APP_URL . '/index.php');
}

// If already logged in, go to dashboard
if (Auth::check() && $action !== 'login' && $action !== 'register' && $action !== 'forgot') {
    redirect(APP_URL . '/admin/index.php');
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'login') {
    if (!verifyCsrf()) { $errors[] = 'Security check failed. Please try again.'; }
    elseif (!rateLimit('login', 5, 300)) { $errors[] = 'Too many login attempts. Wait 5 minutes.'; }
    else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (Auth::login($email, $password)) {
            redirect(APP_URL . '/admin/index.php');
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}

// Handle register
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'register') {
    if (!verifyCsrf()) { $errors[] = 'Security check failed.'; }
    else {
        $name = sanitize($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (empty($name) || empty($email) || empty($password)) { $errors[] = 'All fields are required.'; }
        elseif (!isValidEmail($email)) { $errors[] = 'Invalid email address.'; }
        elseif (strlen($password) < 8) { $errors[] = 'Password must be at least 8 characters.'; }
        else {
            $userId = Auth::register($name, $email, $password);
            if ($userId === false) { $errors[] = 'Email already registered.'; }
            else { redirect(APP_URL . '/admin/index.php'); }
        }
    }
}

$pageTitle = APP_NAME . ' – Sign in';
$currentPage = $action ?: 'login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($pageTitle) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 flex items-center justify-center p-4">
<div class="w-full max-w-md">
  <!-- Logo -->
  <div class="text-center mb-8">
    <a href="<?= APP_URL ?>" class="inline-flex items-center gap-3">
      <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center">
        <i class="fas fa-comments text-white"></i>
      </div>
      <span class="text-2xl font-bold text-gray-900"><?= APP_NAME ?></span>
    </a>
  </div>

  <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8">
    <?php if ($currentPage === 'register'): ?>
      <h1 class="text-2xl font-bold text-gray-900 mb-1">Create your account</h1>
      <p class="text-gray-500 text-sm mb-6">Start collecting product feedback today</p>
      <?php if ($errors): foreach ($errors as $e): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 mb-4 text-sm"><i class="fas fa-exclamation-circle mr-1"></i><?= h($e) ?></div>
      <?php endforeach; endif; ?>
      <form method="POST" action="?action=register" class="space-y-4">
        <input type="hidden" name="_csrf" value="<?= csrf() ?>">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
          <input type="text" name="name" required value="<?= h($_POST['name'] ?? '') ?>"
                 class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition" placeholder="Jane Smith">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input type="email" name="email" required value="<?= h($_POST['email'] ?? '') ?>"
                 class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition" placeholder="you@company.com">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <input type="password" name="password" required minlength="8"
                 class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition" placeholder="Min. 8 characters">
        </div>
        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-xl transition-all flex items-center justify-center gap-2">
          Create Account <i class="fas fa-arrow-right"></i>
        </button>
      </form>
      <p class="text-center text-sm text-gray-500 mt-6">Already have an account? <a href="?action=login" class="text-indigo-600 font-medium hover:underline">Sign in</a></p>

    <?php else: ?>
      <h1 class="text-2xl font-bold text-gray-900 mb-1">Welcome back</h1>
      <p class="text-gray-500 text-sm mb-6">Sign in to your <?= APP_NAME ?> account</p>
      <?php if ($errors): foreach ($errors as $e): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 mb-4 text-sm"><i class="fas fa-exclamation-circle mr-1"></i><?= h($e) ?></div>
      <?php endforeach; endif; ?>
      <?php $flashes = getFlash(); foreach ($flashes as $f): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 rounded-lg p-3 mb-4 text-sm"><?= h($f['msg']) ?></div>
      <?php endforeach; ?>
      <form method="POST" action="?action=login" class="space-y-4">
        <input type="hidden" name="_csrf" value="<?= csrf() ?>">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input type="email" name="email" required value="<?= h($_POST['email'] ?? '') ?>"
                 class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition" placeholder="you@company.com" autofocus>
        </div>
        <div>
          <div class="flex justify-between mb-1">
            <label class="block text-sm font-medium text-gray-700">Password</label>
            <a href="?action=forgot" class="text-xs text-indigo-600 hover:underline">Forgot password?</a>
          </div>
          <input type="password" name="password" required
                 class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition" placeholder="Your password">
        </div>
        <div class="flex items-center gap-2">
          <input type="checkbox" name="remember" id="remember" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
          <label for="remember" class="text-sm text-gray-600">Remember me for 30 days</label>
        </div>
        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-xl transition-all flex items-center justify-center gap-2">
          Sign In <i class="fas fa-arrow-right"></i>
        </button>
      </form>
      <p class="text-center text-sm text-gray-500 mt-6">Don't have an account? <a href="?action=register" class="text-indigo-600 font-medium hover:underline">Sign up free</a></p>
    <?php endif; ?>
  </div>

  <p class="text-center text-xs text-gray-400 mt-6">
    &copy; <?= date('Y') ?> <?= APP_NAME ?> &middot; GDPR Compliant &middot; Secure
  </p>
</div>
</body>
</html>

<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

$currentUser = Auth::require();
if (!Auth::isAdmin($currentUser)) {
    flash('error', 'Access denied.'); redirect(APP_URL . '/admin/index.php');
}
$userProjects = getUserProjects($currentUser['id']);
$pageTitle = 'Billing & Payments – ' . APP_NAME;

// Handle plan upgrade (mock)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    $action = $_POST['action'] ?? '';
    if ($action === 'change_plan') {
        $planSlug = $_POST['plan'] ?? '';
        $validPlans = ['free','starter','growth','pro','enterprise'];
        if (in_array($planSlug, $validPlans)) {
            DB::query("UPDATE ff_users SET plan = ? WHERE id = ?", [$planSlug, $currentUser['id']]);
            flash('success', "Plan updated to $planSlug. In production, this would trigger Stripe checkout.");
        }
    }
    redirect(APP_URL . '/admin/billing.php');
}

$plans = DB::fetchAll("SELECT * FROM ff_billing_plans WHERE is_active = 1 ORDER BY sort_order");
$currentPlan = $currentUser['plan'] ?? 'free';

// Mock usage stats
$feedbackCount = DB::count("SELECT COUNT(*) FROM ff_feedback f JOIN ff_projects p ON p.id = f.project_id WHERE p.owner_id = ? AND f.created_at >= DATE_FORMAT(NOW(),'%Y-%m-01')", [$currentUser['id']]);
$projectCount  = DB::count("SELECT COUNT(*) FROM ff_projects WHERE owner_id = ?", [$currentUser['id']]);

// Invoices
$invoices = DB::fetchAll("SELECT * FROM ff_invoices ORDER BY created_at DESC LIMIT 10");

$flash = getFlash();
include dirname(__DIR__) . '/includes/header.php';
?>
<div class="flex h-full">
<?php include dirname(__DIR__) . '/includes/sidebar.php'; ?>
<main class="ml-64 flex-1 overflow-y-auto">

  <header class="sticky top-0 z-40 bg-white border-b border-gray-200 px-6 py-4">
    <h1 class="text-xl font-bold text-gray-900">Billing & Payments</h1>
    <p class="text-sm text-gray-500 mt-0.5">Manage your subscription, usage, and invoices (Modules 14–17)</p>
  </header>

  <div class="p-6 space-y-6">
    <?php foreach ($flash as $f): ?>
      <div class="rounded-xl px-4 py-3 text-sm font-medium <?= $f['type'] === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
        <?= h($f['msg']) ?>
      </div>
    <?php endforeach; ?>

    <!-- Current Plan Status -->
    <div class="ff-card p-6">
      <div class="flex items-center justify-between mb-5">
        <div>
          <h2 class="text-base font-bold text-gray-900">Current Plan</h2>
          <p class="text-sm text-gray-500 mt-0.5">Your subscription renews on the 1st of each month.</p>
        </div>
        <span class="badge bg-indigo-100 text-indigo-700 capitalize text-sm px-3 py-1"><?= ucfirst($currentPlan) ?></span>
      </div>
      <div class="grid grid-cols-3 gap-4">
        <?php
          $planRow = DB::fetch("SELECT * FROM ff_billing_plans WHERE slug = ?", [$currentPlan]);
          $limits = [
            ['label'=>'Feedback this month','used'=>$feedbackCount,'max'=>$planRow['max_feedback_per_month']??100],
            ['label'=>'Projects','used'=>$projectCount,'max'=>$planRow['max_projects']??1],
            ['label'=>'Campaigns this month','used'=>0,'max'=>$planRow['max_campaigns_per_month']??0],
          ];
        ?>
        <?php foreach ($limits as $lim): ?>
        <div class="bg-gray-50 rounded-xl p-4">
          <p class="text-xs text-gray-500 mb-2"><?= $lim['label'] ?></p>
          <div class="flex items-end justify-between mb-2">
            <span class="text-lg font-bold text-gray-900"><?= number_format($lim['used']) ?></span>
            <span class="text-xs text-gray-400">/ <?= $lim['max'] >= 999999 ? '∞' : number_format($lim['max']) ?></span>
          </div>
          <?php if ($lim['max'] < 999999): ?>
          <div class="w-full bg-gray-200 rounded-full h-1.5">
            <div class="bg-indigo-500 h-1.5 rounded-full" style="width:<?= min(100, round($lim['used']/$lim['max']*100)) ?>%"></div>
          </div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Plans Grid -->
    <div>
      <h2 class="text-base font-bold text-gray-900 mb-4">Available Plans</h2>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
        <?php foreach ($plans as $plan): ?>
        <?php $isCurrentPlan = $plan['slug'] === $currentPlan; ?>
        <div class="ff-card p-5 <?= $plan['slug'] === 'pro' ? 'ring-2 ring-indigo-600' : '' ?> relative">
          <?php if ($plan['slug'] === 'pro'): ?>
          <div class="absolute -top-3 left-1/2 -translate-x-1/2">
            <span class="bg-indigo-600 text-white text-xs font-bold px-3 py-1 rounded-full">Most Popular</span>
          </div>
          <?php endif; ?>
          <h3 class="font-bold text-gray-900 mb-1"><?= h($plan['name']) ?></h3>
          <div class="mb-3">
            <?php if ($plan['price_monthly'] == 0): ?>
              <span class="text-2xl font-bold text-gray-900">Free</span>
            <?php else: ?>
              <span class="text-2xl font-bold text-gray-900">$<?= number_format($plan['price_monthly']) ?></span>
              <span class="text-xs text-gray-400">/mo</span>
            <?php endif; ?>
          </div>
          <?php $features = json_decode($plan['features'] ?? '[]', true) ?? []; ?>
          <ul class="space-y-1.5 mb-4 text-xs text-gray-600">
            <?php foreach (array_slice($features, 0, 4) as $feat): ?>
            <li class="flex items-center gap-1.5">
              <i class="fas fa-check text-green-500" style="font-size:10px"></i>
              <?= h($feat) ?>
            </li>
            <?php endforeach; ?>
          </ul>
          <?php if ($isCurrentPlan): ?>
            <button disabled class="w-full bg-gray-100 text-gray-400 py-2 rounded-xl text-sm font-medium cursor-not-allowed">Current Plan</button>
          <?php else: ?>
            <form method="post">
              <?= csrfInput() ?>
              <input type="hidden" name="action" value="change_plan">
              <input type="hidden" name="plan" value="<?= h($plan['slug']) ?>">
              <button class="w-full <?= $plan['slug'] === 'pro' ? 'bg-indigo-600 hover:bg-indigo-700 text-white' : 'border border-gray-200 hover:bg-gray-50 text-gray-700' ?> py-2 rounded-xl text-sm font-semibold transition">
                <?= $plan['price_monthly'] > ($planRow['price_monthly'] ?? 0) ? 'Upgrade' : 'Downgrade' ?>
              </button>
            </form>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Invoices -->
    <div class="ff-card p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-base font-bold text-gray-900">Invoice History</h2>
        <span class="text-xs text-gray-400">Invoices are generated automatically on billing renewal.</span>
      </div>
      <?php if (empty($invoices)): ?>
      <div class="text-center py-8">
        <i class="fas fa-file-invoice text-3xl text-gray-200 mb-2"></i>
        <p class="text-gray-400 text-sm">No invoices yet. Invoices will appear here after your first billing cycle.</p>
      </div>
      <?php else: ?>
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-100">
            <th class="text-left py-2 px-2 text-xs font-semibold text-gray-500">Invoice #</th>
            <th class="text-left py-2 px-2 text-xs font-semibold text-gray-500">Amount</th>
            <th class="text-left py-2 px-2 text-xs font-semibold text-gray-500">Status</th>
            <th class="text-left py-2 px-2 text-xs font-semibold text-gray-500">Date</th>
            <th></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <?php foreach ($invoices as $inv): ?>
          <tr>
            <td class="py-2.5 px-2 font-mono text-xs text-gray-700"><?= h($inv['invoice_number']) ?></td>
            <td class="py-2.5 px-2 font-semibold"><?= $inv['currency'] ?> <?= number_format($inv['amount'], 2) ?></td>
            <td class="py-2.5 px-2">
              <span class="badge <?= $inv['status'] === 'paid' ? 'bg-green-100 text-green-700' : ($inv['status'] === 'overdue' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600') ?>">
                <?= ucfirst($inv['status']) ?>
              </span>
            </td>
            <td class="py-2.5 px-2 text-xs text-gray-400"><?= formatDate($inv['created_at']) ?></td>
            <td class="py-2.5 px-2">
              <a href="#" class="text-indigo-600 hover:underline text-xs"><i class="fas fa-download mr-1"></i>PDF</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>

    <!-- Payment Methods Placeholder -->
    <div class="ff-card p-6">
      <h2 class="text-base font-bold text-gray-900 mb-1">Payment Methods</h2>
      <p class="text-sm text-gray-500 mb-4">Connect a payment method to enable automatic billing.</p>
      <div class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl p-8 text-center">
        <i class="fas fa-credit-card text-3xl text-gray-300 mb-3"></i>
        <p class="text-gray-500 text-sm font-medium">Stripe integration required</p>
        <p class="text-xs text-gray-400 mt-1">Add <code class="bg-gray-100 px-1 rounded">STRIPE_SECRET_KEY</code> to config.php to enable payment processing.</p>
      </div>
    </div>
  </div>
</main>
</div>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>

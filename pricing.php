<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pricing – FeedbackFlow</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');
  * { font-family: 'Inter', sans-serif; }
  .gradient-text {
    background: linear-gradient(135deg, #6366f1, #8b5cf6, #a855f7);
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
  }
  .plan-card { transition: transform .2s ease, box-shadow .2s ease; }
  .plan-card:hover { transform: translateY(-4px); box-shadow: 0 20px 60px -10px rgba(99,102,241,.18); }
  .popular-card { transform: scale(1.04); }
  .popular-card:hover { transform: scale(1.04) translateY(-4px); }
  .toggle-bg { background: #e5e7eb; transition: background .2s; }
  .toggle-bg.active { background: #6366f1; }
  .toggle-knob { transition: transform .2s; }
  .badge-popular {
    background: linear-gradient(135deg,#6366f1,#8b5cf6);
    color: #fff; font-size: 11px; font-weight: 700; letter-spacing: .06em;
    padding: 3px 12px; border-radius: 99px; text-transform: uppercase;
  }
  .check { color: #6366f1; }
  .cross { color: #d1d5db; }
  .feature-row:hover { background: #fafafa; }
  .cta-bg { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #a855f7 100%); }
  .nav-glass { backdrop-filter: blur(12px); background: rgba(255,255,255,.85); }
  .hero-blob {
    position: absolute; border-radius: 50%; filter: blur(80px); opacity: .15; pointer-events: none;
  }
  .faq-answer { max-height: 0; overflow: hidden; transition: max-height .3s ease; }
  .faq-answer.open { max-height: 300px; }
  .faq-icon { transition: transform .3s ease; }
  .faq-item.open .faq-icon { transform: rotate(45deg); }
</style>
</head>
<body class="bg-white text-gray-900 antialiased">

<!-- ── Navbar ─────────────────────────────────────────────────────────────── -->
<nav class="nav-glass sticky top-0 z-50 border-b border-gray-100">
  <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
    <div class="flex items-center gap-2.5">
      <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-sm font-bold"
           style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
        <i class="fas fa-comments"></i>
      </div>
      <span class="font-bold text-gray-900 text-lg tracking-tight">FeedbackFlow</span>
    </div>
    <div class="hidden md:flex items-center gap-7 text-sm font-medium text-gray-500">
      <a href="#features" class="hover:text-indigo-600 transition">Features</a>
      <a href="#pricing" class="hover:text-indigo-600 transition">Pricing</a>
      <a href="#compare" class="hover:text-indigo-600 transition">Compare</a>
      <a href="#faq" class="hover:text-indigo-600 transition">FAQ</a>
    </div>
    <div class="flex items-center gap-3">
      <?php if (defined('APP_URL')): ?>
      <a href="<?= APP_URL ?>/admin/" class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition">Sign in</a>
      <a href="<?= APP_URL ?>/install.php" class="text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl transition">Get Started Free</a>
      <?php else: ?>
      <a href="#pricing" class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition">Sign in</a>
      <a href="#pricing" class="text-sm font-semibold bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl transition">Get Started Free</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- ── Hero ───────────────────────────────────────────────────────────────── -->
<section class="relative overflow-hidden pt-20 pb-16 px-6">
  <div class="hero-blob w-96 h-96 bg-indigo-500 top-0 left-1/4"></div>
  <div class="hero-blob w-72 h-72 bg-purple-500 top-12 right-1/4"></div>
  <div class="max-w-3xl mx-auto text-center relative">
    <div class="inline-flex items-center gap-2 bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs font-semibold px-4 py-1.5 rounded-full mb-6">
      <i class="fas fa-star text-yellow-500"></i> Trusted by 500+ product teams worldwide
    </div>
    <h1 class="text-5xl font-black text-gray-900 leading-tight mb-5">
      Simple pricing.<br><span class="gradient-text">Powerful results.</span>
    </h1>
    <p class="text-xl text-gray-500 mb-10 max-w-xl mx-auto leading-relaxed">
      Start free. Upgrade when you need more power. No hidden fees, no per-seat charges — just one flat price.
    </p>

    <!-- Billing Toggle -->
    <div class="inline-flex items-center gap-3 bg-gray-100 px-4 py-2 rounded-full">
      <span id="label-monthly" class="text-sm font-semibold text-gray-900">Monthly</span>
      <button onclick="toggleBilling()" id="toggle-btn"
              class="toggle-bg w-11 h-6 rounded-full relative flex items-center px-0.5 focus:outline-none">
        <span id="toggle-knob" class="toggle-knob w-5 h-5 bg-white rounded-full shadow"></span>
      </button>
      <span id="label-annual" class="text-sm font-medium text-gray-400">
        Annual <span id="save-badge" class="bg-green-100 text-green-700 text-xs font-bold px-2 py-0.5 rounded-full ml-1">Save 20%</span>
      </span>
    </div>
  </div>
</section>

<!-- ── Pricing Cards ───────────────────────────────────────────────────────── -->
<section id="pricing" class="px-6 pb-20">
  <div class="max-w-7xl mx-auto">
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-5 items-start">

      <!-- FREE -->
      <div class="plan-card bg-white border-2 border-gray-200 rounded-2xl p-6 flex flex-col">
        <div class="mb-5">
          <span class="text-2xl mb-2 block">🌱</span>
          <h3 class="text-base font-bold text-gray-900 mb-1">Free</h3>
          <p class="text-xs text-gray-400 mb-4">Perfect to get started</p>
          <div class="flex items-end gap-1">
            <span class="text-3xl font-black text-gray-900">€0</span>
            <span class="text-gray-400 text-sm mb-1">/mo</span>
          </div>
        </div>
        <ul class="space-y-2.5 flex-1 mb-6 text-sm">
          <li class="flex items-center gap-2 text-gray-600"><i class="fas fa-check check w-4"></i> 1 project</li>
          <li class="flex items-center gap-2 text-gray-600"><i class="fas fa-check check w-4"></i> 50 feedback items</li>
          <li class="flex items-center gap-2 text-gray-600"><i class="fas fa-check check w-4"></i> Basic dashboard</li>
          <li class="flex items-center gap-2 text-gray-600"><i class="fas fa-check check w-4"></i> Public board</li>
          <li class="flex items-center gap-2 text-gray-300 line-through"><i class="fas fa-times cross w-4"></i> AI Insights</li>
          <li class="flex items-center gap-2 text-gray-300 line-through"><i class="fas fa-times cross w-4"></i> Campaigns</li>
          <li class="flex items-center gap-2 text-gray-300 line-through"><i class="fas fa-times cross w-4"></i> Channels</li>
        </ul>
        <a href="#" class="block text-center text-sm font-semibold py-2.5 rounded-xl border-2 border-gray-200 text-gray-600 hover:border-indigo-300 hover:text-indigo-600 transition">
          Get Started Free
        </a>
      </div>

      <!-- STARTER -->
      <div class="plan-card bg-white border-2 border-gray-200 rounded-2xl p-6 flex flex-col">
        <div class="mb-5">
          <span class="text-2xl mb-2 block">🚀</span>
          <h3 class="text-base font-bold text-gray-900 mb-1">Starter</h3>
          <p class="text-xs text-gray-400 mb-4">For small teams</p>
          <div class="flex items-end gap-1">
            <span class="text-3xl font-black text-gray-900" data-monthly="€19" data-annual="€15">€19</span>
            <span class="text-gray-400 text-sm mb-1">/mo</span>
          </div>
          <p id="starter-save" class="text-xs text-green-600 font-medium mt-1 hidden">Billed €180/yr — save €48</p>
        </div>
        <ul class="space-y-2.5 flex-1 mb-6 text-sm">
          <li class="flex items-center gap-2 text-gray-600"><i class="fas fa-check check w-4"></i> 3 projects</li>
          <li class="flex items-center gap-2 text-gray-600"><i class="fas fa-check check w-4"></i> 500 feedback / mo</li>
          <li class="flex items-center gap-2 text-gray-600"><i class="fas fa-check check w-4"></i> Full dashboard</li>
          <li class="flex items-center gap-2 text-gray-600"><i class="fas fa-check check w-4"></i> Basic AI Insights</li>
          <li class="flex items-center gap-2 text-gray-600"><i class="fas fa-check check w-4"></i> Email campaigns</li>
          <li class="flex items-center gap-2 text-gray-300 line-through"><i class="fas fa-times cross w-4"></i> AI Copilot</li>
          <li class="flex items-center gap-2 text-gray-300 line-through"><i class="fas fa-times cross w-4"></i> QR / WhatsApp</li>
        </ul>
        <a href="#" class="block text-center text-sm font-semibold py-2.5 rounded-xl border-2 border-indigo-200 text-indigo-600 hover:bg-indigo-50 transition">
          Start Free Trial
        </a>
      </div>

      <!-- GROWTH — POPULAR -->
      <div class="plan-card popular-card bg-white border-2 border-indigo-500 rounded-2xl p-6 flex flex-col shadow-xl shadow-indigo-100 relative">
        <div class="absolute -top-4 left-0 right-0 flex justify-center">
          <span class="badge-popular"><i class="fas fa-fire mr-1"></i>Most Popular</span>
        </div>
        <div class="mb-5 mt-2">
          <span class="text-2xl mb-2 block">💥</span>
          <h3 class="text-base font-bold text-gray-900 mb-1">Growth</h3>
          <p class="text-xs text-indigo-500 font-semibold mb-4">Best for growing teams</p>
          <div class="flex items-end gap-1">
            <span class="text-3xl font-black text-gray-900" data-monthly="€49" data-annual="€39">€49</span>
            <span class="text-gray-400 text-sm mb-1">/mo</span>
          </div>
          <p id="growth-save" class="text-xs text-green-600 font-medium mt-1 hidden">Billed €468/yr — save €120</p>
        </div>
        <ul class="space-y-2.5 flex-1 mb-6 text-sm">
          <li class="flex items-center gap-2 text-gray-600"><i class="fas fa-check check w-4"></i> 10 projects</li>
          <li class="flex items-center gap-2 text-gray-600"><i class="fas fa-check check w-4"></i> Unlimited feedback</li>
          <li class="flex items-center gap-2 text-gray-600"><i class="fas fa-check check w-4"></i> Full AI Insights</li>
          <li class="flex items-center gap-2 text-gray-600"><i class="fas fa-check check w-4"></i> <strong>AI Auto-Replies</strong></li>
          <li class="flex items-center gap-2 text-gray-600"><i class="fas fa-check check w-4"></i> Full Campaigns</li>
          <li class="flex items-center gap-2 text-gray-600"><i class="fas fa-check check w-4"></i> QR + Embedded form</li>
          <li class="flex items-center gap-2 text-gray-300 line-through"><i class="fas fa-times cross w-4"></i> AI Copilot</li>
        </ul>
        <a href="#" class="block text-center text-sm font-bold py-2.5 rounded-xl text-white transition"
           style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
          Start Free Trial
        </a>
      </div>

      <!-- PRO -->
      <div class="plan-card bg-white border-2 border-gray-200 rounded-2xl p-6 flex flex-col">
        <div class="mb-5">
          <span class="text-2xl mb-2 block">⚡</span>
          <h3 class="text-base font-bold text-gray-900 mb-1">Pro</h3>
          <p class="text-xs text-gray-400 mb-4">For power users</p>
          <div class="flex items-end gap-1">
            <span class="text-3xl font-black text-gray-900" data-monthly="€99" data-annual="€79">€99</span>
            <span class="text-gray-400 text-sm mb-1">/mo</span>
          </div>
          <p id="pro-save" class="text-xs text-green-600 font-medium mt-1 hidden">Billed €948/yr — save €240</p>
        </div>
        <ul class="space-y-2.5 flex-1 mb-6 text-sm">
          <li class="flex items-center gap-2 text-gray-600"><i class="fas fa-check check w-4"></i> Unlimited projects</li>
          <li class="flex items-center gap-2 text-gray-600"><i class="fas fa-check check w-4"></i> <strong>AI Copilot</strong></li>
          <li class="flex items-center gap-2 text-gray-600"><i class="fas fa-check check w-4"></i> All 8 channels</li>
          <li class="flex items-center gap-2 text-gray-600"><i class="fas fa-check check w-4"></i> Slack + Jira + Email</li>
          <li class="flex items-center gap-2 text-gray-600"><i class="fas fa-check check w-4"></i> Automation</li>
          <li class="flex items-center gap-2 text-gray-600"><i class="fas fa-check check w-4"></i> Release tracking</li>
          <li class="flex items-center gap-2 text-gray-600"><i class="fas fa-check check w-4"></i> Priority support</li>
        </ul>
        <a href="#" class="block text-center text-sm font-semibold py-2.5 rounded-xl border-2 border-gray-800 text-gray-900 hover:bg-gray-900 hover:text-white transition">
          Start Free Trial
        </a>
      </div>

      <!-- ENTERPRISE -->
      <div class="plan-card bg-gray-900 border-2 border-gray-800 rounded-2xl p-6 flex flex-col">
        <div class="mb-5">
          <span class="text-2xl mb-2 block">🏢</span>
          <h3 class="text-base font-bold text-white mb-1">Enterprise</h3>
          <p class="text-xs text-gray-400 mb-4">For large organisations</p>
          <div class="flex items-end gap-1">
            <span class="text-3xl font-black text-white">€299</span>
            <span class="text-gray-500 text-sm mb-1">/mo+</span>
          </div>
          <p class="text-xs text-gray-500 mt-1">Custom pricing available</p>
        </div>
        <ul class="space-y-2.5 flex-1 mb-6 text-sm">
          <li class="flex items-center gap-2 text-gray-300"><i class="fas fa-check text-purple-400 w-4"></i> Everything in Pro</li>
          <li class="flex items-center gap-2 text-gray-300"><i class="fas fa-check text-purple-400 w-4"></i> Predictive AI</li>
          <li class="flex items-center gap-2 text-gray-300"><i class="fas fa-check text-purple-400 w-4"></i> Revenue impact</li>
          <li class="flex items-center gap-2 text-gray-300"><i class="fas fa-check text-purple-400 w-4"></i> API access</li>
          <li class="flex items-center gap-2 text-gray-300"><i class="fas fa-check text-purple-400 w-4"></i> Webhooks</li>
          <li class="flex items-center gap-2 text-gray-300"><i class="fas fa-check text-purple-400 w-4"></i> Custom branding</li>
          <li class="flex items-center gap-2 text-gray-300"><i class="fas fa-check text-purple-400 w-4"></i> Dedicated support</li>
        </ul>
        <a href="mailto:hello@feedbackflow.app" class="block text-center text-sm font-semibold py-2.5 rounded-xl text-gray-900 transition"
           style="background:linear-gradient(135deg,#a855f7,#8b5cf6)">
          Contact Sales
        </a>
      </div>

    </div>

    <!-- Trust bar -->
    <div class="flex flex-wrap items-center justify-center gap-8 mt-12 text-sm text-gray-400">
      <span class="flex items-center gap-2"><i class="fas fa-lock text-green-500"></i> No credit card required</span>
      <span class="flex items-center gap-2"><i class="fas fa-rotate-left text-blue-500"></i> 14-day free trial</span>
      <span class="flex items-center gap-2"><i class="fas fa-server text-indigo-500"></i> Self-hosted — your data</span>
      <span class="flex items-center gap-2"><i class="fas fa-shield-halved text-purple-500"></i> GDPR compliant</span>
      <span class="flex items-center gap-2"><i class="fas fa-times-circle text-red-400"></i> Cancel anytime</span>
    </div>
  </div>
</section>

<!-- ── What makes us different ─────────────────────────────────────────────── -->
<section id="features" class="bg-gray-50 py-20 px-6">
  <div class="max-w-5xl mx-auto">
    <div class="text-center mb-14">
      <h2 class="text-3xl font-black text-gray-900 mb-3">Why teams choose FeedbackFlow</h2>
      <p class="text-gray-500 max-w-xl mx-auto">We don't just collect feedback. We tell you what to do next.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <?php
      $features = [
        ['🤖', 'AI That Thinks For You', 'Auto-groups feedback into clusters, detects what\'s trending, and tells you exactly what to fix first.', 'indigo'],
        ['📊', 'CEO-Level Insights', 'Plain-English insight cards show sentiment shifts, trending issues, and release impact — in seconds.', 'purple'],
        ['📩', 'Close the Loop', 'AI-generated replies sent to users automatically. One click to reply to hundreds of users at once.', 'blue'],
        ['🔗', '8 Collection Channels', 'Widget, email, QR, WhatsApp, SMS, embedded form, public board — all flowing into one inbox.', 'violet'],
        ['📦', '100% Self-Hosted', 'Your data never leaves your server. GDPR compliant by design. No third-party cloud dependency.', 'green'],
        ['🚀', 'Works in 15 Minutes', 'Upload, configure, install. No Docker, no Node.js, no complex setup. Just PHP and MySQL.', 'amber'],
      ];
      foreach ($features as [$icon, $title, $desc, $color]):
        $colors = ['indigo' => ['bg-indigo-50', 'text-indigo-600'], 'purple' => ['bg-purple-50', 'text-purple-600'], 'blue' => ['bg-blue-50', 'text-blue-600'], 'violet' => ['bg-violet-50', 'text-violet-600'], 'green' => ['bg-green-50', 'text-green-600'], 'amber' => ['bg-amber-50', 'text-amber-600']];
        [$bg, $tc] = $colors[$color];
      ?>
      <div class="bg-white rounded-2xl p-6 border border-gray-100 hover:shadow-md transition">
        <div class="w-12 h-12 <?= $bg ?> rounded-xl flex items-center justify-center text-xl mb-4"><?= $icon ?></div>
        <h3 class="font-bold text-gray-900 mb-2"><?= $title ?></h3>
        <p class="text-sm text-gray-500 leading-relaxed"><?= $desc ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── Full Comparison Table ───────────────────────────────────────────────── -->
<section id="compare" class="py-20 px-6">
  <div class="max-w-5xl mx-auto">
    <div class="text-center mb-12">
      <h2 class="text-3xl font-black text-gray-900 mb-3">Full Feature Comparison</h2>
      <p class="text-gray-500">See exactly what's included in each plan.</p>
    </div>

    <div class="rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
      <!-- Table header -->
      <div class="grid grid-cols-6 bg-gray-50 border-b border-gray-200">
        <div class="col-span-2 px-6 py-4 text-sm font-semibold text-gray-500">Feature</div>
        <?php foreach ([['Free','€0','gray'],['Starter','€19','blue'],['Growth','€49','indigo'],['Pro','€99','violet'],['Enterprise','€299+','purple']] as [$p,$pr,$c]): ?>
        <div class="px-3 py-4 text-center">
          <p class="text-xs font-bold text-<?= $c ?>-600"><?= $p ?></p>
          <p class="text-xs text-gray-400 mt-0.5"><?= $pr ?>/mo</p>
        </div>
        <?php endforeach; ?>
      </div>

      <?php
      $sections = [
        'Core' => [
          ['Dashboard',         ['Basic','Full','Full','Full','Full']],
          ['Feedback Inbox',    ['✅','✅','✅','✅','✅']],
          ['Public Board',      ['✅','✅','✅','✅','✅']],
          ['Projects',          ['1','3','10','Unlimited','Unlimited']],
          ['Feedback / month',  ['50','500','Unlimited','Unlimited','Unlimited']],
          ['Team members',      ['1','3','10','Unlimited','Unlimited']],
        ],
        'AI Features' => [
          ['AI Insights',       ['❌','Basic','Full','Advanced','Advanced +']],
          ['AI Auto-Replies',   ['❌','❌','✅','✅','✅']],
          ['AI Copilot',        ['❌','❌','❌','✅','✅']],
          ['Predictive AI',     ['❌','❌','❌','❌','✅']],
          ['Revenue Impact',    ['❌','❌','❌','❌','✅']],
        ],
        'Channels & Campaigns' => [
          ['Email Campaigns',   ['❌','Basic','Full','Full','Full']],
          ['Website Widget',    ['✅','✅','✅','✅','✅']],
          ['QR Code',           ['❌','❌','Limited','✅','✅']],
          ['Embedded Form',     ['❌','❌','Limited','✅','✅']],
          ['WhatsApp / SMS',    ['❌','❌','❌','✅','✅']],
        ],
        'Integrations & Automation' => [
          ['Slack / Jira',      ['❌','❌','❌','✅','✅']],
          ['Webhooks',          ['❌','❌','❌','❌','✅']],
          ['API Access',        ['❌','❌','❌','❌','✅']],
          ['Automation',        ['❌','❌','Basic','Full','Full']],
        ],
        'Roadmap & Analytics' => [
          ['Analytics',         ['Basic','Basic','Full','Full','Full']],
          ['Roadmap',           ['✅','✅','✅','✅','✅']],
          ['Changelog',         ['✅','✅','✅','✅','✅']],
          ['Release Impact',    ['❌','❌','✅','✅','✅']],
        ],
        'Support & Compliance' => [
          ['Email Support',     ['❌','✅','✅','✅','✅']],
          ['Priority Support',  ['❌','❌','❌','✅','✅']],
          ['Dedicated Manager', ['❌','❌','❌','❌','✅']],
          ['Custom Branding',   ['❌','❌','❌','❌','✅']],
          ['GDPR Compliance',   ['✅','✅','✅','✅','✅']],
        ],
      ];
      $odd = false;
      foreach ($sections as $section => $rows):
      ?>
      <!-- Section label -->
      <div class="grid grid-cols-6 bg-indigo-50 border-y border-indigo-100">
        <div class="col-span-6 px-6 py-2.5 text-xs font-bold text-indigo-700 uppercase tracking-wider"><?= $section ?></div>
      </div>
      <?php foreach ($rows as [$label, $vals]):
        $odd = !$odd;
        $bg = $odd ? 'bg-white' : 'bg-gray-50/50';
      ?>
      <div class="feature-row grid grid-cols-6 border-b border-gray-100 <?= $bg ?>">
        <div class="col-span-2 px-6 py-3 text-sm text-gray-700 flex items-center"><?= $label ?></div>
        <?php foreach ($vals as $i => $val):
          $isPopular = $i === 2; // Growth
          $isCheck   = $val === '✅';
          $isCross   = $val === '❌';
        ?>
        <div class="px-3 py-3 text-center flex items-center justify-center">
          <?php if ($isCheck): ?>
            <i class="fas fa-check-circle text-indigo-500 text-base"></i>
          <?php elseif ($isCross): ?>
            <i class="fas fa-times-circle text-gray-200 text-base"></i>
          <?php else: ?>
            <span class="text-xs font-semibold <?= $isPopular ? 'text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-full' : 'text-gray-600' ?>"><?= $val ?></span>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endforeach; endforeach; ?>

      <!-- CTA Row -->
      <div class="grid grid-cols-6 bg-gray-50 py-4">
        <div class="col-span-2 px-6 flex items-center text-sm font-semibold text-gray-700">Get started today</div>
        <?php foreach ([['Free','border-gray-200 text-gray-600 hover:border-indigo-300'],['€19/mo','border-blue-200 text-blue-600 hover:bg-blue-50'],['€49/mo','bg-indigo-600 text-white hover:bg-indigo-700'],['€99/mo','border-gray-800 text-gray-900 hover:bg-gray-900 hover:text-white'],['Contact','bg-gray-900 text-white hover:bg-gray-700']] as [$label, $cls]): ?>
        <div class="px-2 py-1 flex items-center justify-center">
          <a href="#" class="text-xs font-bold px-3 py-2 rounded-xl border-2 transition <?= $cls ?>"><?= $label ?></a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- ── Social Proof ────────────────────────────────────────────────────────── -->
<section class="bg-gray-50 py-20 px-6">
  <div class="max-w-5xl mx-auto">
    <div class="text-center mb-12">
      <h2 class="text-3xl font-black text-gray-900 mb-3">Loved by product teams</h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <?php
      $testimonials = [
        ['Sarah K.', 'Head of Product, TechCorp', 'The AI Insights saved us 5 hours a week. We used to manually read through feedback — now we just open the insights page.', '⭐⭐⭐⭐⭐'],
        ['Marcus L.', 'Founder, SaaSBase', 'We deployed it in 15 minutes on our server. The QR code channel alone brought in 3x more feedback from our events.', '⭐⭐⭐⭐⭐'],
        ['Priya M.', 'CTO, Growthly', 'The self-hosted model was non-negotiable for us — GDPR and all. AI Copilot clustering is genuinely impressive.', '⭐⭐⭐⭐⭐'],
      ];
      foreach ($testimonials as [$name, $role, $quote, $stars]):
      ?>
      <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
        <p class="text-sm mb-1"><?= $stars ?></p>
        <p class="text-gray-700 text-sm leading-relaxed mb-5">"<?= $quote ?>"</p>
        <div>
          <p class="font-bold text-gray-900 text-sm"><?= $name ?></p>
          <p class="text-xs text-gray-400"><?= $role ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── FAQ ────────────────────────────────────────────────────────────────── -->
<section id="faq" class="py-20 px-6">
  <div class="max-w-2xl mx-auto">
    <div class="text-center mb-12">
      <h2 class="text-3xl font-black text-gray-900 mb-3">Frequently asked questions</h2>
    </div>
    <?php
    $faqs = [
      ['Do I need a credit card to start?', 'No. The Free plan is completely free with no credit card required. You can upgrade at any time.'],
      ['What does self-hosted mean?', 'You download the files, upload them to your own server (shared hosting, VPS, or dedicated), and run the app on your own infrastructure. Your data never touches our servers.'],
      ['Can I try Pro features before buying?', 'Yes — every paid plan comes with a 14-day free trial. You can test all features and only get charged after the trial period.'],
      ['What happens if I exceed my feedback limit on Free?', 'New feedback submissions will be paused until you upgrade. Existing data is never deleted.'],
      ['Do I need an OpenAI API key for AI features?', 'No. AI Insights and AI Copilot work without an OpenAI key using our built-in Smart Rules engine. Adding your own OpenAI key unlocks GPT-powered analysis for more accurate results.'],
      ['Can I switch plans at any time?', 'Yes, you can upgrade or downgrade at any time. Upgrades take effect immediately, downgrades take effect at the end of your billing period.'],
      ['Is there a lifetime deal available?', 'Contact us at enterprise pricing for lifetime license options — popular for agencies managing multiple client installations.'],
    ];
    foreach ($faqs as $i => [$q, $a]):
    ?>
    <div class="faq-item border-b border-gray-100 py-5" id="faq-<?= $i ?>">
      <button onclick="toggleFaq(<?= $i ?>)" class="w-full flex items-center justify-between text-left gap-4 focus:outline-none">
        <span class="font-semibold text-gray-900 text-sm"><?= $q ?></span>
        <i class="faq-icon fas fa-plus text-indigo-500 flex-shrink-0"></i>
      </button>
      <div class="faq-answer" id="faq-answer-<?= $i ?>">
        <p class="text-sm text-gray-500 leading-relaxed pt-3 pb-1"><?= $a ?></p>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- ── Final CTA ───────────────────────────────────────────────────────────── -->
<section class="py-20 px-6">
  <div class="max-w-2xl mx-auto cta-bg rounded-3xl p-12 text-center shadow-2xl shadow-indigo-200">
    <p class="text-indigo-200 text-sm font-semibold uppercase tracking-widest mb-3">Start today</p>
    <h2 class="text-3xl font-black text-white mb-4">Your users are talking.<br>Are you listening?</h2>
    <p class="text-indigo-200 mb-8 text-lg">Join 500+ product teams using FeedbackFlow to build better products, faster.</p>
    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
      <a href="#pricing" class="inline-flex items-center gap-2 bg-white text-indigo-700 font-bold px-7 py-3.5 rounded-xl hover:bg-indigo-50 transition text-sm">
        <i class="fas fa-rocket"></i> Start Free — No Card Needed
      </a>
      <a href="mailto:hello@feedbackflow.app" class="inline-flex items-center gap-2 text-white border-2 border-white/30 font-semibold px-7 py-3.5 rounded-xl hover:bg-white/10 transition text-sm">
        <i class="fas fa-comments"></i> Talk to Sales
      </a>
    </div>
    <p class="text-indigo-300 text-xs mt-6">14-day free trial · No contracts · Cancel anytime</p>
  </div>
</section>

<!-- ── Footer ─────────────────────────────────────────────────────────────── -->
<footer class="bg-gray-900 py-12 px-6">
  <div class="max-w-5xl mx-auto">
    <div class="flex flex-col md:flex-row items-start justify-between gap-8 mb-10">
      <div>
        <div class="flex items-center gap-2.5 mb-3">
          <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-sm font-bold" style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
            <i class="fas fa-comments"></i>
          </div>
          <span class="font-bold text-white text-lg tracking-tight">FeedbackFlow</span>
        </div>
        <p class="text-sm text-gray-400 max-w-xs leading-relaxed">Self-hosted product feedback management. Collect, analyse, and act on user feedback — on your own server.</p>
      </div>
      <div class="grid grid-cols-2 gap-x-16 gap-y-2 text-sm text-gray-400">
        <a href="#features" class="hover:text-white transition">Features</a>
        <a href="#pricing" class="hover:text-white transition">Pricing</a>
        <a href="#compare" class="hover:text-white transition">Compare Plans</a>
        <a href="#faq" class="hover:text-white transition">FAQ</a>
        <a href="docs/user-guide.md" class="hover:text-white transition">Documentation</a>
        <a href="mailto:hello@feedbackflow.app" class="hover:text-white transition">Contact</a>
      </div>
    </div>
    <div class="border-t border-gray-800 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-500">
      <p>© <?= date('Y') ?> FeedbackFlow. All rights reserved.</p>
      <div class="flex items-center gap-4">
        <span class="flex items-center gap-1.5"><i class="fas fa-shield-halved text-green-500"></i> GDPR Compliant</span>
        <span class="flex items-center gap-1.5"><i class="fas fa-server text-indigo-400"></i> Self-Hosted</span>
      </div>
    </div>
  </div>
</footer>

<!-- ── JS ─────────────────────────────────────────────────────────────────── -->
<script>
let isAnnual = false;

function toggleBilling() {
  isAnnual = !isAnnual;
  const btn   = document.getElementById('toggle-btn');
  const knob  = document.getElementById('toggle-knob');
  const lbM   = document.getElementById('label-monthly');
  const lbA   = document.getElementById('label-annual');

  btn.classList.toggle('active', isAnnual);
  knob.style.transform = isAnnual ? 'translateX(20px)' : 'translateX(0)';
  lbM.classList.toggle('text-gray-900', !isAnnual);
  lbM.classList.toggle('text-gray-400', isAnnual);
  lbA.classList.toggle('text-gray-900', isAnnual);
  lbA.classList.toggle('text-gray-400', !isAnnual);

  // Update prices
  document.querySelectorAll('[data-monthly]').forEach(el => {
    el.textContent = isAnnual ? el.dataset.annual : el.dataset.monthly;
  });

  // Show/hide savings text
  ['starter', 'growth', 'pro'].forEach(id => {
    const el = document.getElementById(id + '-save');
    if (el) el.classList.toggle('hidden', !isAnnual);
  });
}

function toggleFaq(i) {
  const item   = document.getElementById('faq-' + i);
  const answer = document.getElementById('faq-answer-' + i);
  const isOpen = item.classList.contains('open');

  // Close all
  document.querySelectorAll('.faq-item').forEach(el => el.classList.remove('open'));
  document.querySelectorAll('.faq-answer').forEach(el => el.classList.remove('open'));

  if (!isOpen) {
    item.classList.add('open');
    answer.classList.add('open');
  }
}

// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const target = document.querySelector(a.getAttribute('href'));
    if (target) { e.preventDefault(); target.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
  });
});
</script>
</body>
</html>

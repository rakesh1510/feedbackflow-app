<?php
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$currentProject = $_SESSION['current_project'] ?? null;
// Fetch current project properly
if (!$currentProject && !empty($_SESSION['current_project_id'])) {
    $currentProject = DB::fetch("SELECT * FROM ff_projects WHERE id = ?", [$_SESSION['current_project_id']]);
}
?>
<aside style="width:256px;min-width:256px" class="fixed inset-y-0 left-0 z-50 bg-white border-r border-gray-100 flex flex-col shadow-sm">

  <!-- Logo -->
  <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
    <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-sm font-bold" style="background: linear-gradient(135deg,#6366f1,#8b5cf6)">
      <i class="fas fa-comments"></i>
    </div>
    <span class="font-bold text-gray-900 text-base tracking-tight"><?= APP_NAME ?></span>
  </div>

  <!-- Project Switcher -->
  <?php if (!empty($userProjects)): ?>
  <div class="px-3 py-3 border-b border-gray-50">
    <div x-data="{ open: false }" class="relative">
      <button @click="open = !open"
              class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl bg-gray-50 hover:bg-gray-100 transition-colors text-left">
        <div class="w-6 h-6 rounded-lg flex items-center justify-center flex-shrink-0 text-white text-xs font-bold"
             style="background: linear-gradient(135deg,#6366f1,#8b5cf6)">
          <?= strtoupper(substr($currentProject['name'] ?? 'P', 0, 1)) ?>
        </div>
        <span class="flex-1 text-xs font-semibold text-gray-700 truncate">
          <?= $currentProject ? h($currentProject['name']) : 'Select project' ?>
        </span>
        <i class="fas fa-chevron-down text-gray-400" style="font-size:10px"></i>
      </button>

      <div x-show="open" @click.outside="open = false" x-cloak
           class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg z-50 py-1 max-h-48 overflow-y-auto">
        <?php foreach ($userProjects as $proj): ?>
          <a href="?project_id=<?= $proj['id'] ?>"
             class="flex items-center gap-2.5 px-3 py-2 text-xs hover:bg-gray-50 transition-colors <?= ($currentProject['id'] ?? 0) == $proj['id'] ? 'text-indigo-600 font-semibold' : 'text-gray-700' ?>">
            <div class="w-5 h-5 rounded-md flex items-center justify-center text-white font-bold flex-shrink-0"
                 style="font-size:9px;background:<?= ($currentProject['id'] ?? 0) == $proj['id'] ? '#6366f1' : '#94a3b8' ?>">
              <?= strtoupper(substr($proj['name'], 0, 1)) ?>
            </div>
            <span class="truncate flex-1"><?= h($proj['name']) ?></span>
            <?php if (($currentProject['id'] ?? 0) == $proj['id']): ?>
              <i class="fas fa-check text-indigo-500" style="font-size:10px"></i>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
        <div class="border-t border-gray-100 mt-1 pt-1">
          <a href="<?= APP_URL ?>/admin/projects.php?action=new"
             class="flex items-center gap-2 px-3 py-2 text-xs text-indigo-600 hover:bg-indigo-50 transition-colors font-medium">
            <i class="fas fa-plus" style="font-size:10px"></i> New Project
          </a>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Navigation -->
  <nav class="flex-1 overflow-y-auto px-3 py-3">

    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3 mb-1.5">Main</p>

    <a href="<?= APP_URL ?>/admin/index.php"
       class="nav-link <?= $currentPage === 'index' ? 'active' : '' ?>">
      <span class="icon"><i class="fas fa-chart-pie"></i></span>
      Dashboard
    </a>

    <a href="<?= APP_URL ?>/admin/feedback.php"
       class="nav-link <?= $currentPage === 'feedback' ? 'active' : '' ?>">
      <span class="icon"><i class="fas fa-comments"></i></span>
      Feedback
      <?php
        if (!empty($currentProject)) {
          $newCount = DB::count("SELECT COUNT(*) FROM ff_feedback WHERE project_id = ? AND status = 'new'", [$currentProject['id']]);
          if ($newCount > 0):
      ?>
        <span style="margin-left:auto;background:#6366f1;color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:99px;line-height:1.6"><?= $newCount ?></span>
      <?php endif; } ?>
    </a>

    <a href="<?= APP_URL ?>/admin/roadmap.php"
       class="nav-link <?= $currentPage === 'roadmap' ? 'active' : '' ?>">
      <span class="icon"><i class="fas fa-map-signs"></i></span>
      Roadmap
    </a>

    <a href="<?= APP_URL ?>/admin/changelog.php"
       class="nav-link <?= $currentPage === 'changelog' ? 'active' : '' ?>">
      <span class="icon"><i class="fas fa-scroll"></i></span>
      Changelog
    </a>

    <a href="<?= APP_URL ?>/admin/analytics.php"
       class="nav-link <?= $currentPage === 'analytics' ? 'active' : '' ?>">
      <span class="icon"><i class="fas fa-chart-bar"></i></span>
      Analytics
    </a>

    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3 mb-1.5 mt-4">AI Copilot</p>

    <a href="<?= APP_URL ?>/admin/ai-insights.php"
       class="nav-link <?= $currentPage === 'ai-insights' ? 'active' : '' ?>">
      <span class="icon"><i class="fas fa-brain"></i></span>
      AI Insights
      <?php
        if (!empty($currentProject)) {
          $aiInsightCount = DB::count("SELECT COUNT(*) FROM ff_ai_insights WHERE project_id = ? AND is_read = 0", [$currentProject['id']]);
          if ($aiInsightCount > 0):
      ?>
        <span style="margin-left:auto;background:#8b5cf6;color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:99px;line-height:1.6"><?= $aiInsightCount ?></span>
      <?php endif; } ?>
    </a>

    <a href="<?= APP_URL ?>/admin/ai-copilot.php"
       class="nav-link <?= $currentPage === 'ai-copilot' ? 'active' : '' ?>">
      <span class="icon"><i class="fas fa-robot"></i></span>
      AI Copilot
    </a>

    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3 mb-1.5 mt-4">Manage</p>

    <a href="<?= APP_URL ?>/admin/projects.php"
       class="nav-link <?= $currentPage === 'projects' ? 'active' : '' ?>">
      <span class="icon"><i class="fas fa-folder"></i></span>
      Projects
    </a>

    <a href="<?= APP_URL ?>/admin/team.php"
       class="nav-link <?= $currentPage === 'team' ? 'active' : '' ?>">
      <span class="icon"><i class="fas fa-users"></i></span>
      Team
    </a>

    <a href="<?= APP_URL ?>/admin/channels.php"
       class="nav-link <?= $currentPage === 'channels' ? 'active' : '' ?>">
      <span class="icon"><i class="fas fa-broadcast-tower"></i></span>
      Channels
    </a>

    <a href="<?= APP_URL ?>/admin/widget.php"
       class="nav-link <?= $currentPage === 'widget' ? 'active' : '' ?>">
      <span class="icon"><i class="fas fa-code"></i></span>
      Widget
    </a>

    <a href="<?= APP_URL ?>/admin/integrations.php"
       class="nav-link <?= $currentPage === 'integrations' ? 'active' : '' ?>">
      <span class="icon"><i class="fas fa-plug"></i></span>
      Integrations
    </a>

    <a href="<?= APP_URL ?>/admin/settings.php"
       class="nav-link <?= $currentPage === 'settings' ? 'active' : '' ?>">
      <span class="icon"><i class="fas fa-cog"></i></span>
      Settings
    </a>

    <?php if (!empty($currentProject)): ?>
    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider px-3 mb-1.5 mt-4">Public Pages</p>

    <a href="<?= APP_URL ?>/public/board.php?slug=<?= h($currentProject['slug']) ?>" target="_blank"
       class="nav-link">
      <span class="icon"><i class="fas fa-globe"></i></span>
      <span class="flex-1">Feedback Board</span>
      <i class="fas fa-external-link-alt text-gray-300" style="font-size:10px"></i>
    </a>

    <a href="<?= APP_URL ?>/public/roadmap.php?slug=<?= h($currentProject['slug']) ?>" target="_blank"
       class="nav-link">
      <span class="icon"><i class="fas fa-road"></i></span>
      <span class="flex-1">Public Roadmap</span>
      <i class="fas fa-external-link-alt text-gray-300" style="font-size:10px"></i>
    </a>

    <a href="<?= APP_URL ?>/public/changelog.php?slug=<?= h($currentProject['slug']) ?>" target="_blank"
       class="nav-link">
      <span class="icon"><i class="fas fa-newspaper"></i></span>
      <span class="flex-1">Changelog</span>
      <i class="fas fa-external-link-alt text-gray-300" style="font-size:10px"></i>
    </a>
    <?php endif; ?>
  </nav>

  <!-- User Profile -->
  <div class="border-t border-gray-100 p-3">
    <div x-data="{ open: false }" class="relative">
      <button @click="open = !open"
              class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-gray-50 transition-colors text-left">
        <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
             style="background: linear-gradient(135deg,#6366f1,#8b5cf6)">
          <?= strtoupper(substr($currentUser['name'] ?? 'U', 0, 1)) ?>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-gray-800 truncate leading-tight"><?= h($currentUser['name'] ?? '') ?></p>
          <p class="text-xs text-gray-400 truncate leading-tight"><?= h($currentUser['email'] ?? '') ?></p>
        </div>
        <i class="fas fa-chevron-up text-gray-400" style="font-size:10px"></i>
      </button>

      <div x-show="open" @click.outside="open = false" x-cloak
           class="absolute bottom-full left-0 right-0 mb-1 bg-white border border-gray-200 rounded-xl shadow-xl py-1 z-50">
        <a href="<?= APP_URL ?>/admin/settings.php"
           class="flex items-center gap-2.5 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
          <i class="fas fa-user w-4 text-center text-gray-400" style="font-size:13px"></i> Profile & Settings
        </a>
        <div class="border-t border-gray-100 my-1"></div>
        <a href="<?= APP_URL ?>/index.php?action=logout"
           class="flex items-center gap-2.5 px-3 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
          <i class="fas fa-sign-out-alt w-4 text-center" style="font-size:13px"></i> Sign out
        </a>
      </div>
    </div>
  </div>

</aside>

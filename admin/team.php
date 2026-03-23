<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/billing.php';
require_once dirname(__DIR__) . '/includes/limit-check.php';

$currentUser = Auth::require();
$userProjects = getUserProjects($currentUser['id']);
Auth::start();
if (isset($_GET['project_id'])) $_SESSION['current_project_id'] = (int)$_GET['project_id'];
$projectId = $_SESSION['current_project_id'] ?? ($userProjects[0]['id'] ?? null);
$currentProject = $projectId ? getProject($projectId, $currentUser['id']) : null;
if (!$currentProject && !empty($userProjects)) { $currentProject = $userProjects[0]; $projectId = $currentProject['id']; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrf()) { flash('error','Security check failed.'); redirect($_SERVER['REQUEST_URI']); }
    $pAction = $_POST['_action'] ?? '';
    if ($pAction === 'invite') {
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'member';
        if (!isValidEmail($email)) { flash('error','Invalid email.'); redirect($_SERVER['REQUEST_URI']); }
        // ── Limit check: max_users ────────────────────────────────────
        $__co = BillingService::getCompany($currentUser['id']);
        $__cid = $__co ? (int)$__co['id'] : (int)$currentUser['id'];
        $__memberCount = DB::count("SELECT COUNT(*) FROM ff_users WHERE company_id = ? AND is_active = 1", [$__cid]);
        LimitCheck::gateNumeric($__cid, 'max_users', $__memberCount, 'team member', true, APP_URL . '/admin/billing.php');
        // ─────────────────────────────────────────────────────────────
        $inviteUser = DB::fetch("SELECT * FROM ff_users WHERE email = ?", [$email]);
        if (!$inviteUser) {
            // Create placeholder account and send invite
            $tempPassword = randomKey(12);
            $hash = password_hash($tempPassword, PASSWORD_BCRYPT);
            $uid = DB::insert('ff_users', ['name' => explode('@',$email)[0], 'email' => $email, 'password' => $hash, 'role' => 'member']);
            $inviteUser = DB::fetch("SELECT * FROM ff_users WHERE id = ?", [$uid]);
            sendEmail($email, "You've been invited to " . APP_NAME, "<p>You've been invited to join <strong>" . h($currentProject['name']) . "</strong> on " . APP_NAME . ".</p><p>Login at <a href='" . APP_URL . "'>" . APP_URL . "</a> with email: $email and temporary password: $tempPassword</p>");
        }
        if (!DB::fetch("SELECT id FROM ff_project_members WHERE project_id = ? AND user_id = ?", [$projectId, $inviteUser['id']])) {
            DB::insert('ff_project_members', ['project_id' => $projectId, 'user_id' => $inviteUser['id'], 'role' => $role, 'invited_by' => $currentUser['id']]);
            flash('success', 'Team member invited!');
        } else { flash('error', 'User already a member.'); }
    } elseif ($pAction === 'update_role') {
        DB::update('ff_project_members', ['role' => $_POST['role']], 'id = ? AND project_id = ?', [(int)$_POST['member_id'], $projectId]);
        flash('success', 'Role updated!');
    } elseif ($pAction === 'remove') {
        DB::delete('ff_project_members', 'id = ? AND project_id = ?', [(int)$_POST['member_id'], $projectId]);
        flash('success', 'Member removed.');
    }
    redirect(APP_URL . '/admin/team.php');
}

$members = DB::fetchAll(
    "SELECT pm.*, u.name, u.email, u.avatar, u.last_login, u.created_at as user_created
     FROM ff_project_members pm JOIN ff_users u ON u.id = pm.user_id WHERE pm.project_id = ?
     ORDER BY pm.joined_at",
    [$projectId]
);
// Add project owner if not in members list
$owner = DB::fetch("SELECT u.* FROM ff_users u JOIN ff_projects p ON p.owner_id = u.id WHERE p.id = ?", [$projectId]);
$pageTitle = 'Team – ' . APP_NAME;
include dirname(__DIR__) . '/includes/header.php';
?>
<div class="flex h-full">
<?php include dirname(__DIR__) . '/includes/sidebar.php'; ?>
<main class="ml-64 flex-1 overflow-y-auto">
  <header class="sticky top-0 z-40 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
    <h1 class="text-xl font-bold text-gray-900">Team Members</h1>
    <button onclick="document.getElementById('inviteModal').classList.remove('hidden')" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition">
      <i class="fas fa-user-plus"></i> Invite Member
    </button>
  </header>

  <?php foreach (getFlash() as $f): ?>
    <div data-flash class="mx-6 mt-4 px-4 py-3 rounded-xl text-sm <?= $f['type']==='success'?'bg-green-50 text-green-700 border border-green-200':'bg-red-50 text-red-700 border border-red-200' ?>"><?= h($f['msg']) ?></div>
  <?php endforeach; ?>

  <div class="p-6">
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
      <!-- Owner -->
      <div class="px-6 py-4 border-b border-gray-50 flex items-center gap-4 bg-indigo-50/50">
        <div class="w-10 h-10 bg-indigo-600 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0">
          <?= strtoupper(substr($owner['name'] ?? 'O', 0, 1)) ?>
        </div>
        <div class="flex-1">
          <p class="font-semibold text-gray-900"><?= h($owner['name'] ?? '') ?></p>
          <p class="text-sm text-gray-500"><?= h($owner['email'] ?? '') ?></p>
        </div>
        <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-3 py-1 rounded-full">Owner</span>
      </div>

      <?php if (empty($members)): ?>
        <div class="py-12 text-center text-gray-400">
          <i class="fas fa-users text-3xl block mb-2"></i>
          <p class="font-medium">No team members yet</p>
          <p class="text-sm mt-1">Invite your team to collaborate</p>
        </div>
      <?php else: foreach ($members as $member): ?>
        <div class="flex items-center gap-4 px-6 py-4 border-b border-gray-50 hover:bg-gray-50 transition">
          <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-600 font-bold flex-shrink-0">
            <?= strtoupper(substr($member['name'] ?? 'M', 0, 1)) ?>
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-medium text-gray-900"><?= h($member['name']) ?></p>
            <p class="text-sm text-gray-500"><?= h($member['email']) ?></p>
            <?php if ($member['last_login']): ?><p class="text-xs text-gray-400">Last login: <?= timeAgo($member['last_login']) ?></p><?php endif; ?>
          </div>
          <form method="POST" class="flex items-center gap-2">
            <input type="hidden" name="_csrf" value="<?= csrf() ?>">
            <input type="hidden" name="_action" value="update_role">
            <input type="hidden" name="member_id" value="<?= $member['id'] ?>">
            <select name="role" onchange="this.form.submit()" class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm outline-none focus:ring-2 focus:ring-indigo-500">
              <?php foreach (['admin','manager','member','viewer'] as $r): ?>
                <option value="<?= $r ?>" <?= $member['role']===$r?'selected':'' ?>><?= ucfirst($r) ?></option>
              <?php endforeach; ?>
            </select>
          </form>
          <form method="POST" onsubmit="return confirm('Remove this member?')">
            <input type="hidden" name="_csrf" value="<?= csrf() ?>">
            <input type="hidden" name="_action" value="remove">
            <input type="hidden" name="member_id" value="<?= $member['id'] ?>">
            <button type="submit" class="text-red-400 hover:text-red-600 px-2 py-1"><i class="fas fa-user-times"></i></button>
          </form>
        </div>
      <?php endforeach; endif; ?>
    </div>

    <!-- Roles Info -->
    <div class="mt-6 bg-white rounded-2xl border border-gray-200 p-6">
      <h3 class="font-semibold text-gray-900 mb-4">Role Permissions</h3>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead><tr class="border-b border-gray-100">
            <th class="text-left py-2 text-gray-500 font-medium">Permission</th>
            <th class="text-center py-2 px-3 text-gray-500 font-medium">Owner</th>
            <th class="text-center py-2 px-3 text-gray-500 font-medium">Admin</th>
            <th class="text-center py-2 px-3 text-gray-500 font-medium">Manager</th>
            <th class="text-center py-2 px-3 text-gray-500 font-medium">Member</th>
            <th class="text-center py-2 px-3 text-gray-500 font-medium">Viewer</th>
          </tr></thead>
          <tbody class="divide-y divide-gray-50">
            <?php $perms = [['View feedback','✅','✅','✅','✅','✅'],['Reply & comment','✅','✅','✅','✅','❌'],['Change status','✅','✅','✅','✅','❌'],['Manage roadmap','✅','✅','✅','❌','❌'],['Invite members','✅','✅','❌','❌','❌'],['Delete feedback','✅','✅','❌','❌','❌'],['Project settings','✅','✅','❌','❌','❌'],['Delete project','✅','❌','❌','❌','❌']];
            foreach ($perms as [$label, ...$vals]): ?>
              <tr class="hover:bg-gray-50">
                <td class="py-2 text-gray-700"><?= $label ?></td>
                <?php foreach ($vals as $v): ?><td class="text-center py-2 px-3"><?= $v ?></td><?php endforeach; ?>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>
</div>

<!-- Invite Modal -->
<div id="inviteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
    <div class="flex items-center justify-between mb-5">
      <h3 class="text-lg font-bold">Invite Team Member</h3>
      <button onclick="document.getElementById('inviteModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
    </div>
    <form method="POST" class="space-y-4">
      <input type="hidden" name="_csrf" value="<?= csrf() ?>">
      <input type="hidden" name="_action" value="invite">
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
        <input type="email" name="email" required placeholder="teammate@company.com" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-500"></div>
      <div><label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
        <select name="role" class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm outline-none">
          <option value="admin">Admin – Full access</option>
          <option value="manager">Manager – Manage feedback & roadmap</option>
          <option value="member" selected>Member – View & respond</option>
          <option value="viewer">Viewer – Read only</option>
        </select></div>
      <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 text-xs text-blue-700">
        <i class="fas fa-info-circle mr-1"></i> If the user doesn't have an account, they'll receive an email invitation with login credentials.
      </div>
      <div class="flex gap-3 pt-2">
        <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-xl transition">Send Invitation</button>
        <button type="button" onclick="document.getElementById('inviteModal').classList.add('hidden')" class="px-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600">Cancel</button>
      </div>
    </form>
  </div>
</div>
<?php include dirname(__DIR__) . '/includes/footer.php'; ?>

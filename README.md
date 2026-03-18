# FeedbackFlow – Self-Hosted LAMP Feedback Management

A comprehensive product feedback management system for your own LAMP server (Linux, Apache, MySQL, PHP 8.0+).

## Features

### Core
- ✅ Beautiful, responsive admin dashboard
- ✅ Multi-project support
- ✅ Fast feedback list with search, filters, sorting, pagination
- ✅ Embeddable JavaScript widget (drop one `<script>` tag)
- ✅ Mobile-responsive design

### Feedback Management
- ✅ Feedback categories (Bug, Feature, Improvement, etc.)
- ✅ Status tracking (New → Under Review → Planned → In Progress → Done → Declined)
- ✅ Priority levels (Critical, High, Medium, Low)
- ✅ Assign to team members
- ✅ Internal notes (private to team)
- ✅ Public comments
- ✅ Tags system
- ✅ File attachments (images, PDFs, etc.)
- ✅ User email capture
- ✅ Public/private feedback mode
- ✅ Merge duplicate requests
- ✅ Vote counting + emoji reactions

### AI Features (requires OpenAI API key)
- ✅ Sentiment analysis (positive/neutral/negative)
- ✅ AI-generated summary
- ✅ Priority scoring
- ✅ Suggested reply drafts
- ✅ AI tag suggestions
- ✅ On-demand per-feedback analysis

### Product Tools
- ✅ Feature voting system (upvote)
- ✅ Public roadmap (Planned / In Progress / Done)
- ✅ Public changelog with versioning
- ✅ Analytics dashboard with charts
- ✅ Trend over time, status breakdown, category distribution, sentiment charts

### Team & Business
- ✅ Multi-user accounts with roles (Owner, Admin, Manager, Member, Viewer)
- ✅ Team invitation system (auto-creates accounts for new users)
- ✅ Role-based permissions
- ✅ Multiple projects
- ✅ CSRF protection on all forms

### Integrations
- ✅ Slack webhook notifications
- ✅ Email notifications (SMTP or PHP mail())
- ✅ Jira configuration
- ✅ Custom webhooks (with HMAC signature verification)
- ✅ Zapier-compatible via webhooks
- ✅ JSON REST API

### Widget
- ✅ Custom colors, position, theme (light/dark/auto)
- ✅ Custom title and placeholder text
- ✅ Tab-based form (Feedback / Bug / Idea)
- ✅ Emoji reactions
- ✅ JavaScript API (`FeedbackFlow.open()`, `FeedbackFlow.identify()`)
- ✅ Anonymous submission support
- ✅ Rate limiting

### Security
- ✅ CSRF protection
- ✅ Rate limiting (DB-backed)
- ✅ Secure file uploads (extension whitelist)
- ✅ PHP uploads directory protected from execution
- ✅ Password hashing (bcrypt)
- ✅ Session security (HttpOnly, SameSite=Lax)
- ✅ GDPR data export

---

## Requirements

| Requirement | Minimum Version |
|-------------|----------------|
| PHP         | 8.0+           |
| MySQL       | 5.7+ or MariaDB 10.3+ |
| Apache      | 2.4+ (with mod_rewrite) |
| PHP Extensions | PDO, PDO_MySQL, cURL, JSON, mbstring |

---

## Installation

### 1. Upload Files
Upload the `feedbackflow-lamp/` folder to your web server's document root or a subdirectory.

### 2. Create Database
```sql
CREATE DATABASE feedbackflow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ff_user'@'localhost' IDENTIFIED BY 'strongpassword';
GRANT ALL PRIVILEGES ON feedbackflow.* TO 'ff_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Configure
Edit `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'feedbackflow');
define('DB_USER', 'ff_user');
define('DB_PASS', 'strongpassword');
define('APP_URL', 'https://yourdomain.com/feedbackflow');
define('SECRET_KEY', 'generate-a-random-64-char-string-here');
```

### 4. Run Installer
Visit: `https://yourdomain.com/feedbackflow/install.php`

The installer will:
- Check server requirements
- Run the database schema
- Create your admin account
- Seed demo data

### 5. ⚠️ Delete install.php
After installation, **delete `install.php`** for security!

---

## Widget Installation

Add to any page of your website:

```html
<script src="https://yourdomain.com/feedbackflow/widget/widget.js" 
        data-key="YOUR_WIDGET_KEY" 
        defer>
</script>
```

Find your widget key in **Admin → Widget** page.

### Advanced Usage

```javascript
// Open widget programmatically
FeedbackFlow.open();

// Pre-fill user info (call after page load)
FeedbackFlow.identify('user@email.com', 'John Doe');

// Open on button click
<button onclick="FeedbackFlow.open()">Give Feedback</button>
```

---

## AI Features Setup

1. Get an OpenAI API key from https://platform.openai.com
2. Add to `config.php`:
```php
define('OPENAI_API_KEY', 'sk-your-key-here');
define('OPENAI_MODEL', 'gpt-4o-mini');
```
3. AI features activate automatically — analyze any feedback from its detail page.

---

## Email Setup (SMTP)

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'yourapp@gmail.com');
define('SMTP_PASS', 'your-app-password');
define('SMTP_FROM', 'noreply@yourapp.com');
define('SMTP_FROM_NAME', 'FeedbackFlow');
```

---

## Folder Structure

```
feedbackflow-lamp/
├── index.php               Login / Register
├── install.php             Web installer (DELETE after use!)
├── install.sql             Database schema
├── config.php              Configuration
├── .htaccess               Apache security rules
├── includes/
│   ├── db.php              PDO database wrapper
│   ├── auth.php            Authentication
│   ├── functions.php       Helper functions
│   ├── header.php          HTML head
│   ├── footer.php          HTML foot + JS
│   └── sidebar.php         Admin sidebar
├── admin/
│   ├── index.php           Dashboard
│   ├── feedback.php        Feedback management
│   ├── analytics.php       Analytics & charts
│   ├── roadmap.php         Roadmap (kanban)
│   ├── changelog.php       Changelog
│   ├── projects.php        Project settings
│   ├── team.php            Team management
│   ├── widget.php          Widget configuration
│   ├── integrations.php    Slack, Jira, webhooks
│   └── settings.php        Account settings
├── public/
│   ├── board.php           Public feedback board
│   ├── roadmap.php         Public roadmap
│   └── changelog.php       Public changelog
├── widget/
│   ├── widget.js           Embeddable JavaScript widget
│   ├── config.php          Widget config endpoint
│   └── submit.php          Widget submission endpoint
├── api/
│   ├── feedback.php        REST API
│   └── vote.php            Vote endpoint
└── uploads/
    ├── attachments/        Uploaded feedback files
    └── avatars/            User avatars
```

---

## REST API

Use your widget key as the API key:

```
GET /api/feedback.php?key=YOUR_KEY&action=list
GET /api/feedback.php?key=YOUR_KEY&action=single&id=1
POST /api/feedback.php (Header: X-API-Key: YOUR_KEY)

POST /api/vote.php
  Body: feedback_id=1&emoji=👍
```

---

## Public Pages

| Page | URL |
|------|-----|
| Feedback Board | `/public/board.php?slug=your-project-slug` |
| Roadmap | `/public/roadmap.php?slug=your-project-slug` |
| Changelog | `/public/changelog.php?slug=your-project-slug` |

---

## License

MIT License — free to use, modify, and self-host.

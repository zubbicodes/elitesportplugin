# Elite Sports Connect

> A premium WordPress plugin that connects elite coaches with athletes — featuring coach registration, student lead capture, a beautiful public directory, and full Elementor widget support.

---

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Shortcodes](#shortcodes)
- [Elementor Widgets](#elementor-widgets)
- [How It Works](#how-it-works)
  - [Coach Registration Flow](#coach-registration-flow)
  - [Student Lead Flow](#student-lead-flow)
  - [Approving a Coach](#approving-a-coach)
- [Email Notifications](#email-notifications)
- [Admin Management](#admin-management)
- [Security](#security)
- [Recommended Companion Plugins](#recommended-companion-plugins)
- [File Structure](#file-structure)

---

## Requirements

| Requirement | Minimum Version |
|---|---|
| WordPress | 6.0+ |
| PHP | 7.4+ |
| Elementor *(optional)* | 3.5+ |

---

## Installation

1. Download `elite-sports-connect.zip`
2. In your WordPress dashboard go to **Plugins → Add New → Upload Plugin**
3. Upload the zip, click **Install Now**, then **Activate**

That's it. The plugin registers its post types and flushes rewrite rules automatically on activation.

---

## Quick Start

Create three pages in WordPress and add the following shortcodes:

| Page | Shortcode |
|---|---|
| Coach Registration | `[register_coach]` |
| Find a Coach | `[find_a_coach]` |
| Coach Directory | `[directory_coaches]` |

Or drag the **Elite Sports Connect** widgets onto any Elementor page — no shortcode knowledge needed.

---

## Shortcodes

### `[register_coach]`

Displays the coach registration form. Submissions are saved as `coach` posts with `pending` status, awaiting admin approval.

```
[register_coach
  title="Join Our Coaching Platform"
  subtitle="Connect with athletes looking for expert guidance."
  button_text="Submit Application"
]
```

| Attribute | Default | Description |
|---|---|---|
| `title` | *Join Our Coaching Platform* | Form heading |
| `subtitle` | *Connect with athletes…* | Sub-heading below the title |
| `button_text` | *Submit Application* | Label on the submit button |

**Fields collected:** Full Name, Email, Phone, City/Location, Postal Code, Sport (dropdown), Experience Level (radio), Bio, Profile Photo, Website (optional).

---

### `[find_a_coach]`

Displays the student lead form. Submissions are saved as `student_lead` posts with `private` status (admin-only).

```
[find_a_coach
  title="Find Your Perfect Coach"
  subtitle="Tell us what you're looking for."
  button_text="Send My Request"
]
```

| Attribute | Default | Description |
|---|---|---|
| `title` | *Find Your Perfect Coach* | Form heading |
| `subtitle` | *Tell us what you're looking for…* | Sub-heading |
| `button_text` | *Send My Request* | Submit button label |

**Fields collected:** Name, Email, Phone, Location, Preferred Sport (dropdown), Looking For (textarea).

---

### `[directory_coaches]`

Displays the public coach grid. Only `publish` status coaches are shown.

```
[directory_coaches
  title="Our Coaching Team"
  posts_per_page="12"
  columns="3"
  sport_filter="yes"
]
```

| Attribute | Default | Options | Description |
|---|---|---|---|
| `title` | *Our Coaching Team* | Any text | Section heading |
| `posts_per_page` | `12` | Any number | Coaches shown per page |
| `columns` | `3` | `1`, `2`, `3`, `4` | Grid columns (responsive on mobile) |
| `sport_filter` | `yes` | `yes` / `no` | Show/hide the sport filter pill bar |

**Tip:** The sport filter works via a URL query parameter (`?esc_sport=Basketball`), so it's bookmarkable and shareable.

---

## Elementor Widgets

After activating the plugin, a new **Elite Sports Connect** category appears at the bottom of the Elementor widget panel.

### Widget: Coach Registration Form

Found under **Elite Sports Connect → Coach Registration Form**.

**Content tab controls:**
- Form Title
- Form Subtitle
- Submit Button Text
- Show/Hide Website Field toggle

**Style tab controls:**
- Card background colour
- Card padding (responsive)
- Card border radius
- Title colour + typography
- Subtitle colour
- Submit button background + text colour

---

### Widget: Find a Coach Form

Found under **Elite Sports Connect → Find a Coach Form**.

**Content tab controls:**
- Form Title
- Form Subtitle
- Submit Button Text
- Show/Hide Location Field toggle

**Style tab controls:**
- Card background colour + padding + radius
- Title colour + typography
- Accent / button colour (also tints the header bar)

---

### Widget: Coach Directory

Found under **Elite Sports Connect → Coach Directory**.

**Content tab controls:**
- Section Title
- Coaches Per Page (number)
- Grid Columns (1–4)
- Show Sport Filter Bar toggle

**Style tab controls:**
- Card background colour
- Card border radius
- Coach name colour
- Sport tag background + text colour
- Section heading colour + typography

---

## How It Works

### Coach Registration Flow

```
Visitor fills form
       ↓
Nonce + honeypot verified
       ↓
Fields sanitised & validated
       ↓
Photo uploaded (MIME-checked, max 5 MB)
       ↓
coach post created → status: "pending"
       ↓
Confirmation email → coach applicant
Admin notification email → site admin
       ↓
Admin reviews in WP Dashboard → Coaches
       ↓
Admin sets status to "Publish"
       ↓
"Welcome to the Platform" email → coach
Coach appears in public directory
```

---

### Student Lead Flow

```
Visitor fills form
       ↓
Nonce + honeypot verified
       ↓
Fields sanitised & validated
       ↓
student_lead post created → status: "private"
(never publicly visible)
       ↓
Confirmation email → student
Admin notification email → site admin
       ↓
Admin views leads under Dashboard → Student Leads
Admin manually matches student with an approved coach
```

---

### Approving a Coach

1. Go to **Coaches** in the WordPress admin sidebar
2. Click into any **Pending** coach post
3. In the top-right **Publish** box, change Status to **Published** and click **Update**
4. The coach's profile goes live on the directory page instantly
5. A branded **"Welcome to the Platform"** email is automatically sent to the coach's registered email address

---

## Email Notifications

The plugin sends the following emails using WordPress's native `wp_mail()`:

| Trigger | Recipient | Subject |
|---|---|---|
| Coach form submitted | Coach applicant | *Your coaching application was received* |
| Coach form submitted | Site admin | *New Coach Application — [Name]* |
| Student form submitted | Student | *We received your coach request* |
| Student form submitted | Site admin | *New Student Lead — [Name]* |
| Coach post published | Coach | *Your coaching profile is now live* |

All emails use a branded HTML template with your site name in the header.

> **Important:** WordPress's default mail function often lands in spam. Install **WP Mail SMTP** and connect it to a proper mail provider (Gmail, SendGrid, Mailgun, Postmark, etc.) to ensure reliable delivery.

---

## Admin Management

### Coaches (`/wp-admin/edit.php?post_type=coach`)

The admin list view includes custom columns:

| Column | Source |
|---|---|
| Coach Name | Post title |
| Sport | `_esc_sport` meta |
| Experience | `_esc_experience` meta |
| Location | `_esc_location` meta |
| Email | `_esc_email` meta (clickable mailto) |
| Submitted | Post date |

Each coach edit screen includes a **Coach Details** meta box with all custom fields editable by the admin.

### Student Leads (`/wp-admin/edit.php?post_type=student_lead`)

| Column | Source |
|---|---|
| Student Name | Post title |
| Preferred Sport | `_esc_s_preferred_sport` meta |
| Location | `_esc_s_location` meta |
| Email | `_esc_s_email` meta |
| Phone | `_esc_s_phone` meta |
| Submitted | Post date |

Each lead edit screen shows all submitted details including the "Looking For" notes from the student.

---

## Security

The plugin was built with security as a first principle:

- **Nonce verification** on every form submission — prevents CSRF attacks
- **Honeypot field** — catches bot submissions silently
- **Server-side MIME type validation** using PHP's `finfo` extension — photo uploads are checked by file content, not just extension, preventing disguised file attacks
- **File size cap** — 5 MB maximum per photo upload
- **Whitelist validation** — sport and experience values are validated against the allowed list, not just sanitised
- **Full sanitisation** — all inputs pass through `sanitize_text_field()`, `sanitize_email()`, `sanitize_textarea_field()`, or `esc_url_raw()` before touching the database
- **Capability checks** — meta box saving verifies `edit_post` capability
- **`wp_safe_redirect()`** — all redirects use the safe variant to prevent open redirect attacks

---

## Recommended Companion Plugins

| Plugin | Purpose |
|---|---|
| **WP Mail SMTP** | Reliable email delivery via SMTP provider |
| **Smush** or **ShortPixel** | Compress uploaded coach photos automatically |
| **Yoast SEO** | Individual coach profile pages benefit from SEO metadata |

---

## File Structure

```
elite-sports-connect/
│
├── elite-sports-connect.php          Main plugin header & bootstrapper
│
├── includes/
│   ├── class-esc-core.php            Singleton, loads all sub-classes & assets
│   ├── class-esc-cpt.php             CPT registration, meta boxes, admin columns
│   ├── class-esc-forms.php           Form submission handling & security
│   ├── class-esc-shortcodes.php      Shortcode registration & template loading
│   ├── class-esc-emails.php          All email triggers & branded HTML template
│   └── class-esc-elementor.php       Elementor widget loader & category
│
├── elementor-widgets/
│   ├── widget-coach-form.php         Elementor: Coach registration form widget
│   ├── widget-student-form.php       Elementor: Find-a-coach form widget
│   └── widget-directory.php          Elementor: Coach directory grid widget
│
├── templates/
│   ├── form-coach.php                Frontend HTML: coach registration form
│   ├── form-student.php              Frontend HTML: student lead form
│   └── directory-coaches.php         Frontend HTML: coach directory grid
│
└── assets/
    ├── css/
    │   └── esc-style.css             Premium UI stylesheet (forms + directory)
    └── js/
        └── esc-scripts.js            Upload preview, form validation UX, filter bar
```

---

*Built by Stratonally Dev Team — a bespoke WordPress plugin.*

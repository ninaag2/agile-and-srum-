# Project Context / Rules

This document defines the architectural standards, coding rules, and workflows for the **Born Car** project (Native PHP MVC). All AI assistants and developers must audit their work against this file before marking a task as complete.

## Project Overview

- **Type**: Coursework / Scrum MVP 1.0
- **Tech Stack**: Native PHP (No Framework), MySQL, HTML/CSS/JS.
- **Architecture**: MVC (Model-View-Controller) pattern.
- **Entry Point**: `index.php` (centralized routing via `?action=...` parameter).
- **State**: Active development. Refactoring phase to separate concerns (CSS from Views).

## Repository Structure

```text
/
├── index.php             # Main entry point & Router
├── config/               # Database connection (database.php)
├── controllers/          # Business logic (Admin, Auth, Booking, Car, etc.)
├── datafunctions/        # Helper functions for data access/logic
├── database/             # SQL scripts (car_booking_db.sql)
├── css/                  # STYLESHEET LOCATION (style.css)
├── PHPMailer/            # Email library dependencies
└── views/                # Presentation layer
    ├── admin/            # Admin panel views
    ├── layouts/          # Shared templates (footer.php, header.php)
    └── pages/            # Public facing pages (cars.php, login.php, etc.)
```

## Bootstrap & Lifecycle Rules

1.  **Session Management**: `session_start()` must be called **only once** at the very top of `index.php`. Do not call it in views or controllers to avoid errors.
2.  **Database Connection (Method A)**: `index.php` must `require_once 'config/database.php'` globally. Data functions should assume the connection is available (or use `require_once` defensively).
3.  **No `bootstrap.php`**: The project does not currently use a dedicated `config/bootstrap.php`. `index.php` acts as the primary orchestrator for including necessary files.

## Execution Environment (How to Run)

- **Environment**: XAMPP / Apache (Windows).
- **Pathing Strategy**: The application runs in a **subdirectory**.
  - Example: `localhost/coursework_scrum1.0%20mvp1.0/` (Note `%20` for spaces).
  - Recommendation: Rename folder to remove spaces if possible.

* **link_rel Rule**: Always use **relative paths** (e.g., `css/style.css`, `index.php?action=home`). **Never** use root-relative paths (e.g., `/css/style.css`) as they will break in the subdirectory environment.

## Non-negotiable Rules (Must Follow)

1.  **Single Source of Truth for Styles**:
    - All CSS must reside in `css/style.css`.
    - **Strictly Forbidden**: Creating new CSS files or using CDNs unless explicitly authorized.

2.  **No Inline CSS**:
    - **Forbidden**: `style="..."` attributes in PHP/HTML files.
    - **Action**: Extract any discovered inline styles into a class in `style.css`.

3.  **No Internal Style Blocks**:
    - **Forbidden**: `<style>...</style>` tags within `views/` or `layouts/`.

4.  **Clean Views**:
    - **Allowed**: Light display logic (`if`, `foreach`) is permitted for rendering data.
    - **Forbidden**: Database calls, business logic, and heavy validation must not occur in views.
    - Do not write CSS inside views.

5.  **Single CSS Link**:
    - The stylesheet should be linked **exactly once** in the main layout (e.g., `<head>` section).
    - Format: `<link rel="stylesheet" href="css/style.css">`.

6.  **Strict MVC Pattern**:
    - `index.php` handles routing.
    - `controllers/` handle logic and data retrieval; they should **never** `echo` HTML directly.
    - `views/` display data passed from controllers.

7.  **Visual Consistency (Refactor Rule)**:
    - When converting inline styles to CSS classes, the visual output (visual regression) must be **zero**.
    - Retain exact colors, spacing, fonts, and alignment.

8.  **Security Basics**:
    - **XSS Prevention**: Always use `htmlspecialchars()` when outputting user-provided data or session variables.
    - **SQL Injection**: Prefer prepared statements in `datafunctions/`.
    - **Access Control**: Admin routes must verify `$_SESSION['user']['role'] === 'admin'`.

## UI Design System (CSS Classes)

Refer to `css/style.css` for the complete definition. Common utility classes include:

- **Layout**: `.container`, `.navbar`, `.sidebar`, `.main-content`, `.footer-container`
- **Buttons**:
  - `.btn-book` (Primary Action - Booking)
  - `.btn-submit` (Form Submission)
  - `.btn-back` (Navigation/Return)
  - `.btn-confirm` (Positive Action)
  - `.btn-cancel`, `.btn-delete` (Destructive/Negative Action)
- **Components**:
  - `.car-card`, `.car-image`, `.car-info`
  - `.form-group`, `.form-control`
  - `.alert-danger`, `.alert-success`
- **Colors (Branding)**:
  - Primary Orange: `#f48f0c` / `#ffc107`
  - Primary Black: `#1a1a1a`
  - Danger Red: `#e74c3c` / `#dc3545`

## Refactor Playbook (Step-by-step)

When asking AI to refactor a view or fix styling:

1.  **Analyze**: Read the target view file to identify `style="..."` attributes or `<style>` tags.
2.  **Extract**: standardise the style properties.
3.  **Define**: Create a semantic class name (e.g., `.profile-card`, `.text-highlight`) in `css/style.css`.
4.  **Replace**: Remove the inline style in the PHP file and add the `class="..."`.
5.  **Clean**: Remove any unused CSS or commented-out old code.
6.  **Verify**: Ensure the new class matches the old inline style's visual result.

## Definition of Done (Checklist)

- [ ] **Code Location**: Is the CSS solely in `css/style.css`?
- [ ] **Clean Code**: Are all `style="..."` attributes removed from the view?
- [ ] **No Overrides**: Are there any `<style>` tags remaining in the view?
- [ ] **Visual Check**: Does the button/element look exactly as requested (colors, padding, alignment)?
- [ ] **Responsiveness**: Does the change break the layout on smaller screens (check `@media` blocks)?
- [ ] **Security**: Is dynamic data escaped (`htmlspecialchars`)?

## Verification Commands

Use these search patterns (VS Code Search or grep) to self-audit:

1.  **No Inline CSS**: Search `style="` inside `views/` → **Must be 0 results**.
2.  **No Internal Styles**: Search `<style` inside `views/` → **Must be 0 results**.
3.  **Relative Paths**: Search `href="/` or `src="/` → **Must be 0 results** (avoids breaking in subdirectories).

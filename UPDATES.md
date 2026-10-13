# 🚀 Doc Academy - Changelog & Recent Updates

This document summarizes the recent features, architectural enhancements, and API updates implemented across the platform.

---

## 1. 🖼️ Student Profile Picture Upload via API
Enhanced the student profile API to allow direct image file uploads from the mobile application.

* **Endpoint:** `PUT /api/v1/student/profile` (or `POST` with `_method=PUT`)
* **Controller:** [`AuthController::updateProfile`](file:///c:/Users/workstation/Documents/docac/app/Http/Controllers/Api/V1/Auth/AuthController.php)
* **Key Enhancements:**
  - **Direct File Upload:** Supports `multipart/form-data` uploads using field names `avatar`, `avatar_file`, `image`, or `photo` (up to 10MB).
  - **URL String Fallback:** Continues to accept direct image URLs as strings.
  - **Bunny Storage CDN Integration:** Automatically processes and uploads image files to Bunny CDN (`avatars/` directory) and stores the permanent URL.
  - **Full URLs in Responses:** Both `GET /api/v1/student/profile` (`me`) and `updateProfile` now return the full CDN asset URL in the `avatar` field.

---

## 2. ⚡ Bulk Quiz Questions Import (AI & Text Parser)
Added a bulk import feature allowing instructors to paste and import multiple MCQ questions at once from Word documents, PDFs, or ChatGPT.

* **Endpoint:** `POST /instructor/quizzes/{quiz}/questions/bulk`
* **Route Name:** `instructor.questions.bulk`
* **Controller:** [`InstructorQuizController::storeBulkQuestions`](file:///c:/Users/workstation/Documents/docac/app/Http/Controllers/Web/Instructor/InstructorQuizController.php)
* **Key Enhancements:**
  - **Smart Text Parser:** Parses questions written in Arabic (`أ، ب، ج، د`) and English (`A, B, C, D`), detecting options and correct answers marked with `*`, `[x]`, or `Answer: B` / `الإجابة: ب`.
  - **Real-Time Live Preview:** Instant client-side analysis and preview cards with custom checkmarks and point badges.
  - **Interactive Controls:** Instructors can click on any option in the preview to change the correct answer, remove individual questions, or adjust points.
  - **Quick Sample Templates:** 1-click Arabic (`🇸🇦`) and English (`🇬🇧`) sample presets for rapid testing.
  - **Atomic Transaction:** All questions and options are saved in a single database transaction.

---

## 3. 📂 Course Sections Management (Update & Delete)
Enabled full CRUD capabilities for course sections in the instructor dashboard.

* **Routes:**
  - `PUT /instructor/sections/{section}` (`instructor.sections.update`)
  - `DELETE /instructor/sections/{section}` (`instructor.sections.destroy`)
* **Controller:** [`InstructorCourseController`](file:///c:/Users/workstation/Documents/docac/app/Http/Controllers/Web/Instructor/InstructorCourseController.php)
* **View:** [`manage.blade.php`](file:///c:/Users/workstation/Documents/docac/resources/views/instructor/courses/manage.blade.php)
* **Key Enhancements:**
  - **Edit Section:** Edit section title via a dedicated modal.
  - **Delete Section:** Delete an entire section along with its lessons and quizzes with confirmation protection.

---

## 4. 📄 Enrolled Courses Pagination
Updated default pagination for student enrolled courses.

* **Endpoint:** `GET /api/v1/student/my-courses`
* **Controller:** [`CourseController::myCourses`](file:///c:/Users/workstation/Documents/docac/app/Http/Controllers/Api/V1/Courses/CourseController.php)
* **Key Enhancements:**
  - Default page size increased from `15` to **`1000`** items per page.
  - Added support for custom page size via `?per_page={number}` query parameter.

---

## 5. 🔔 Live Session Reminders & Push Notifications
Verified and confirmed automated push notification delivery for live events.

* **Architecture:**
  - **Console Command:** `php artisan live-sessions:send-reminders` in [`SendLiveSessionReminders.php`](file:///c:/Users/workstation/Documents/docac/app/Console/Commands/SendLiveSessionReminders.php)
  - **Scheduler:** Configured in [`routes/console.php`](file:///c:/Users/workstation/Documents/docac/routes/console.php) to execute every 10 minutes.
  - **FCM Service:** Modern FCM HTTP v1 implementation via [`FirebaseNotificationService.php`](file:///c:/Users/workstation/Documents/docac/app/Services/FirebaseNotificationService.php) with OAuth2 JWT bearer authentication.
  - **Timing:** Sends automated reminders **24 hours** and **1 hour** prior to scheduled live events.
  - **Credentials:** Verified `storage/app/firebase-credentials.json` securely protected in `.gitignore`.

---

## 6. 🌐 Dual Base URL Support (`v1` and `v2`)
Enabled seamless routing for both API versions (`/api/v1` and `/api/v2`) pointing to the unified controller handlers.

* **Route File:** [`routes/api.php`](file:///c:/Users/workstation/Documents/docac/routes/api.php)
* **Key Enhancements:**
  - All public and protected API routes are registered under both `v1` and `v2` prefixes.
  - Full backward and forward compatibility for mobile app releases calling either `https://domain.com/api/v1/...` or `https://domain.com/api/v2/...`.

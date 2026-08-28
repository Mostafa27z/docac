# توثيق مسارات نظام الطلاب (Student LMS API Documentation)

يحتوي هذا الملف على التفاصيل الكاملة لجميع مسارات (Endpoints) الطلاب في منصة LMS.

**Base URL:** `https://mediumspringgreen-aardvark-947551.hostingersite.com`

> **ملاحظة مهمة عن التوثيق (Authentication):**
> جميع المسارات المحمية تتطلب إرسال Bearer Token في ترويسة `Authorization`.
> يتم الحصول على هذا الـ Token من مسار تسجيل الدخول أو التسجيل.
> استبدل `{YOUR_TOKEN}` بالـ Token الفعلي في جميع الأمثلة أدناه.

> **ملاحظة مهمة عن حماية الأجهزة (Device Binding):**
> المسارات المحمية بمحتوى الكورسات تتطلب إرسال ترويسة `X-Device-ID` تحتوي على نفس الـ device_id الذي تم تسجيل الدخول به.
> بدون هذه الترويسة سيتم رفض الطلب برسالة "هذا الحساب مسجل على جهاز آخر" وحذف جميع الـ tokens.
> استبدل `{YOUR_DEVICE_ID}` بالـ device_id الفعلي في جميع الأمثلة أدناه.

---

## 1. المصادقة وتوثيق الجهاز (Authentication & Device Binding)

يتم ربط كل حساب طالب بـ `device_id` فريد يتم توليده وحفظه في الـ Local Storage الخاص بالتطبيق لمنع مشاركة الحسابات.

### تسجيل طالب جديد

* **المسار:** `POST /api/v1/student/register`
* **التوثيق:** غير مطلوب (Public)
* **حماية الجهاز:** غير مطلوبة

**cURL:**
```bash
curl --location --request POST 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/register' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--data-raw '{
    "name": "Mohamed Student",
    "email": "student@lms.com",
    "password": "password123",
    "password_confirmation": "password123",
    "device_id": "STUDENT-DEVICE-UUID-12345"
}'
```

**المُخرجات (Response 201 Created):**
```json
{
  "success": true,
  "message": "Registration successful.",
  "token": "1|student_auth_token...",
  "data": {
    "id": 2,
    "name": "Mohamed Student",
    "email": "student@lms.com",
    "role": "student",
    "status": "active",
    "active_device_id": "STUDENT-DEVICE-UUID-12345"
  }
}
```

---

### تسجيل الدخول والتحقق من الجهاز

* **المسار:** `POST /api/v1/student/login`
* **التوثيق:** غير مطلوب (Public)
* **حماية الجهاز:** غير مطلوبة (يتم التحقق داخلياً من الـ device_id المرسل في الـ body)
* **آلية الباك إند:**
  * إذا كان حقل `active_device_id` في قاعدة البيانات فارغاً: سيتم حفظ الـ `device_id` المرسل والسماح بالدخول.
  * إذا كان الـ `device_id` المرسل يطابق المسجل: يتم تسجيل الدخول وإصدار الـ Token.
  * إذا كان الـ `device_id` المرسل مختلفاً: يتم رفض الطلب بإرجاع خطأ `403 Forbidden`.

**cURL:**
```bash
curl --location --request POST 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/login' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--data-raw '{
    "email": "student@lms.com",
    "password": "password123",
    "device_id": "STUDENT-DEVICE-UUID-12345"
}'
```

**المُخرجات (Success 200 OK):**
```json
{
  "success": true,
  "message": "Login successful.",
  "token": "15|7lAaS0SPop3zQ7PIPZOxYL7OlcXeLbHPLLpgfcBU1daa0d66",
  "data": {
    "id": 2,
    "name": "Mohamed Student",
    "email": "student@lms.com",
    "role": "student",
    "status": "active",
    "active_device_id": "STUDENT-DEVICE-UUID-12345"
  }
}
```

**المُخرجات (Error 403 Forbidden):**
```json
{
  "success": false,
  "message": "هذا الحساب مسجل على جهاز آخر، يرجى التواصل مع الإدارة"
}
```

---

### تسجيل الخروج

* **المسار:** `POST /api/v1/student/logout`
* **التوثيق:** مطلوب (Bearer Token)
* **حماية الجهاز:** غير مطلوبة
* **ملاحظة:** لا يتم حذف الـ `active_device_id` من قاعدة البيانات لضمان عدم دخول الطالب من جهاز آخر.

**cURL:**
```bash
curl --location --request POST 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/logout' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {YOUR_TOKEN}'
```

**المُخرجات (200 OK):**
```json
{
  "success": true,
  "message": "Logged out successfully."
}
```

---

### تسجيل رمز جهاز الإشعارات (FCM Device Token)

* **المسار:** `POST /api/v1/student/device-token`
* **التوثيق:** مطلوب (Bearer Token)
* **حماية الجهاز:** غير مطلوبة

**cURL:**
```bash
curl --location --request POST 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/device-token' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {YOUR_TOKEN}' \
--data-raw '{
    "token": "fcm_device_token_abc_123...",
    "platform": "android"
}'
```

**المُخرجات (200 OK):**
```json
{
  "success": true,
  "message": "Device token registered successfully."
}
```

---

### عرض بيانات الملف الشخصي

* **المسار:** `GET /api/v1/student/profile`
* **التوثيق:** مطلوب (Bearer Token)
* **حماية الجهاز:** غير مطلوبة

**cURL:**
```bash
curl --location --request GET 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/profile' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {YOUR_TOKEN}'
```

**المُخرجات (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 2,
    "name": "Mohamed Student",
    "email": "student@lms.com",
    "phone": "+201111111111",
    "role": "student",
    "avatar": null,
    "status": "active",
    "active_device_id": "STUDENT-DEVICE-UUID-12345"
  }
}
```

---

### تحديث الملف الشخصي

* **المسار:** `PUT /api/v1/student/profile`
* **التوثيق:** مطلوب (Bearer Token)
* **حماية الجهاز:** غير مطلوبة
* **المُدخلات:** جميع الحقول اختيارية

**cURL:**
```bash
curl --location --request PUT 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/profile' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {YOUR_TOKEN}' \
--data-raw '{
    "name": "Mohamed New Name",
    "avatar": "https://lms.test/avatars/new.jpg",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}'
```

**المُخرجات (200 OK):**
```json
{
  "success": true,
  "message": "Profile updated successfully.",
  "data": {
    "id": 2,
    "name": "Mohamed New Name",
    "email": "student@lms.com",
    "role": "student",
    "avatar": "https://lms.test/avatars/new.jpg",
    "status": "active",
    "active_device_id": "STUDENT-DEVICE-UUID-12345"
  }
}
```

---

## البنرات والإعلانات (Banners)

### استرجاع قائمة البنرات الإعلانية النشطة

* **المسار:** `GET /api/v1/student/banners`
* **التوثيق:** غير مطلوب (Public)
* **حماية الجهاز:** غير مطلوبة

**cURL:**
```bash
curl --location --request GET 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/banners' \
--header 'Accept: application/json'
```

**المُخرجات (200 OK):**
```json
{
  "success": true,
  "message": "Banners retrieved successfully.",
  "data": [
    {
      "id": 1,
      "title": "خصم 20% على دورات الباطنة",
      "description": "استخدم كود الخصم عند الاشتراك للاستفادة من العرض لفترة محدودة",
      "image": "https://mediumspringgreen-aardvark-947551.hostingersite.com/storage/banners/1787413058_banner-1.png",
      "sort_order": 1,
      "created_at": "2026-08-23T14:00:00.000000Z"
    }
  ]
}
```

---

### استرجاع قائمة الإعلانات الترويجية النشطة (Ads)

* **المسار:** `GET /api/v1/student/ads`
* **التوثيق:** غير مطلوب (Public)
* **حماية الجهاز:** غير مطلوبة

**cURL:**
```bash
curl --location --request GET 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/ads' \
--header 'Accept: application/json'
```

**المُخرجات (200 OK):**
```json
{
  "success": true,
  "message": "Ads retrieved successfully.",
  "data": [
    {
      "id": 1,
      "title": "انضم إلى قناة التلجرام الرسمية",
      "description": "تابع آخر الأخبار والخصومات الحصرية لأكاديميتنا",
      "image": "https://mediumspringgreen-aardvark-947551.hostingersite.com/storage/ads/telegram-promo.png",
      "link": "https://t.me/example",
      "sort_order": 1,
      "created_at": "2026-08-27T22:00:00.000000Z"
    }
  ]
}
```

---

## 2. التصنيفات (Categories)

### استرجاع جميع التصنيفات الأساسية

* **المسار:** `GET /api/v1/student/categories`
* **التوثيق:** مطلوب (Bearer Token)
* **حماية الجهاز:** غير مطلوبة

**cURL:**
```bash
curl --location --request GET 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/categories' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {YOUR_TOKEN}'
```

**المُخرجات (200 OK):**
```json
{
  "success": true,
  "message": "Categories retrieved successfully.",
  "data": [
    {
      "id": 1,
      "name": "Cardiology (القلب والأوعية الدموية)",
      "image_url": "https://lms.test/categories/cardiology.jpg"
    }
  ]
}
```

---

### استرجاع التصنيفات الفرعية

* **المسار:** `GET /api/v1/student/categories/{category_id}/subcategories`
* **التوثيق:** مطلوب (Bearer Token)
* **حماية الجهاز:** غير مطلوبة

**cURL:**
```bash
curl --location --request GET 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/categories/1/subcategories' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {YOUR_TOKEN}'
```

**المُخرجات (200 OK):**
```json
{
  "success": true,
  "message": "Subcategories retrieved successfully.",
  "data": [
    {
      "id": 11,
      "category_id": 1,
      "name": "ECG Reading (قراءة رسم القلب)",
      "slug": "ecg-reading",
      "child_subcategories": [
        {
          "id": 101,
          "name": "ECG Advanced (رسم القلب المتقدم)",
          "slug": "ecg-advanced"
        }
      ]
    }
  ]
}
```

---

### استرجاع التصنيفات الفرعية الفرعية (المستوى الثالث - Child Subcategories)

* **المسار:** `GET /api/v1/student/subcategories/{subcategory_id}/child-subcategories`
* **التوثيق:** مطلوب (Bearer Token)
* **حماية الجهاز:** غير مطلوبة

**cURL:**
```bash
curl --location --request GET 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/subcategories/11/child-subcategories' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {YOUR_TOKEN}'
```

**المُخرجات (200 OK):**
```json
{
  "success": true,
  "message": "Child subcategories retrieved successfully.",
  "data": [
    {
      "id": 101,
      "subcategory_id": 11,
      "name": "ECG Advanced (رسم القلب المتقدم)",
      "slug": "ecg-advanced"
    }
  ]
}
```

---

## 3. الكورسات (Courses)

> **تنبيه:** جميع مسارات الكورسات محمية بـ middleware حماية الجهاز (`single.device`).
> يجب إرسال ترويسة `X-Device-ID` تحتوي على نفس الـ device_id الذي تم تسجيل الدخول به.
> بدون هذه الترويسة سيتم رفض الطلب وحذف جميع الـ tokens.

### استعراض الكورسات

* **المسار:** `GET /api/v1/student/courses`
* **التوثيق:** مطلوب (Bearer Token)
* **حماية الجهاز:** مطلوبة (X-Device-ID)
* **المدخلات (Query Params اختيارية):** `category_id`, `subcategory_id`, `child_subcategory_id`, `search`, `type`

**cURL (بدون فلترة):**
```bash
curl --location --request GET 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/courses' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {YOUR_TOKEN}' \
--header 'X-Device-ID: {YOUR_DEVICE_ID}'
```

**cURL (مع فلترة بالـ 3 مستويات من التصنيفات):**
```bash
curl --location --request GET 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/courses?category_id=1&subcategory_id=11&child_subcategory_id=101&type=recorded&search=cardiology' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {YOUR_TOKEN}' \
--header 'X-Device-ID: {YOUR_DEVICE_ID}'
```

**المُخرجات (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Cardiology Basics",
      "slug": "cardiology-basics",
      "description": "Introduction course to ECG...",
      "thumbnail": "courses/cardiology-basics.jpg",
      "type": "recorded",
      "price": 150.00
    }
  ]
}
```

---

### عرض تفاصيل كورس معين

* **المسار:** `GET /api/v1/student/courses/{course_id}`
* **التوثيق:** مطلوب (Bearer Token)
* **حماية الجهاز:** مطلوبة (X-Device-ID)

**cURL:**
```bash
curl --location --request GET 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/courses/1' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {YOUR_TOKEN}' \
--header 'X-Device-ID: {YOUR_DEVICE_ID}'
```

**المُخرجات (200 OK):**
```json
{
  "data": {
    "id": 1,
    "title": "Cardiology Basics",
    "description": "Introduction course to ECG...",
    "price": 150.00,
    "instructor": {
      "id": 1,
      "name": "Dr. Ahmed Ali"
    },
    "sections": [
      {
        "id": 1,
        "title": "Section 1: ECG Introduction",
        "lessons": [
          {
            "id": 1,
            "title": "Lesson 1: Introduction to Cardiac Cycles",
            "type": "video"
          }
        ]
      }
    ]
  }
}
```

---

### تسجيل الطالب في الكورس

* **المسار:** `POST /api/v1/student/courses/{course_id}/enroll`
* **التوثيق:** مطلوب (Bearer Token)
* **حماية الجهاز:** مطلوبة (X-Device-ID)

**cURL:**
```bash
curl --location --request POST 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/courses/1/enroll' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {YOUR_TOKEN}' \
--header 'X-Device-ID: {YOUR_DEVICE_ID}' \
--data-raw '{
    "code": "CARD-A1B2-C3D4"
}'
```

**المُخرجات (201 Created):**
```json
{
  "success": true,
  "message": "Successfully enrolled in course using activation code."
}
```

---

### استرجاع الكورسات المشترك بها الطالب

* **المسار:** `GET /api/v1/student/my-courses`
* **التوثيق:** مطلوب (Bearer Token)
* **حماية الجهاز:** مطلوبة (X-Device-ID)

**cURL:**
```bash
curl --location --request GET 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/my-courses' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {YOUR_TOKEN}' \
--header 'X-Device-ID: {YOUR_DEVICE_ID}'
```

**المُخرجات (200 OK):**
```json
{
  "success": true,
  "message": "Enrolled courses retrieved successfully.",
  "data": [
    {
      "id": 1,
      "title": "Cardiology Basics",
      "slug": "cardiology-basics",
      "description": "Introduction course to ECG...",
      "thumbnail": "courses/cardiology-basics.jpg",
      "type": "recorded",
      "price": 150.00,
      "overall_progress_percentage": 50.0
    }
  ]
}
```

---

### تفعيل كورس بكود التفعيل (بدون تحديد كورس)

* **المسار:** `POST /api/v1/courses/activate-with-code`
* **التوثيق:** مطلوب (Bearer Token)
* **حماية الجهاز:** غير مطلوبة

**cURL:**
```bash
curl --location --request POST 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/courses/activate-with-code' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {YOUR_TOKEN}' \
--data-raw '{
    "code": "CARD-A1B2-C3D4"
}'
```

**المُخرجات (200 OK):**
```json
{
  "success": true,
  "message": "Course activated successfully."
}
```

---

### استعلام الطالب عن الأقساط والمبالغ المدفوعة والمتبقية

* **المسار:** `GET /api/v1/student/installments`
* **التوثيق:** مطلوب (Bearer Token)
* **حماية الجهاز:** مطلوبة (X-Device-ID)
* **المدخلات (Query Params اختيارية):** `course_id` (تحديد كورس معين للاستعلام عنه)

**cURL (عرض جميع الكورسات والأقساط):**
```bash
curl --location --request GET 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/installments' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {YOUR_TOKEN}' \
--header 'X-Device-ID: {YOUR_DEVICE_ID}'
```

**cURL (مع تصفية لكورس معين):**
```bash
curl --location --request GET 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/installments?course_id=1' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {YOUR_TOKEN}' \
--header 'X-Device-ID: {YOUR_DEVICE_ID}'
```

**المُخرجات (200 OK):**
```json
{
  "success": true,
  "message": "Installments retrieved successfully.",
  "data": [
    {
      "enrollment_id": 1,
      "course": {
        "id": 1,
        "title": "Cardiology Basics",
        "thumbnail": "courses/cardiology-basics.jpg",
        "type": "recorded"
      },
      "total_price": 1000.0,
      "paid_amount": 600.0,
      "remaining_amount": 400.0,
      "payment_status": "partially_paid",
      "payments_history": [
        {
          "id": 1,
          "amount": 300.0,
          "notes": "الدفعة الأولى",
          "created_at": "2026-08-15T12:00:00.000000Z"
        },
        {
          "id": 2,
          "amount": 300.0,
          "notes": "الدفعة الثانية",
          "created_at": "2026-08-18T12:00:00.000000Z"
        }
      ]
    }
  ]
}
```

---

## 4. المحاضرات ومعدل التقدم (Lectures & Progress)

> **تنبيه:** جميع مسارات المحاضرات محمية بـ middleware حماية الجهاز (`single.device`) والتحقق من اشتراك الطالب في الكورس (`course.enrollment`).
> يجب إرسال ترويسة `X-Device-ID` في كل طلب.

### قائمة محاضرات الكورس مقسمة

* **المسار:** `GET /api/v1/student/courses/{course_id}/lectures`
* **التوثيق:** مطلوب (Bearer Token)
* **حماية الجهاز:** مطلوبة (X-Device-ID)

**cURL:**
```bash
curl --location --request GET 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/courses/1/lectures' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {YOUR_TOKEN}' \
--header 'X-Device-ID: {YOUR_DEVICE_ID}'
```

**المُخرجات (200 OK):**
```json
{
  "success": true,
  "message": "Course lectures retrieved successfully.",
  "data": [
    {
      "id": 1,
      "title": "Section 1: ECG Introduction",
      "lessons": [
        {
          "id": 1,
          "title": "Lesson 1: Introduction to Cardiac Cycles",
          "type": "video",
          "video_duration_seconds": 600,
          "is_preview": true,
          "watched_seconds": 300,
          "is_completed": false
        }
      ]
    }
  ]
}
```

---

### جلب بيانات المحاضرة لعرضها

* **المسار:** `GET /api/v1/student/lectures/{lecture_id}`
* **التوثيق:** مطلوب (Bearer Token)
* **حماية الجهاز:** مطلوبة (X-Device-ID)

**cURL:**
```bash
curl --location --request GET 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/lectures/1' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {YOUR_TOKEN}' \
--header 'X-Device-ID: {YOUR_DEVICE_ID}'
```

**المُخرجات (200 OK):**
```json
{
  "data": {
    "id": 1,
    "title": "Lesson 1: Introduction to Cardiac Cycles",
    "description": "...",
    "type": "video",
    "video_url": "https://secure-bunny-cdn-signed-url.com/..."
  }
}
```

---

### تحديث سجل مشاهدة الطالب

* **المسار:** `PUT /api/v1/student/lectures/{lecture_id}/progress`
* **التوثيق:** مطلوب (Bearer Token)
* **حماية الجهاز:** مطلوبة (X-Device-ID)

**cURL:**
```bash
curl --location --request PUT 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/lectures/1/progress' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {YOUR_TOKEN}' \
--header 'X-Device-ID: {YOUR_DEVICE_ID}' \
--data-raw '{
    "watched_seconds": 300,
    "is_completed": true
}'
```

**المُخرجات (200 OK):**
```json
{
  "success": true,
  "message": "Progress tracked successfully.",
  "data": {
    "watched_seconds": 300,
    "percentage": 50.00,
    "last_position_seconds": 300,
    "completed_at": "2026-08-15 12:00:00"
  }
}
```

---

## 5. الملفات والمرفقات (Attachments/Files)

### عرض قائمة الملفات المتاحة للتحميل

* **المسار:** `GET /api/v1/student/courses/{course_id}/files`
* **التوثيق:** مطلوب (Bearer Token)
* **حماية الجهاز:** مطلوبة (X-Device-ID)

**cURL:**
```bash
curl --location --request GET 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/courses/1/files' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {YOUR_TOKEN}' \
--header 'X-Device-ID: {YOUR_DEVICE_ID}'
```

**المُخرجات (200 OK):**
```json
{
  "success": true,
  "message": "Files retrieved successfully.",
  "data": [
    {
      "id": 1,
      "title": "Cardiology Basics PDF Handout",
      "file_name": "handout.pdf",
      "mime_type": "application/pdf",
      "file_size_bytes": 1048576,
      "download_url": "https://secure-signed-url.com/handout.pdf?token=..."
    }
  ]
}
```

---

### تحميل الملف مباشرة برابط آمن

* **المسار:** `GET /api/v1/student/files/{file_id}/download`
* **التوثيق:** مطلوب (Bearer Token)
* **حماية الجهاز:** مطلوبة (X-Device-ID)

**cURL:**
```bash
curl --location --request GET 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/files/1/download' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {YOUR_TOKEN}' \
--header 'X-Device-ID: {YOUR_DEVICE_ID}'
```

**المُخرجات (200 OK):**
```json
{
  "success": true,
  "message": "Secure download link generated.",
  "download_url": "https://secure-signed-url.com/handout.pdf?token=..."
}
```

---

## 6. البث المباشر (Live Events)

### استرجاع مواعيد وروابط البث المباشر

* **المسار:** `GET /api/v1/student/courses/{course_id}/live-events`
* **التوثيق:** مطلوب (Bearer Token)
* **حماية الجهاز:** مطلوبة (X-Device-ID)

**cURL:**
```bash
curl --location --request GET 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/courses/1/live-events' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {YOUR_TOKEN}' \
--header 'X-Device-ID: {YOUR_DEVICE_ID}'
```

**المُخرجات (200 OK):**
```json
{
  "success": true,
  "message": "Live sessions retrieved successfully.",
  "data": [
    {
      "id": 1,
      "course_id": 1,
      "title": "Live Q&A Session",
      "description": "...",
      "start_at": "2026-08-17T12:00:00.000000Z",
      "end_at": "2026-08-17T14:00:00.000000Z",
      "meeting_provider": "zoom",
      "meeting_url": "https://zoom.us/j/9876543210",
      "meeting_id": "987 654 3210",
      "status": "scheduled"
    }
  ]
}
```

---

## 7. الامتحانات (Exams / MCQs)

### استرجاع امتحانات الكورس الكاملة (Get Course Quizzes)

* **المسار:** `GET /api/v1/student/courses/{course_id}/quizzes`
* **التوثيق:** مطلوب (Bearer Token)
* **حماية الجهاز:** مطلوبة (X-Device-ID)

**cURL:**
```bash
curl --location --request GET 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/courses/1/quizzes' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {YOUR_TOKEN}' \
--header 'X-Device-ID: {YOUR_DEVICE_ID}'
```

**المُخرجات (200 OK):**
```json
{
  "success": true,
  "message": "Course quizzes retrieved successfully.",
  "data": [
    {
      "id": 1,
      "lesson_id": 1,
      "title": "ECG Reading Basics MCQ Quiz",
      "pass_percentage": "50.00",
      "time_limit_minutes": null,
      "attempts_allowed": null,
      "created_at": "2026-08-28T12:00:00.000000Z",
      "updated_at": "2026-08-28T12:00:00.000000Z",
      "lesson": {
        "id": 1,
        "section_id": 1,
        "title": "ECG Leads Waveform",
        "sort_order": 1
      }
    }
  ]
}
```

---

### استرجاع أسئلة MCQ الخاصة بالمحاضرة

* **المسار:** `GET /api/v1/student/lectures/{lecture_id}/quiz`
* **التوثيق:** مطلوب (Bearer Token)
* **حماية الجهاز:** مطلوبة (X-Device-ID)
* **ملاحظة:** لا يتم إرجاع `is_correct` لحماية الإجابات قبل الحل.

**cURL:**
```bash
curl --location --request GET 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/lectures/1/quiz' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {YOUR_TOKEN}' \
--header 'X-Device-ID: {YOUR_DEVICE_ID}'
```

**المُخرجات (200 OK):**
```json
{
  "success": true,
  "message": "Quiz retrieved successfully.",
  "data": {
    "id": 1,
    "title": "ECG Reading Basics MCQ Quiz",
    "questions": [
      {
        "id": 1,
        "question_text": "What does the P-wave represent on an ECG?",
        "options": [
          {
            "id": 1,
            "question_id": 1,
            "option_text": "Atrial depolarization"
          }
        ]
      }
    ]
  }
}
```

---

### إرسال إجابات الطالب

* **المسار:** `POST /api/v1/student/lectures/{lecture_id}/quiz/submit`
* **التوثيق:** مطلوب (Bearer Token)
* **حماية الجهاز:** مطلوبة (X-Device-ID)

**cURL:**
```bash
curl --location --request POST 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/lectures/1/quiz/submit' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {YOUR_TOKEN}' \
--header 'X-Device-ID: {YOUR_DEVICE_ID}' \
--data-raw '{
    "answers": [
        {
            "question_id": 1,
            "option_id": 1
        }
    ]
}'
```

**المُخرجات (200 OK):**
```json
{
  "success": true,
  "message": "Quiz submitted successfully.",
  "score": 100.0,
  "passed": true,
  "correct_answers": [
    {
      "question_id": 1,
      "correct_option_id": 1,
      "explanation": "Correct option verified."
    }
  ]
}
```

---

## 8. المحادثات (Chat System)

### استرجاع غرف الشات النشطة

* **المسار:** `GET /api/v1/student/chats`
* **التوثيق:** مطلوب (Bearer Token)
* **حماية الجهاز:** مطلوبة (X-Device-ID)

**cURL:**
```bash
curl --location --request GET 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/chats' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {YOUR_TOKEN}' \
--header 'X-Device-ID: {YOUR_DEVICE_ID}'
```

**المُخرجات (200 OK):**
```json
{
  "success": true,
  "message": "Conversations retrieved successfully.",
  "data": [
    {
      "id": 1,
      "course_id": 1,
      "student_id": 2,
      "instructor_id": 1,
      "last_message_at": "2026-08-15T12:00:00.000000Z"
    }
  ]
}
```

---

### استرجاع سجل الرسائل الخاص بكورس معين

* **المسار:** `GET /api/v1/student/courses/{course_id}/chat`
* **التوثيق:** مطلوب (Bearer Token)
* **حماية الجهاز:** مطلوبة (X-Device-ID)

**cURL:**
```bash
curl --location --request GET 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/courses/1/chat' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {YOUR_TOKEN}' \
--header 'X-Device-ID: {YOUR_DEVICE_ID}'
```

**المُخرجات (200 OK):**
```json
{
  "success": true,
  "message": "Messages retrieved successfully.",
  "data": [
    {
      "id": 1,
      "conversation_id": 1,
      "sender_id": 1,
      "message_text": "Hello Student! Welcome to the course.",
      "type": "text",
      "attachment_path": null
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 20,
    "total": 1
  }
}
```

---

### إرسال رسالة جديدة للمدرس

* **المسار:** `POST /api/v1/student/courses/{course_id}/chat/messages`
* **التوثيق:** مطلوب (Bearer Token)
* **حماية الجهاز:** مطلوبة (X-Device-ID)
* **المُدخلات:**
  * `message_text` (مطلوب - نص الرسالة)
  * `attachment` (اختياري - ملف مرفق)

**cURL (نص فقط):**
```bash
curl --location --request POST 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/courses/1/chat/messages' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {YOUR_TOKEN}' \
--header 'X-Device-ID: {YOUR_DEVICE_ID}' \
--data-raw '{
    "message_text": "Thank you Doctor."
}'
```

**cURL (مع مرفق - استخدام form-data):**
```bash
curl --location --request POST 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/student/courses/1/chat/messages' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer {YOUR_TOKEN}' \
--header 'X-Device-ID: {YOUR_DEVICE_ID}' \
--form 'message_text="Thank you Doctor."' \
--form 'attachment=@"/path/to/file.pdf"'
```

**المُخرجات (201 Created):**
```json
{
  "success": true,
  "message": "Message sent successfully.",
  "data": {
    "id": 2,
    "conversation_id": 1,
    "sender_id": 2,
    "message_text": "Thank you Doctor.",
    "type": "text",
    "attachment_path": null
  }
}
```

---

## 9. بيانات التواصل والدعم (Support & Contacts)

### استرجاع قنوات التواصل والدعم الفني

* **المسار:** `GET /api/v1/contacts`
* **التوثيق:** غير مطلوب (Public API)
* **حماية الجهاز:** غير مطلوبة

**cURL:**
```bash
curl --location --request GET 'https://mediumspringgreen-aardvark-947551.hostingersite.com/api/v1/contacts' \
--header 'Accept: application/json'
```

**المُخرجات (200 OK):**
```json
{
  "success": true,
  "message": "Contact details retrieved successfully.",
  "data": {
    "facebook_url": "https://www.facebook.com/share/1YMP1n8ySD/",
    "youtube_url": "https://youtube.com/@docacademy23?si=TIrT50jyVpi5DYn3",
    "whatsapp_number": "01090214254",
    "telegram_number": "01090214254",
    "telegram_username": "DocAcademyy"
  }
}
```

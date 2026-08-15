# لتوثيق مسارات نظام الطلاب (Student LMS API Documentation)

يحتوي هذا الملف على التفاصيل الكاملة لجميع مسارات (Endpoints) الطلاب في منصة LMS والمدخلات والمخرجات المتوقعة لكل طلب مع آليات حماية ومزامنة الأجهزة.

---

## 1. المصادقة وتوثيق الجهاز (Authentication & Device Binding)

يتم ربط كل حساب طالب بـ `device_id` فريد يتم توليده وحفظه في الـ Local Storage الخاص بالتطبيق لمنع مشاركة الحسابات.

### تسجيل طالب جديد
* **المسار:** `POST /api/v1/student/register`
* **المُدخلات (Payload):**
  ```json
  {
    "name": "Mohamed Student",
    "email": "student@lms.com",
    "password": "password123",
    "password_confirmation": "password123",
    "device_id": "device_a_123"
  }
  ```
* **المُخرجات (Response 201 Created):**
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
      "active_device_id": "device_a_123"
    }
  }
  ```

### تسجيل الدخول والتحقق من الجهاز
* **المسار:** `POST /api/v1/student/login`
* **المُدخلات (Payload):**
  ```json
  {
    "email": "student@lms.com",
    "password": "password123",
    "device_id": "device_a_123"
  }
  ```
* **آلية الباك إند:**
  * إذا كان حقل `active_device_id` في قاعدة البيانات فارغاً: سيتم حفظ الـ `device_id` المرسل والسماح بالدخول.
  * إذا كان الـ `device_id` المرسل يطابق المسجل: يتم تسجيل الدخول وإصدار الـ Token.
  * إذا كان الـ `device_id` المرسل مختلفاً: يتم رفض الطلب بإرجاع خطأ `403 Forbidden` برسالة:
    `"هذا الحساب مسجل على جهاز آخر، يرجى التواصل مع الإدارة"`.
* **المُخرجات (Success 200 OK):**
  ```json
  {
    "success": true,
    "message": "Login successful.",
    "token": "2|student_auth_token...",
    "data": {
      "id": 2,
      "name": "Mohamed Student",
      "email": "student@lms.com",
      "role": "student",
      "status": "active",
      "active_device_id": "device_a_123"
    }
  }
  ```
* **المُخرجات (Error 403 Forbidden):**
  ```json
  {
    "success": false,
    "message": "هذا الحساب مسجل على جهاز آخر، يرجى التواصل مع الإدارة"
  }
  ```

### تسجيل الخروج
* **المسار:** `POST /api/v1/student/logout`
* **ملاحظة:** لا يتم حذف الـ `active_device_id` من قاعدة البيانات لضمان عدم دخول الطالب من جهاز آخر.
* **المُخرجات (200 OK):**
  ```json
  {
    "success": true,
    "message": "Logged out successfully."
  }
  ```

### عرض بيانات الملف الشخصي
* **المسار:** `GET /api/v1/student/profile`
* **المُخرجات (200 OK):**
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
      "active_device_id": "device_a_123"
    }
  }
  ```

### تحديث الملف الشخصي
* **المسار:** `PUT /api/v1/student/profile`
* **المُدخلات (Payload):** (جميع الحقول اختيارية)
  ```json
  {
    "name": "Mohamed New Name",
    "avatar": "https://lms.test/avatars/new.jpg",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
  }
  ```
* **المُخرجات (200 OK):**
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
      "active_device_id": "device_a_123"
    }
  }
  ```

---

## 2. التصنيفات (Categories)

### استرجاع جميع التصنيفات الأساسية
* **المسار:** `GET /api/v1/student/categories`
* **المُخرجات (200 OK):**
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

### استرجاع التصنيفات الفرعية
* **المسار:** `GET /api/v1/student/categories/{category_id}/subcategories`
* **المُخرجات (200 OK):**
  ```json
  {
    "success": true,
    "message": "Subcategories retrieved successfully.",
    "data": [
      {
        "id": 11,
        "category_id": 1,
        "name": "ECG Reading (قراءة رسم القلب)"
      }
    ]
  }
  ```

---

## 3. الكورسات (Courses)

### استعراض الكورسات
* **المسار:** `GET /api/v1/student/courses`
* **المدخلات (Query Params):** `category_id`, `subcategory_id`, `search`, `type`
* **المُخرجات (200 OK):**
  ```json
  {
    "data": [
      {
        "id": 1,
        "title": "Cardiology Basics",
        "slug": "cardiology-basics",
        "description": "Introduction course to ECG...",
        "thumbnail": "courses/cardiology-basics.jpg",
        "type": "recorded"
      }
    ]
  }
  ```

### عرض تفاصيل كورس معين
* **المسار:** `GET /api/v1/student/courses/{course_id}`
* **المُخرجات (200 OK):**
  ```json
  {
    "data": {
      "id": 1,
      "title": "Cardiology Basics",
      "description": "Introduction course to ECG...",
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

### تسجيل الطالب في الكورس
* **المسار:** `POST /api/v1/student/courses/{course_id}/enroll`
* **المُدخلات (Payload):**
  ```json
  {
    "code": "CARD-A1B2-C3D4"
  }
  ```
* **المُخرجات (201 Created):**
  ```json
  {
    "success": true,
    "message": "Successfully enrolled in course using activation code."
  }
  ```

### استرجاع الكورسات المشترك بها الطالب
* **المسار:** `GET /api/v1/student/my-courses`
* **المُخرجات (200 OK):**
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
        "overall_progress_percentage": 50.0
      }
    ]
  }
  ```

---

## 4. المحاضرات ومعدل التقدم (Lectures & Progress)

### قائمة محاضرات الكورس مقسمة
* **المسار:** `GET /api/v1/student/courses/{course_id}/lectures`
* **المُخرجات (200 OK):**
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

### جلب بيانات المحاضرة لعرضها
* **المسار:** `GET /api/v1/student/lectures/{lecture_id}`
* **المُخرجات (200 OK):**
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

### تحديث سجل مشاهدة الطالب
* **المسار:** `PUT /api/v1/student/lectures/{lecture_id}/progress`
* **المُدخلات (Payload):**
  ```json
  {
    "watched_seconds": 300,
    "is_completed": true
  }
  ```
* **المُخرجات (200 OK):**
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
* **المُخرجات (200 OK):**
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

### تحميل الملف مباشرة برابط آمن
* **المسار:** `GET /api/v1/student/files/{file_id}/download`
* **المُخرجات (200 OK):**
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
* **المُخرجات (200 OK):**
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

### استرجاع أسئلة MCQ الخاصة بالمحاضرة
* **المسار:** `GET /api/v1/student/lectures/{lecture_id}/quiz`
* **المُخرجات (200 OK):** (لاحظ عدم وجود `is_correct` لحماية الإجابات قبل الحل)
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

### إرسال إجابات الطالب
* **المسار:** `POST /api/v1/student/lectures/{lecture_id}/quiz/submit`
* **المُدخلات (Payload):**
  ```json
  {
    "answers": [
      {
        "question_id": 1,
        "option_id": 1
      }
    ]
  }
  ```
* **المُخرجات (200 OK):**
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
* **المُخرجات (200 OK):**
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

### استرجاع سجل الرسائل الخاص بكورس معين
* **المسار:** `GET /api/v1/student/courses/{course_id}/chat`
* **المُخرجات (200 OK):**
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

### إرسال رسالة جديدة للمدرس
* **المسار:** `POST /api/v1/student/courses/{course_id}/chat/messages`
* **المُدخلات (Payload):**
  * `message_text` (مطلوب - نص الرسالة)
  * `attachment` (اختياري - ملف مرفق)
* **المُخرجات (201 Created):**
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

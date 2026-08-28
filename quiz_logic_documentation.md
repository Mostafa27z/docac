# توثيق نظام الامتحانات والاختبارات (Quiz Logic Documentation)

يغطي هذا الملف البنية الكاملة لنظام الامتحانات والاختبارات التقييمية (Quizzes & MCQs) في منصة LMS، بما يشمل بنية قاعدة البيانات، علاقات النماذج (Models)، القواعد البرمجية لحساب الدرجات، والمسارات الخاصة بالطلاب والمحاضرين.

---

## 1. بنية قاعدة البيانات وعلاقات النماذج (Database & Models Schema)

يعتمد نظام الامتحانات على 5 جداول رئيسية مترابطة في قاعدة البيانات لتنظيم الامتحانات، الأسئلة، الخيارات، المحاولات، وإجابات الطلاب.

```mermaid
erDiagram
    Course ||--o{ CourseSection : contains
    CourseSection ||--o{ Lesson : contains
    Lesson ||--o? Quiz : "has one"
    Quiz ||--o{ Question : contains
    Quiz ||--o{ QuizAttempt : has
    Question ||--o{ QuestionOption : has
    QuizAttempt ||--o{ QuizAnswer : records
    QuestionOption ||--o{ QuizAnswer : selected
```

### 1. نموذج الامتحان `Quiz`
يمثل إعدادات الامتحان المرتبط بدرس معين.
* **الحقول (Fields):**
  * `id` (Primary Key)
  * `lesson_id` (Foreign Key -> `lessons`)
  * `title` (string): عنوان الامتحان.
  * `pass_percentage` (decimal): نسبة النجاح المطلوبة (مثال: `50.00` تعني 50%).
  * `time_limit_minutes` (integer|null): الوقت المحدد بالدقائق (اختياري).
  * `attempts_allowed` (integer|null): عدد المحاولات المسموح بها للطالب (اختياري).
* **العلاقات (Relationships):**
  * `lesson()`: belongsTo `Lesson` (الدرس المرتبط بالامتحان).
  * `questions()`: hasMany `Question` (الأسئلة المدرجة في الامتحان).
  * `attempts()`: hasMany `QuizAttempt` (محاولات الطلاب لحل الامتحان).

### 2. نموذج السؤال `Question`
يمثل الأسئلة التابعة للامتحان.
* **الحقول (Fields):**
  * `id` (Primary Key)
  * `quiz_id` (Foreign Key -> `quizzes`)
  * `question_text` (text): نص السؤال.
  * `type` (string): نوع السؤال (القيمة الافتراضية: `mcq`).
  * `points` (integer): عدد النقاط/الدرجات المخصصة للسؤال (القيمة الافتراضية: `1`).
* **العلاقات (Relationships):**
  * `quiz()`: belongsTo `Quiz`.
  * `options()`: hasMany `QuestionOption` (الخيارات المتاحة للإجابة).

### 3. نموذج خيار السؤال `QuestionOption`
يمثل الاختيارات الخاصة بكل سؤال من نوع MCQ.
* **الحقول (Fields):**
  * `id` (Primary Key)
  * `question_id` (Foreign Key -> `questions`)
  * `option_text` (string): نص الخيار.
  * `is_correct` (boolean): يحدد ما إذا كان هذا الخيار هو الإجابة الصحيحة.

### 4. نموذج محاولة حل الامتحان `QuizAttempt`
يسجل تفاصيل محاولات الطلاب لحل الامتحانات ونتائجهم.
* **الحقول (Fields):**
  * `id` (Primary Key)
  * `quiz_id` (Foreign Key -> `quizzes`)
  * `student_id` (Foreign Key -> `users`)
  * `score` (decimal): النتيجة المئوية النهائية المحرزة (من 0 إلى 100).
  * `passed` (boolean): يحدد ما إذا كان الطالب قد تجاوز نسبة النجاح المطلوبة.
  * `started_at` (timestamp): وقت بدء المحاولة.
  * `submitted_at` (timestamp|null): وقت تسليم المحاولة.
* **العلاقات (Relationships):**
  * `quiz()`: belongsTo `Quiz`.
  * `student()`: belongsTo `User`.
  * `answers()`: hasMany `QuizAnswer` (الإجابات التي تم تسجيلها في هذه المحاولة).

### 5. نموذج إجابة السؤال `QuizAnswer`
يسجل الخيار الذي حدده الطالب لكل سؤال في محاولة معينة.
* **الحقول (Fields):**
  * `id` (Primary Key)
  * `quiz_attempt_id` (Foreign Key -> `quiz_attempts`)
  * `question_id` (Foreign Key -> `questions`)
  * `selected_option_id` (Foreign Key -> `question_options`)
  * `is_correct` (boolean): حالة الإجابة (صحيحة / خاطئة).

---

## 2. آلية الحساب وقواعد العمل (Business Logic & Score Calculation)

عند قيام الطالب بتسليم إجابات الامتحان (`submit`):

1. **التحقق من المحاولات المتاحة:**
   إذا كان للامتحان حد أقصى للمحاولات (`attempts_allowed`)، يتم التحقق من عدد المحاولات السابقة للطالب للتأكد من عدم تجاوزه للحد المسموح به.
2. **تصحيح الإجابات وحساب النسبة:**
   * يتم المرور على مصفوفة الإجابات المرسلة من الطالب.
   * لكل سؤال، يتم مقارنة المعرّف المختار `selected_option_id` مع الإجابة الصحيحة المسجلة في جدول خيارات الأسئلة `is_correct = true`.
   * يتم احتساب عدد الإجابات الصحيحة.
   * تُحسب النسبة المئوية للدرجة على النحو التالي:
     $$\text{Score Percentage} = \left( \frac{\text{Correct Answers Count}}{\text{Total Questions Count}} \right) \times 100$$
3. **تحديد حالة النجاح:**
   * إذا كانت النسبة المئوية المحسوبة أكبر من أو تساوي نسبة النجاح المحددة للامتحان (`pass_percentage`)، يتم تمييز المحاولة كـ ناجحة (`passed = true`).
4. **تحديث تقدم الطالب والدورة الدراسية تلقائياً:**
   إذا نجح الطالب في الامتحان وكان هذا الامتحان مرتبطاً بدرس معين:
   * يتم تحديث حالة تقدم الطالب في هذا الدرس (`LessonProgress`) تلقائياً إلى مكتمل (`completed_at = now()`, `percentage = 100.00`).
   * يتم إعادة حساب النسبة الكلية لتقدم الطالب في الكورس بناءً على نسبة المحاضرات والامتحانات المكتملة وتحديث حقل `progress_percentage` في جدول التسجيلات `CourseEnrollment`.

---

## 3. مسارات واجهة برمجة تطبيقات الطلاب (Student API Endpoints)

جميع المسارات أدناه تتطلب إرسال Bearer Token في ترويسة `Authorization` وترويسة الجهاز `X-Device-ID`.

### 1. استرجاع امتحانات الكورس الكاملة
يُرجع قائمة بجميع الامتحانات الخاصة بكورس معين.
* **المسار:** `GET /api/v1/student/courses/{course_id}/quizzes`
* **التوثيق:** مطلوب (Bearer Token)
* **حماية الجهاز:** مطلوبة (X-Device-ID)

**المُخرجات (200 OK):**
```json
{
  "success": true,
  "message": "Course quizzes retrieved successfully.",
  "data": [
    {
      "id": 1,
      "lesson_id": 12,
      "title": "اختبار فسيولوجيا القلب الأساسي",
      "pass_percentage": "60.00",
      "time_limit_minutes": 30,
      "attempts_allowed": 3,
      "created_at": "2026-08-28T12:00:00.000000Z",
      "lesson": {
        "id": 12,
        "section_id": 3,
        "title": "رسم القلب وفهم الموجات",
        "sort_order": 2
      }
    }
  ]
}
```

---

### 2. استرجاع أسئلة الامتحان الخاص بدرس معين
يُرجع تفاصيل الامتحان والأسئلة والخيارات المتاحة للطالب (مع حجب حقل `is_correct` لحماية الإجابات).
* **المسار:** `GET /api/v1/student/lectures/{lecture_id}/quiz`
* **التوثيق:** مطلوب (Bearer Token)
* **حماية الجهاز:** مطلوبة (X-Device-ID)

**المُخرجات (200 OK):**
```json
{
  "success": true,
  "message": "Quiz retrieved successfully.",
  "data": {
    "id": 1,
    "lesson_id": 12,
    "title": "اختبار فسيولوجيا القلب الأساسي",
    "pass_percentage": "60.00",
    "time_limit_minutes": 30,
    "attempts_allowed": 3,
    "questions": [
      {
        "id": 5,
        "question_text": "ما هي الموجة المسئولة عن انقباض الأذينين؟",
        "points": 1,
        "options": [
          {
            "id": 15,
            "question_id": 5,
            "option_text": "P Wave"
          },
          {
            "id": 16,
            "question_id": 5,
            "option_text": "QRS Complex"
          },
          {
            "id": 17,
            "question_id": 5,
            "option_text": "T Wave"
          }
        ]
      }
    ]
  }
}
```

---

### 3. إرسال وتصحيح إجابات الطالب
يستقبل إجابات الطالب، يقوم بتصحيحها، وتسجيل المحاولة في قاعدة البيانات، ثم إرجاع النتيجة وتفاصيل الإجابات الصحيحة.
* **المسار:** `POST /api/v1/student/lectures/{lecture_id}/quiz/submit`
* **التوثيق:** مطلوب (Bearer Token)
* **حماية الجهاز:** مطلوبة (X-Device-ID)
* **المدخلات (Request Body):**
```json
{
    "answers": [
        {
            "question_id": 5,
            "option_id": 15
        }
    ]
}
```

**المُخرجات (200 OK):**
```json
{
  "success": true,
  "message": "Quiz submitted successfully.",
  "score": 100,
  "passed": true,
  "correct_answers": [
    {
      "question_id": 5,
      "correct_option_id": 15,
      "explanation": "Correct option verified."
    }
  ]
}
```

---

## 4. مسارات إدارية للمحاضر (Instructor Web Routes)

تُستخدم من خلال لوحة تحكم المحاضر لإدارة وإنشاء الامتحانات وربطها بالدروس.

### 1. إنشاء امتحان لدرس من نوع (Quiz)
* **المسار:** `POST /instructor/lessons/{lesson_id}/quizzes`
* **الهدف:** تفعيل إعدادات الامتحان لدرس تم إنشاؤه مسبقاً وتحديد نوعه كـ امتحان.

### 2. إنشاء امتحان وربطه بأي درس قائم (فيديو أو نصي)
* **المسار:** `POST /instructor/courses/{course_id}/quizzes`
* **الهدف:** إضافة امتحان جديد وتحديد الدرس المرتبط به من قائمة المنسدلة لدروس الكورس الحالية.

### 3. إضافة سؤال اختيار من متعدد للامتحان
* **المسار:** `POST /instructor/quizzes/{quiz_id}/questions`
* **المدخلات (Form Data):**
  * `question_text`: نص السؤال.
  * `points`: درجة السؤال.
  * `correct_option_index`: مؤشر الخيار الصحيح (0, 1, 2).
  * `options`: مصفوفة النصوص للخيارات المتاحة.

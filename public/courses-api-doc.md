# 📚 Courses API Documentation

## 1. Get List of Courses

Retrieves a paginated list of published courses. Supports search and filtering by category, subcategory, child subcategory, and course type.

### Endpoint Details
- **HTTP Method:** `GET`
- **URL Path:** `/api/v1/student/courses`
- **Authentication:** `Bearer Token` (Sanctum)

---

### Request Headers
| Header | Type | Value | Required |
| :--- | :--- | :--- | :--- |
| `Authorization` | `string` | `Bearer {your_token}` | Yes |
| `Accept` | `string` | `application/json` | Yes |

---

### Query Parameters
| Parameter | Type | Description | Example |
| :--- | :--- | :--- | :--- |
| `search` | `string` | Search query for title or description | `biology` |
| `category_id` | `integer` | Filter by category ID | `1` |
| `subcategory_id` | `integer` | Filter by subcategory ID | `3` |
| `child_subcategory_id` | `integer` | Filter by child subcategory ID | `5` |
| `type` | `string` | Course type (`recorded`, `live`, `mixed`) | `recorded` |
| `page` | `integer` | Page number for pagination | `1` |

---

### Response Example (`200 OK`)

```json
{
  "data": [
    {
      "id": 1,
      "title": "Introduction to Biology",
      "slug": "introduction-to-biology",
      "description": "Learn the fundamentals of biological science.",
      "thumbnail": "https://example.com/storage/courses/thumbnails/bio101.jpg",
      "type": "recorded",
      "status": "published",
      "allowed": true,
      "published_at": "2026-09-01 10:00:00",
      "price": 150.00,
      "category": {
        "id": 1,
        "name": "Science",
        "slug": "science"
      },
      "subcategory": {
        "id": 3,
        "name": "Biology",
        "slug": "biology"
      },
      "child_subcategory": {
        "id": 5,
        "name": "Cell Biology",
        "slug": "cell-biology"
      },
      "instructor": {
        "id": 10,
        "name": "Dr. Sarah Smith",
        "email": "sarah.smith@example.com",
        "role": "instructor",
        "avatar_url": "https://example.com/storage/avatars/sarah.jpg"
      },
      "enrollment": {
        "status": "active",
        "progress_percentage": 45.5,
        "enrolled_at": "2026-09-10 14:20:00",
        "completed_at": null
      }
    }
  ],
  "links": {
    "first": "https://example.com/api/v1/student/courses?page=1",
    "last": "https://example.com/api/v1/student/courses?page=3",
    "prev": null,
    "next": "https://example.com/api/v1/student/courses?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 3,
    "per_page": 15,
    "to": 15,
    "total": 42
  },
  "success": true,
  "message": "Courses retrieved successfully."
}
```

---

## 2. Get Single Course Details

Retrieves details for a specific published course, including its sections and lectures.

### Endpoint Details
- **HTTP Method:** `GET`
- **URL Path:** `/api/v1/student/courses/{course_id}`
- **Authentication:** `Bearer Token` (Sanctum)

---

### Request Headers
| Header | Type | Value | Required |
| :--- | :--- | :--- | :--- |
| `Authorization` | `string` | `Bearer {your_token}` | Yes |
| `Accept` | `string` | `application/json` | Yes |

---

### Path Parameters
| Parameter | Type | Description |
| :--- | :--- | :--- |
| `course_id` | `integer` | Unique ID of the course |

---

### Response Example (`200 OK`)

```json
{
  "data": {
    "id": 1,
    "title": "Introduction to Biology",
    "slug": "introduction-to-biology",
    "description": "Comprehensive course covering cellular structure and genetics.",
    "thumbnail": "https://example.com/storage/courses/thumbnails/bio101.jpg",
    "type": "recorded",
    "status": "published",
    "allowed": true,
    "published_at": "2026-09-01 10:00:00",
    "price": 150.00,
    "category": {
      "id": 1,
      "name": "Science",
      "slug": "science"
    },
    "subcategory": {
      "id": 3,
      "name": "Biology",
      "slug": "biology"
    },
    "child_subcategory": {
      "id": 5,
      "name": "Cell Biology",
      "slug": "cell-biology"
    },
    "instructor": {
      "id": 10,
      "name": "Dr. Sarah Smith",
      "email": "sarah.smith@example.com",
      "role": "instructor",
      "avatar_url": "https://example.com/storage/avatars/sarah.jpg"
    },
    "sections": [
      {
        "id": 12,
        "title": "Section 1: Cell Structure",
        "order": 1,
        "lessons": [
          {
            "id": 101,
            "title": "Lecture 1: Introduction to Cells",
            "type": "video",
            "duration": 900,
            "is_free": true
          }
        ]
      }
    ],
    "enrollment": null
  },
  "success": true,
  "message": "Course details retrieved successfully."
}
```

---

### Error Responses

#### `404 Not Found` (Course not published or non-existent)
```json
{
  "success": false,
  "message": "Course not found or is in draft mode."
}
```

#### `401 Unauthorized`
```json
{
  "message": "Unauthenticated."
}
```

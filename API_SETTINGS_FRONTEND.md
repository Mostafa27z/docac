# ⚙️ App Settings API Documentation (Frontend Handover)

This document contains all the necessary details for the Frontend / Mobile development team to integrate the new **App Settings** feature.

---

## 📌 Overview

The **Settings API** allows the client application (Web, Android, iOS) to fetch global platform settings and feature flags (such as the `enable` boolean toggle).

Future platform settings will also be delivered through this same endpoint.

---

## 🌐 Endpoint Details

### **1. Base URL & Endpoints**
* **HTTP Method:** `GET`
* **Route:** `/api/v1/settings`
* **Aliases supported:**
  * `/api/v1/app-settings`
  * `/api/v2/settings`
  * `/api/v2/app-settings`
* **Authentication:** **Public** (No Bearer token required. Can be called at app startup/splash screen).

---

## 🔄 Quick Browser Toggle Endpoint (One-Click)

You can toggle the `enable` boolean value directly from any web browser URL bar without authentication or payload:

* **Direct Browser URL:**
  * `http://YOUR_DOMAIN/settings/toggle`
  * or `http://YOUR_DOMAIN/api/v1/settings/toggle`
* **HTTP Methods:** `GET` / `POST`
* **Behavior:** Every time this URL is visited / entered in the browser, the `enable` flag automatically flips (`false` ⇄ `true`) and returns the updated state immediately.

#### Example Response after clicking/visiting in browser:
```json
{
  "success": true,
  "message": "Setting 'enable' successfully toggled to true (enabled).",
  "data": {
    "enable": true
  }
}
```

---

## 📥 Request Headers

```http
Accept: application/json
Content-Type: application/json
```

---

## 📤 Response Specification

### **Status Code:** `200 OK`

```json
{
  "success": true,
  "message": "Settings retrieved successfully.",
  "data": {
    "enable": false
  }
}
```

### **Response Fields:**

| Field | Type | Default | Description |
| :--- | :--- | :--- | :--- |
| `success` | `boolean` | `true` | Indicates whether the request succeeded. |
| `message` | `string` | `"Settings retrieved successfully."` | Informational status message. |
| `data` | `object` | `{ ... }` | Object containing all active platform settings. |
| `data.enable` | `boolean` | `false` | Feature toggle flag (`true` or `false`). |

> **Note:** Additional setting keys may be introduced in the `data` object in future updates. Frontend implementations should handle extra keys gracefully without breaking.

---

## 💻 Frontend Code Examples

### 1. **JavaScript / TypeScript (Fetch API)**

```typescript
interface AppSettings {
  enable: boolean;
  [key: string]: any;
}

interface SettingsResponse {
  success: boolean;
  message: string;
  data: AppSettings;
}

async function fetchAppSettings(): Promise<AppSettings> {
  try {
    const response = await fetch('https://YOUR_API_DOMAIN/api/v1/settings', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const result: SettingsResponse = await response.json();
    return result.data;
  } catch (error) {
    console.error('Failed to load app settings:', error);
    // Safe fallback default
    return { enable: false };
  }
}

// Example Usage:
fetchAppSettings().then(settings => {
  if (settings.enable) {
    console.log('Feature is enabled');
  } else {
    console.log('Feature is disabled');
  }
});
```

---

### 2. **Axios Example**

```javascript
import axios from 'axios';

export const getAppSettings = async () => {
  try {
    const response = await axios.get('/api/v1/settings');
    return response.data.data;
  } catch (error) {
    console.error('Error fetching settings:', error);
    return { enable: false };
  }
};
```

---

### 3. **Flutter / Dart Example**

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class AppSettings {
  final bool enable;

  AppSettings({required this.enable});

  factory AppSettings.fromJson(Map<String, dynamic> json) {
    return AppSettings(
      enable: json['enable'] as bool? ?? false,
    );
  }
}

Future<AppSettings> fetchAppSettings() async {
  final uri = Uri.parse('https://YOUR_API_DOMAIN/api/v1/settings');

  try {
    final response = await http.get(
      uri,
      headers: {'Accept': 'application/json'},
    );

    if (response.statusCode == 200) {
      final jsonResponse = jsonDecode(response.body);
      return AppSettings.fromJson(jsonResponse['data']);
    } else {
      throw Exception('Failed to load settings');
    }
  } catch (e) {
    // Return safe default when offline or on error
    return AppSettings(enable: false);
  }
}
```

---

## 💡 Best Practices for Frontend

1. **Call on Startup / Splash Screen:** Fetch this endpoint when initializing the application to configure feature visibility or app behavior immediately.
2. **Offline Fallback:** If the network request fails or times out, safely default `enable` to `false`.
3. **Extensibility:** The backend automatically forwards any future settings added to this table inside the `data` object.


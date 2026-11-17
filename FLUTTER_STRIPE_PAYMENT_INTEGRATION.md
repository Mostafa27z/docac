# DocAC — In-App Course Purchases & Stripe Integration Guide

This guide is designed for the **Flutter Mobile Developer** to integrate in-app course purchases supporting **Apple Pay**, **Google Pay**, and **Credit / Debit Cards** via Stripe.

---

## 1. System Overview

Students can get enrolled into courses in **two ways**:

1. **Admin Manual Enrollment (Dashboard)**:
   - Admin adds a student directly from the web admin panel.
   - The student does **not** need to perform any payment in the app.
   - The course immediately appears in the student's courses list:  
     `GET /api/v1/student/my-courses`

2. **In-App Purchase via Stripe**:
   - Students tap **"Enroll / Buy Now"** on any paid course.
   - The app initializes the **Stripe PaymentSheet** (which automatically displays **Apple Pay** on iOS, **Google Pay** on Android, or Card input).
   - Once payment is successful, the app notifies the backend to activate enrollment instantly.

---

## 2. API Endpoints Reference

Base URL: `https://your-domain.com/api/v1` (or your current server IP/domain)  
All student endpoints require `Authorization: Bearer <STUDENT_SANCTUM_TOKEN>`.

### Endpoint A: Initialize Payment Sheet
Generates the Stripe PaymentIntent, Ephemeral Key, and Customer ID.

* **Method**: `POST`
* **URL**: `/student/courses/{course_id}/payment-sheet`
* **Headers**:
  ```http
  Authorization: Bearer <TOKEN>
  Accept: application/json
  Content-Type: application/json
  ```
* **Success Response (`200 OK`)**:
  ```json
  {
    "success": true,
    "message": "Payment sheet parameters generated successfully.",
    "data": {
      "paymentIntentClientSecret": "pi_3Ucs..._secret_...",
      "paymentIntentId": "pi_3Ucs...",
      "ephemeralKeySecret": "ek_test_...",
      "customerId": "cus_...",
      "publishableKey": "pk_test_51UcqQH46PTpY0HHH1NSgHA4gYbg3YnIsZqBT9NqWs4lK6XV32pLyGHlWs0O9g5jryqtJFH36mIKaXwMqiXanL1tf00IgLEfvJZ",
      "merchantDisplayName": "DocAC Academy",
      "amount": 49.99,
      "currency": "usd",
      "course": {
        "id": 6,
        "title": "Clinical Cardiology",
        "price": 49.99
      }
    }
  }
  ```
* **Error Responses**:
  - `400 Bad Request`: If already enrolled (`"You are already enrolled in this course."`) or course is free.
  - `401 Unauthorized`: Token missing or expired.
  - `404 Not Found`: Course doesn't exist.

---

### Endpoint B: Confirm Payment & Activate Enrollment
Validates the transaction with Stripe servers and activates the student's access.

* **Method**: `POST`
* **URL**: `/student/courses/{course_id}/confirm-payment`
* **Headers**:
  ```http
  Authorization: Bearer <TOKEN>
  Accept: application/json
  Content-Type: application/json
  ```
* **Request Body**:
  ```json
  {
    "payment_intent_id": "pi_3Ucs..."
  }
  ```
* **Success Response (`200 OK`)**:
  ```json
  {
    "success": true,
    "message": "Payment confirmed and enrollment activated successfully!",
    "data": {
      "enrollment_id": 28,
      "course_id": 6,
      "status": "active",
      "payment_status": "fully_paid",
      "enrolled_at": "2026-11-17T19:26:03.000000Z"
    }
  }
  ```

---

## 3. Flutter Setup & Configuration

### Step 1: Add Dependency
In your Flutter app's `pubspec.yaml`:
```yaml
dependencies:
  flutter:
    sdk: flutter
  flutter_stripe: ^11.4.0 # or latest version
  http: ^1.2.0            # or dio
```
Run:
```bash
flutter pub get
```

---

### Step 2: Android Configuration

1. **Use `FlutterFragmentActivity`**:
   Open `android/app/src/main/kotlin/.../MainActivity.kt` and ensure it extends `FlutterFragmentActivity`:
   ```kotlin
   package com.example.docac

   import io.flutter.embedding.android.FlutterFragmentActivity

   class MainActivity: FlutterFragmentActivity() {
   }
   ```

2. **Set Theme**:
   In `android/app/src/main/res/values/styles.xml`, ensure your theme uses a Material Components theme (required by Stripe):
   ```xml
   <style name="NormalTheme" parent="Theme.MaterialComponents.DayNight.NoActionBar">
       <item name="android:windowBackground">?android:colorBackground</item>
   </style>
   ```

3. **Enable Google Pay (Optional)**:
   In `android/app/src/main/AndroidManifest.xml` inside `<application>`:
   ```xml
   <meta-data
       android:name="com.google.android.gms.wallet.api.enabled"
       android:value="true" />
   ```

---

### Step 3: iOS Configuration (Apple Pay & Stripe)

1. **Set iOS Deployment Target**:
   In `ios/Podfile`:
   ```ruby
   platform :ios, '13.0'
   ```

2. **Apple Pay Capability in Xcode**:
   - Open `ios/Runner.xcworkspace` in Xcode.
   - Go to **Signing & Capabilities** -> click **+ Capability** -> select **Apple Pay**.
   - Check or add your Merchant Identifier (e.g. `merchant.com.docac.app`).
   - *Note*: You register your Merchant ID in your [Apple Developer Account](https://developer.apple.com/account/resources/identifiers/list/merchantId) and link it in the [Stripe Apple Pay Settings](https://dashboard.stripe.com/settings/payments/apple_pay).

---

## 4. Flutter Implementation Code

### A. Stripe Initialization (in `main.dart`)
Initialize Stripe before running `runApp`:

```dart
import 'package:flutter/material.dart';
import 'package:flutter_stripe/flutter_stripe.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Test Publishable Key provided by backend
  Stripe.publishableKey = 'pk_test_51UcqQH46PTpY0HHH1NSgHA4gYbg3YnIsZqBT9NqWs4lK6XV32pLyGHlWs0O9g5jryqtJFH36mIKaXwMqiXanL1tf00IgLEfvJZ';
  
  // Set your Apple Pay Merchant ID (configured in Xcode & Stripe Dashboard)
  Stripe.merchantIdentifier = 'merchant.com.docac.app';
  await Stripe.instance.applySettings();

  runApp(const MyApp());
}
```

---

### B. Payment Service (`stripe_payment_service.dart`)

```dart
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter_stripe/flutter_stripe.dart';
import 'package:http/http.dart' as http;

class StripePaymentService {
  final String baseUrl;
  final String userToken;

  StripePaymentService({
    required this.baseUrl,
    required this.userToken,
  });

  /// Purchases a course using Stripe PaymentSheet (Card, Apple Pay, Google Pay)
  Future<bool> buyCourse({
    required BuildContext context,
    required int courseId,
  }) async {
    try {
      // 1. Request Payment Sheet parameters from Laravel backend
      final sheetResponse = await http.post(
        Uri.parse('$baseUrl/student/courses/$courseId/payment-sheet'),
        headers: {
          'Authorization': 'Bearer $userToken',
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      );

      final sheetData = jsonDecode(sheetResponse.body);

      if (sheetResponse.statusCode != 200 || sheetData['success'] != true) {
        final message = sheetData['message'] ?? 'Could not start payment.';
        _showSnackbar(context, message, isError: true);
        return false;
      }

      final data = sheetData['data'];
      final clientSecret = data['paymentIntentClientSecret'];
      final ephemeralKeySecret = data['ephemeralKeySecret'];
      final customerId = data['customerId'];
      final paymentIntentId = data['paymentIntentId'];
      final merchantName = data['merchantDisplayName'] ?? 'DocAC Academy';
      final currency = data['currency'] ?? 'usd';

      // 2. Initialize PaymentSheet
      await Stripe.instance.initPaymentSheet(
        paymentSheetParameters: SetupPaymentSheetParameters(
          paymentIntentClientSecret: clientSecret,
          customerEphemeralKeySecret: ephemeralKeySecret,
          customerId: customerId,
          merchantDisplayName: merchantName,
          style: ThemeMode.system,
          // Apple Pay Configuration
          applePay: const PaymentSheetApplePay(
            merchantCountryCode: 'US',
          ),
          // Google Pay Configuration
          googlePay: PaymentSheetGooglePay(
            merchantCountryCode: 'US',
            currencyCode: currency,
            testEnv: true, // Set to false in production
          ),
        ),
      );

      // 3. Present the native Stripe bottom sheet to the user
      await Stripe.instance.presentPaymentSheet();

      // 4. Payment succeeded in Stripe SDK! Now confirm and enroll on backend
      final confirmResponse = await http.post(
        Uri.parse('$baseUrl/student/courses/$courseId/confirm-payment'),
        headers: {
          'Authorization': 'Bearer $userToken',
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: jsonEncode({
          'payment_intent_id': paymentIntentId,
        }),
      );

      final confirmData = jsonDecode(confirmResponse.body);

      if (confirmResponse.statusCode == 200 && confirmData['success'] == true) {
        _showSnackbar(context, 'Successfully enrolled in course!');
        return true;
      } else {
        _showSnackbar(context, confirmData['message'] ?? 'Failed to activate enrollment.', isError: true);
        return false;
      }

    } on StripeException catch (e) {
      if (e.error.code == FailureCode.Canceled) {
        // User dismissed the payment sheet - no action needed
        _showSnackbar(context, 'Payment cancelled.');
      } else {
        _showSnackbar(context, 'Payment error: ${e.error.localizedMessage}', isError: true);
      }
      return false;
    } catch (e) {
      _showSnackbar(context, 'An unexpected error occurred: $e', isError: true);
      return false;
    }
  }

  void _showSnackbar(BuildContext context, String message, {bool isError = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: isError ? Colors.red : Colors.green,
      ),
    );
  }
}
```

---

### C. Example Usage on Course Details Screen

```dart
ElevatedButton.icon(
  icon: const Icon(Icons.payment),
  label: Text('Enroll Now (\$${course.price})'),
  onPressed: () async {
    final paymentService = StripePaymentService(
      baseUrl: 'https://your-api.com/api/v1',
      userToken: currentUserToken,
    );

    // Show loading indicator
    final bool success = await paymentService.buyCourse(
      context: context,
      courseId: course.id,
    );

    if (success) {
      // 1. Refresh "My Courses" list
      // 2. Unlock lectures & quizzes
      Navigator.pushReplacementNamed(context, '/course-lessons', arguments: course.id);
    }
  },
)
```

---

## 5. Testing with Stripe Test Cards

When the PaymentSheet opens in test mode:
* **Test Card Number**: `4242 4242 4242 4242`
* **Expiry Date**: Any future date (e.g., `12/28`)
* **CVC**: Any 3 digits (e.g., `123`)
* **Postal Code**: Any valid code (e.g., `90210`)

For **Apple Pay** in Test Mode:
* Add a Sandbox test card to your Apple Wallet in the iOS Simulator or TestFlight sandbox account.

---

## 6. App Store Review Note (Important)

If you submit to the **Apple App Store**:
* Apple Guideline 3.1.1 states that purchases for purely digital courses consumed entirely within the mobile app may require Apple StoreKit (In-App Purchase).
* However, Stripe with Apple Pay is standard and approved when:
  1. The course involves **live interactive sessions** / webinars with instructors.
  2. Multiplatform accounts where students can also access content via the web portal.
  3. B2B / corporate accounts or real-world events.
* Both options (Admin manual subscription + In-App Stripe checkout) are fully supported by this backend.

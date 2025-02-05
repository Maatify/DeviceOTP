
[pkg]: <https://packagist.org/packages/maatify/device-otp>
[pkg-stats]: <https://packagist.org/packages/maatify/device-otp/stats>

# **Device OTP Manager Library**

![**Maatify.dev**](https://www.Maatify.dev/assets/img/img/maatify_logo_white.svg)
---
[![Current version](https://img.shields.io/packagist/v/maatify/device-otp)][pkg]
[![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/maatify/device-otp)][pkg]
[![Monthly Downloads](https://img.shields.io/packagist/dm/maatify/device-otp)][pkg-stats]
[![Total Downloads](https://img.shields.io/packagist/dt/maatify/device-otp)][pkg-stats]
[![Stars](https://img.shields.io/packagist/stars/maatify/device-otp)](https://github.com/maatify/DeviceOTP/stargazers)

---

## **Table of Contents**
- [Overview](#overview)
- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
    - [1. Instantiate the OTP Manager using the Factory](#1-instantiate-the-otp-manager-using-the-factory)
    - [2. Requesting an OTP](#2-requesting-an-otp)
    - [3. Confirming an OTP](#3-confirming-an-otp)
    - [4. Check for Pending OTP Requests If its need](#4-check-for-pending-otp-requests-if-its-need)
    - [5. Troubleshooting Common Issues](#5-troubleshooting-common-issues)
- [Configuration](#configuration)
- [Architecture Overview](#architecture-overview)
- [Error Codes](#error-codes)
- [Requesting OTP Codes with Error Explanations](#requesting-otp-codes-with-error-explanations)
- [How OTP Request Validation Works](#how-otp-request-validation-works)
- [Confirming OTP Codes with Error Explanations](#confirming-otp-codes-with-error-explanations)
- [How OTP Verification Works](#how-otp-verification-works)
- [Extending the Library](#extending-the-library)
- [Security Considerations](#security-considerations)
- [Contributing](#contributing)
- [License](#license)
- [Contact](#contact)

---

## **Overview**
The `Device OTP Manager` library is a robust, secure OTP (One-Time Password) management system that handles OTP requests and verifications for various recipient roles and devices. It integrates OTP creation, retry policies, expiration management, and device-specific role checking using **PHP Data Objects (PDO)** and **MySQL** as the backend.

---

## **Features**
- Secure OTP generation using `random_int` and fallback encryption.
- Flexible retry handling and configurable delays to control abuse.
- OTP role and device-specific limits.
- OTP expiry and validation handling.
- Interface-driven OTP hashing and verification using `OTPEncryptionInterface`.
- Supports various OTP delivery methods: SMS, Email, WhatsApp, Telegram.
- Secure OTP generation and storage with custom hashing mechanisms.


## **Installation**

1. Option 1: Clone or download the project manually
    ```shell
    composer require maatify/device-otp
    ```

2. If cloning manually, download and install dependencies:
    ```bash
    git clone https://github.com/Maatify/DeviceOTP.git  
    cd DeviceOTP  
    composer install  
    ```

3. Set up the database table using this SQL schema:
    ```sql
   CREATE TABLE `ct_otp_code` (
    `otp_id` INT AUTO_INCREMENT PRIMARY KEY,
    `recipient_type_id` INT NOT NULL,
    `recipient_id` INT NOT NULL,
    `app_type_id` INT NOT NULL,
    `device_id` VARCHAR(255) NOT NULL,
    `code` VARCHAR(255) NOT NULL,
    `time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expiry` INT NOT NULL,
    `otp_sender_type_id` INT NOT NULL,
    `is_success` TINYINT(1) DEFAULT 0,
    KEY (`recipient_id`, `device_id`, `is_success`)
   );
    ```

---

## **Usage**
### 1. **Instantiate the OTP Manager using the Factory**
You can create an instance of `OTPManager` using the factory method with default or custom settings:

```php
use Maatify\AppController\Enums\EnumAppTypeId;
use Maatify\OTPManager\Enums\OtpSenderTypeIdEnum;
use Maatify\OTPManager\Enums\RecipientTypeIdEnum;
use Maatify\OTPManager\OTPManagerFactory;  

$pdo = new PDO('mysql:host=localhost;dbname=your_database', 'username', 'password');  

$otpManager = OTPManagerFactory::create(  
    pdo: $pdo,  
    otpEncryption: new OTPEncryption(),  
    tableName: 'ct_otp_code',  
    recipientTypeId: RecipientTypeIdEnum::Customer,  
    appTypeId: EnumAppTypeId::Web,  
    otpSenderTypeId: OtpSenderTypeIdEnum::SMS  
); 
```

### 2. **Requesting an OTP**
To request a new OTP for a recipient and device:

```php
$response = $otpManager->requestOTP(recipientId: 1234, deviceId: 'device_001');  

if ($response['status'] === 'success') {
    echo "OTP: " . $response['otp']; // ⚠️ Warning: Never expose OTPs in production. The example shown is for development purposes only.
} else {
    echo "Error: " . $response['message'];
}
```

### 3. **Confirming an OTP**
To confirm the OTP provided by the user:
```php
$response = $otpManager->confirmOTP($recipientId = 1234, $otpCode = '123456', $deviceId = 'device_001');

if ($response['status'] === 'success') {
    echo $response['message'];  // Display success message
} else {
    echo $response['message'];  // Display failure message
}
```

### 4. **Check for Pending OTP Requests If its need**

The `isCodePendingExist()` method helps you determine if a new OTP can be sent or if the user needs to wait.

```php
$result = $otpManager->isCodePendingExist($recipientId, $deviceId);

if ($result['pending']) {
    echo "Please wait {$result['waiting_seconds']} seconds before requesting a new OTP.";
} else {
    // Proceed to send a new OTP
    $response = $otpManager->requestOTP($recipientId, $deviceId);
    echo $response['message'];
}
```

#### **Response Examples:**

#### 1. **When the user needs to wait**
```json
{
    "pending": true,
    "waiting_seconds": 120
}
```

#### 2. **When the user can't request a new OTP**
```json
{
    "pending": true,
    "waiting_seconds": 0
}
```

#### 3. **When the user can request a new OTP**
```json
{
    "pending": false,
    "waiting_seconds": 0
}
```

---

### **5. Troubleshooting Common Issues**
Here are solutions to some common issues when using the OTP Manager:
- [Error Code 429 or 430 (Too Many Requests)](#error-code-429-or-430-too-many-requests)
- [OTP Verification Fails with 401 (Invalid OTP)](#otp-verification-fails-with-401-invalid-otp)
- [OTP Expiry Issues (410 Expired)](#otp-expiry-issues-410-expired)

#### **Error Code 429 or 430 (Too Many Requests)**
**Cause:** You may be hitting the maximum allowed pending OTP requests for the recipient or device.  
**Solution:**
- Check the `$maxRolePendingOTPs` setting in the configuration.
- Adjust the limits based on your application’s traffic or business requirements.
- Consider increasing retry delays to balance OTP generation requests.

#### **OTP Verification Fails with 401 (Invalid OTP)**
**Cause:** The OTP provided by the user does not match the one stored in the database.  
**Solution:**
- Double-check that you’re hashing and comparing the OTP correctly using the implementation of the `OTPEncryptionInterface`.
- Ensure you are validating against the latest OTP (not an expired or previously used one).

#### **OTP Expiry Issues (`410` Expired)**
**Cause:** The OTP’s expiration time may be set too low, causing it to expire before the user can input it.  
**Solution:**
- Increase the `$expiry_of_code` parameter in the configuration. For example:
```php
$otpManager = OTPManagerFactory::create(
    pdo: $pdo,
    expiry_of_code: 300  // Set expiration to 5 minutes
);
```

#### **Getting a database connection error.**
**Cause:** Incorrect database credentials or the database service is down.  
**Solution:**
- Verify your `$pdo` connection string, username, and password.
- Ensure the database server is running and accessible.

#### **OTP not being sent (no SMS or email received).**
**Cause:** There may be an issue with the configured OTP sender service (e.g., SMS or email).  
**Solution:**
- Ensure that the selected sender type (e.g., `SMS`, `Email`) is properly configured.
- Check for any errors in the sender service logs or API responses.
- If your primary delivery method (e.g., SMS) fails, consider implementing a backup delivery method such as email or push notifications to ensure that the user receives the OTP.

---

## **Configuration**
When creating the OTP manager using the factory, you can customize the following parameters:

| **Parameter**          | **Default Value** | **Description**                                                            |
|------------------------|-------------------|----------------------------------------------------------------------------|
| `$tableName`           | `'ct_otp_code'`   | Database table to store and manage OTPs.                                   |
| `$recipientTypeId`     | `Customer`        | The role of the recipient (e.g., Customer, Admin, Merchant).               |
| `$appTypeId`           | `Web`             | The type of app requesting the OTP (Web, Mobile, etc.).                    |
| `$otpSenderTypeId`     | `SMS`             | The method used to send the OTP (SMS, Email).                              |
| `$retryDelays`         | `[60, 180, 300]`  | Custom retry delays between successive OTP requests.                       |
| `$maxRolePendingOTPs`  | `5`               | The maximum number of pending OTPs allowed per role.                       |
| `$maxTimeForDenied`    | `6000`            | Maximum time before permanently denying requests after too many failures.  |
| `$expiry_of_code`      | `180` seconds     | Time before an OTP expires.                                                |

---

## **Architecture Overview**
This library follows a clean, modular structure to handle OTP functionality effectively:

### **1. Core Classes**
- **`OTPManager`**:  
  Manages OTP requests, validations, and responses. Handles error scenarios such as pending requests, retry delays, and expired or invalid OTPs.

- **`OTPManagerFactory`**:  
  Provides a factory method to instantiate the `OTPManager` with custom configurations.

- **`OTPAppDeviceRepository`**:  
  Handles database interactions for storing, retrieving, and validating OTP codes.

### **2. Helper Classes**
- **`OTPAppDeviceRoleChecker`**:  
  Ensures that role-based and device-specific limits on OTP requests are respected.

- **`OTPAppDeviceRetryHandler`**:  
  Implements retry logic and enforces delay policies. Determines whether a user can retry an OTP request and calculates the wait time.

### **3. Enums**
- **`RecipientTypeIdEnum`**:  
  Defines recipient types such as:
    - `Customer`
    - `Admin`
    - `Merchant`
    - `Channel`

- **`OtpSenderTypeIdEnum`**:  
  Defines OTP sender types such as:
    - `SMS`
    - `Email`
    - `WhatsApp`
    - `Telegram`

---

## **Error Codes**
The library provides detailed error codes when an OTP request or verification fails. You can use these codes to handle errors programmatically and provide user-friendly feedback in your application.

| **Error Code** | **Description**                                                  |
|----------------|------------------------------------------------------------------|
| `430`          | Too many pending OTP requests for this device.                   |
| `429`          | Too many pending OTP requests for this recipient.                |
| `400`          | Retry time has not been reached.                                 |
| `410`          | OTP code has expired.                                            |
| `401`          | OTP code is invalid.                                             |
| `404`          | No OTP found for the given recipient and device combination.     |

### **Handling Error Codes:**
You can handle the errors programmatically based on the returned code from the response:

#### **Requesting OTP Codes with Error Explanations**

When requesting an OTP using the `requestOTP` method, the library validates conditions such as pending requests, retry delays, and role-based limits before sending the OTP. It returns appropriate status codes and error messages in case of issues.

##### Error Example
Here's an example of a response when a user has exceeded the maximum OTP requests:
```php
// Example response when the user has hit the maximum pending OTP requests.
[
'status' => 'error',
'code' => 429,
'error' => 'E002',
'message' => 'Too many pending OTP requests for this recipient.',
'waiting_seconds' => 0,
]
```
#### **Response Codes and Explanations**
| **Response Code** | **Error Code** | **Description**                                                             |
|-------------------|----------------|-----------------------------------------------------------------------------|
| `200`             | -              | OTP request successful, OTP has been generated and sent.                    |
| `429`             | `E002`         | Too many pending OTP requests for the recipient.                            |
| `430`             | `E001`         | Too many pending OTP requests for the specific device.                      |
| `400`             | `E004`         | Retry time has not been reached. Please wait before requesting again.       |
| `500`             | -              | Internal server error (e.g., issues with OTP generation or database).       |

---
Note: Internal error codes (e.g., `E001`) are mapped to external response codes (e.g., `429`). These codes can be customized in your application logic if needed.


---

## **How OTP Request Validation Works**

Before sending a new OTP, the library performs several checks to ensure that the request is valid and compliant with the configured limits and retry policies. This helps prevent abuse and ensures efficient OTP management.

### **Step-by-Step Process**

1. **Checking role-based and device-specific limits:**
    - The library checks the number of **pending OTP requests** for the given recipient (`recipientId`) and device (`deviceId`).
    - It ensures that the number of pending requests does not exceed the configured limits:
        - **429 (E002):** Too many pending OTPs for the recipient.
        - **430 (E001):** Too many pending OTPs for the device.

2. **Enforcing retry delays:**
    - The library checks if the **required waiting time** since the last OTP request has passed.
    - If the time elapsed is less than the required delay, it returns a `400` (E004) response with the remaining wait time (`waiting_seconds`).

3. **Generating the OTP:**
    - If all checks pass, the library generates a **secure OTP** using `random_int()` (with fallback to a custom generator if needed).
    - The OTP is hashed using the `OTPEncryption` class before being stored in the database.

4. **Saving the OTP in the database:**
    - The OTP, recipient information, and expiry time are stored in the database for future verification.

   example:
    ```mysql
    INSERT INTO ct_otp_code (recipient_id, device_id, code, expiry, otp_sender_type_id, is_success)
    VALUES (1234, 'device_001', 'hashed_otp_here', UNIX_TIMESTAMP(NOW()) + 180, 1, 0);
    
    ```
5. **Returning the response:**
    - The response includes the OTP (for testing or development purposes), the expiry time, and the time users must wait before requesting another OTP.

### **Flowchart of OTP Request Validation**

1. Request to **requestOTP**
2. Check pending OTPs for `recipientId` and `deviceId`
3. **Exceeds pending limits?**
    - **Yes:** Return `429` or `430` error
    - **No:** Proceed to next step
4. Check if retry delay has been met
5. **Is retry allowed?**
    - **No:** Return `400` with wait time
    - **Yes:** Proceed to next step
6. Generate OTP and store it in the database
7. Return `200` (Success) with OTP and retry wait time

#### **Example Flowchart**
```plaintext
1. Receive OTP Request       →  2. Check Limits  
    ↓                                ↓  
3. Enforce Retry Delays       →  4. Generate OTP  
    ↓                                ↓  
5. Hash and Store OTP         →  6. Respond with Status  
```

#### **Summary of Possible Responses**
| **Response Code** | **Error Code** | **Description**                                                             |
|-------------------|----------------|-----------------------------------------------------------------------------|
| `200`             | -              | OTP request successful, OTP has been generated and sent.                    |
| `429`             | `E002`         | Too many pending OTP requests for the recipient.                            |
| `430`             | `E001`         | Too many pending OTP requests for the specific device.                      |
| `400`             | `E004`         | Retry time has not been reached. Please wait before requesting again.       |
| `500`             | -              | Internal server error (e.g., issues with OTP generation or database).       |


##### **Example:**
You can call the `requestOTP` method to generate and send an OTP:
```php
$response = $otpManager->requestOTP($recipientId = 1234, $deviceId = 'device_001');

switch ($response['code']) {
    case 200:
        echo "OTP Sent: " . $response['otp'];
        echo "Please wait " . $response['waiting_seconds'] . " seconds before retrying.";
        break;
    case 429:
        echo "Error: " . $response['message'];  // "Too many pending OTP requests for this recipient."
        break;
    case 430:
        echo "Error: " . $response['message'];  // "Too many pending OTP requests for this device."
        break;
    case 400:
        echo "Error: " . $response['message'];  // "Please wait X seconds before retrying."
        break;
    default:
        echo "Unexpected error: " . $response['message'];
}
```

---

## **Confirming OTP Codes with Error Explanations**

When a user submits an OTP for confirmation, the library validates the code and returns an appropriate response code. The `confirmOTP` method checks if the OTP is valid, expired, or incorrect, and returns one of the following error codes:

### **Response Codes and Explanations**
| **Response Code**  | **Description**                                                 |
|--------------------|-----------------------------------------------------------------|
| `200`              | OTP verification successful.                                    |
| `410`              | The OTP has expired and is no longer valid.                     |
| `401`              | The OTP provided is incorrect.                                  |
| `404`              | No matching OTP was found for the given recipient and device.   |

---

## **How OTP Verification Works**

The OTP verification process involves validating the provided OTP against the stored OTP and checking for expiration or errors. The process ensures that only valid OTP submissions are accepted, preventing unauthorized access or misuse.

### **Step-by-Step Process**
1. **Fetching the latest OTP:**
    - The library retrieves the most recent pending OTP for the given `recipientId` and `deviceId` from the database.
    - It ensures that any previously used or successful OTPs are ignored.

2. **Checking if the OTP exists:**
    - If no matching OTP is found, the library returns a `404` response (OTP Not Found).

3. **Validating the OTP:**
    - If an OTP is found, the provided OTP is compared with the stored, hashed OTP using the configured hashing mechanism (e.g., bcrypt, Argon2).
    - If the OTP does not match, a `401` response (Invalid OTP) is returned.

4. **Checking expiration:**
    - If the OTP is valid, the library checks if the OTP has expired based on the configured expiration time.
    - If the OTP has expired, a `410` response (Expired OTP) is returned.

5. **Marking the OTP as used:**
    - If the OTP is valid and within the expiration time, it is marked as **used** in the database to prevent reuse.
    - The library returns a `200` response (OTP Verified).

### **Flowchart of OTP Verification**

1. Request to **confirmOTP**
2. Retrieve the latest OTP for `recipientId` and `deviceId`
3. **Is OTP found?**
    - **No:** Return `404` (OTP Not Found)
    - **Yes:** Proceed to next step
4. **Does the provided OTP match the stored OTP?**
    - **No:** Return `401` (Invalid OTP)
    - **Yes:** Proceed to next step
5. **Is the OTP expired?**
    - **Yes:** Return `410` (Expired OTP)
    - **No:** Proceed to next step
6. Mark OTP as **used** and return `200` (OTP Verified)


### **Example Flowchart**
```plaintext
1. Receive OTP Verification   →  2. Fetch Latest OTP  
    ↓                                ↓  
3. Verify OTP Exists          →  4. Compare OTP  
    ↓                                ↓  
5. Check Expiry               →  6. Mark as Used  
    ↓  
7. Return Success
```

### **Summary of Possible Responses**

| **Response Code** | **Description**                                                 |
|-------------------|-----------------------------------------------------------------|
| `200`             | OTP is valid and successfully verified.                         |
| `410`             | OTP has expired and can no longer be used.                      |
| `401`             | Provided OTP is incorrect.                                      |
| `404`             | No matching OTP was found for the given recipient and device.   |


### **Example:**
You can call the `confirmOTP` method to validate the OTP provided by the user:
```php
$response = $otpManager->confirmOTP($recipientId = 1234, $otpCode = '123456', $deviceId = 'device_001');

switch ($response['code']) {
    case 200:
        echo "OTP Verified Successfully!";
        break;
    case 410:
        echo "Error: " . $response['message'];  // "Expired OTP code."
        break;
    case 401:
        echo "Error: " . $response['message'];  // "Invalid OTP code."
        break;
    case 404:
        echo "Error: " . $response['message'];  // "Not Found OTP code."
        break;
    default:
        echo "Unexpected error occurred.";
}
```
This process ensures that only secure and valid OTP submissions are accepted, protecting sensitive operations like password resets, logins, or transactions.

---

## **Extending the Library**
You can customize or extend the library to suit your needs by adding additional OTP sender types or modifying retry mechanisms.

### **1. Adding a New OTP Sender Type**
To add a new sender type (e.g., WhatsApp, Push Notifications), extend the `OtpSenderTypeIdEnum`:

```php
enum OtpSenderTypeIdEnum: int {
    case SMS = 1;
    case EMAIL = 2;
    case WHATSAPP = 3;  // New Type
}
```
Then, update the logic in the appropriate classes (e.g., sending the OTP through the new method in your messaging service).

### **2. Modifying Retry Delays**
The `$retryDelays` array controls how long users must wait between retrying OTP requests. You can customize this array when creating the OTP manager using the factory:

#### **Example:**
```php
$retryDelays = [30, 120, 240];  // Custom retry durations in seconds
$otpManager = OTPManagerFactory::create(
    pdo: $pdo,
    tableName: 'ct_otp_code',
    retryDelays: $retryDelays
);
```

- **First retry** requires a 30-second wait.
- **Second retry** requires a 120-second wait.
- **Third retry** requires a 240-second wait.

You can adjust these values as needed to prevent abuse of OTP requests.

#### **How It Works:**
The library uses the `OTPAppDeviceRetryHandler` class to enforce the retry delays. The retry handler compares the time elapsed since the last OTP request to the corresponding delay defined in the `$retryDelays` array:

1. If the user has waited the required time, the request is allowed.
2. If not, the system returns an error (`400` with `E004`) indicating how many seconds remain before the next request is allowed.


---

## **Advanced Customization:**
If you want different retry delays for specific users or devices, you can extend the `OTPAppDeviceRetryHandler` to add custom logic:

#### **Example:**
```php
class CustomRetryHandler extends OTPAppDeviceRetryHandler {
    public function getCustomDelay(int $retryAttempt): int {
        if ($retryAttempt === 1) {
            return 15;  // Custom first retry delay of 15 seconds
        }
        return parent::successTimeLeft($retryAttempt);
    }
}
```
Then, inject the custom retry handler into the factory to apply your custom logic:

```php
$customRetryHandler = new CustomRetryHandler($retryDelays, $otpRepository);
```
This flexibility allows you to tailor the retry policy to meet the specific requirements of your application. For example, you can:

- **Set shorter retry delays** for trusted users or verified devices.
- **Apply stricter delays** for high-risk users or untrusted devices.
- **Dynamically adjust retry times** based on user behavior or system load.

By customizing the retry mechanism, you can optimize user experience while maintaining security and preventing abuse of the OTP system.

---

## **Security Considerations**

Security is a core focus of this library. It is designed to handle OTPs securely, preventing vulnerabilities such as OTP brute-forcing, replay attacks, and SQL injection. Below are key security features and considerations:

### **1. SQL Injection Prevention**
- All database interactions are handled using **prepared statements** with **bound parameters** via **PDO**.
- This ensures that user input is properly sanitized, preventing SQL injection attacks.

#### **Example:**
```php
$stmt = $this->pdo->prepare("
    SELECT * FROM {$this->tableName} WHERE recipient_id = :recipient_id
");
$stmt->execute([':recipient_id' => $recipientId]);
```

### **2. OTP Encryption with Custom Class**
- To enhance security and customization, use the custom `OTPEncryption` class implementing the `OTPEncryptionInterface` interface 
  (`Maatify\OTPManager\Contracts\OTPEncryptionInterface`). You can customize it to use any hashing mechanism (e.g., Argon2id or bcrypt).
- This allows you to implement any hashing mechanism, including **bcrypt**, **Argon2**, or **external encryption services**.
- While **Argon2id** is recommended for its strong resistance to side-channel attacks and GPU cracking, you can also use other secure options like 
  **bcrypt** if compatibility or performance is a concern. Choose the mechanism that best fits your security and performance requirements.

#### **Custom OTPEncryption Class:**
```php
use Maatify\OTPManager\Contracts\OTPEncryptionInterface;

class OTPEncryption implements OTPEncryptionInterface
{
    /**
     * Hash the OTP before saving it.
     *
     * @param string $otp The plain OTP.
     * @return string The hashed OTP.
     */
    public function hashOTP(string $otp): string
    {
        // Hash using Argon2id for secure hashing
        return password_hash($otp, PASSWORD_ARGON2ID);
    }

    /**
     * Verify the provided OTP against the stored hashed OTP.
     *
     * @param string $otp The user-provided OTP.
     * @param string $hash The hashed OTP from the database.
     * @return bool True if valid, false otherwise.
     */
    public function confirmOTP(string $otp, string $hash): bool
    {
        return password_verify($otp, $hash);
    }
}

```
> ⚙️ **Extensibility:** This implementation uses Argon2id, but you can easily replace it with other secure hashing mechanisms, such as **bcrypt**, **SHA256**, or **external encryption APIs**, by modifying the `hashOTP()` method in the `OTPEncryption` class.

### **3. Rate Limiting and Retry Delays**
- To prevent **brute-force attacks**, the library enforces customizable **retry delays** using the `OTPAppDeviceRetryHandler` class.
- Retry delays are crucial for preventing **brute-force attacks** and ensuring a balance between security and usability. Short delays may be more user-friendly but could allow brute-force attempts, while long delays reduce attack risks but may inconvenience users. We recommend starting with conservative values like `[60, 180, 300]` and adjusting based on your application’s security requirements.
- Retry delays are essential for preventing **brute-force attacks** on OTPs while maintaining a good user experience. A well-balanced configuration ensures that legitimate users are not significantly inconvenienced, while attackers are effectively blocked from attempting repeated OTP verifications.
- If users request or verify OTPs too frequently, they are temporarily blocked.

#### **Example Configuration**
```php
$retryDelays = [60, 180, 300];  // Delays between retries in seconds

$otpManager = OTPManagerFactory::create(
pdo: $pdo,
retryDelays: $retryDelays
);
```

- **60 seconds** for the first retry.
- **180 seconds** for the second retry.
- **300 seconds** for the third retry.

This configuration ensures that OTP requests and verifications are throttled appropriately, protecting the system from abuse while maintaining a good user experience.

### **4. OTP Expiration**
- OTPs have a configurable **expiration time** to limit their validity and prevent **replay attacks**.
- By default, the expiration time is set to **180 seconds**, but it can be adjusted through the factory configuration.

#### **Example Configuration**
```php
$otpManager = OTPManagerFactory::create(
    pdo: $pdo,
    expiry_of_code: 300  // Set OTP expiration to 300 seconds
);
```
This ensures that OTPs remain valid only for a limited time, reducing the risk of unauthorized use or replay attacks.

### **5. Storing and Verifying the OTP**
When requesting and confirming OTPs, the `OTPEncryption` class is used to securely hash and verify OTPs.

#### **Saving the OTP:**
```php

$otpCode = '123456';
$hashedOtp = (new OTPEncryption())->hashOTP($otpCode);

⚠️ **Security Note:** Always store OTPs securely by hashing them. This protects sensitive data from being compromised in the event of a database breach.

$stmt = $pdo->prepare("
INSERT INTO {$tableName} (recipient_id, device_id, code, expiry)
VALUES (:recipient_id, :device_id, :code, :expiry)
");
$stmt->execute([
':recipient_id' => $recipientId,
':device_id' => $deviceId,
':code' => $hashedOtp,
':expiry' => time() + 180  // OTP expires in 3 minutes
]);
```

#### **Verifying the OTP:**
```php

// The user-provided OTP to verify.
$userProvidedOtp = '123456';

// Fetch the stored hashed OTP from the database.
$stmt = $pdo->prepare("
    SELECT code 
    FROM {$tableName} 
    WHERE recipient_id = :recipient_id 
      AND device_id = :device_id 
      AND is_success = 0 
      AND expiry > :current_time
");
$stmt->execute([
    ':recipient_id' => $recipientId,
    ':device_id' => $deviceId,
    ':current_time' => time()
]);

// Retrieve the OTP from the query result.
$storedHashedOtp = $stmt->fetchColumn();

if ($storedHashedOtp) {
    // Verify the user-provided OTP against the stored, hashed OTP.
    if ((new OTPEncryption())->confirmOTP($userProvidedOtp, $storedHashedOtp)) {
        echo "✅ OTP verified successfully!";
        
        // Mark the OTP as used to prevent reuse.
        $stmt = $pdo->prepare("
            UPDATE {$tableName} 
            SET is_success = 1 
            WHERE recipient_id = :recipient_id 
              AND device_id = :device_id 
              AND code = :code
        ");
        $stmt->execute([
            ':recipient_id' => $recipientId,
            ':device_id' => $deviceId,
            ':code' => $storedHashedOtp,
        ]);
    } else {
        echo "❌ Invalid OTP.";
    }
} else {
    echo "❌ No matching OTP found or it has expired.";
}
```

### **6. Marking OTPs as Used**
- Once an OTP is successfully verified, it is marked as **used** in the database to prevent **replay attacks**.
- Subsequent attempts to use the same OTP will result in a failed verification, ensuring that OTPs are **one-time use only**.

### **7. Error Messaging**
- The library returns **clear and informative error messages** for failed attempts, helping users understand the reason for failure.
- However, it avoids exposing **sensitive information** that could be useful to attackers, such as whether a specific OTP or user exists.
- We recommend using a centralized logging library (such as [Monolog](https://github.com/Seldaek/monolog)) or an external monitoring service (like Sentry or New Relic) to track OTP-related errors and failures. This will help you detect abuse, troubleshoot issues, and monitor system health effectively.

#### **Example:**
```php
try {
    // Simulate database operation
} catch (Exception $e) {
    // Log the actual error internally
    error_log($e->getMessage());
    // Display a generic message to the user
    die("An error occurred. Please try again later.");
}
```
By following these security practices, you can protect sensitive operations (such as **logins** or **password resets**) while offering users a **safe and reliable OTP experience**.

---

## **Contributing**

We welcome contributions to improve this library! If you want to report bugs, suggest new features, or contribute code, follow these steps:


### **1. Fork the Repository**
Fork the repository on GitHub to create your copy.

```bash
git clone https://github.com/Maatify/DeviceOTP.git
cd DeviceOTP
```
### **2. Create a Feature Branch**
Create a new branch for your feature or bug fix.

```bash
git checkout -b feature/your-feature-name
```
### **3. Make Your Changes**
Make sure to write clean, well-documented code following the existing coding standards.

### **4. Commit Your Changes**
Stage and commit your changes with a meaningful message.
```bash
git add .
git commit -m "Description of the changes you made"
```
### **5. Push Your Changes**
```bash
git push origin feature/your-feature-name
```

### **6. Open a Pull Request**
Go to the original repository on GitHub and open a pull request. Provide a clear description of the changes and any related issues.


### **7. Code Review**
Your pull request will be reviewed, and you may be asked to make changes. Once approved, it will be merged into the main branch.
### **Contribution Guidelines:**
- **Write clean, maintainable code** with proper comments to ensure readability and ease of maintenance.
- **Follow [PSR-12 coding standards](https://www.php-fig.org/psr/psr-12/)** to maintain consistency across the codebase.
- **Write unit tests** for new features if possible to ensure reliability and prevent regressions.
- **Ensure that your code does not introduce security vulnerabilities**, such as SQL injection or insecure OTP handling.

---

## **License**
This project is licensed under the **MIT License**. See the [LICENSE](LICENSE) file for details.

---

## **Contact**
Developed by **[Maatify.dev](https://www.Maatify.dev)**  
For support, contact us at [support@maatify.dev](mailto:support@maatify.dev)


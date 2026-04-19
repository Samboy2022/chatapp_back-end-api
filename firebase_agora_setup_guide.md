# Firebase & Agora Integration Guide

## 1. Setting Up Firebase Cloud Messaging (FCM)

### Step 1: Create a Firebase Project
1. Go to the [Firebase Console](https://console.firebase.google.com/).
2. Click **Add project** and follow the prompts to create your new project.

### Step 2: Generate the Service Account JSON File
1. In your Firebase project dashboard, click the gear icon (⚙️) next to "Project Overview" in the top left and select **Project settings**.
2. Go to the **Service accounts** tab.
3. Verify that the selected language is **Node.js/Java/Python/Go**, and click the **Generate new private key** button.
4. A `.json` file will be downloaded to your computer. This file contains the secure credentials your Laravel API needs to send push notifications.

### Step 3: Where to Place the JSON File in Laravel
You should place the JSON file somewhere secure, strictly outside of the public directory. The recommended location is the `storage` folder, as it is protected from web access.

1. Create a `firebase` folder inside your `storage/app` directory.
2. Rename the downloaded JSON file to something simple, like `firebase-credentials.json`.
3. Move the file into your Laravel project: `storage/app/firebase/firebase-credentials.json`.
4. Update your `.env` file to point to this path. It is often easiest to use an absolute path depending on your server setup, but a relative path works locally:

```env
# Example absolute path (Windows)
FIREBASE_CREDENTIALS=C:\laragon\www\chat-app-backend\chatapp_back-end-api\storage\app\firebase\firebase-credentials.json

# Example relative path
# FIREBASE_CREDENTIALS=storage/app/firebase/firebase-credentials.json
```

---

## 2. Connecting the Mobile App (Flutter) to the REST API

### Step 1: Receive Push Notifications in Flutter
To receive the push notifications that the Laravel backend sends from `CallController`, you'll need the `firebase_messaging` package.

1. Install the package in Flutter:
   ```bash
   flutter pub add firebase_messaging
   ```
2. Retrieve the device FCM token in Flutter on startup and send it to your Laravel API. Save it in the database for the user (usually a `device_token` or `fcm_token` column). 
3. Listen for incoming messages in your Flutter app. When you receive a data payload with `type: 'incoming_call'`, immediately trigger your custom Incoming Call UI.

### Step 2: Agora Setup in Flutter
1. Install the Agora SDK package:
   ```bash
   flutter pub add agora_rtc_engine
   ```
2. Have your `AGORA_APP_ID` ready on the mobile side (it should match the backend).

### Step 3: The Calling Flow (API Connection)

#### User A Initiates the Call
1. **Flutter App (Caller):** Makes an authenticated `POST` request to your Laravel server at `/api/calls` with the body `{ "receiver_id": 2, "type": "video" }`.
2. **Laravel Backend:**
   - Creates the call record.
   - Generates Agora tokens for User A and User B.
   - Sends an FCM push notification containing the `channel` name to User B.
   - Returns User A's token, `channel`, and `app_id` to the Caller (User A).
3. **Flutter App (Caller):** Uses the `agora_rtc_engine` SDK to immediately join the channel using the returned `caller_token`.

#### User B Receives the Call
1. **Flutter App (Receiver):** Receives the FCM push notification (which contains the `channel` and `call_id` in the data payload). It wakes up and shows the "Incoming Call" screen.
2. **Flutter App (Receiver):** If User B clicks "Accept", the app makes an authenticated `POST` request to `/api/calls/{callId}/accept`.
3. **Laravel Backend:**
   - Marks the call as `answered`.
   - Returns the updated call details.
4. **Flutter App (Receiver):** Makes a `GET` request to `/api/calls/{callId}/agora-tokens` to retrieve User B's token. 
5. **Flutter App (Receiver):** Uses the `agora_rtc_engine` SDK to join the same `channel` using the retrieved `receiver_token`. Agora automatically connects the video/audio streams.

### Step 4: Ending the Call
1. Any user can click "End Call", or the call drops.
2. **Flutter App:** Leaves the Agora channel locally, destroying the Agora engine instance.
3. **Flutter App:** Makes a `POST` request to `/api/calls/{callId}/end`.
4. **Laravel Backend:** Updates the call duration and status to `ended` in the database.

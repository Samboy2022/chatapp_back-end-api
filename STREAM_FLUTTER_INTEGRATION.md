# Stream Video Flutter Integration Guide

## Overview

This guide shows how to integrate Stream Video SDK in your Flutter app with the Laravel backend.

## Backend Setup

### 1. Environment Configuration

Add your Stream credentials to your Laravel `.env` file:

```env
STREAM_API_KEY=your_stream_api_key_here
STREAM_API_SECRET=your_stream_api_secret_here
```

### 2. API Endpoints

#### Generate Stream Token
```dart
Future<Map<String, dynamic>> generateStreamToken({
  String? callId,
  String? roomId,
}) async {
  final response = await http.post(
    Uri.parse('${baseUrl}/api/stream/token'),
    headers: {
      'Authorization': 'Bearer $token',
      'Content-Type': 'application/json',
    },
    body: jsonEncode({
      if (callId != null) 'call_id': callId,
      if (roomId != null) 'room_id': roomId,
    }),
  );

  if (response.statusCode == 200) {
    return jsonDecode(response.body);
  } else {
    throw Exception('Failed to generate Stream token');
  }
}
```

#### Get Stream Configuration
```dart
Future<Map<String, dynamic>> getStreamConfig() async {
  final response = await http.get(
    Uri.parse('${baseUrl}/api/stream/config'),
    headers: {
      'Authorization': 'Bearer $token',
    },
  );

  if (response.statusCode == 200) {
    return jsonDecode(response.body);
  } else {
    throw Exception('Failed to get Stream config');
  }
}
```

## Flutter Implementation

### 1. Dependencies

Add Stream Video SDK to your `pubspec.yaml`:

```yaml
dependencies:
  stream_video_flutter: ^0.4.0
  stream_video: ^0.4.0
  flutter_webrtc: ^0.9.34
```

### 2. Initialize Stream Client

```dart
import 'package:stream_video/stream_video.dart';

class StreamVideoService {
  late StreamVideoClient _client;
  String? _apiKey;

  Future<void> initialize() async {
    // Get Stream configuration from your backend
    final config = await getStreamConfig();
    _apiKey = config['data']['api_key'];

    // Initialize Stream client
    _client = StreamVideoClient(
      _apiKey!,
      user: User.regular(
        userId: currentUserId,
        name: currentUserName,
      ),
    );
  }

  Future<String> getToken(String userId) async {
    final response = await generateStreamToken();
    return response['data']['token'];
  }
}
```

### 3. Join Video Call

```dart
class VideoCallScreen extends StatefulWidget {
  final String callId;
  final bool isCaller;

  const VideoCallScreen({
    Key? key,
    required this.callId,
    required this.isCaller,
  }) : super(key: key);

  @override
  _VideoCallScreenState createState() => _VideoCallScreenState();
}

class _VideoCallScreenState extends State<VideoCallScreen> {
  late StreamVideoService _streamService;
  late Call _call;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _initializeCall();
  }

  Future<void> _initializeCall() async {
    try {
      // Get Stream token
      final token = await _streamService.getToken(currentUserId);

      // Create or join call
      final callId = 'call_${widget.callId}';

      if (widget.isCaller) {
        // Create new call
        _call = _streamService.client.makeCall(
          callType: StreamCallType.defaultType(),
          id: callId,
        );
        await _call.create();
      } else {
        // Join existing call
        _call = _streamService.client.makeCall(
          callType: StreamCallType.defaultType(),
          id: callId,
        );
        await _call.join(create: false);
      }

      setState(() {
        _isLoading = false;
      });
    } catch (e) {
      print('Error initializing call: $e');
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return Scaffold(
        body: Center(
          child: CircularProgressIndicator(),
        ),
      );
    }

    return StreamVideoCall(
      call: _call,
      onBackPressed: () => Navigator.of(context).pop(),
      onCallEnded: () => Navigator.of(context).pop(),
    );
  }
}
```

### 4. Handle Incoming Calls

```dart
class CallNotificationHandler {
  static void handleIncomingCall(Map<String, dynamic> callData) {
    // Show incoming call UI
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Incoming Call'),
        content: Text('${callData['caller_name']} is calling you'),
        actions: [
          TextButton(
            onPressed: () => _acceptCall(callData),
            child: Text('Accept'),
          ),
          TextButton(
            onPressed: () => _declineCall(callData),
            child: Text('Decline'),
          ),
        ],
      ),
    );
  }

  static void _acceptCall(Map<String, dynamic> callData) async {
    Navigator.of(context).pop(); // Close dialog

    // Navigate to video call screen
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => VideoCallScreen(
          callId: callData['call_id'],
          isCaller: false,
        ),
      ),
    );
  }

  static void _declineCall(Map<String, dynamic> callData) {
    Navigator.of(context).pop(); // Close dialog

    // Send decline request to backend
    declineCall(callData['call_id']);
  }
}
```

### 5. WebSocket Integration

```dart
class StreamWebSocketService {
  late StreamVideoClient _client;

  void initialize() {
    _client = StreamVideoClient(
      apiKey,
      user: User.regular(userId: currentUserId),
    );

    // Listen for call events
    _client.on('call.incoming').listen((event) {
      CallNotificationHandler.handleIncomingCall(event.data);
    });

    _client.on('call.ended').listen((event) {
      // Handle call ended
      Navigator.of(context).pop();
    });
  }
}
```

## Usage Example

### 1. Initialize Stream Service

```dart
void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize Stream service
  final streamService = StreamVideoService();
  await streamService.initialize();

  runApp(MyApp(streamService: streamService));
}
```

### 2. Make Video Call

```dart
void makeVideoCall(String receiverId) async {
  try {
    // Initiate call via your backend
    final callResponse = await initiateCall({
      'receiver_id': receiverId,
      'type': 'video'
    });

    if (callResponse['success']) {
      final callData = callResponse['data'];

      // Navigate to video call screen
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (context) => VideoCallScreen(
            callId: callData['id'].toString(),
            isCaller: true,
          ),
        ),
      );
    }
  } catch (e) {
    print('Error making video call: $e');
  }
}
```

### 3. Handle Call Events

```dart
class CallEventHandler {
  static void setupCallEventListeners() {
    // Listen for WebSocket events
    socket.on('CallInitiated', (data) {
      if (data['recipient_id'] == currentUserId) {
        CallNotificationHandler.handleIncomingCall(data);
      }
    });

    socket.on('CallAccepted', (data) {
      // Handle call accepted
      print('Call accepted by ${data['recipient_id']}');
    });

    socket.on('CallEnded', (data) {
      // Handle call ended
      Navigator.of(context).pop();
    });
  }
}
```

## Error Handling

```dart
class StreamErrorHandler {
  static void handleError(dynamic error) {
    if (error is StreamVideoException) {
      switch (error.code) {
        case 'CALL_NOT_FOUND':
          showError('Call not found');
          break;
        case 'PERMISSION_DENIED':
          showError('Permission denied');
          break;
        case 'NETWORK_ERROR':
          showError('Network error. Please check your connection.');
          break;
        default:
          showError('An error occurred: ${error.message}');
      }
    } else {
      showError('An unexpected error occurred');
    }
  }
}
```

## Testing

### 1. Test Token Generation
```bash
curl -X POST http://your-backend/api/stream/token \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

### 2. Test Configuration
```bash
curl http://your-backend/api/stream/config \
  -H "Authorization: Bearer YOUR_TOKEN"
```

This integration provides a complete video calling solution using Stream Video SDK with your Laravel backend.
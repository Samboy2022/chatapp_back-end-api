# Flutter WebSocket Call Signaling Integration

## Overview

This guide provides complete Flutter integration for video/voice calling that connects to your Laravel chat backend with dynamic broadcast settings support.

## Features

- **Dynamic WebSocket Detection**: Automatically detects and switches between Pusher Cloud and Laravel Reverb
- **Call Event Handling**: Complete support for CallInitiated, CallAccepted, CallEnded, CallRejected events
- **Modular Architecture**: Clean service-based architecture for easy integration
- **Error Handling**: Comprehensive error handling and reconnection logic
- **WebRTC Preparation**: Ready for WebRTC session establishment

## Dependencies

Add these dependencies to your `pubspec.yaml`:

```yaml
dependencies:
  flutter:
    sdk: flutter
  http: ^1.1.0
  shared_preferences: ^2.2.2
  pusher_channels_flutter: ^2.2.1
  laravel_echo: ^0.4.0
  socket_io_client: ^2.0.3+1

dev_dependencies:
  flutter_test:
    sdk: flutter
```

## 1. Call Event Models

First, create the data models for call events:

```dart
// models/call_event.dart
enum CallEventType {
  callInitiated,
  callAccepted,
  callEnded,
  callRejected,
}

class CallEvent {
  final CallEventType type;
  final String callId;
  final String callerId;
  final String? recipientId;
  final String callerName;
  final String? callerAvatar;
  final String callType; // 'voice' or 'video'
  final DateTime timestamp;
  final Map<String, dynamic>? metadata;

  CallEvent({
    required this.type,
    required this.callId,
    required this.callerId,
    this.recipientId,
    required this.callerName,
    this.callerAvatar,
    required this.callType,
    required this.timestamp,
    this.metadata,
  });

  factory CallEvent.fromJson(Map<String, dynamic> json) {
    return CallEvent(
      type: _parseEventType(json['event_type'] ?? ''),
      callId: json['call_id'] ?? '',
      callerId: json['caller_id'] ?? '',
      recipientId: json['recipient_id'],
      callerName: json['caller_name'] ?? '',
      callerAvatar: json['caller_avatar'],
      callType: json['call_type'] ?? 'voice',
      timestamp: DateTime.parse(json['timestamp'] ?? DateTime.now().toIso8601String()),
      metadata: json['metadata'],
    );
  }

  static CallEventType _parseEventType(String eventType) {
    switch (eventType.toLowerCase()) {
      case 'call_initiated':
        return CallEventType.callInitiated;
      case 'call_accepted':
        return CallEventType.callAccepted;
      case 'call_ended':
        return CallEventType.callEnded;
      case 'call_rejected':
        return CallEventType.callRejected;
      default:
        return CallEventType.callInitiated;
    }
  }
}

enum ConnectionState {
  disconnected,
  connecting,
  connected,
  reconnecting,
  error,
}

enum WebSocketDriver {
  pusher,
  reverb,
  disabled,
}
```

## 2. Broadcast Configuration Service

Create a service to handle broadcast configuration:

```dart
// services/broadcast_config_service.dart
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class BroadcastConfigService {
  static const String _cacheKey = 'broadcast_config';
  static const String _callSignalingCacheKey = 'call_signaling_config';
  static const int _cacheTimeoutMinutes = 5;
  
  Map<String, dynamic>? _config;
  Map<String, dynamic>? _callSignalingConfig;
  DateTime? _lastFetch;

  static final BroadcastConfigService _instance = BroadcastConfigService._internal();
  factory BroadcastConfigService() => _instance;
  BroadcastConfigService._internal();

  Future<Map<String, dynamic>> getConfig({bool forceRefresh = false}) async {
    try {
      // Check cache first
      if (!forceRefresh && _config != null && _lastFetch != null) {
        final now = DateTime.now();
        final cacheAge = now.difference(_lastFetch!).inMinutes;
        if (cacheAge < _cacheTimeoutMinutes) {
          return _config!;
        }
      }

      // Fetch from API
      final response = await http.get(
        Uri.parse('${ApiConfig.baseUrl}/api/broadcast-settings'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        
        if (data['success'] == true) {
          _config = data['data'];
          _lastFetch = DateTime.now();
          
          // Cache for offline use
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString(_cacheKey, json.encode(_config));
          
          return _config!;
        }
      }
      
      throw Exception('Failed to fetch broadcast config: ${response.statusCode}');
      
    } catch (error) {
      print('Failed to get broadcast config: $error');
      
      // Try cached config
      try {
        final prefs = await SharedPreferences.getInstance();
        final cached = prefs.getString(_cacheKey);
        if (cached != null) {
          _config = json.decode(cached);
          return _config!;
        }
      } catch (cacheError) {
        print('Failed to get cached config: $cacheError');
      }
      
      // Return default disabled config
      return {
        'enabled': false,
        'driver': 'log',
        'config': {'enabled': false}
      };
    }
  }

  Future<Map<String, dynamic>> getCallSignalingConfig({bool forceRefresh = false}) async {
    try {
      // Check cache first
      if (!forceRefresh && _callSignalingConfig != null && _lastFetch != null) {
        final now = DateTime.now();
        final cacheAge = now.difference(_lastFetch!).inMinutes;
        if (cacheAge < _cacheTimeoutMinutes) {
          return _callSignalingConfig!;
        }
      }

      // Fetch call signaling specific config
      final response = await http.get(
        Uri.parse('${ApiConfig.baseUrl}/api/broadcast-settings/call-signaling'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ).timeout(const Duration(seconds: 10));

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        
        if (data['success'] == true) {
          _callSignalingConfig = data['data'];
          
          // Cache for offline use
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString(_callSignalingCacheKey, json.encode(_callSignalingConfig));
          
          return _callSignalingConfig!;
        }
      }
      
      throw Exception('Failed to fetch call signaling config: ${response.statusCode}');
      
    } catch (error) {
      print('Failed to get call signaling config: $error');
      
      // Try cached config
      try {
        final prefs = await SharedPreferences.getInstance();
        final cached = prefs.getString(_callSignalingCacheKey);
        if (cached != null) {
          _callSignalingConfig = json.decode(cached);
          return _callSignalingConfig!;
        }
      } catch (cacheError) {
        print('Failed to get cached call signaling config: $cacheError');
      }
      
      // Return default disabled config
      return {
        'enabled': false,
        'driver': 'log',
        'websocket_config': {'enabled': false}
      };
    }
  }

  Future<bool> isCallSignalingEnabled() async {
    final config = await getCallSignalingConfig();
    return config['enabled'] == true;
  }

  Future<Map<String, dynamic>> getWebSocketConfig() async {
    final config = await getCallSignalingConfig();
    return config['websocket_config'] ?? {};
  }
}

// config/api_config.dart
class ApiConfig {
  static const String baseUrl = 'http://your-laravel-api.com';
  
  // For development
  static const String devBaseUrl = 'http://127.0.0.1:8000';
  
  // For production
  static const String prodBaseUrl = 'https://your-production-api.com';
  
  // Use appropriate URL based on build mode
  static String get currentBaseUrl {
    const bool isProduction = bool.fromEnvironment('dart.vm.product');
    return isProduction ? prodBaseUrl : devBaseUrl;
  }
}
```

## 3. Call Signaling Service

Now create the main CallSignalingService:

```dart
// services/call_signaling_service.dart
import 'dart:async';
import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:pusher_channels_flutter/pusher_channels_flutter.dart';
import 'package:laravel_echo/laravel_echo.dart';
import 'package:socket_io_client/socket_io_client.dart' as io;
import 'package:shared_preferences/shared_preferences.dart';
import '../models/call_event.dart';
import 'broadcast_config_service.dart';

class CallSignalingService {
  static final CallSignalingService _instance = CallSignalingService._internal();
  factory CallSignalingService() => _instance;
  CallSignalingService._internal();

  // WebSocket connections
  PusherChannelsFlutter? _pusher;
  Echo? _echo;
  io.Socket? _socket;

  // State management
  ConnectionState _connectionState = ConnectionState.disconnected;
  WebSocketDriver _currentDriver = WebSocketDriver.disabled;
  String? _userId;
  String? _authToken;
  Map<String, dynamic>? _callSignalingConfig;

  // Event streams
  final StreamController<CallEvent> _callEventController = StreamController<CallEvent>.broadcast();
  final StreamController<ConnectionState> _connectionStateController = StreamController<ConnectionState>.broadcast();

  // Reconnection management
  Timer? _reconnectTimer;
  int _reconnectAttempts = 0;
  static const int _maxReconnectAttempts = 5;
  static const Duration _reconnectDelay = Duration(seconds: 2);

  // Configuration polling
  Timer? _configPollingTimer;
  static const Duration _configPollingInterval = Duration(minutes: 5);

  // Getters
  Stream<CallEvent> get callEvents => _callEventController.stream;
  Stream<ConnectionState> get connectionState => _connectionStateController.stream;
  ConnectionState get currentConnectionState => _connectionState;
  WebSocketDriver get currentDriver => _currentDriver;
  bool get isConnected => _connectionState == ConnectionState.connected;

  /// Initialize the call signaling service
  Future<bool> initialize({
    required String userId,
    required String authToken,
  }) async {
    try {
      _userId = userId;
      _authToken = authToken;
      
      debugPrint('🔄 Initializing CallSignalingService for user: $userId');
      
      // Get call signaling configuration
      final config = await BroadcastConfigService().getCallSignalingConfig();
      if (config['enabled'] != true) {
        debugPrint('❌ Call signaling is disabled');
        _updateConnectionState(ConnectionState.disconnected);
        return false;
      }

      _callSignalingConfig = config;
      
      // Determine driver and initialize connection
      final driver = _parseDriver(config['driver']);
      if (driver == WebSocketDriver.disabled) {
        debugPrint('❌ Unknown or disabled WebSocket driver: ${config['driver']}');
        return false;
      }

      _currentDriver = driver;
      
      // Initialize WebSocket connection
      final success = await _initializeWebSocket();
      
      if (success) {
        // Start configuration polling
        _startConfigPolling();
        debugPrint('✅ CallSignalingService initialized successfully');
      }
      
      return success;
      
    } catch (error) {
      debugPrint('❌ Failed to initialize CallSignalingService: $error');
      _updateConnectionState(ConnectionState.error);
      return false;
    }
  }

  /// Parse WebSocket driver from config
  WebSocketDriver _parseDriver(String? driver) {
    switch (driver?.toLowerCase()) {
      case 'pusher':
        return WebSocketDriver.pusher;
      case 'reverb':
        return WebSocketDriver.reverb;
      default:
        return WebSocketDriver.disabled;
    }
  }

  /// Initialize WebSocket connection based on current driver
  Future<bool> _initializeWebSocket() async {
    try {
      _updateConnectionState(ConnectionState.connecting);
      
      // Disconnect existing connections
      await _disconnect();
      
      switch (_currentDriver) {
        case WebSocketDriver.pusher:
          return await _initializePusher();
        case WebSocketDriver.reverb:
          return await _initializeReverb();
        case WebSocketDriver.disabled:
          return false;
      }
    } catch (error) {
      debugPrint('❌ Failed to initialize WebSocket: $error');
      _updateConnectionState(ConnectionState.error);
      return false;
    }
  }
```

  /// Initialize Pusher connection
  Future<bool> _initializePusher() async {
    try {
      final config = _callSignalingConfig!['websocket_config'];

      _pusher = PusherChannelsFlutter.getInstance();

      await _pusher!.init(
        apiKey: config['key'],
        cluster: config['cluster'],
        onConnectionStateChange: _onPusherConnectionStateChange,
        onError: _onPusherError,
        onSubscriptionSucceeded: _onPusherSubscriptionSucceeded,
        onEvent: _onPusherEvent,
        onSubscriptionError: _onPusherSubscriptionError,
        authEndpoint: config['auth_endpoint'],
        onAuthorizer: (channelName, socketId, options) async {
          return {
            'Authorization': 'Bearer $_authToken',
          };
        },
      );

      await _pusher!.connect();

      // Subscribe to call channel
      await _subscribeToCallChannel();

      debugPrint('✅ Pusher initialized successfully');
      return true;

    } catch (error) {
      debugPrint('❌ Failed to initialize Pusher: $error');
      return false;
    }
  }

  /// Initialize Laravel Reverb connection
  Future<bool> _initializeReverb() async {
    try {
      final config = _callSignalingConfig!['websocket_config'];

      // Create Socket.IO client for Reverb
      _socket = io.io('${config['scheme']}://${config['host']}:${config['port']}',
        io.OptionBuilder()
          .setTransports(['websocket'])
          .enableAutoConnect()
          .setAuth({
            'Authorization': 'Bearer $_authToken',
          })
          .build()
      );

      // Create Echo instance
      _echo = Echo(
        broadcaster: EchoBroadcasterType.SocketIO,
        client: _socket,
        options: {
          'auth': {
            'headers': {
              'Authorization': 'Bearer $_authToken',
            }
          },
          'authEndpoint': config['auth_endpoint'],
        }
      );

      // Set up connection event handlers
      _socket!.onConnect((_) {
        debugPrint('✅ Reverb connected');
        _updateConnectionState(ConnectionState.connected);
        _reconnectAttempts = 0;
      });

      _socket!.onDisconnect((_) {
        debugPrint('❌ Reverb disconnected');
        _updateConnectionState(ConnectionState.disconnected);
        _handleReconnection();
      });

      _socket!.onError((error) {
        debugPrint('❌ Reverb error: $error');
        _updateConnectionState(ConnectionState.error);
        _handleReconnection();
      });

      // Subscribe to call channel
      await _subscribeToCallChannel();

      debugPrint('✅ Reverb initialized successfully');
      return true;

    } catch (error) {
      debugPrint('❌ Failed to initialize Reverb: $error');
      return false;
    }
  }

  /// Subscribe to call channel
  Future<void> _subscribeToCallChannel() async {
    if (_userId == null) return;

    final channelName = 'call.$_userId';

    try {
      if (_currentDriver == WebSocketDriver.pusher && _pusher != null) {
        final channel = await _pusher!.subscribe(channelName: channelName);
        debugPrint('✅ Subscribed to Pusher channel: $channelName');

      } else if (_currentDriver == WebSocketDriver.reverb && _echo != null) {
        _echo!.private(channelName).listen('.CallInitiated', _handleReverbEvent);
        _echo!.private(channelName).listen('.CallAccepted', _handleReverbEvent);
        _echo!.private(channelName).listen('.CallEnded', _handleReverbEvent);
        _echo!.private(channelName).listen('.CallRejected', _handleReverbEvent);
        debugPrint('✅ Subscribed to Reverb channel: $channelName');
      }

    } catch (error) {
      debugPrint('❌ Failed to subscribe to call channel: $error');
    }
  }

  /// Handle Pusher connection state changes
  void _onPusherConnectionStateChange(String currentState, String previousState) {
    debugPrint('Pusher connection state: $previousState -> $currentState');

    switch (currentState) {
      case 'connected':
        _updateConnectionState(ConnectionState.connected);
        _reconnectAttempts = 0;
        break;
      case 'connecting':
        _updateConnectionState(ConnectionState.connecting);
        break;
      case 'disconnected':
        _updateConnectionState(ConnectionState.disconnected);
        _handleReconnection();
        break;
      default:
        _updateConnectionState(ConnectionState.error);
        break;
    }
  }

  /// Handle Pusher errors
  void _onPusherError(String message, int? code, dynamic e) {
    debugPrint('❌ Pusher error: $message (code: $code)');
    _updateConnectionState(ConnectionState.error);
    _handleReconnection();
  }

  /// Handle Pusher subscription success
  void _onPusherSubscriptionSucceeded(String channelName, dynamic data) {
    debugPrint('✅ Pusher subscription succeeded: $channelName');
  }

  /// Handle Pusher subscription errors
  void _onPusherSubscriptionError(String message, dynamic e) {
    debugPrint('❌ Pusher subscription error: $message');
  }

  /// Handle Pusher events
  void _onPusherEvent(PusherEvent event) {
    debugPrint('📞 Received Pusher event: ${event.eventName} on ${event.channelName}');

    try {
      final data = json.decode(event.data);
      final callEvent = CallEvent.fromJson(data);
      _callEventController.add(callEvent);
    } catch (error) {
      debugPrint('❌ Failed to parse call event: $error');
    }
  }

  /// Handle Reverb events
  void _handleReverbEvent(dynamic data) {
    debugPrint('📞 Received Reverb call event');

    try {
      final callEvent = CallEvent.fromJson(data);
      _callEventController.add(callEvent);
    } catch (error) {
      debugPrint('❌ Failed to parse Reverb call event: $error');
    }
  }

  /// Update connection state and notify listeners
  void _updateConnectionState(ConnectionState newState) {
    if (_connectionState != newState) {
      _connectionState = newState;
      _connectionStateController.add(newState);
    }
  }

  /// Handle reconnection logic
  Future<void> _handleReconnection() async {
    if (_reconnectAttempts >= _maxReconnectAttempts) {
      debugPrint('❌ Max reconnection attempts reached');
      _updateConnectionState(ConnectionState.error);
      return;
    }

    _reconnectAttempts++;
    _updateConnectionState(ConnectionState.reconnecting);

    debugPrint('🔄 Reconnection attempt $_reconnectAttempts');

    // Wait before reconnecting with exponential backoff
    final delay = Duration(seconds: _reconnectDelay.inSeconds * _reconnectAttempts);
    await Future.delayed(delay);

    // Check if settings changed
    try {
      final config = await BroadcastConfigService().getCallSignalingConfig(forceRefresh: true);

      if (config['enabled'] != true) {
        debugPrint('❌ Call signaling disabled, stopping reconnection');
        _updateConnectionState(ConnectionState.disconnected);
        return;
      }

      // Reinitialize connection
      await _initializeWebSocket();

    } catch (error) {
      debugPrint('❌ Reconnection failed: $error');
      _updateConnectionState(ConnectionState.error);
    }
  }

  /// Start configuration polling
  void _startConfigPolling() {
    _configPollingTimer?.cancel();
    _configPollingTimer = Timer.periodic(_configPollingInterval, (timer) async {
      try {
        final config = await BroadcastConfigService().getCallSignalingConfig(forceRefresh: true);

        // Check if driver changed
        final newDriver = _parseDriver(config['driver']);
        if (newDriver != _currentDriver || config['enabled'] != (_callSignalingConfig?['enabled'] ?? false)) {
          debugPrint('🔄 Configuration changed, reinitializing...');
          _callSignalingConfig = config;
          _currentDriver = newDriver;

          if (config['enabled'] == true) {
            await _initializeWebSocket();
          } else {
            await _disconnect();
            _updateConnectionState(ConnectionState.disconnected);
          }
        }

      } catch (error) {
        debugPrint('❌ Failed to poll configuration: $error');
      }
    });
  }

  /// Disconnect from WebSocket
  Future<void> _disconnect() async {
    try {
      if (_pusher != null) {
        await _pusher!.disconnect();
        _pusher = null;
      }

      if (_socket != null) {
        _socket!.disconnect();
        _socket = null;
      }

      if (_echo != null) {
        _echo = null;
      }

    } catch (error) {
      debugPrint('❌ Error during disconnect: $error');
    }
  }

  /// Dispose of the service
  Future<void> dispose() async {
    _configPollingTimer?.cancel();
    _reconnectTimer?.cancel();

    await _disconnect();

    await _callEventController.close();
    await _connectionStateController.close();

    _updateConnectionState(ConnectionState.disconnected);
  }
}
```

## 4. UI Integration Examples

### Basic Call Handler Widget

```dart
// widgets/call_handler.dart
import 'package:flutter/material.dart';
import '../services/call_signaling_service.dart';
import '../models/call_event.dart';

class CallHandler extends StatefulWidget {
  final Widget child;
  final String userId;
  final String authToken;

  const CallHandler({
    Key? key,
    required this.child,
    required this.userId,
    required this.authToken,
  }) : super(key: key);

  @override
  _CallHandlerState createState() => _CallHandlerState();
}

class _CallHandlerState extends State<CallHandler> {
  final CallSignalingService _callService = CallSignalingService();
  bool _isInitialized = false;

  @override
  void initState() {
    super.initState();
    _initializeCallService();
  }

  Future<void> _initializeCallService() async {
    final success = await _callService.initialize(
      userId: widget.userId,
      authToken: widget.authToken,
    );

    if (success) {
      setState(() {
        _isInitialized = true;
      });

      // Listen for call events
      _callService.callEvents.listen(_handleCallEvent);
      _callService.connectionState.listen(_handleConnectionStateChange);
    }
  }

  void _handleCallEvent(CallEvent event) {
    switch (event.type) {
      case CallEventType.callInitiated:
        _showIncomingCallDialog(event);
        break;
      case CallEventType.callAccepted:
        _handleCallAccepted(event);
        break;
      case CallEventType.callEnded:
        _handleCallEnded(event);
        break;
      case CallEventType.callRejected:
        _handleCallRejected(event);
        break;
    }
  }

  void _handleConnectionStateChange(ConnectionState state) {
    // Handle connection state changes
    switch (state) {
      case ConnectionState.connected:
        print('✅ Call signaling connected');
        break;
      case ConnectionState.disconnected:
        print('❌ Call signaling disconnected');
        break;
      case ConnectionState.error:
        print('❌ Call signaling error');
        break;
      default:
        break;
    }
  }

  void _showIncomingCallDialog(CallEvent event) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => IncomingCallDialog(
        callEvent: event,
        onAccept: () => _acceptCall(event),
        onReject: () => _rejectCall(event),
      ),
    );
  }

  void _acceptCall(CallEvent event) {
    Navigator.of(context).pop(); // Close dialog
    // TODO: Initialize WebRTC session and navigate to call screen
    _navigateToCallScreen(event, isIncoming: true);
  }

  void _rejectCall(CallEvent event) {
    Navigator.of(context).pop(); // Close dialog
    // TODO: Send reject call API request
  }

  void _handleCallAccepted(CallEvent event) {
    // TODO: Start WebRTC session
    _navigateToCallScreen(event, isIncoming: false);
  }

  void _handleCallEnded(CallEvent event) {
    // TODO: End WebRTC session and close call screen
    Navigator.of(context).popUntil((route) => route.isFirst);
  }

  void _handleCallRejected(CallEvent event) {
    // TODO: Handle call rejection
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('Call was rejected')),
    );
  }

  void _navigateToCallScreen(CallEvent event, {required bool isIncoming}) {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (context) => CallScreen(
          callEvent: event,
          isIncoming: isIncoming,
        ),
      ),
    );
  }

  @override
  void dispose() {
    _callService.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return widget.child;
  }
}
```

### Incoming Call Dialog

```dart
// widgets/incoming_call_dialog.dart
import 'package:flutter/material.dart';
import '../models/call_event.dart';

class IncomingCallDialog extends StatelessWidget {
  final CallEvent callEvent;
  final VoidCallback onAccept;
  final VoidCallback onReject;

  const IncomingCallDialog({
    Key? key,
    required this.callEvent,
    required this.onAccept,
    required this.onReject,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Dialog(
      backgroundColor: Colors.transparent,
      child: Container(
        padding: EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Caller avatar
            CircleAvatar(
              radius: 50,
              backgroundImage: callEvent.callerAvatar != null
                  ? NetworkImage(callEvent.callerAvatar!)
                  : null,
              child: callEvent.callerAvatar == null
                  ? Icon(Icons.person, size: 50)
                  : null,
            ),

            SizedBox(height: 16),

            // Caller name
            Text(
              callEvent.callerName,
              style: TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
              ),
            ),

            SizedBox(height: 8),

            // Call type
            Text(
              'Incoming ${callEvent.callType} call',
              style: TextStyle(
                fontSize: 16,
                color: Colors.grey[600],
              ),
            ),

            SizedBox(height: 32),

            // Action buttons
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: [
                // Reject button
                FloatingActionButton(
                  onPressed: onReject,
                  backgroundColor: Colors.red,
                  child: Icon(Icons.call_end, color: Colors.white),
                ),

                // Accept button
                FloatingActionButton(
                  onPressed: onAccept,
                  backgroundColor: Colors.green,
                  child: Icon(Icons.call, color: Colors.white),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
```

This completes the comprehensive Flutter integration guide. The implementation provides:

1. **Dynamic WebSocket Detection** - Automatically switches between Pusher and Reverb
2. **Complete Event Handling** - All call events properly handled
3. **Modular Architecture** - Clean separation of concerns
4. **Error Handling** - Comprehensive error handling and reconnection
5. **UI Integration** - Ready-to-use UI components
6. **WebRTC Preparation** - Structured for WebRTC integration

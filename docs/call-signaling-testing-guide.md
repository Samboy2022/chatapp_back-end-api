# Call Signaling Testing Guide

## Overview

This guide provides comprehensive testing procedures for the Laravel backend call signaling system and Flutter integration.

## Prerequisites

1. Laravel backend running on `http://127.0.0.1:8000`
2. Database migrated with call tables
3. Broadcast settings configured (Pusher Cloud or Laravel Reverb)
4. Two test user accounts created
5. Authentication tokens for both users

## Backend Testing

### 1. Test Broadcast Settings API

```bash
# Test broadcast configuration endpoint
curl -X GET "http://127.0.0.1:8000/api/broadcast-settings" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Expected response should show enabled: true and correct driver
```

### 2. Test Call Signaling Configuration

```bash
# Test call signaling specific config
curl -X GET "http://127.0.0.1:8000/api/broadcast-settings/call-signaling" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Should return detailed call signaling configuration
```

### 3. Test Call Initiation

```bash
# Initiate a video call
curl -X POST "http://127.0.0.1:8000/api/calls/initiate" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer USER1_TOKEN" \
  -d '{
    "receiver_id": 2,
    "type": "video"
  }'

# Should return call object and trigger CallInitiated event
```

### 4. Test Call Answer

```bash
# Answer the call (use call_id from previous response)
curl -X POST "http://127.0.0.1:8000/api/calls/CALL_ID/answer" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer USER2_TOKEN"

# Should return success and trigger CallAccepted event
```

### 5. Test Call End

```bash
# End the call
curl -X POST "http://127.0.0.1:8000/api/calls/CALL_ID/end" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer USER1_TOKEN"

# Should return success and trigger CallEnded event
```

### 6. Test Call Rejection

```bash
# Reject a call
curl -X POST "http://127.0.0.1:8000/api/calls/CALL_ID/decline" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer USER2_TOKEN"

# Should return success and trigger CallRejected event
```

## Admin Panel Testing

### 1. Access Admin Panel

1. Navigate to `http://127.0.0.1:8000/admin/calls`
2. Login with admin credentials
3. Verify real-time statistics are displayed
4. Check active calls monitor section

### 2. Test Real-time Updates

1. Open admin panel in one browser tab
2. Initiate calls via API in another tab
3. Verify real-time statistics update automatically
4. Check active calls appear in monitor section

### 3. Test Admin Call Control

1. Initiate a call via API
2. In admin panel, click "End Call" button on active call
3. Verify call is ended and participants receive CallEnded event

## WebSocket Testing

### 1. Test Pusher Connection (if using Pusher)

```javascript
// Browser console test
const pusher = new Pusher('YOUR_PUSHER_KEY', {
  cluster: 'YOUR_CLUSTER',
  authEndpoint: 'http://127.0.0.1:8000/broadcasting/auth',
  auth: {
    headers: {
      'Authorization': 'Bearer YOUR_TOKEN'
    }
  }
});

const channel = pusher.subscribe('call.USER_ID');

channel.bind('CallInitiated', function(data) {
  console.log('CallInitiated event received:', data);
});

channel.bind('CallAccepted', function(data) {
  console.log('CallAccepted event received:', data);
});

channel.bind('CallEnded', function(data) {
  console.log('CallEnded event received:', data);
});

channel.bind('CallRejected', function(data) {
  console.log('CallRejected event received:', data);
});
```

### 2. Test Laravel Reverb Connection (if using Reverb)

```javascript
// Browser console test
const socket = io('ws://127.0.0.1:6001', {
  auth: {
    'Authorization': 'Bearer YOUR_TOKEN'
  }
});

socket.on('connect', () => {
  console.log('Connected to Reverb');
  
  // Subscribe to call channel
  socket.emit('subscribe', {
    channel: 'call.USER_ID',
    auth: {
      'Authorization': 'Bearer YOUR_TOKEN'
    }
  });
});

socket.on('CallInitiated', (data) => {
  console.log('CallInitiated event received:', data);
});

socket.on('CallAccepted', (data) => {
  console.log('CallAccepted event received:', data);
});

socket.on('CallEnded', (data) => {
  console.log('CallEnded event received:', data);
});

socket.on('CallRejected', (data) => {
  console.log('CallRejected event received:', data);
});
```

## Flutter Testing

### 1. Test Service Initialization

```dart
// Test in Flutter app
void testCallSignalingService() async {
  final service = CallSignalingService();
  
  // Test initialization
  final success = await service.initialize(
    userId: 'USER_ID',
    authToken: 'USER_TOKEN',
  );
  
  print('Service initialized: $success');
  
  // Listen for connection state changes
  service.connectionState.listen((state) {
    print('Connection state: $state');
  });
  
  // Listen for call events
  service.callEvents.listen((event) {
    print('Call event received: ${event.type}');
    print('Caller: ${event.callerName}');
    print('Call type: ${event.callType}');
  });
}
```

### 2. Test Event Handling

```dart
// Test call event handling
void testCallEventHandling() {
  final service = CallSignalingService();
  
  service.callEvents.listen((event) {
    switch (event.type) {
      case CallEventType.callInitiated:
        print('✅ Incoming call from ${event.callerName}');
        // Test UI should show incoming call dialog
        break;
        
      case CallEventType.callAccepted:
        print('✅ Call accepted');
        // Test UI should navigate to call screen
        break;
        
      case CallEventType.callEnded:
        print('✅ Call ended');
        // Test UI should close call screen
        break;
        
      case CallEventType.callRejected:
        print('✅ Call rejected');
        // Test UI should show rejection message
        break;
    }
  });
}
```

## End-to-End Testing Scenarios

### Scenario 1: Successful Video Call

1. **Setup**: Two users (User A and User B) with Flutter apps
2. **Step 1**: User A initiates video call to User B
3. **Expected**: User B receives CallInitiated event and sees incoming call dialog
4. **Step 2**: User B accepts the call
5. **Expected**: Both users receive CallAccepted event and navigate to call screen
6. **Step 3**: User A ends the call
7. **Expected**: Both users receive CallEnded event and return to main screen

### Scenario 2: Call Rejection

1. **Setup**: Two users with Flutter apps
2. **Step 1**: User A initiates call to User B
3. **Expected**: User B receives CallInitiated event
4. **Step 2**: User B rejects the call
5. **Expected**: User A receives CallRejected event and sees rejection message

### Scenario 3: Admin Intervention

1. **Setup**: Active call between two users, admin panel open
2. **Step 1**: Admin sees active call in real-time monitor
3. **Step 2**: Admin clicks "End Call" button
4. **Expected**: Both users receive CallEnded event and call terminates

### Scenario 4: Network Reconnection

1. **Setup**: User with active WebSocket connection
2. **Step 1**: Simulate network disconnection
3. **Expected**: Service enters reconnecting state
4. **Step 2**: Restore network connection
5. **Expected**: Service automatically reconnects and resumes functionality

### Scenario 5: Driver Switching

1. **Setup**: System configured with Pusher
2. **Step 1**: Admin switches to Laravel Reverb in admin panel
3. **Expected**: Mobile apps automatically detect change and reconnect to Reverb
4. **Step 2**: Test call functionality with new driver
5. **Expected**: All call events work correctly with Reverb

## Performance Testing

### 1. Load Testing

```bash
# Test multiple simultaneous calls
for i in {1..10}; do
  curl -X POST "http://127.0.0.1:8000/api/calls/initiate" \
    -H "Content-Type: application/json" \
    -H "Authorization: Bearer TOKEN_$i" \
    -d '{"receiver_id": '$((i+1))', "type": "video"}' &
done
```

### 2. WebSocket Connection Testing

```javascript
// Test multiple WebSocket connections
const connections = [];
for (let i = 0; i < 100; i++) {
  const pusher = new Pusher('KEY', {
    cluster: 'CLUSTER',
    authEndpoint: 'http://127.0.0.1:8000/broadcasting/auth'
  });
  connections.push(pusher);
}
```

## Troubleshooting Common Issues

### 1. WebSocket Connection Fails

**Symptoms**: Connection state remains disconnected
**Solutions**:
- Check broadcast settings are enabled
- Verify Pusher/Reverb credentials
- Check network connectivity
- Verify authentication token is valid

### 2. Events Not Received

**Symptoms**: CallInitiated events not triggering
**Solutions**:
- Check channel subscription is successful
- Verify user ID matches channel pattern
- Check Laravel logs for broadcast errors
- Test with browser console first

### 3. Admin Panel Not Updating

**Symptoms**: Real-time stats not refreshing
**Solutions**:
- Check JavaScript console for errors
- Verify API endpoints are accessible
- Check CSRF token is valid
- Refresh page and try again

### 4. Call API Errors

**Symptoms**: API calls return 500 errors
**Solutions**:
- Check Laravel logs for detailed errors
- Verify database tables exist
- Check user permissions
- Validate request data format

## Testing Checklist

- [ ] Broadcast settings API returns correct configuration
- [ ] Call signaling config endpoint works
- [ ] Call initiation API works and triggers events
- [ ] Call answer API works and triggers events
- [ ] Call end API works and triggers events
- [ ] Call rejection API works and triggers events
- [ ] WebSocket connections establish successfully
- [ ] Events are received on correct channels
- [ ] Admin panel displays real-time data
- [ ] Admin can end calls remotely
- [ ] Flutter service initializes correctly
- [ ] Flutter receives all call events
- [ ] UI responds correctly to events
- [ ] Reconnection works after network issues
- [ ] Driver switching works automatically
- [ ] Performance is acceptable under load

## Success Criteria

✅ **Backend**: All API endpoints return expected responses
✅ **WebSocket**: Events are delivered reliably to correct channels  
✅ **Admin Panel**: Real-time monitoring works without refresh
✅ **Flutter**: Service connects and handles all events correctly
✅ **Integration**: End-to-end call flow works seamlessly
✅ **Performance**: System handles expected load without issues
✅ **Reliability**: Automatic reconnection and error recovery work

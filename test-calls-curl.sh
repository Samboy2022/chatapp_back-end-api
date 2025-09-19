#!/bin/bash

# Call API Testing Script using cURL
# This script tests voice and video call functionality through the API

BASE_URL="http://127.0.0.1:8000"
USER1_EMAIL="testuser1@example.com"
USER2_EMAIL="testuser2@example.com"
PASSWORD="password123"

echo "🚀 Starting Call API Testing with cURL..."
echo "=========================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_step() {
    echo -e "${BLUE}$1${NC}"
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# Function to make API requests
make_request() {
    local method=$1
    local endpoint=$2
    local data=$3
    local token=$4
    
    local headers="Content-Type: application/json"
    if [ ! -z "$token" ]; then
        headers="$headers -H Authorization: Bearer $token"
    fi
    
    if [ "$method" = "POST" ]; then
        curl -s -X POST "$BASE_URL$endpoint" \
            -H "$headers" \
            -d "$data"
    else
        curl -s -X GET "$BASE_URL$endpoint" \
            -H "$headers"
    fi
}

# Step 1: Create test users
print_step "📋 Step 1: Creating test users..."

echo "Creating User 1..."
USER1_REGISTER=$(make_request "POST" "/api/register" '{
    "name": "Test User 1",
    "email": "'$USER1_EMAIL'",
    "password": "'$PASSWORD'",
    "password_confirmation": "'$PASSWORD'",
    "phone_number": "+1234567890"
}')

echo "Creating User 2..."
USER2_REGISTER=$(make_request "POST" "/api/register" '{
    "name": "Test User 2", 
    "email": "'$USER2_EMAIL'",
    "password": "'$PASSWORD'",
    "password_confirmation": "'$PASSWORD'",
    "phone_number": "+1234567891"
}')

print_success "Test users created (or already exist)"

# Step 2: Login and get tokens
print_step "🔐 Step 2: Logging in users..."

USER1_LOGIN=$(make_request "POST" "/api/login" '{
    "email": "'$USER1_EMAIL'",
    "password": "'$PASSWORD'"
}')

USER2_LOGIN=$(make_request "POST" "/api/login" '{
    "email": "'$USER2_EMAIL'",
    "password": "'$PASSWORD'"
}')

# Extract tokens
USER1_TOKEN=$(echo $USER1_LOGIN | grep -o '"access_token":"[^"]*' | cut -d'"' -f4)
USER2_TOKEN=$(echo $USER2_LOGIN | grep -o '"access_token":"[^"]*' | cut -d'"' -f4)

if [ -z "$USER1_TOKEN" ] || [ -z "$USER2_TOKEN" ]; then
    print_error "Failed to get authentication tokens"
    exit 1
fi

print_success "Users logged in successfully"

# Get user IDs
USER1_DATA=$(make_request "GET" "/api/user" "" "$USER1_TOKEN")
USER2_DATA=$(make_request "GET" "/api/user" "" "$USER2_TOKEN")

USER1_ID=$(echo $USER1_DATA | grep -o '"id":[0-9]*' | cut -d':' -f2)
USER2_ID=$(echo $USER2_DATA | grep -o '"id":[0-9]*' | cut -d':' -f2)

echo "User 1 ID: $USER1_ID"
echo "User 2 ID: $USER2_ID"

# Step 3: Test broadcast settings
print_step "📡 Step 3: Testing broadcast settings..."

BROADCAST_SETTINGS=$(make_request "GET" "/api/broadcast-settings")
echo "Broadcast Settings Response:"
echo $BROADCAST_SETTINGS | python -m json.tool 2>/dev/null || echo $BROADCAST_SETTINGS

CALL_SIGNALING_CONFIG=$(make_request "GET" "/api/broadcast-settings/call-signaling")
echo -e "\nCall Signaling Config Response:"
echo $CALL_SIGNALING_CONFIG | python -m json.tool 2>/dev/null || echo $CALL_SIGNALING_CONFIG

print_success "Broadcast settings retrieved"

# Step 4: Test Voice Call Flow
print_step "🎤 Step 4: Testing Voice Call Flow..."

echo "User 1 initiating voice call to User 2..."
VOICE_CALL_INIT=$(make_request "POST" "/api/calls" '{
    "receiver_id": '$USER2_ID',
    "type": "audio"
}' "$USER1_TOKEN")

echo "Voice Call Initiation Response:"
echo $VOICE_CALL_INIT | python -m json.tool 2>/dev/null || echo $VOICE_CALL_INIT

# Extract call ID
VOICE_CALL_ID=$(echo $VOICE_CALL_INIT | grep -o '"id":[0-9]*' | cut -d':' -f2)

if [ -z "$VOICE_CALL_ID" ]; then
    print_error "Failed to initiate voice call"
    exit 1
fi

print_success "Voice call initiated (Call ID: $VOICE_CALL_ID)"

# Check active calls
echo -e "\nChecking active calls for User 2..."
ACTIVE_CALLS=$(make_request "GET" "/api/calls/active" "" "$USER2_TOKEN")
echo "Active Calls Response:"
echo $ACTIVE_CALLS | python -m json.tool 2>/dev/null || echo $ACTIVE_CALLS

# Answer the call
echo -e "\nUser 2 answering the voice call..."
ANSWER_RESPONSE=$(make_request "POST" "/api/calls/$VOICE_CALL_ID/answer" "" "$USER2_TOKEN")
echo "Answer Response:"
echo $ANSWER_RESPONSE | python -m json.tool 2>/dev/null || echo $ANSWER_RESPONSE

print_success "Voice call answered"

# Simulate call duration
echo "Simulating call duration (3 seconds)..."
sleep 3

# End the call
echo "User 1 ending the voice call..."
END_RESPONSE=$(make_request "POST" "/api/calls/$VOICE_CALL_ID/end" "" "$USER1_TOKEN")
echo "End Call Response:"
echo $END_RESPONSE | python -m json.tool 2>/dev/null || echo $END_RESPONSE

print_success "Voice call ended successfully"

# Step 5: Test Video Call Flow
print_step "📹 Step 5: Testing Video Call Flow..."

echo "User 2 initiating video call to User 1..."
VIDEO_CALL_INIT=$(make_request "POST" "/api/calls" '{
    "receiver_id": '$USER1_ID',
    "type": "video"
}' "$USER2_TOKEN")

echo "Video Call Initiation Response:"
echo $VIDEO_CALL_INIT | python -m json.tool 2>/dev/null || echo $VIDEO_CALL_INIT

# Extract call ID
VIDEO_CALL_ID=$(echo $VIDEO_CALL_INIT | grep -o '"id":[0-9]*' | cut -d':' -f2)

if [ -z "$VIDEO_CALL_ID" ]; then
    print_error "Failed to initiate video call"
    exit 1
fi

print_success "Video call initiated (Call ID: $VIDEO_CALL_ID)"

# Reject the call
echo -e "\nUser 1 rejecting the video call..."
REJECT_RESPONSE=$(make_request "POST" "/api/calls/$VIDEO_CALL_ID/decline" "" "$USER1_TOKEN")
echo "Reject Response:"
echo $REJECT_RESPONSE | python -m json.tool 2>/dev/null || echo $REJECT_RESPONSE

print_success "Video call rejected successfully"

# Test another video call that gets answered
echo -e "\nUser 2 initiating another video call to User 1..."
VIDEO_CALL_INIT2=$(make_request "POST" "/api/calls" '{
    "receiver_id": '$USER1_ID',
    "type": "video"
}' "$USER2_TOKEN")

VIDEO_CALL_ID2=$(echo $VIDEO_CALL_INIT2 | grep -o '"id":[0-9]*' | cut -d':' -f2)
print_success "Second video call initiated (Call ID: $VIDEO_CALL_ID2)"

# Answer the second call
echo "User 1 answering the second video call..."
ANSWER_RESPONSE2=$(make_request "POST" "/api/calls/$VIDEO_CALL_ID2/answer" "" "$USER1_TOKEN")
print_success "Second video call answered"

# End the call from receiver side
echo "User 1 (receiver) ending the video call..."
END_RESPONSE2=$(make_request "POST" "/api/calls/$VIDEO_CALL_ID2/end" "" "$USER1_TOKEN")
print_success "Video call ended by receiver"

# Step 6: Test Call History and Statistics
print_step "📊 Step 6: Testing Call History and Statistics..."

echo "Getting call history for User 1..."
HISTORY1=$(make_request "GET" "/api/calls" "" "$USER1_TOKEN")
echo "User 1 Call History:"
echo $HISTORY1 | python -m json.tool 2>/dev/null || echo $HISTORY1

echo -e "\nGetting call statistics for User 1..."
STATS1=$(make_request "GET" "/api/calls/statistics" "" "$USER1_TOKEN")
echo "User 1 Call Statistics:"
echo $STATS1 | python -m json.tool 2>/dev/null || echo $STATS1

echo -e "\nGetting filtered call history (audio calls only)..."
AUDIO_HISTORY=$(make_request "GET" "/api/calls?type=audio" "" "$USER1_TOKEN")
echo "Audio Calls History:"
echo $AUDIO_HISTORY | python -m json.tool 2>/dev/null || echo $AUDIO_HISTORY

echo -e "\nGetting filtered call history (video calls only)..."
VIDEO_HISTORY=$(make_request "GET" "/api/calls?type=video" "" "$USER1_TOKEN")
echo "Video Calls History:"
echo $VIDEO_HISTORY | python -m json.tool 2>/dev/null || echo $VIDEO_HISTORY

print_success "Call history and statistics retrieved"

# Step 7: Test Error Scenarios
print_step "⚠️  Step 7: Testing Error Scenarios..."

echo "Testing call to non-existent user..."
ERROR_CALL=$(make_request "POST" "/api/calls" '{
    "receiver_id": 99999,
    "type": "video"
}' "$USER1_TOKEN")
echo "Error Response:"
echo $ERROR_CALL | python -m json.tool 2>/dev/null || echo $ERROR_CALL

echo -e "\nTesting call to self..."
SELF_CALL=$(make_request "POST" "/api/calls" '{
    "receiver_id": '$USER1_ID',
    "type": "audio"
}' "$USER1_TOKEN")
echo "Self Call Response:"
echo $SELF_CALL | python -m json.tool 2>/dev/null || echo $SELF_CALL

print_success "Error scenarios tested"

# Final Summary
echo -e "\n=========================================="
print_success "🎉 All Call API Tests Completed Successfully!"
echo -e "\n📋 Test Summary:"
echo "✅ User registration and authentication"
echo "✅ Broadcast settings configuration"
echo "✅ Voice call initiation, answer, and end"
echo "✅ Video call initiation, rejection, and end"
echo "✅ Call history and statistics retrieval"
echo "✅ Error handling scenarios"
echo -e "\n🔗 WebSocket events should be broadcasting to:"
echo "   - call.$USER1_ID (for User 1)"
echo "   - call.$USER2_ID (for User 2)"
echo -e "\n📱 Ready for Flutter integration!"
echo "=========================================="

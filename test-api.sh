#!/bin/bash
# Comprehensive API test for messaging system
# Run this on the production server
TOKEN="12|wmwhS0s0lfGqAJHlR49mg6n8kgRq34P5II1QD0DS4f92af32"
BASE="https://collegelibrary.duncowebsolutions.co.ke/api/v1"
AUTH="-H 'Accept: application/json' -H 'Authorization: Bearer $TOKEN'"

echo "=== 1. SEARCH USERS ==="
eval curl -s $AUTH "$BASE/users/search?q=john" | python3 -m json.tool

echo -e "\n=== 2. INBOX ==="
eval curl -s $AUTH "$BASE/messages/inbox" | python3 -m json.tool

echo -e "\n=== 3. SENT ==="
eval curl -s $AUTH "$BASE/messages/sent" | python3 -m json.tool

echo -e "\n=== 4. MESSAGE DETAIL (id=4) ==="
eval curl -s $AUTH "$BASE/messages/4" | python3 -m json.tool

echo -e "\n=== 5. SEND NEW MESSAGE ==="
eval curl -s -X POST $AUTH "$BASE/messages/send" \
  -H 'Content-Type: application/json' \
  -d '{"subject":"APK Test from curl","body":"Hello from API test","recipient_ids":[4],"priority":"normal","type":"direct"}' | python3 -m json.tool

echo -e "\n=== 6. SENT AFTER SEND ==="
eval curl -s $AUTH "$BASE/messages/sent" | python3 -m json.tool

echo -e "\n=== 7. UNREAD COUNT ==="
eval curl -s $AUTH "$BASE/messages/unread-count" | python3 -m json.tool

echo -e "\n=== 8. ARCHIVE MESSAGE (id=5) ==="
eval curl -s -X POST $AUTH "$BASE/messages/5/archive" | python3 -m json.tool

echo -e "\n=== 9. ARCHIVED LIST ==="
eval curl -s $AUTH "$BASE/messages/archived" | python3 -m json.tool

echo -e "\n=== 10. RESTORE FROM ARCHIVE ==="
eval curl -s -X POST $AUTH "$BASE/messages/5/unarchive" | python3 -m json.tool

echo -e "\nALL ENDPOINTS TESTED"

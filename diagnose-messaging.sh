#!/bin/bash
set -euo pipefail
# Usage: bash diagnose-messaging.sh [user_email]
# Tests the messaging API endpoints to verify fixes

USER_EMAIL="${1:-librarian@ollmchs.ac.ke}"
BASE="https://collegelibrary.duncowebsolutions.co.ke/api/v1"

echo "=== Messaging API Diagnostics ==="

# Create a test token
TOKEN=$(php artisan tinker --execute="echo \App\Models\User::where('email', '${USER_EMAIL}')->first()?->createToken('diag')->plainTextToken;" 2>/dev/null)
if [ -z "$TOKEN" ]; then
    echo "FAIL: Could not create token for $USER_EMAIL"
    echo "Try: php artisan tinker"
    echo 'then: App\Models\User::where("email","your@email.com")->first()->createToken("diag")->plainTextToken'
    exit 1
fi

echo "Token: ${TOKEN:0:20}..."

AUTH="Authorization: Bearer $TOKEN"
ACCEPT="Accept: application/json"

echo ""
echo "--- 1. User Search ---"
RESP=$(curl -s -H "$AUTH" -H "$ACCEPT" "$BASE/users/search?q=admin" -w '\n%{http_code}')
HTTP_CODE=$(echo "$RESP" | tail -1)
BODY=$(echo "$RESP" | head -n -1)
echo "HTTP $HTTP_CODE"
echo "$BODY" | python3 -m json.tool 2>/dev/null || echo "$BODY" | head -c 300
COUNT=$(echo "$BODY" | python3 -c "import sys,json; d=json.load(sys.stdin); print(len(d.get('data',[])))" 2>/dev/null || echo "parse-error")
echo "Result count: $COUNT"
if [ "$HTTP_CODE" = "200" ] && [ "$COUNT" -gt "0" ]; then echo "PASS"; else echo "FAIL"; fi

echo ""
echo "--- 2. Send Message ---"
RESP=$(curl -s -X POST -H "$AUTH" -H "$ACCEPT" \
  -H "Content-Type: application/json" \
  -d '{"recipient_ids":[1],"subject":"Diag Test","body":"Diagnostic message","priority":"normal","type":"direct"}' \
  "$BASE/messages/send" -w '\n%{http_code}')
HTTP_CODE=$(echo "$RESP" | tail -1)
BODY=$(echo "$RESP" | head -n -1)
echo "HTTP $HTTP_CODE"
if [ "$HTTP_CODE" = "201" ]; then
    MSG_ID=$(echo "$BODY" | python3 -c "import sys,json; print(json.load(sys.stdin)['data']['id'])" 2>/dev/null)
    echo "Message ID: $MSG_ID"
    echo "PASS"
else
    echo "$BODY" | head -c 300
    echo "FAIL"
fi

echo ""
echo "--- 3. Sent Messages ---"
RESP=$(curl -s -H "$AUTH" -H "$ACCEPT" "$BASE/messages/sent" -w '\n%{http_code}')
HTTP_CODE=$(echo "$RESP" | tail -1)
BODY=$(echo "$RESP" | head -n -1)
echo "HTTP $HTTP_CODE"
if [ "$HTTP_CODE" = "200" ]; then
    HAS_SENDER=$(echo "$BODY" | python3 -c "
import sys,json
d=json.load(sys.stdin)
items=d.get('data',[])
if not items: print('empty-list')
else:
    ok=all('sender' in m and m['sender'] is not None for m in items)
    print('ok' if ok else 'missing-sender')
" 2>/dev/null)
    HAS_RECIPIENTS=$(echo "$BODY" | python3 -c "
import sys,json
d=json.load(sys.stdin)
items=d.get('data',[])
if not items: print('empty-list')
else:
    ok=all('recipients' in m for m in items)
    print('ok' if ok else 'missing-recipients')
" 2>/dev/null)
    echo "Sender check: $HAS_SENDER"
    echo "Recipients check: $HAS_RECIPIENTS"
    if [ "$HAS_SENDER" = "ok" ] && [ "$HAS_RECIPIENTS" = "ok" ]; then echo "PASS"
    elif [ "$HAS_SENDER" = "empty-list" ]; then echo "SKIP (no sent messages)"
    else echo "FAIL"; fi
else
    echo "$BODY" | head -c 200
    echo "FAIL"
fi

echo ""
echo "--- 4. Inbox Messages ---"
RESP=$(curl -s -H "$AUTH" -H "$ACCEPT" "$BASE/messages/inbox" -w '\n%{http_code}')
HTTP_CODE=$(echo "$RESP" | tail -1)
BODY=$(echo "$RESP" | head -n -1)
echo "HTTP $HTTP_CODE"
if [ "$HTTP_CODE" = "200" ]; then
    HAS_SENDER=$(echo "$BODY" | python3 -c "
import sys,json
d=json.load(sys.stdin)
items=d.get('data',[])
if not items: print('empty-list')
else:
    ok=all('sender' in m and m['sender'] is not None for m in items)
    print('ok' if ok else 'missing-sender')
" 2>/dev/null)
    echo "Sender check: $HAS_SENDER"
    if [ "$HAS_SENDER" = "ok" ]; then echo "PASS"
    elif [ "$HAS_SENDER" = "empty-list" ]; then echo "SKIP (empty inbox)"
    else echo "FAIL"; fi
else
    echo "$BODY" | head -c 200
    echo "FAIL"
fi

echo ""
echo "=== Diagnostics Complete ==="

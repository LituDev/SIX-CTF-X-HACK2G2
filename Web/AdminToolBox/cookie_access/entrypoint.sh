#!/bin/bash

echo "Starting the app..."
apache2-foreground &

echo "Waiting for creds to be up..."
while ! curl -s http://creds > /dev/null; do sleep 3; done

JSON_RESPONSE=$(curl "http://creds/?route=login&username=admin&password=$ADMIN_PASSWORD&FLAG=$FLAG")
TOKEN=$(echo $JSON_RESPONSE | jq -r '.token')
echo $TOKEN

rm -rf /app/cache

curl "http://127.0.0.1" -H "Cookie: token=$TOKEN"

unset ADMIN_PASSWORD
unset FLAG
unset TOKEN

echo "App started."

# wait forever
while [ 1 ] ; do
  sleep 1
done

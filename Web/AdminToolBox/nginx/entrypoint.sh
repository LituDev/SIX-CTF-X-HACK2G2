#!/bin/sh

# wait for http://cookie_access to be up before starting nginx with curl
echo "Waiting for cookie_access to be up..."
while ! curl -s http://cookie_access > /dev/null; do sleep 3; done

echo "Waiting for creds to be up..."
# wait for http://creds to be up before starting nginx with curl
while ! curl -s http://creds > /dev/null; do sleep 3; done

echo "Starting nginx..."
/bin/sh /docker-entrypoint.sh nginx -g "daemon off;"
echo "Done!"
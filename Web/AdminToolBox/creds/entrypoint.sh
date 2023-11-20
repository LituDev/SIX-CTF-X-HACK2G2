#!/bin/bash

echo "Configuring the app... (FLAG: $FLAG)"
echo $FLAG > /secret.txt

echo "Replacing the JWT secret..."
sed -i "s/the_jwt_secret_from_env/$JWT_SECRET/g" /app/index.php

unset JWT_SECRET
unset FLAG

echo "Starting the app..."
apache2-foreground
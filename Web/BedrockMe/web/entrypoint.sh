#!/bin/bash

echo "Configuring the app... (FLAG: $FLAG)"
echo $FLAG > /secret.txt

unset FLAG

echo "Starting the app..."
apache2-foreground
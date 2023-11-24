#!/bin/bash

sudo docker rm coffee1 --force
sudo docker build -t coffee .
sudo docker run -dp 8081:80 --cap-add LINUX_IMMUTABLE --network bridge --name coffee1 coffee


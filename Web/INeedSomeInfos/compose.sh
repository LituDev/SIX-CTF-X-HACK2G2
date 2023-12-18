#!/bin/bash

sudo docker rm coffee1 --force
sudo docker build -t ineedsomeinfos .
sudo docker run -dp 8082:80 --name ineedsomeinfos1 ineedsomeinfos


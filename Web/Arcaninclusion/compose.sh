#!/bin/bash

sudo docker rm arcaninclusion1 --force
sudo docker build -t arcaninclusion .
sudo docker run -dp 8084:80  --name arcaninclusion1 arcaninclusion


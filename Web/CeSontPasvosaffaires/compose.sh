#!/bin/bash

sudo docker rm cesontpasvosaffaires01 --force
sudo docker build -t cesontpasvosaffaires .
sudo docker run -dp 8083:80 --network bridge --name cesontpasvosaffaires01 cesontpasvosaffaires


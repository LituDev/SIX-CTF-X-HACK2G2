#!/bin/bash

# Function to check if a docker image exists
image_exists() {
    docker inspect --type=image $1 > /dev/null 2>&1
    return $?
}

# Function to check if a docker container exists
container_exists() {
    docker inspect --type=container $1 > /dev/null 2>&1
    return $?
}

# Function to run a docker container
run_container() {
    local container_name=$1
    local image_name=$2
    local port=$3

    # Check if the container already exists
    if container_exists $container_name; then
        echo "Removing existing $container_name container..."
        docker rm -f $container_name
    fi

    echo "Running $container_name container..."
    docker run -d --name $container_name -p $port $image_name
}

docker stop agarcc agarsc

docker system prune -a

# Build and run the server
SERVER_IMAGE="agarsi"
SERVER_CONTAINER="agarsc"

if ! image_exists $SERVER_IMAGE; then
    echo "Building server image..."
    docker build -f Dockerfile.server -t $SERVER_IMAGE .
fi

run_container $SERVER_CONTAINER $SERVER_IMAGE "8080:8080"

# Build and run the client
CLIENT_IMAGE="agarci"
CLIENT_CONTAINER="agarcc"

if ! image_exists $CLIENT_IMAGE; then
    echo "Building client image..."
    docker build -f Dockerfile.client -t $CLIENT_IMAGE .
fi

run_container $CLIENT_CONTAINER $CLIENT_IMAGE "3000:3000"

echo "Script finished."

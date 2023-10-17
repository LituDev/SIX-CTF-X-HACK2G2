#!/bin/bash

# Utilisé pour compresser le flag
for k in {1..50}
do 
    if [ $((k % 5)) -eq 0 ]; then
        bzip2 "./flag"
        mv "./flag.bz2" "./flag"
    else if [ $((k % 2)) -eq 0 ]; then
    	zip -r flag.zip "flag"
    	mv "./flag.zip" "./flag"
    else
    	gzip "./flag"
    	mv "./flag.gz" "./flag"
    fi
    fi
done


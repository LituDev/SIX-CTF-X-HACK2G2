#!/bin/bash
FLAG_ENCRYPTED="U2FsdGVkX1/pAgWRQR0BreGfxi5KsZlyBE3Ve6+i1ivwjG4S2LtU3sTqEArG/Cwx"
PASSWORD="enter_me_senpai_uwu"

read -sp "Entrez le mot de passe: " input_password

if [ "$input_password" == "$PASSWORD" ]; then
  echo $FLAG_ENCRYPTED | openssl enc -aes-256-cbc -a -d -salt -pass pass:$PASSWORD 2>/dev/null
else
  echo "Mot de passe incorrect!"
fi

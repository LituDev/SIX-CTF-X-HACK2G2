#!/bin/python
import os
import requests

def main(protocol,host,port) : 

	if os.path.exists("/tmp/verifications.txt") :
		# Recupération du contenu du fichier
		with open("/tmp/verifications.txt","r") as f : 
			content = f.read()

		# Reinitialisation du contenu du fichier
		# with open("/tmp/verifications.txt","w") as f : 
		# 	pass
		
		# Recupération des chemins
		paths = content.split("\n")

		for line in paths : 
			uri = protocol + "://" + host + ":" + port + line
			print(uri)
			os.system("node ../bot/bot.js " + uri + " &")



if __name__ == '__main__' :
	protocol = "http" 
	host = "localhost"
	port = '80'
	main(protocol,host,port)
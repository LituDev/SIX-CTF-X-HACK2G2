#!/usr/bin/python3
import os
import requests

def main(protocol,host,port) : 

	os.system("echo 'Script lance le '$(date) >> /tmp/routine.log")
	if os.path.exists("/tmp/verifications.txt") :
		with open("/tmp/verifications.txt","r") as f : 
			content = f.read()

		with open("/tmp/verifications.txt","w") as f : 
			pass
		
		paths = content.split("\n")

		for line in paths[:-1] : 
			uri = protocol + "://" + host + ":" + port + line
			print(uri)
			os.system(f"node /bot/bot.js '{uri}' &")




if __name__ == '__main__' :
	protocol = "http" 
	host = "localhost"
	port = '80'
	main(protocol,host,port)


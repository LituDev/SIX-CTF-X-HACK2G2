def main(flag):
	for char in flag : 
		printInWhiteSpace(char)
	for _ in range(3) :
		print()

def printInWhiteSpace(char):
	print("   " + binaryWhiteSpaceFormatted(char))		# 3xSpaces + character
	print("	") 											# Tab
	print("  ", end = "")								# 2xSpaces

def binaryWhiteSpaceFormatted(char) : 
	rawBin = format(ord(char), 'b')
	whiteSpaceBin = ""
	for bit in rawBin : 
		if bit == "0" :
			whiteSpaceBin += " "
		elif bit == "1" :
			whiteSpaceBin += "	"
		else :
			print("ERROR IN BINARY " + rawBin)
			break
	return whiteSpaceBin

if __name__ == "__main__" :
	flag = 'IUT{AHH_JAI_VU_QUE_TU_AS_VU}'
	main(flag)
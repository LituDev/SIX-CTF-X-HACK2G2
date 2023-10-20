import random

flag = "C3773_F01S_C1_J3_5U15_ARR1V3!!"
length = len(flag)
# print(length)
startPoint = [21,23]

""" 
    Create an empty 2D array 50*50
    Nodes looks like this ["", [-1,-1]] 
"""
def createEmptyTab() : 
    chemins = []
    for k in range (50) : 
        chemins.append([])
        for i in range(50) : 
            chemins[k].append(["", [-1,-1]])
    return chemins
            
"""
    Find random nodes to hide the flag starting from startpoint
"""
def setFlag(chemins,flag) : 
    for index,char in enumerate(flag) : 
        if index == 0  : 
            currentAddress = startPoint
            chemins[currentAddress[0]][currentAddress[1]][0] = char
            pastAddress = currentAddress
        else :
            currentAddress = findValidAddress(chemins)
            chemins[currentAddress[0]][currentAddress[1]][0] = char
            chemins[pastAddress[0]][pastAddress[1]][1] = currentAddress
            # print("Past : ", end = "" )
            # print(chemins[pastAddress[0]][pastAddress[1]])
            pastAddress = currentAddress
    # print("Past : ", end = "" )
    # print(chemins[pastAddress[0]][pastAddress[1]])
    return chemins

"""
    Find random nodes to hide the string
"""
def setString(chemins,string) : 
    for index,char in enumerate(string) : 
        if index == 0  : 
            currentAddress = findValidAddress(chemins)
            chemins[currentAddress[0]][currentAddress[1]][0] = char
            pastAddress = currentAddress
        else :
            currentAddress = findValidAddress(chemins)
            chemins[currentAddress[0]][currentAddress[1]][0] = char
            chemins[pastAddress[0]][pastAddress[1]][1] = currentAddress
            # print("Past : ", end = "" )
            # print(chemins[pastAddress[0]][pastAddress[1]])
            pastAddress = currentAddress
    # print("Past : ", end = "" )
    # print(chemins[pastAddress[0]][pastAddress[1]])


"""
    Returns a random empty address in the 2D tab
"""
def findValidAddress(chemins) : 
    x = random.randint(0,len(chemins)-1)
    y = random.randint(0,len(chemins[x])-1)
    while (chemins[x][y][0] != "") : 
        x = random.randint(0,len(chemins)-1)
        y = random.randint(0,len(chemins[x])-1)
    return [x,y]

""" 
    Fullfill the 2D array with random 28 char strings
"""
def fullfill(chemins) : 
    for k in range(78) : 
        print(k)
        string = ""
        for _ in range(28) : 
            string += random.choice(list(flag))
        #print(string)
        setString(chemins, string)

"""
    Display the 2D array better
"""
def printTab(chemins) : 
    for k in range(len(chemins)) : 
        if k == 0 : 
            print("chemins = [",end = "")
        print(chemins[k], end = "")
        if k == len(chemins) -1 : 
            print("]")
        else : 
            print(",")
        


if __name__ == "__main__" : 
    chemins = createEmptyTab()
    setFlag(chemins,flag)
    setString(chemins, "watch?v=dQw4w9WgXcQ")
    setString(chemins, "IUT{this_is_not_the_flag}")
    setString(chemins, "Franchement je trouve ça cool")
    setString(chemins, "Eh noooooon")
    setString(chemins, "Le flag est par la -> ou pas")
    setString(chemins, "Une petite douche ?")
    setString(chemins, "Quelqu'un paie sa tournée ?")
    setString(chemins, "J'adore les easter eggs")
    fullfill(chemins)
    printTab(chemins)
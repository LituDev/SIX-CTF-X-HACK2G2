On ouvre l'APK dans jadx.
Dans com.example.snakegame.MainActivity, la fonction moveSnake vérifie que notre score est pas égal à 0xdeadcoffe1337.
Si c'est le cas, on décode en AES/CBC les bytes b581ef9ae5a42240eb9d3f29713e312739cc098a5f7fea38d15bd5d577d801fa7ad59ab03868e8dae609445fee492c7c.
L'IV est T0tallyR4nd0m_IV
La clée est 0xdeadcoffe1337 (3917405579252535) en int.

FLAG = IUT{St4tic_An4ly5i5_0n_APK_r0ck5}

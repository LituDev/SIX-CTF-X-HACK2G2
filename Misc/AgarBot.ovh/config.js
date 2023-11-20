module.exports = Object.seal({
    // MultiOgarII configurations file
    // Lines starting with // are comments
    
    // [NOTES]
    // MultiOgarII uses cell size instead of cell mass to improve performance!
    // In order to get the cell size from mass value, you need to calculate using this formula:
    //    size: SQRT( mass * 100 )
    //
    // For example, to set start mass: 43:
    //     size: SQRT( 43 * 100 ): SQRT( 4300 ): 65.57
    // Set playerStartSize: 66
    //
    // Also, you can use the following syntax to specify mass:
    //     playerStartSize: massToSize(43)
    // It will be automatically converted to 66
    
    // [LOGGING]
    // logVerbosity: Console log level (0=NONE// 1=FATAL// 2=ERROR// 3=WARN// 4=INFO// 5=DEBUG)
    // logFileVerbosity: File log level
    "logVerbosity": 4,
    "logFileVerbosity": 5,
    
    // [SERVER]
    // serverTimeout: Seconds to keep connection alive for non-responding client
    // serverMaxConnections: Maximum number of connections to the server. (0 for no limit)
    // serverPort: Server port which will be used to listen for incoming connections
    // serverBind: Server network interface which will be used to listen for incoming connections (0.0.0.0 for all IPv4 interfaces)
    // serverTracker: Set to 1 if you want to show your server on the tracker http://ogar.mivabe.nl/master (check that your server port is opened for external connections first!)
    // serverGamemode: Gamemodes: 0: FFA, 1: Teams, 2: Experimental, 3: Rainbow
    // serverBots: Number of player bots to spawn (Experimental)
    // serverViewBase: Base view distance of players. Warning: high values may cause lag! Min value is 1920x1080
    // serverMinScale: Minimum viewbox scale for player (low value leads to lags due to large visible area for big cell)
    // serverSpectatorScale: Scale (field of view) used for free roam spectators (low value leads to lag, vanilla: 0.4, old vanilla: 0.25)
    // serverStatsPort: Port for the stats server. Having a negative number will disable the stats server.
    // serverStatsUpdate: Update interval of server stats in seconds
    // mobilePhysics: Whether or not the server uses mobile agar.io physics
    // badWordFilter: Toggle whether you enable bad word filter (set to 0 to disable)
    // serverRestart: Toggle whether you want your server to auto-restart in minutes. (set to 0 to disable)
    "serverTimeout": 300,
    "serverMaxConnections": 500,
    "serverPort": 8080,
    "serverBind": "0.0.0.0",
    "serverTracker": 0,
    "serverGamemode": 0,
    "serverBots": 0,
    "serverViewBaseX": 1920,
    "serverViewBaseY": 1080,
    "serverMinScale": 0.15,
    "serverSpectatorScale": 0.4,
    "serverStatsPort": 88,
    "serverStatsUpdate": 60,
    "mobilePhysics": 0,
    "badWordFilter": 1,
    "serverRestart": -1,
    
    // [CLIENT]
    // serverMaxLB: Controls the maximum players displayed on the leaderboard.
    // serverChat: Allows the usage of server chat. 0: no chat, 1: use chat.
    // serverChatAscii: Set to 1 to disable non-ANSI letters in the chat (english only)
    // serverName: Server name, for example "My great server"
    // serverWelcome1: First server welcome message
    // serverWelcome2: Second server welcome message (optional, for info, etc)
    // clientBind: Only allow connections to the server from specified client (eg: http://agar.io - http://mywebsite.com - http://more.com) [Use ' - ' to seperate different websites]
    "serverMaxLB": 10,
    "serverChat": 1,
    "serverChatAscii": 1,
    "separateChatForTeams": 0,
    "serverName": "MultiOgarII #1",
    "serverWelcome1": "Welcome to MultiOgarII!",
    "serverWelcome2": "",
    "clientBind": "",
    
    // [ANTI-BOT]
    // serverIpLimit: Controls the maximum number of connections from the same IP (0 for no limit)
    // serverMinionIgnoreTime: minion detection disable time on server startup [seconds]
    // serverMinionThreshold: max connections within serverMinionInterval time period, which will not be marked as minion
    // serverMinionInterval: minion detection interval [milliseconds]
    // serverScrambleLevel: Toggles scrambling of coordinates. 0: No scrambling, 1: lightweight scrambling. 2: full scrambling (also known as scramble minimap), 3 - high level scrambling (no border)
    // playerBotGrow: Cells greater than 625 mass cannot grow from players under 17 mass (set to 1 to disable)
    "serverIpLimit": 30,
    "serverMinionIgnoreTime": 30,
    "serverMinionThreshold": 10,
    "serverMinionInterval": 1000,
    "serverScrambleLevel": 0,
    "playerBotGrow": 0,
    
    // [BORDER]
    // Border size (vanilla 14142.135623730952)
    "borderWidth": 4000,
    "borderHeight": 4000,
    
    // [FOOD]
    // foodMinSize: vanilla 10 (mass: 10*10/100: 1 mass)
    // foodMaxSize: vanilla 20 (mass: 20*20/100: 4 mass)
    // foodAmount: The number of food to spawn
    // foodMassGrow: Enable food mass grow ?
    "foodMinSize": 10,
    "foodMaxSize": 20,
    "foodAmount": 0,
    "foodMassGrow": 1,
    
    // [VIRUSES]
    // virusMinSize: Minimum virus size. (vanilla: mass: val*val/100: 100 mass)
    // virusMaxSize: Maximum virus size (vanilla: mass: val*val/100: 200 mass)
    // virusMaxPoppedSize: Maximum size a popped cell can have
    // virusEqualPopSize: Whether popped cells have equal size or not (1 to enable)
    // virusAmount: Amount of viruses to spawn
    // virusMaxAmount": Maximum amount of viruses allowed ingame.
    // motherCellMaxMass: Maximum amount of mass a mothercell is allowed to have (0 for no limit)
    // virusVelocity: Velocity of moving viruses (speed and distance)
    // virusMaxCells: Maximum cells a player is allowed to have from virus splits (0 for playerMaxCells)
    "virusMinSize": 100,
    "virusMaxSize": 141.421356237,
    "virusMaxPoppedSize": 60,
    "virusEqualPopSize": 0,
    "virusAmount": 0,
    "virusMaxAmount": 100,
    "motherCellMaxMass": 0,
    "virusVelocity": 780,
    "virusMaxCells": 0,
    
    // [EJECTED MASS]
    // ejectSize: vanilla: mass: val*val/100: 13 mass?
    // ejectSizeLoss: Eject size which will be substracted from player cell (vanilla: mass: val*val/100: 18 mass?)
    // ejectCooldown: Tick count until a player can eject mass again in ticks (1 tick: 40 ms)
    // ejectSpawnPercent: Chance for a player to spawn from ejected mass. 0.5: 50% (set to 0 to disable)
    // ejectVirus: Whether or not players can eject viruses instead of mass
    // ejectVelocity: Velocity of ejecting cells (speed and distance)
    "ejectSize": 36.06,
    "ejectSizeLoss": 42.43,
    "ejectCooldown": 3,
    "ejectSpawnPercent": 0.5,
    "ejectVirus": 0,
    "ejectVelocity": 780,
    
    // [PLAYERS]
    // Reminder: MultiOgarII uses cell size instead of mass!
    //       playerStartMass replaced with playerStartSize
    //
    // playerMinSize: Minimum size a player cell can decay too. (vanilla: val*val/100: 10 mass)
    // playerMaxSize: Maximum size a player cell can have before auto-splitting. (vanilla: mass: val*val/100: 22500 mass)
    // playerMinSplitSize: Mimimum size a player cell has to be to split. (vanilla: mass: val*val/100: 35 mass)
    // playerMinEjectSize: Minimum size a player cell has to be to eject mass. (vanilla: mass: val*val/100: 35 mass)
    // playerStartSize: Start size of the player cell. (vanilla: mass: val*val/100: 10 mass)
    // playerMaxCells: Maximum cells a player is allowed to have. (vanilla is 16)
    // playerSpeed: Player speed multiplier (1: normal speed, 2: twice the normal speed)
    // playerRecombineTime: Base time in seconds before a cell is allowed to recombine (vanilla: 30 seconds)
    // playerDecayRate: Amount of player cell size lost per second
    // playerDecayCap: Maximum mass a cell can have before it's decayrate multiplies by 10. (0 to disable)
    // playerDisconnectTime: Time in seconds before a disconnected player's cell is removed from the server (Set to -1 to never remove)
    // splitVelocity: Velocity of splitting playercells (speed and distance)
    "playerMinSize": 31.6227766017,
    "playerMaxSize": 1500,
    "playerMinSplitSize": 59.16079783,
    "playerMinEjectSize": 59.16079783,
    "playerStartSize": 500,
    "playerMaxCells": 16,
    "playerSpeed": 1,
    "playerDecayRate": 0.002,
    "playerDecayCap": 0,
    "playerRecombineTime": 30,
    "playerMaxNickLength": 15,
    "playerDisconnectTime": -1,
    "splitVelocity": 780,
    
    // [MINIONS]
    // Custom minion settings
    // minionStartSize: Start size of minions (mass: val*val/100: 10 mass)
    // minionMaxStartSize: Maximum value of random start size for minions (set value higher than minionStartSize to enable)
    // minionCollideTeam: Determines whether minions colide with their team in the Teams gamemode (0: OFF, 1: ON)
    // serverMinions: Amount of minions each player gets once they spawn
    // defaultName: Default name for all minions if name is not specified using command (put <r> before the name for random skins!)
    // minionsOnLeaderboard: Whether or not to show minions on the leaderboard. (Set 0 to disable)
    "minionStartSize": 31.6227766017,
    "minionMaxStartSize": 31.6227766017,
    "minionCollideTeam": 0,
    "serverMinions": 0,
    "defaultName": "minion",
    "minionsOnLeaderboard": 0,
    
    // [Gamemode]
    // Custom gamemode settings
    
    })
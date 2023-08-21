CREATE TABLE IF NOT EXISTS utilisateurs (
    email TEXT PRIMARY KEY,
    nom TEXT NOT NULL,
    prenom TEXT NOT NULL,
    sexe TEXT NOT NULL,
    taille INTEGER NOT NULL, 
    poids INTEGER NOT NULL,
    password_hash TEXT NOT NULL,
    birthDate DATE NOT NULL
);

CREATE TABLE IF NOT EXISTS activity (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    description TEXT NOT NULL,
    date DATE NOT NULL,
    userMail INTEGER NOT NULL,
    FOREIGN KEY (userMail) REFERENCES utilisateurs(email)
);

CREATE TABLE IF NOT EXISTS mesures (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    time DATE NOT NULL,
    cardio_frequency INTEGER NOT NULL,
    longitude FLOAT NOT NULL,
    lattitude FLOAT NOT NULL,
    altitude INTEGER NOT NULL,
    activityId INTEGER NOT NULL,
    FOREIGN KEY (activityId) REFERENCES activity(id)
);

INSERT INTO utilisateurs VALUES ("admin@sporttrack.fr", "Onewheal", "Olivier", "Homme", 130, 120, "$2y$10$/NAWbkA0KegKBIgTRi/.LOBfSRF6fNX4VdiLX4ZhQh2X6kuKF26dW", "1995-01-01");
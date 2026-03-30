CREATE DATABASE agent_site;
USE agent_site;

CREATE TABLE users (
    ncode       VARCHAR(50)  PRIMARY KEY,
    nom         VARCHAR(50)  DEFAULT NULL,
    prenom      VARCHAR(50)  DEFAULT NULL,
    age         INT          DEFAULT NULL,
    specialite  VARCHAR(100) NOT NULL,
    grade       VARCHAR(50)  NOT NULL,
    email       VARCHAR(100) NOT NULL UNIQUE,
    passwords   VARCHAR(255) NOT NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    last_login  TIMESTAMP    NULL,
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE specialite (
    ncode       INT AUTO_INCREMENT PRIMARY KEY,
    specialite  ENUM ('agent_double', 'agent_informateur', 'cyber_espion') NOT NULL,
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE grade (
    ncode INT AUTO_INCREMENT PRIMARY KEY,
    grade ENUM ('expert', 'confirme', 'novice') NOT NULL,
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

 CREATE TABLE messages (
    id_message INT AUTO_INCREMENT PRIMARY KEY,
    id_expediteur INT NOT NULL,
    id_receveur INT NOT NULL,
    contenu TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    INDEX (id_expediteur),
    INDEX (id_receveur),

    FOREIGN KEY (id_expediteur) REFERENCES users(id),
    FOREIGN KEY (id_receveur) REFERENCES users(id),
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

    -- =====================
    -- INSERT USERS
    -- =====================
    INSERT INTO users (ncode, nom, prenom, age, specialite, grade, email, passwords) 
    VALUES
    ('agent007', 'James', 'Bond', '42',     'agent_double',       'expert',   'agent007@mail.com',      'azerty123'),
    ('agentcorbeau', 'Daniel', 'Cormier', '35', 'agent_informateur',  'expert',   'agentcorbeau@mail.com',  'ytreza123'),
    ('agentspy',  'Marc', 'Dupont', '28',   'cyber_espion',       'expert',   'agentspy@mail.com',      'wxcvb123'),
    ('agentfaucon', 'Jack', 'Frost', '32', 'agent_informateur',  'confirme', 'agentfaucon@mail.com',   'bvcxw123'),
    ('agentshadow', 'John', 'Doe', '38',  'agent_double',       'confirme', 'agentshadow@mail.com',   'qsdfg123'),
    ('agentviper', 'Elizabeth', 'Bennet', '25',   'cyber_espion',       'novice',   'agentviper@mail.com',    'gfdsq123'),
    ('agenthunter', 'Cassandra', 'Stone', '31',  'agent_double',       'expert',   'agenthunter@mail.com',   'hunter123'),
    ('agentphantom', 'Olivia', 'Wilde', '29', 'agent_informateur',  'novice',   'agentphantom@mail.com',  'phantom123'),
    ('agentneo',  'Ethan', 'Hunt', '34',    'cyber_espion',       'novice',   'agentneo@mail.com',      'neo123'),
    ('agentchamber', 'Liam', 'Neeson', '37', 'agent_double',       'confirme', 'agentchamber@mail.com',  'chamber123');
CREATE TABLE Rôle(
   Id_Rôle INT AUTO_INCREMENT,
   Libellé TEXT,
   PRIMARY KEY(Id_Rôle)
);

CREATE TABLE Utilisateur(
   Id_Utilisateur INT AUTO_INCREMENT,
   nom VARCHAR(50),
   mdp VARCHAR(50),
   Id_Rôle INT NOT NULL,
   PRIMARY KEY(Id_Utilisateur),
   FOREIGN KEY(Id_Rôle) REFERENCES Rôle(Id_Rôle)
);

CREATE TABLE Articles(
   Id_Articles INT AUTO_INCREMENT,
   titre VARCHAR(50),
   Contenu TEXT,
   date_publi DATE,
   Id_Utilisateur INT NOT NULL,
   PRIMARY KEY(Id_Articles),
   FOREIGN KEY(Id_Utilisateur) REFERENCES Utilisateur(Id_Utilisateur)
);

CREATE TABLE Like_DislikeArticles(
   Id_Like_DislikeArticles INT AUTO_INCREMENT,
   type BOOLEAN,
   Id_Articles INT NOT NULL,
   PRIMARY KEY(Id_Like_DislikeArticles),
   FOREIGN KEY(Id_Articles) REFERENCES Articles(Id_Articles)
);

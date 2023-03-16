CREATE TABLE Role(
   Id_Role INT AUTO_INCREMENT,
   Libellé TEXT,
   PRIMARY KEY(Id_Role)
);

CREATE TABLE Utilisateur(
   Id_Utilisateur INT AUTO_INCREMENT,
   nom VARCHAR(50),
   mdp VARCHAR(50),
   Id_Role INT NOT NULL,
   PRIMARY KEY(Id_Utilisateur),
   FOREIGN KEY(Id_Role) REFERENCES Role(Id_Role)
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
   Id_Utilisateur INT NOT NULL,
   Id_Articles INT NOT NULL,
   PRIMARY KEY(Id_Like_DislikeArticles),
   FOREIGN KEY(Id_Utilisateur) REFERENCES Utilisateur(Id_Utilisateur),
   FOREIGN KEY(Id_Articles) REFERENCES Articles(Id_Articles)
);

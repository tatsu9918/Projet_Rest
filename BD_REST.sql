CREATE TABLE Article(
   Id_article INT AUTO_INCREMENT,
   date_publication DATETIME,
   contenu TEXT,
   Auteur VARCHAR(50),
   PRIMARY KEY(Id_article)
);

CREATE TABLE Utilisateur(
   id_user INT AUTO_INCREMENT,
   Nom VARCHAR(50),
   mot_de_passe VARCHAR(50),
   PRIMARY KEY(id_user)
);

CREATE TABLE Rôle(
   Id_rôle INT AUTO_INCREMENT,
   Intitulé VARCHAR(50),
   id_user INT NOT NULL,
   PRIMARY KEY(Id_rôle),
   FOREIGN KEY(id_user) REFERENCES Utilisateur(id_user)
);

CREATE TABLE Like_dislike(
   id_like_dislike INT AUTO_INCREMENT,
   Liker BOOLEAN,
   Disliker BOOLEAN,
   id_user INT,
   Id_article INT NOT NULL,
   PRIMARY KEY(id_like_dislike),
   FOREIGN KEY(id_user) REFERENCES Utilisateur(id_user),
   FOREIGN KEY(Id_article) REFERENCES Article(Id_article)
);

CREATE TABLE Intéragir(
   Id_article INT,
   id_user INT,
   PRIMARY KEY(Id_article, id_user),
   FOREIGN KEY(Id_article) REFERENCES Article(Id_article),
   FOREIGN KEY(id_user) REFERENCES Utilisateur(id_user)
);

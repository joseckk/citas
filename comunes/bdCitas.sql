--------------------------
-- Base de datos bdCitas --
--------------------------

DROP TABLE IF EXISTS usuarios CASCADE;


CREATE TABLE usuarios
(
        id          bigserial        PRIMARY KEY
    ,   login       varchar(255)     NOT NULL UNIQUE
    ,   password    varchar(255)     NOT NULL      
);

DROP TABLE IF EXISTS citas CASCADE;


CREATE TABLE citas
(
        id          bigserial        PRIMARY KEY
    ,   hora        timestamp        NOT NULL UNIQUE
    ,   duracion    numeric(2)       NOT NULL
    ,   usuario_id  bigint           REFERENCES usuarios (id)
);

INSERT INTO usuarios (login, password)
VALUES ('admin', crypt('admin', gen_salt('bf', 10)))
     , ('pepe', crypt('pepe', gen_salt('bf', 10)))
     , ('manolo', crypt('manolo', gen_salt('bf', 10)));

INSERT INTO citas (hora, duracion, usuario_id)
VALUES ('2015-06-02 18:23:15', 15, 2)
     , ('2015-06-20 16:23:15', 15, 3)
     , ('2020-09-25 17:23:15', 15, NULL)
     , ('2018-07-02 19:23:15', 15, NULL);
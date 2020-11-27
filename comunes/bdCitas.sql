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
    ,   fecha_hora  timestamp(0)     NOT NULL UNIQUE
    ,   usuario_id  bigint           NOT NULL REFERENCES usuarios (id)
);

CREATE INDEX idx_citas_usuario_id ON citas (usuario_id);

INSERT INTO usuarios (login, password)
VALUES ('admin', crypt('admin', gen_salt('bf', 10)))
     , ('pepe', crypt('pepe', gen_salt('bf', 10)))
     , ('manolo', crypt('manolo', gen_salt('bf', 10)));

INSERT INTO citas (fecha_hora, usuario_id)
VALUES ('2015-06-02 18:23:15', 2)
     , ('2015-06-20 16:23:15', 3);
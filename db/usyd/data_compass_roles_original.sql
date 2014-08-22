BEGIN;
-- BLOCK CHAIR
INSERT INTO blockchair (uid, block_id) values ('zorba', 2);

INSERT INTO blockchair (uid, block_id) values ('ianc', 3);
INSERT INTO blockchair (uid, block_id) values ('kcurry', 3);
INSERT INTO blockchair (uid, block_id) values ('rward', 3);

INSERT INTO blockchair (uid, block_id) values ('ianc', 4);
INSERT INTO blockchair (uid, block_id) values ('kcurry', 4);
INSERT INTO blockchair (uid, block_id) values ('rward', 4);

INSERT INTO blockchair (uid, block_id) values ('jpseale', 5);

INSERT INTO blockchair (uid, block_id) values ('sfuller', 6);

INSERT INTO blockchair (uid, block_id) values ('mphelps', 7);
INSERT INTO blockchair (uid, block_id) values ('cooperc', 7);

INSERT INTO blockchair (uid, block_id) values ('rogerp', 8);
INSERT INTO blockchair (uid, block_id) values ('dburke', 8);

INSERT INTO blockchair (uid, block_id) values ('dkoorey', 9);
INSERT INTO blockchair (uid, block_id) values ('jennieb', 9);

INSERT INTO blockchair (uid, block_id) values ('dkoorey', 10);
INSERT INTO blockchair (uid, block_id) values ('jennieb', 10);

INSERT INTO blockchair (uid, block_id) values ('dkoorey', 11);
INSERT INTO blockchair (uid, block_id) values ('jennieb', 11);

INSERT INTO blockchair (uid, block_id) values ('gkr', 12);
INSERT INTO blockchair (uid, block_id) values ('ollerenshaw', 12);

INSERT INTO blockchair (uid, block_id) values ('stockler', 13);

-- STAGE COORDINATOR
INSERT INTO stagecoordinator (stage_id, uid) values (2, 'zorba');
INSERT INTO stagecoordinator (stage_id, uid) values (3, 'mfrommer');
INSERT INTO stagecoordinator (stage_id, uid) values (4, 'cdennis');

COMMIT;

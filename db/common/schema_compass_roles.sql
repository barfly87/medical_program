-- admin (super) users are in an LDAP group called "compassadmin"

CREATE TABLE domainadmin (
    auto_id SERIAL,
    domain_id INT,
    uid VARCHAR(32),
    PRIMARY KEY (auto_id)
);
CREATE INDEX domainadmin_domain_id ON domainadmin (domain_id);


CREATE TABLE stagecoordinator (
    auto_id SERIAL,
    domain_id INT DEFAULT 1,
    stage_id INT,
    uid VARCHAR(32),
    PRIMARY KEY (auto_id)
);


CREATE TABLE blockchair (
    auto_id SERIAL,
    domain_id INT DEFAULT 1,
    uid VARCHAR(32),
    block_id INT,
    PRIMARY KEY (auto_id)
);


CREATE TABLE pblcoordinator(
    auto_id SERIAL,
    domain_id INT default 1,
    pbl_id  INT,
    uid     VARCHAR(256),
    primary key (auto_id)
);

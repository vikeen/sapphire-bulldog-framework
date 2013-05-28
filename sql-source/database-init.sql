DROP TABLE IF EXISTS sb_members;
DROP TABLE IF EXISTS sb_config;

CREATE TABLE sb_members (
    id    int      not null auto_increment,
    name  varchar(255) not null,
    email varchar(255) not null,
    PRIMARY KEY  (id)
) ENGINE=InnoDB COMMENT='Members Directory';

CREATE TABLE sb_config (
    param_key   varchar(255) not null,
    param_value varchar(255) not null,
    PRIMARY KEY (param_key)
) ENGINE=InnoDB COMMENT='System Configuration Values';

INSERT INTO sb_members (id, name, email) VALUES
(1, 'John Rake', 'john.rake12@gmail.com'),
(2, 'Richard Fairwater', 'richard-fairwater@aol.com'),
(3, 'Joe Bloggs', ''),
(4, 'John Smith', '')
;

INSERT INTO sb_config VALUES
( 'skin', 'default' )
;

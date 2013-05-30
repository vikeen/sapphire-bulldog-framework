DROP TABLE IF EXISTS sb_member;
DROP TABLE IF EXISTS sb_r_member_status;
DROP TABLE IF EXISTS sb_config;

CREATE TABLE sb_r_member_status (
    member_status_id int not null,
    description varchar(255) not null,
    text varchar(255) not null,
    PRIMARY KEY (member_status_id)
) ENGINE=InnoDB COMMENT='Member Status Reference';

CREATE TABLE sb_member (
    member_id int not null auto_increment,
    first_name varchar(255) not null,
    middle_initial varchar(1) not null,
    last_name varchar(255) not null,
    email varchar(255) not null,
    password varchar(255) not null,
    member_status_id int not null,
    PRIMARY KEY (member_id),
    CONSTRAINT FOREIGN KEY (member_status_id) REFERENCES sb_r_member_status(member_status_id)
) ENGINE=InnoDB COMMENT='Members Directory';

CREATE TABLE sb_config (
    param_key   varchar(255) not null,
    param_value varchar(255) not null,
    PRIMARY KEY (param_key)
) ENGINE=InnoDB COMMENT='System Configuration Values';

INSERT INTO sb_r_member_status VALUES
(0, 'Active', 'Active' ),
(1, 'Locked', 'Locked' ),
(2, 'Expired password. Forced reset upon login', 'Expired Password' )
;

INSERT INTO sb_member VALUES
(1, 'John', 'M', 'Rake', 'john.rake12@gmail.com', 'FAKE', 0)
;

INSERT INTO sb_config VALUES
( 'skin', 'default' )
;


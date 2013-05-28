DROP TABLE IF EXISTS members;

CREATE TABLE members (
  id    int      not null auto_increment,
  name  varchar(255) not null,
  email varchar(255) not null,
  PRIMARY KEY  (id)
) ENGINE=InnoDB COMMENT='Members Directory';

INSERT INTO members (id, name, email) VALUES 
(1, 'John Rake', 'john.rake12@gmail.com'),
(2, 'Richard Fairwater', 'richard-fairwater@aol.com'),
(3, 'Joe Bloggs', ''),
(4, 'John Smith', '');

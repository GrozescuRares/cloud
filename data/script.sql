create table hotels
(
  hotelId     int auto_increment
    primary key,
  name        varchar(255) not null,
  location    varchar(255) not null,
  description longtext     not null,
  owner_id    int          null,
  employees   int          not null,
  facilities  longtext     not null
)
  collate = utf8_unicode_ci;

create index IDX_E402F6257E3C61F9
  on hotels (owner_id);

create table roles
(
  roleId      int auto_increment
    primary key,
  description varchar(255) not null
)
  collate = utf8_unicode_ci;

create table rooms
(
  roomId   int auto_increment
    primary key,
  capacity int        not null,
  price    int        not null,
  smoking  tinyint(1) not null,
  pet      tinyint(1) not null,
  hotel_id int        null,
  constraint FK_7CA11A963243BB18
  foreign key (hotel_id) references hotels (hotelId)
)
  collate = utf8_unicode_ci;

create index IDX_7CA11A963243BB18
  on rooms (hotel_id);

create table users
(
  userId          int auto_increment
    primary key,
  username        varchar(255) not null,
  password        varchar(64)  not null,
  email           varchar(255) not null,
  firstName       varchar(255) not null,
  lastName        varchar(255) not null,
  gender          varchar(10)  not null,
  dateOfBirth     varchar(30)  not null,
  address         varchar(255) null,
  bio             longtext     null,
  profilePicture  varchar(255) null,
  role_id         int          null,
  isActivated     tinyint(1)   not null,
  activationToken varchar(255) null,
  expirationDate  datetime     null,
  hotel_id        int          null,
  deletedAt       datetime     null,
  constraint UNIQ_1483A5E9E7927C74
  unique (email),
  constraint UNIQ_1483A5E9F85E0677
  unique (username),
  constraint FK_1483A5E93243BB18
  foreign key (hotel_id) references hotels (hotelId),
  constraint FK_1483A5E9D60322AC
  foreign key (role_id) references roles (roleId)
)
  collate = utf8_unicode_ci;

alter table hotels
  add constraint FK_E402F6257E3C61F9
foreign key (owner_id) references users (userId);

create table reservations
(
  user_id       int          null,
  hotel_id      int          null,
  room_id       int          null,
  reservationId int auto_increment
    primary key,
  startDate     datetime     not null,
  endDate       datetime     not null,
  days          varchar(255) not null,
  deletedAt     datetime     null,
  constraint FK_4DA2393243BB18
  foreign key (hotel_id) references hotels (hotelId),
  constraint FK_4DA23954177093
  foreign key (room_id) references rooms (roomId),
  constraint FK_4DA239A76ED395
  foreign key (user_id) references users (userId)
)
  collate = utf8_unicode_ci;

create index IDX_4DA2393243BB18
  on reservations (hotel_id);

create index IDX_4DA23954177093
  on reservations (room_id);

create index IDX_4DA239A76ED395
  on reservations (user_id);

create index IDX_1483A5E93243BB18
  on users (hotel_id);

create index IDX_1483A5E9D60322AC
  on users (role_id);



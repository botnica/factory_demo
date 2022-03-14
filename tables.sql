

/*
database: demo

TABLES:
ingredients
categories
tags
dishes
dish_ingredients
dish_tags
languages
t_ingredients
t_dishes
t_tags
t_categories
*/


drop database if exists demo;
create database demo;
connect demo;


drop table if exists ingredients;
create table ingredients (
  id int auto_increment primary key,
  label_id int,
  slug varchar(30) not null
);

drop table if exists categories;
create table categories (
  id int auto_increment primary key,
  label_id int,
  slug varchar(30) not null
);

drop table if exists tags;
create table tags (
  id int auto_increment primary key,
  label_id int,
  slug varchar(30) not null
);

drop table if exists dishes;
create table dishes (
  id int auto_increment primary key,
  label_id int,
  slug varchar(30) not null,
  description_id int,
  category_id int,
  status varchar(8) default 'created',
  created timestamp default current_timestamp
);

drop table if exists dish_ingredients;
create table dish_ingredients (
  dish_id int,
  ingredient_id int
);

drop table if exists dish_tags;
create table dish_tags (
  dish_id int,
  tag_id int
);

drop table if exists languages;
create table languages (
  id varchar(3),
  label varchar(20)
);

drop table if exists t_dishes;
create table t_dishes (
  label_id int,
  language_id varchar(3),
  label varchar(200),
  description varchar(500)
);

drop table if exists t_ingredients;
create table t_ingredients (
  label_id int,
  language_id varchar(3),
  label varchar(100)
);

drop table if exists t_tags;
create table t_tags (
  label_id int,
  language_id varchar(3),
  label varchar(200)
);

drop table if exists t_categories;
create table t_categories (
  label_id int,
  language_id varchar(3),
  label varchar(200)
);

/* set languages */
insert into languages(id, label) values ('hr', 'Croatian');
insert into languages(id, label) values ('en', 'English');
insert into languages(id, label) values ('de', 'German');

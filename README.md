Запуск для админа
------------------

- set host for site on the instance
- git clone currnet repository
- composer install in directory where are you clone the progect
- create directory storage and give appropriated access to storage directory( in this directory will create additional directory called /logs, /cache)
- to config the mysql credential open file /resourse/config/prod.php
- to create table in the mysql database cope body of the file /resourse/sql/schema.sql

Работа с endpoints для frontend developer
------------------

HOST - Ваш хост

-- получить список 10 последних добавленных Bookmark
REQUEST: method GET {HOST}/api/v1/bookmarks

RESPONSE:
[
  {
    "uid": "5751554cd2b49",
    "url": "http://www.w3schools.com7",
    "created_at": "2016-06-03 13:00:47"
  },
 ...
]

-- добавить Bookmark по url и получить Bookmark.uid. Если уже есть Bookmark с таким url, не добавлять ещё один, но получить Bookmark.uid.
REQUEST: method POST http://bee:9001/api/v1/bookmarks

BODY:
url => http://www.w3schools.com7

RESPONSE:
{
  "uid": "5751554cd2b49"
}

-- получить Bookmark (с комментариями) по Bookmark.url. Если такого ещё нет, не создавать.
REQUEST: method GET http://bee:9001/api/v1/bookmarks/www-w3schools-com

RESPONSE:
{
  "uid": "57506cf2db153",
  "created_at": "2016-06-02 20:29:22",
  "comments": [
    {
      "uid": "57506cf2db151",
      "created_at": "2016-06-03 11:49:15",
      "text": "dfsfsfdsfdsf"
    },
    ...
  ]
}

-- добавить Comment к Bookmark (по uid) и получить Comment.uid
REQUEST: method POST http://bee:9001/api/v1/comments

BODY:
bookmark_id = 57506cf2db153
text => bla-bla

RESPONSE:
{
  “uid”: 57506cf2db159
}

-- изменить Comment.text по uid (если он добавлен с этого же IP и прошло меньше часа после добавления)
REQUEST: method PUT http://bee:9001/api/v1/comments/57506cf2db153

BODY:
text => bla-bla

RESPONSE:
{
  "success": true
}

-- удалить Comment по uid (если он добавлен с этого же IP и прошло меньше часа после добавления)
REQUEST: method DELETE http://bee:9001/api/v1/comments

RESPONSE:
{
  "success": true
}
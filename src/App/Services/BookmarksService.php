<?php

namespace App\Services;

class BookmarksService extends BaseService
{
    public function getAll($limit)
    {
        return $this->db->fetchAll("SELECT id as uid, url, created_at FROM bookmarks ORDER BY id DESC LIMIT {$limit}");
    }

    public function getOneBy($field, $value)
    {
        return $this->db->fetchAssoc("SELECT id as uid, created_at FROM bookmarks WHERE {$field} = ? LIMIT 1", [$value]);
    }

    public function save($bookmark)
    {
        return $this->db->insert("bookmarks", $bookmark);
    }

    public function getComments($bookmarks_id)
    {
        return $this->db->fetchAll("SELECT id as uid, created_at, text
FROM `comments`
WHERE `bookmark_id` = ?", [$bookmarks_id]);
    }
}
<?php

namespace App\Services;

class CommentsService extends BaseService
{
    public function getOneBy($field, $value, $select = 'id') {
        return $this->db->fetchAssoc("SELECT {$select} FROM comments WHERE {$field} = '{$value}' limit 1");
    }

    function save($bookmark)
    {
       return $this->db->insert("comments", $bookmark);
    }

    function update($id, $bookmarks)
    {
        return $this->db->update('comments', $bookmarks, ['id' => $id]);
    }

    function delete($id)
    {
        return $this->db->delete("comments", array("id" => $id));
    }
}
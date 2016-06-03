<?php

namespace App\Services;

class CommentsService extends BaseService
{
    public function getOneBy($field, $value) {
        return $this->db->fetchAssoc("SELECT id FROM bookmark_comment WHERE {$field} = '{$value}'");
    }

    function save($bookmark)
    {
        $this->db->insert("bookmark_comment", $bookmark);
        return $this->db->lastInsertId();
    }

    function delete($id)
    {
        return $this->db->delete("bookmark_comment", array("id" => $id));
    }

}

<?php

namespace App\Interfaces\ContentManagement;

interface TagInterface
{
    public function get();
    public function getById($id);
    public function store($data);
    public function update($id, $data);
    public function destroy($id);
}

<?php

namespace App\Interfaces\ContentManagement;

interface FaqInterface
{
    public function get();
    public function getById($id);
    public function store($data);
    public function update($id, $data);
    public function destroy($id);
}

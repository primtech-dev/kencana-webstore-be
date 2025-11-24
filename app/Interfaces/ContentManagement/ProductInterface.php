<?php

namespace App\Interfaces\ContentManagement;

interface ProductInterface
{
    public function get();
    public function getActive();
    public function getBySlug($slug);
    public function getExceptSlug($slug);
    public function getById($id);
    public function store($data, $image);
    public function update($id, $data);
    public function destroy($id);
}

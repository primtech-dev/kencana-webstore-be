<?php

namespace App\Interfaces\ContentManagement;

interface TestimonialInterface
{
    public function get();
    public function getById($id);
    public function store($data, $image);
    public function update($id, $data, $image = null);
    public function destroy($id);
}

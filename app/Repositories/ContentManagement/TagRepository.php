<?php

namespace App\Repositories\ContentManagement;

use App\Interfaces\ContentManagement\TagInterface;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;

class TagRepository implements TagInterface
{
    public function __construct(private Tag $tag) {}
    public function get()
    {
        return $this->tag->get();
    }

    public function getById($id)
    {
        return $this->tag->find($id);
    }

    public function store($data)
    {
        return DB::transaction(function () use ($data) {
            $this->tag->create([
                'name' => $data['name']
            ]);
        });
    }

    public function update($id, $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $this->tag->find($id)->update([
                'name' => $data['name']
            ]);
        });
    }

    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $this->tag->find($id)->delete();
        });
    }
}

<?php

namespace App\Repositories\ContentManagement;

use App\Interfaces\ContentManagement\FaqInterface;
use App\Models\Faq;
use Illuminate\Support\Facades\DB;

class FaqRepository implements FaqInterface
{
    public function __construct(private Faq $faq) {}
    public function get()
    {
        return $this->faq->get();
    }

    public function getById($id)
    {
        return $this->faq->find($id);
    }

    public function store($data)
    {
        return DB::transaction(function () use ($data) {
            $this->faq->create([
                'question' => $data['question'],
                'answer' => $data['answer']
            ]);
        });
    }

    public function update($id, $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $this->faq->find($id)->update([
                'question' => $data['question'],
                'answer' => $data['answer']
            ]);
        });
    }

    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $this->faq->find($id)->delete();
        });
    }
}

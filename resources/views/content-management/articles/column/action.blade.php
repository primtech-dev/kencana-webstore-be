<div class="d-flex gap-2 justify-content-center">
    <a href="{{ route('articles.edit', $article->id) }}"
       class="btn btn-sm btn-soft-warning"
       data-bs-toggle="tooltip"
       title="Edit Artikel">
        <i class="ti ti-edit"></i>
    </a>

    <button onclick="btnDelete({{ $article->id }}, '{{ addslashes($article->title) }}')"
            class="btn btn-sm btn-soft-danger"
            data-bs-toggle="tooltip"
            title="Hapus Artikel">
        <i class="ti ti-trash"></i>
    </button>
</div>

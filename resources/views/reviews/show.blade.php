@extends('layouts.vertical', ['title' => 'Detail Review'])

@section('content')
    <h5>{{ $review->product->name }}</h5>

    <span class="badge {{ $review->ratingBadgeClass() }}">
    {{ $review->rating }} ★
</span>

    <p class="mt-2">{{ $review->body }}</p>

    <hr>

    <form method="POST"
          action="{{ route('admin.reviews.update-status',$review->id) }}">
        @csrf @method('PATCH')
        <select name="status" class="form-select w-auto d-inline">
            @foreach(['published','pending','hidden'] as $s)
                <option value="{{ $s }}"
                    @selected($review->status === $s)>
                    {{ ucfirst($s) }}
                </option>
            @endforeach
        </select>
        <button class="btn btn-primary btn-sm">Update</button>
    </form>

    <hr>

    <div class="row g-2">
        @foreach($review->images as $img)
            <div class="col-6 col-md-3">
                <img src="{{ $img->url }}" class="img-fluid rounded">
                <form method="POST"
                      action="{{ route('admin.review-images.destroy',$img->id) }}">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger w-100 mt-1">
                        Hapus
                    </button>
                </form>
            </div>
        @endforeach
    </div>
@endsection

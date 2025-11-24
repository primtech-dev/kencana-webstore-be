@extends('layouts.vertical', ['title' => 'Manajemen FAQ'])

@section('styles')
    @vite(['node_modules/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css'])
@endsection

@section('content')
    @include('layouts.shared.page-title', [
        'title' => 'Manajemen FAQ',
        'subTitle' => 'Kelola pertanyaan yang sering diajukan untuk membantu pelanggan menemukan jawaban dengan cepat.',
        'breadcrumbs' => [
            ['name' => 'Manajemen Konten', 'url' => '#'],
            ['name' => 'FAQ']
        ]
    ])

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Daftar FAQ</h5>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#createFaqModal">
                        <i class="ti ti-plus me-1"></i> Tambah FAQ
                    </button>
                </div>
                <div class="card-body">
                    <table class="table table-striped dt-responsive align-middle w-100" id="faqs-table">
                        <thead class="thead-sm text-uppercase fs-xxs">
                        <tr>
                            <th width="5%">No</th>
                            <th width="25%">Pertanyaan</th>
                            <th>Jawaban</th>
                            <th width="12%">Dibuat Pada</th>
                            <th width="12%" class="text-center">Aksi</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createFaqModal" tabindex="-1" aria-labelledby="createFaqModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createFaqModalLabel">Tambah FAQ Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('faqs.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="faq_question" class="form-label">
                                Pertanyaan <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('question') is-invalid @enderror"
                                   id="faq_question"
                                   name="question"
                                   value="{{ old('question') }}"
                                   placeholder="Masukkan pertanyaan"
                                   required>
                            @error('question')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="faq_answer" class="form-label">
                                Jawaban <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('answer') is-invalid @enderror"
                                      id="faq_answer"
                                      name="answer"
                                      rows="5"
                                      placeholder="Masukkan jawaban"
                                      required>{{ old('answer') }}</textarea>
                            @error('answer')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i> Simpan FAQ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editFaqModal" tabindex="-1" aria-labelledby="editFaqModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editFaqModalLabel">Edit FAQ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('faqs.update', ':id') }}" method="POST" id="editFaqForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_faq_question" class="form-label">
                                Pertanyaan <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('question') is-invalid @enderror"
                                   id="edit_faq_question"
                                   name="question"
                                   value="{{ old('question') }}"
                                   placeholder="Masukkan pertanyaan"
                                   required>
                            @error('question')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="edit_faq_answer" class="form-label">
                                Jawaban <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('answer') is-invalid @enderror"
                                      id="edit_faq_answer"
                                      name="answer"
                                      rows="5"
                                      placeholder="Masukkan jawaban"
                                      required>{{ old('answer') }}</textarea>
                            @error('answer')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i> Perbarui FAQ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Delete Modal Component --}}
    <x-delete-modal
        id="deleteFaqModal"
        formId="deleteFaqForm"
        :route="route('faqs.destroy', ':id')"
        itemNameId="delete_faq_question"
        title="Konfirmasi Hapus FAQ"
        message="Apakah Anda yakin ingin menghapus FAQ"
        itemType="faq"
    />
@endsection

@section('scripts')
    @vite(['resources/js/pages/content-management/faq.js'])

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                @if(old('_method') === 'PUT')
                const editModal = new bootstrap.Modal(document.getElementById('editFaqModal'));
                editModal.show();
                @else
                const createModal = new bootstrap.Modal(document.getElementById('createFaqModal'));
                createModal.show();
                @endif
            });
        </script>
    @endif

    <script>
        window.faqRoutes = {
            index: '{{ route('faqs.index') }}',
            destroy: '{{ route('faqs.destroy', ':id') }}'
        };
    </script>
@endsection

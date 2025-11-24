{{-- Application Form Section --}}
<section class="application-section" id="pengajuan-online">
    <div class="container">
        <h2 class="section-title text-white">Ajukan Pembiayaan Sekarang</h2>
        <p class="section-subtitle text-white">Isi formulir di bawah ini dan tim kami akan segera menghubungi Anda</p>
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="application-form">
                    <form id="applicationForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Masukkan nama lengkap" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone_number" class="form-label">No. Telepon <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone_number" name="phone_number" placeholder="Contoh: 081234567890" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="car_unit" class="form-label">Unit Mobil <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="car_unit" name="car_unit" placeholder="Contoh: Toyota Avanza 2023" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label">Alamat <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="address" name="address" placeholder="Masukkan alamat lengkap" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="message" class="form-label">Pesan (Opsional)</label>
                                <textarea class="form-control" id="message" name="message" rows="4" placeholder="Tuliskan pesan atau pertanyaan Anda"></textarea>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <span class="submit-text">Kirim Pengajuan</span>
                                <span class="submit-loading d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    Mengirim...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="success-icon mb-3">
                    <i class="ti ti-circle-check" style="font-size: 4rem; color: #28a745;"></i>
                </div>
                <h4 class="mb-3">Pengajuan Berhasil Dikirim!</h4>
                <p class="text-muted mb-0" id="successMessage">Terima kasih atas pengajuan Anda. Tim kami akan segera menghubungi Anda.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="error-icon mb-3">
                    <i class="ti ti-alert-circle" style="font-size: 4rem; color: #dc3545;"></i>
                </div>
                <h4 class="mb-3">Terjadi Kesalahan</h4>
                <p class="text-muted mb-0" id="errorMessage">Maaf, terjadi kesalahan saat mengirim pengajuan. Silakan coba lagi.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // Phone number validation - only allow numbers
        document.getElementById('phone_number').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        function getDeviceId() {
            let deviceId = localStorage.getItem('device_id');
            if (!deviceId) {
                deviceId = 'device_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                localStorage.setItem('device_id', deviceId);
            }
            return deviceId;
        }

        // Application Form Handler
        document.getElementById('applicationForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            // Get button elements
            const submitBtn = document.getElementById('submitBtn');
            const submitText = submitBtn.querySelector('.submit-text');
            const submitLoading = submitBtn.querySelector('.submit-loading');

            // Clear previous errors
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

            // Show loading state
            submitBtn.disabled = true;
            submitText.classList.add('d-none');
            submitLoading.classList.remove('d-none');

            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            // Add device ID
            data.device_id = getDeviceId();

            try {
                const response = await fetch('{{ route('landing.store-submission') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    // Show success modal
                    document.getElementById('successMessage').textContent = result.message;
                    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                    successModal.show();

                    // Reset form
                    this.reset();
                } else {
                    // Check if rate limited
                    if (result.rate_limited || response.status === 429) {
                        // Show error modal with rate limit message
                        document.getElementById('errorMessage').textContent = result.message;
                        const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                        errorModal.show();
                    } else if (result.errors) {
                        // Handle validation errors
                        for (const [field, messages] of Object.entries(result.errors)) {
                            const input = document.getElementById(field);
                            const feedback = input.nextElementSibling;

                            if (input && feedback) {
                                input.classList.add('is-invalid');
                                feedback.textContent = messages[0];
                            }
                        }
                    } else {
                        // Show error modal
                        document.getElementById('errorMessage').textContent = result.message || 'Terjadi kesalahan saat mengirim pengajuan';
                        const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                        errorModal.show();
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                // Show error modal
                document.getElementById('errorMessage').textContent = 'Terjadi kesalahan saat mengirim pengajuan. Silakan coba lagi.';
                const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                errorModal.show();
            } finally {
                // Reset button state
                submitBtn.disabled = false;
                submitText.classList.remove('d-none');
                submitLoading.classList.add('d-none');
            }
        });
    </script>
@endpush

{{-- Simulation Form Section --}}
<section class="py-5">
    <div class="container">
        <div class="simulation-card">
            <h3 class="text-center mb-4" style="color: var(--primary-blue);">Simulasi Pembiayaan</h3>
            <form id="simulationForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="financing_amount" class="form-label">Jumlah Pembiayaan</label>
                        <select class="form-select" id="financing_amount" name="financing_amount" required>
                            <option value="">Pilih Jumlah Pembiayaan</option>
                            @foreach($loanAmounts as $loanAmount)
                                <option value="{{ $loanAmount->id }}" data-amount="{{ floatval($loanAmount->amount) }}">
                                    Rp {{ number_format($loanAmount->amount, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tenor" class="form-label">Tenor (Bulan)</label>
                        <select class="form-select" id="tenor" name="tenor" required disabled>
                            <option value="">Pilih jumlah pembiayaan terlebih dahulu</option>
                        </select>
                    </div>
                </div>
                <div class="text-center">
                    <button type="button" id="showResultBtn" class="btn btn-primary btn-lg" disabled>
                        Lihat Hasil Simulasi
                    </button>
                </div>
                <div id="simulationResult" class="result-box" style="display: none;">
                    <h5 class="text-center mb-3">Hasil Simulasi</h5>
                    <div class="result-item">
                        <span>Jumlah Pembiayaan:</span>
                        <span id="result_amount"></span>
                    </div>
                    <div class="result-item">
                        <span>Tenor:</span>
                        <span id="result_tenor"></span>
                    </div>
                    <div class="result-item">
                        <span>Angsuran per Bulan:</span>
                        <span id="result_installment" class="fw-bold text-primary"></span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

@push('scripts')
    <script>
        let tenorsData = [];
        let selectedLoanAmount = 0;

        // Handle financing amount change
        document.getElementById('financing_amount').addEventListener('change', async function() {
            const loanAmountId = this.value;
            const selectedOption = this.options[this.selectedIndex];
            selectedLoanAmount = parseFloat(selectedOption.getAttribute('data-amount')) || 0;

            const tenorSelect = document.getElementById('tenor');
            const showResultBtn = document.getElementById('showResultBtn');
            const resultBox = document.getElementById('simulationResult');

            // Reset tenor dropdown and hide result
            tenorSelect.innerHTML = '<option value="">Memuat tenor...</option>';
            tenorSelect.disabled = true;
            showResultBtn.disabled = true;
            resultBox.style.display = 'none';

            if (!loanAmountId) {
                tenorSelect.innerHTML = '<option value="">Pilih jumlah pembiayaan terlebih dahulu</option>';
                return;
            }

            try {
                const response = await fetch(`/getTenors/${loanAmountId}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (result.success && result.data.length > 0) {
                    tenorsData = result.data;

                    // Populate tenor dropdown
                    tenorSelect.innerHTML = '<option value="">Pilih Tenor</option>';
                    result.data.forEach(tenor => {
                        const option = document.createElement('option');
                        option.value = tenor.tenor_id;
                        option.textContent = `${tenor.months} Bulan`;
                        option.setAttribute('data-months', tenor.months);
                        option.setAttribute('data-installment', tenor.installment);
                        tenorSelect.appendChild(option);
                    });

                    tenorSelect.disabled = false;
                } else {
                    tenorSelect.innerHTML = '<option value="">Tidak ada tenor tersedia</option>';
                    alert('Tidak ada data tenor untuk jumlah pembiayaan ini');
                }
            } catch (error) {
                console.error('Error:', error);
                tenorSelect.innerHTML = '<option value="">Gagal memuat tenor</option>';
                alert('Terjadi kesalahan saat memuat data tenor');
            }
        });

        // Handle tenor change
        document.getElementById('tenor').addEventListener('change', function() {
            const showResultBtn = document.getElementById('showResultBtn');

            if (this.value) {
                showResultBtn.disabled = false;
            } else {
                showResultBtn.disabled = true;
            }
        });

        // Handle show result button
        document.getElementById('showResultBtn').addEventListener('click', function() {
            const tenorSelect = document.getElementById('tenor');
            const selectedOption = tenorSelect.options[tenorSelect.selectedIndex];

            if (!tenorSelect.value) {
                alert('Silakan pilih tenor terlebih dahulu');
                return;
            }

            const months = parseInt(selectedOption.getAttribute('data-months'));
            const installment = parseFloat(selectedOption.getAttribute('data-installment'));
            const totalPayment = installment * months;

            // Display results
            document.getElementById('result_amount').textContent = formatRupiah(selectedLoanAmount);
            document.getElementById('result_tenor').textContent = months + ' Bulan';
            document.getElementById('result_installment').textContent = formatRupiah(installment);

            // Show result box with smooth scroll
            const resultBox = document.getElementById('simulationResult');
            resultBox.style.display = 'block';

            // Smooth scroll to result
            setTimeout(() => {
                resultBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 100);
        });

        function formatRupiah(amount) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(amount));
        }
    </script>
@endpush

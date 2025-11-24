<section class="faq-section" id="faq">
    <div class="container">
        <h2 class="section-title">Pertanyaan yang Sering Diajukan</h2>
        <p class="section-subtitle">Temukan jawaban atas pertanyaan yang sering ditanyakan</p>
        <div class="accordion" id="faqAccordion">
            @foreach($faqs as $index => $faq)
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#faq{{ $index }}">
                            {{ $faq['question'] }}
                        </button>
                    </h2>
                    <div id="faq{{ $index }}"
                         class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}"
                         data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            {{ $faq['answer'] }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<?php

namespace App\Http\Controllers\Frontend;

use App\Helpers\ExcerptHelper;
use App\Http\Controllers\Controller;
use App\Interfaces\ContentManagement\ArticleInterface;
use App\Interfaces\ContentManagement\FaqInterface;
use App\Interfaces\ContentManagement\ProductInterface;
use App\Interfaces\ContentManagement\TestimonialInterface;
use App\Interfaces\CreditSimulation\CreditSimulationInterface;
use App\Interfaces\Customers\SubmissionInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LandingController extends Controller
{
    public function __construct(private readonly FaqInterface $faq, private readonly TestimonialInterface $testimonial, private readonly ArticleInterface $article, private readonly SubmissionInterface $submission, private readonly CreditSimulationInterface $creditSimulation, private readonly ProductInterface $product) {}

    public function index()
    {
        $hero = [
            'title' => 'Solusi Pembiayaan Terpercaya',
            'subtitle' => 'Wujudkan Impian Anda dengan Pembiayaan Mudah dan Cepat',
            'description' => 'Dapatkan pembiayaan multiguna dengan proses yang cepat, bunga kompetitif, dan pelayanan terbaik dari kami.',
        ];

        $loanAmounts = $this->creditSimulation->getLoanAmount();

        $faqs = $this->faq->get();

        $testimonials = $this->testimonial->get();

        $news = $this->article->getArticleLatest(3)->map(function ($item) {
            $item->excerpt = ExcerptHelper::makeExcerpt($item->content, 150);
            return $item;
        });

        return view('landing.index', compact('hero', 'loanAmounts', 'faqs', 'testimonials', 'news'));
    }

    public function aboutUs()
    {
        return view('landing.about-us.index');
    }

    public function contact()
    {
        return view('landing.contact.index');
    }

    public function product()
    {
        $products = $this->product->getActive();

        return view('landing.product.index', compact('products'));
    }

    public function showProduct($slug)
    {
        $otherProducts = $this->product->getExceptSlug($slug);
        $product = $this->product->getBySlug($slug);

        if (!$product) {
            return view('errors.404-public');
        }

        return view('landing.product.show', compact('product', 'otherProducts'));
    }

    public function storeSubmission(Request $request)
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $deviceId = $request->input('device_id', '');

        $key = 'submission_' . md5($ip . $userAgent . $deviceId);

        if (Cache::has($key)) {
            $remainingTime = Cache::get($key) - now()->timestamp;
            $minutes = ceil($remainingTime / 60);

            return response()->json([
                'success' => false,
                'message' => "Anda baru saja mengirim pengajuan. Silakan tunggu {$minutes} menit lagi untuk mengirim pengajuan berikutnya.",
                'rate_limited' => true
            ], 429);
        }

        $validated = $request->validate([
            'name' => 'required|max:255',
            'phone_number' => 'required|numeric',
            'car_unit' => 'required',
            'address' => 'required',
            'message' => 'nullable',
            'device_id' => 'nullable|string' // Add this
        ], [
            'name.required' => 'Nama tidak boleh kosong',
            'phone_number.required' => 'Nomor telepon tidak boleh kosong',
            'phone_number.numeric' => 'Nomor telepon harus berupa angka',
            'car_unit.required' => 'Unit mobil tidak boleh kosong',
            'address.required' => 'Alamat tidak boleh kosong'
        ]);

        unset($validated['device_id']);

        try {
            $this->submission->store($validated);

            Cache::put($key, now()->addMinutes(5)->timestamp, 300);

            return response()->json([
                'success' => true,
                'message' => 'Terima kasih atas pengajuan Anda. Tim kami akan segera menghubungi Anda.'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses pengajuan. Silakan coba lagi.'
            ], 500);
        }
    }

    public function getTenors(Request $request,$loanAmountId)
    {
        if ($request->wantsJson())
        {
            $tenors = $this->creditSimulation
                ->getAssignedTenorsWithInstallment($loanAmountId)
                ->map(function ($item) {
                    return [
                        'tenor_id' => $item->tenor->id,
                        'months'   => $item->tenor->months,
                        'installment' => $item->installment
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $tenors
            ]);
        }

        return view('errors.404-public');
    }
}

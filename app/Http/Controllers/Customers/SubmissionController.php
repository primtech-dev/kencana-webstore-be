<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Interfaces\Customers\SubmissionInterface;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function __construct(private readonly SubmissionInterface $submission) {}

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $submissions = $this->submission->get();

            return datatables()->of($submissions)
                ->addIndexColumn()
                ->addColumn('submission_datetime', function ($submission) {
                    return $submission->created_at->format('d M Y H:i');
                })
                ->make(true);
        }

        return view('customers.submissions.index');
    }
}

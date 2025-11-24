<?php

namespace App\Http\Controllers\CreditSimulation;

use App\Http\Controllers\Controller;
use App\Interfaces\CreditSimulation\CreditSimulationInterface;
use Illuminate\Http\Request;

class CreditSimulationController extends Controller
{
    public function __construct(private CreditSimulationInterface $creditSimulation) {}

    public function index(Request $request)
    {
        if ($request->ajax() && $request->type === 'loan_amount') {
            $loanAmounts = $this->creditSimulation->getLoanAmount();

            return datatables()->of($loanAmounts)
                ->addIndexColumn()
                ->addColumn('amount', fn($row) => number_format($row->amount, 0, ',', '.'))
                ->addColumn('action', function ($row) {
                    return view('credit-simulation.column.loan-amount-action', compact('row'))->render();
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        if ($request->ajax() && $request->type === 'tenor') {
            $tenors = $this->creditSimulation->getTenors();

            return datatables()->of($tenors)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    return view('credit-simulation.column.tenor-action', compact('row'))->render();
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('credit-simulation.index');
    }

    public function storeLoanAmount(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1|unique:loan_amounts,amount'
        ], [
            'amount.required' => 'Jumlah pencairan tidak boleh kosong',
            'amount.numeric' => 'Jumlah pencairan harus berupa angka',
            'amount.min' => 'Jumlah pencairan minimal 1',
            'amount.unique' => 'Jumlah pencairan sudah ada'
        ]);

        try {
            $this->creditSimulation->storeLoanAmount($validated);
            return back()->with('success', 'Jumlah pencairan berhasil ditambahkan');
        } catch (\Throwable $th) {
            return back()->with('error', $th->getMessage());
        }
    }

    public function updateLoanAmount(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1|unique:loan_amounts,amount,' . $id . ',id'
        ], [
            'amount.required' => 'Jumlah pencairan tidak boleh kosong',
            'amount.numeric' => 'Jumlah pencairan harus berupa angka',
            'amount.min' => 'Jumlah pencairan minimal 1',
            'amount.unique' => 'Jumlah pencairan sudah ada'
        ]);

        try {
            $this->creditSimulation->updateLoanAmount($id, $validated);
            return back()->with('success', 'Jumlah pencairan berhasil diubah');
        } catch (\Throwable $th) {
            return back()->with('error', $th->getMessage());
        }
    }

    public function destroyLoanAmount($id)
    {
        try {
            $this->creditSimulation->destroyLoanAmount($id);
            return redirect()->back()->with('success', 'Jumlah pencairan berhasil dihapus');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function storeTenor(Request $request)
    {
        $validated = $request->validate([
            'months' => 'required|numeric|min:1|unique:tenors,months'
        ], [
            'months.required' => 'Tenor tidak boleh kosong',
            'months.numeric' => 'Tenor harus berupa angka',
            'months.min' => 'Tenor minimal 1',
            'months.unique' => 'Tenor sudah ada'
        ]);

        try {
            $this->creditSimulation->storeTenors($validated);
            return redirect()->back()->with('success', 'Tenor berhasil ditambahkan');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function updateTenor(Request $request, $id)
    {
        $validated = $request->validate([
            'months' => 'required|numeric|min:1|unique:tenors,months,'.$id . ',id'
        ], [
            'months.required' => 'Tenor tidak boleh kosong',
            'months.numeric' => 'Tenor harus berupa angka',
            'months.min' => 'Tenor minimal 1',
            'months.unique' => 'Tenor sudah ada'
        ]);

        try {
            $this->creditSimulation->updateTenors($id, $validated);
            return back()->with('success', 'Tenor berhasil diubah');
        } catch (\Throwable $th) {
            return back()->with('error', $th->getMessage());
        }
    }

    public function destroyTenor($id)
    {
        try {
            $this->creditSimulation->destroyTenors($id);
            return back()->with('success', 'Tenor berhasil dihapus');
        } catch (\Throwable $th) {
            return back()->with('error', $th->getMessage());
        }
    }

    public function showInstallments($loanAmountId)
    {
        $loanAmount = $this->creditSimulation->getLoanAmountById($loanAmountId);

        $assignedTenors = $this->creditSimulation->getAssignedTenorsWithInstallment($loanAmountId);
        $unassignedTenors = $this->creditSimulation->getUnassignedTenors($loanAmountId);

        return view('credit-simulation.show', compact(
            'loanAmount',
            'assignedTenors',
            'unassignedTenors'
        ));
    }

    public function bulkSaveInstallments(Request $request)
    {
        $validated = $request->validate([
            'installments' => 'required|array|min:1',
            'installments.*.loan_amount_id' => 'required|exists:loan_amounts,id',
            'installments.*.tenor_id'       => 'required|exists:tenors,id',
            'installments.*.installment'    => 'required|numeric|min:0',
        ], [
            'installments.required' => 'Data angsuran tidak boleh kosong',
            'installments.array'    => 'Format data angsuran tidak valid',
            'installments.*.tenor_id.required'    => 'Tenor harus dipilih',
            'installments.*.tenor_id.exists'      => 'Tenor tidak ditemukan',
            'installments.*.installment.required' => 'Nilai angsuran wajib diisi',
            'installments.*.installment.numeric'  => 'Nilai angsuran harus angka',
        ]);

        try {
            $this->creditSimulation->bulkSaveInstallments($validated['installments']);

            return redirect()->route('credit-simulation.index')->with('success', 'Tabel angsuran berhasil disimpan');

        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }


}

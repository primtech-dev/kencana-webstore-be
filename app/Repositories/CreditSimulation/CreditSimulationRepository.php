<?php

namespace App\Repositories\CreditSimulation;

use App\Interfaces\CreditSimulation\CreditSimulationInterface;
use App\Models\LoanAmount;
use App\Models\LoanInstallment;
use App\Models\Tenor;
use Illuminate\Support\Facades\DB;

class CreditSimulationRepository implements CreditSimulationInterface
{
    public function __construct(private LoanAmount $loanAmount, private Tenor $tenor, private LoanInstallment $loanInstallment) {}

    public function getLoanAmount()
    {
        return $this->loanAmount->orderBy('amount', 'asc')->get();
    }

    public function getLoanAmountById($id)
    {
        return $this->loanAmount->find($id);
    }

    public function storeLoanAmount($data)
    {
        return DB::transaction(function () use ($data) {
            $this->loanAmount->create([
                'amount' => $data['amount']
            ]);
        });
    }

    public function updateLoanAmount($id, $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $this->loanAmount->find($id)->update([
                'amount' => $data['amount']
            ]);
        });
    }

    public function destroyLoanAmount($id)
    {
        return DB::transaction(function () use ($id) {
            $this->getLoanAmountById($id)->delete();
        });
    }

    public function getTenors()
    {
        return $this->tenor->get();
    }

    public function storeTenors($data)
    {
        return DB::transaction(function () use ($data) {
            return $this->tenor->create([
                'months' => $data['months']
            ]);
        });
    }

    public function updateTenors($id, $data)
    {
        return DB::transaction(function () use ($id, $data) {
            return $this->tenor->findOrFail($id)->update([
                'months' => $data['months']
            ]);
        });
    }

    public function destroyTenors($id)
    {
        return DB::transaction(function () use ($id) {
            return $this->tenor->findOrFail($id)->delete();
        });
    }

    public function findInstallment($loanAmountId, $tenorId)
    {
        return $this->loanInstallment
            ->where('loan_amount_id', $loanAmountId)
            ->where('tenor_id', $tenorId)
            ->first();
    }

    public function getUnassignedTenors($loanAmountId)
    {
        return $this->tenor
            ->whereNotIn('id', function ($query) use ($loanAmountId) {
                $query->select('tenor_id')
                    ->from('loan_installments')
                    ->where('loan_amount_id', $loanAmountId);
            })
            ->orderBy('months')
            ->get();
    }

    public function getAssignedTenorsWithInstallment($loanAmountId)
    {
        return $this->loanInstallment
            ->with('tenor')
            ->where('loan_amount_id', $loanAmountId)
            ->orderBy('tenor_id')
            ->get();
    }

    public function bulkSaveInstallments(array $items)
    {
        return DB::transaction(function () use ($items) {

            if (empty($items)) {
                return false;
            }

            $loanAmountId = $items[0]['loan_amount_id'];

            $submittedTenorIds = collect($items)->pluck('tenor_id')->toArray();

            $existingTenorIds = $this->loanInstallment
                ->where('loan_amount_id', $loanAmountId)
                ->pluck('tenor_id')
                ->toArray();

            $tenorsToDelete = array_diff($existingTenorIds, $submittedTenorIds);

            if (!empty($tenorsToDelete)) {
                $this->loanInstallment
                    ->where('loan_amount_id', $loanAmountId)
                    ->whereIn('tenor_id', $tenorsToDelete)
                    ->delete();
            }

            foreach ($items as $row) {

                $existing = $this->findInstallment(
                    $row['loan_amount_id'],
                    $row['tenor_id']
                );

                if ($existing) {
                    $existing->update([
                        'installment' => $row['installment']
                    ]);
                } else {
                    $this->loanInstallment->create([
                        'loan_amount_id' => $row['loan_amount_id'],
                        'tenor_id'       => $row['tenor_id'],
                        'installment'    => $row['installment']
                    ]);
                }
            }

            return true;
        });
    }
}

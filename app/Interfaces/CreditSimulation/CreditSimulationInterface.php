<?php

namespace App\Interfaces\CreditSimulation;

interface CreditSimulationInterface
{
    public function getLoanAmount();
    public function getLoanAmountById($id);
    public function storeLoanAmount($data);
    public function updateLoanAmount($id, $data);
    public function destroyLoanAmount($id);
    public function getTenors();
    public function storeTenors($data);
    public function updateTenors($id, $data);
    public function destroyTenors($id);
    public function findInstallment($loanAmountId, $tenorId);
    public function getUnassignedTenors($loanAmountId);
    public function getAssignedTenorsWithInstallment($loanAmountId);
    public function bulkSaveInstallments(array $items);
}

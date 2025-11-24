<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Interfaces\Customers\CustomerInterface;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    private const VALIDATION_MESSAGES = [
        'name.required' => 'Nama tidak boleh kosong',
        'address.required' => 'Alamat tidak boleh kosong',
        'car_unit.required' => 'Unit mobil tidak boleh kosong',
        'phone_number.required' => 'Nomor telepon tidak boleh kosong',
        'phone_number.numeric' => 'Nomor telepon harus berupa angka',
        'phone_number.digits_between' => 'Nomor telepon harus antara 10 sampai 15 digit'
    ];

    public function __construct(private CustomerInterface $customer) {}

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $customers = $this->customer->get();

            return datatables()->of($customers)
                ->addIndexColumn()
                ->addColumn('created_at', function ($customer) {
                    return $customer->created_at->format('d M Y H:i');
                })
                ->addColumn('action', function ($customer) {
                    return view('customers.customers.column.action', compact('customer'))->render();
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('customers.customers.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'address' => 'required|max:255',
            'car_unit' => 'required|max:255',
            'phone_number' => 'required|numeric|digits_between:10,15',
        ], self::VALIDATION_MESSAGES);

        try {
            $this->customer->store($validated);
            return redirect()->back()->with('success', 'Pelanggan berhasil ditambahkan');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'address' => 'required|max:255',
            'car_unit' => 'required|max:255',
            'phone_number' => 'required|numeric|digits_between:10,15',
        ], self::VALIDATION_MESSAGES);

        try {
            $this->customer->update($id, $validated);
            return redirect()->back()->with('success', 'Pelanggan berhasil diubah');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function destroy(int $id)
    {
        try {
            $this->customer->destroy($id);
            return redirect()->back()->with('success', 'Pelanggan berhasil dihapus');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }
}

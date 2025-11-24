<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Interfaces\Users\UserInterface;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private const VALIDATION_MESSAGES = [
        'name.required' => 'Please enter a name.',
        'email.required' => 'Please enter a email.',
        'email.email' => 'Please enter a valid email address.',
        'email.unique' => 'Please enter a unique email address.',
    ];

    public function __construct(private UserInterface $user) {}

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $users = $this->user->get();

            return datatables()->of($users)
                ->addIndexColumn()
                ->addColumn('created_at', function ($user) {
                    return $user->created_at->format('d M Y H:i');
                })
                ->addColumn('action', function ($user) {
                    return view('users.column.action', compact('user'))->render();
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('users.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
        ], self::VALIDATION_MESSAGES);

        try {
            $this->user->store($validated);
            return redirect()->back()->with('success', 'User berhasil ditambahkan');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$id,
        ], self::VALIDATION_MESSAGES);

        try {
            $this->user->update($id, $validated);
            return redirect()->back()->with('success', 'User berhasil diubah');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $this->user->destroy($id);
            return redirect()->back()->with('success', 'User berhasil dihapus');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function resetPassword($id)
    {
        try {
            $this->user->resetPassword($id);
            return redirect()->back()->with('success', 'Password berhasil direset');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }
}

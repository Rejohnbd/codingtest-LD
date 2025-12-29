<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        return view('dashboard');
    }

    // Get users data for DataTables
    public function getUsers(Request $request)
    {
        if ($request->ajax()) {
            $data = User::select(['id', 'name', 'email', 'created_at']);

            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    return '<button class="editUser btn btn-sm btn-primary" data-id="' . $row->id . '" data-name="' . $row->name . '" data-email="' . $row->email . '">Edit</button>';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at->format('Y-m-d H:i:s');
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        abort(403);
    }
    // Update user data via AJAX
    public function updateUser(Request $request, $id)
    {
        if ($request->ajax()) {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            try {
                $user = User::findOrFail($id);
                $user->update([
                    'name' => $request->name,
                    'email' => $request->email,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'User updated successfully'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating user: ' . $e->getMessage()
                ], 500);
            }
        }
        abort(403);
    }
}

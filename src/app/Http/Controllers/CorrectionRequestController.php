<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceCorrection;

class CorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $status = $request->get('status', 'pending');

        $query = AttendanceCorrection::where('requester_id', $user->id)
            ->with('attendance.user')
            ->orderBy('created_at', 'asc');

        if ($status === 'pending') {
            $query->where('status', 'pending');
        } elseif ($status === 'approved') {
            $query->where('status', 'approved');
        }

        $corrections = $query->get();

        return view('attendance.correction_list', compact('corrections', 'status'));
    }
}

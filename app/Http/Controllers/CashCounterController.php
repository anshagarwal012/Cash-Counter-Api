<?php

namespace App\Http\Controllers;

use Validator;
use App\Models\CashCounter;
use App\Models\users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashCounterController extends Controller
{
    public function list_user()
    {
        return users::get();
    }
    public function add_user(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'mob' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => 'false',
                'message' => $validator->errors()
            ];
            return response()->json($response, 400);
        }
        $cc = new users;
        $cc->email = $request->email;
        $cc->password = bcrypt($request->password);
        $cc->mob = $request->mob;
        $cc->save();

        return ['success' => true, 'data' => $cc];
    }
    public function add_entry(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'amount' => 'required',
            'payment_mode' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'success' => 'false',
                'message' => $validator->errors()
            ];
            return response()->json($response, 400);
        }

        $user = users::find($request->user_id);
        if ($user == null) {
            $response = [
                'success' => 'false',
                'message' => "User Not Found"
            ];
            return response()->json($response, 400);
        }
        $data = DB::select('select (select SUM(amount) FROM cash_counters WHERE status = 1 and user_id = ' . $request->user_id . ') - (select SUM(amount) FROM cash_counters WHERE status = 0 and user_id = ' . $request->user_id . ') as amount');
        CashCounter::create($request->all());
        $response = [
            'success' => 'true',
            'message' => 'Entry Added',
            'net_balance' => $data
        ];
        return response()->json($response, 200);
    }
}

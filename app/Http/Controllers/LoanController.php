<?php

namespace App\Http\Controllers;

use App\Http\Response\BaseResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Bank;
use App\Models\LoanReason;
use App\Models\Loan;
use Carbon\Carbon;

class LoanController extends Controller
{

    // TODO: 
    public function loan(Request $request)
    {
        // validate user input
        $validate = Validator::make($request->all(), [
            'user_id' => 'required|numeric|min:1',
            'loan_amount' => 'required|numeric|min:1000',
        ]);

        // check validation status
        if ($validate->fails()) return BaseResponse::error($validate->messages());

        // get user profile
        $user = User::where('id', $request->input('user_id'))->first();
        // check user availability
        if (!$user) return BaseResponse::error('User not found!');
        // check user role
        if ($user->role_id == 2) return BaseResponse::error("This user is not permitted!");
        // check if user has submitted user profile
        if (!$user->user_profile) return BaseResponse::error('Please Submit your profile first!');


        // get bank data
        $banks = Bank::all();
        // reasons varbles
        $reasons = collect([]);
        // marital status mapping
        $marital_statuses = ['single', 'married'];
        // employment mapping
        $employments = ['Non Full Time', 'Full Time'];

        foreach ($banks as $idx => $bank) {
            $reasons[$bank->id] = collect([
                'bank_name' => $bank->name,
                'reasons' => collect([])
            ]);
            // check marital status
            if ($bank->marital_status != $user->user_profile->marital_status && $bank->marital_status != 2) $reasons[$bank->id]['reasons']->push($bank->name . ' only accept ' . $marital_statuses[$bank->marital_status] . ' Person!');
            // check age
            if (Carbon::parse($user->user_profile->dob)->age > $bank->max_age) $reasons[$bank->id]['reasons']->push($bank->name . ' only accept person below ' . $bank->max_age . ' years old');
            // check employment status
            if ($bank->emplyment != $user->user_profile->employement && $bank->employment != 2) $reasons[$bank->id]['reasons']->push($bank->name . ' only accept person who has ' . $employments[$bank->employment] . ' employment');
            // check user nationality
            if ($bank->nationality == 0 && strtolower($user->user_profile->country->country_name) != 'indonesia') $reasons[$bank->id]['reasons']->push($bank->name . ' only accept Indonesian citizen employment');
        }

        $rejected_banks = $reasons->filter(function ($reason) {
            if (count($reason['reasons']) > 0) return $reason;
        });
        dd($rejected_banks);

        // if no bank accept
        if (count($rejected_banks) > 0) {
            $loan_reasons =
                $loan = Loan::create(
                    [
                        'user_id' => $request->input('user_id'),
                        'loan_amount' => $request->input('loan_amount'),
                        'status' => 0,
                        'loaned_amount' => 0,
                    ]
                );
        }
    }
}

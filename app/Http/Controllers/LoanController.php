<?php

namespace App\Http\Controllers;

use App\Http\Response\BaseResponse;
use App\Models\AcceptedBank;
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
                'bank_id'   => $bank->id,
                'max_loan'  => $bank->loaning_percentage,
                'bank_name' => $bank->name,
                'reasons' => collect([])
            ]);
            // check marital status
            if ($bank->marital_status != $user->user_profile->marital_status && $bank->marital_status != 2) $reasons[$bank->id]['reasons']->push($bank->name . ' only accept ' . $marital_statuses[$bank->marital_status] . ' Person!');
            // check age
            if (Carbon::parse($user->user_profile->dob)->age > $bank->max_age) $reasons[$bank->id]['reasons']->push($bank->name . ' only accept person below ' . $bank->max_age . ' years old');
            // check employment status
            if ($bank->employment != $user->user_profile->employement && $bank->employment != 2) $reasons[$bank->id]['reasons']->push($bank->name . ' only accept person who has ' . $employments[$bank->employment] . ' employment');
            // check user nationality
            if ($bank->nationality == 0 && strtolower($user->user_profile->country->country_name) != 'indonesia') $reasons[$bank->id]['reasons']->push($bank->name . ' only accept Indonesian citizen');
        }


        $accepted_banks = $reasons->filter(function ($reason) {
            if (count($reason['reasons']) == 0) return $reason;
        });

        // if no bank accept
        if (count($accepted_banks) == 0) {
            $rejected_banks = $reasons->filter(function ($reason) {
                if (count($reason['reasons']) > 0) return $reason;
            });
            $loan = Loan::create(
                [
                    'user_id' => $request->input('user_id'),
                    'loan_amount' => $request->input('loan_amount'),
                    'status' => 0,
                ]
            );
            $rejection_reasons = [];
            foreach ($rejected_banks as $rejected_bank) {
                foreach ($rejected_bank['reasons'] as $reason) {
                    array_push($rejection_reasons, [
                        'loan_id' => $loan->id,
                        'bank_id' => $rejected_bank['bank_id'],
                        'rejection_reason' => $reason,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            $loan_reason = LoanReason::insert($rejection_reasons);
            return BaseResponse::success(data: ['reasons' => $rejection_reasons], message: 'Your loan has been rejected!');
        } else {
            $loan = Loan::create(
                [
                    'user_id' => $request->input('user_id'),
                    'loan_amount' => $request->input('loan_amount'),
                    'status' => 1,
                ]
            );
            $accepted = [];
            foreach ($accepted_banks as $accepted_bank) {
                array_push($accepted, [
                    'loan_id' => $loan->id,
                    'bank_id' => $accepted_bank['bank_id'],
                    'loaned_amount' => floor(($accepted_bank['max_loan'] / 100) * $loan->loan_amount),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            return BaseResponse::success(['loan' => $loan, 'banks' => $accepted], 'Your loan has been accepted!');
        }
    }
}

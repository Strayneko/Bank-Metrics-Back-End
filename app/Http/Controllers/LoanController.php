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
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
{

    // TODO: 
    public function loan(Request $request)
    {
        // validate user input
        $validate = Validator::make($request->all(), [
            'loan_amount' => 'required|numeric|min:1000',
        ]);

        // check validation status
        if ($validate->fails()) return BaseResponse::error($validate->messages());

        // get user profile
        $user = Auth::user();
        // check user availability
        if (!$user) return BaseResponse::error('User not found!', 404);
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
            // check min age
            if (Carbon::parse($user->user_profile->dob)->age < $bank->min_age) $reasons[$bank->id]['reasons']->push($bank->name . ' only accept person above ' . $bank->min_age . ' years old');
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
            // filter banks to rejected banks only
            $rejected_banks = $reasons->filter(function ($reason) {
                if (count($reason['reasons']) > 0) return $reason;
            });
            // insert loan data
            $loan = Loan::create(
                [
                    'user_id' => $user->id,
                    'loan_amount' => $request->input('loan_amount'),
                    'status' => 0,
                ]
            );
            // collecting reasons into array
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
            // insert loan reasons
            LoanReason::insert($rejection_reasons);
            return BaseResponse::success(data: ['reasons' => $rejection_reasons], message: 'Your loan has been rejected!');
        } else {
            // if there is bank accepted the loaning
            // insert loan data
            $loan = Loan::create(
                [
                    'user_id' => $user->id,
                    'loan_amount' => $request->input('loan_amount'),
                    'status' => 1,
                ]
            );
            // filter banks to accepted banks only
            // if there is 2 banks or more that accept the loan
            // insert all data
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
            AcceptedBank::insert($accepted);
            return BaseResponse::success(['loan' => $loan, 'banks' => $accepted], 'Your loan has been accepted!');
        }
    }


    public function list_loan()
    {
        $user_id = Auth::user()->id;
        $loans = Loan::with(['accepted_bank', 'accepted_bank.bank' => function ($query) {
            return $query->get('name');
        }])->where('user_id', $user_id)->get();
        // check loan data
        if (count($loans) == 0) return BaseResponse::error('No loan data found for userid = ' . $user_id, 404);
        return BaseResponse::success(data: [
            'loans' => $loans->makeHidden(['created_at', 'updated_at']),
        ]);
    }

    public function index(Request $request)
    {

        // check user access
        try {
            $this->authorize('admin');
        } catch (AuthorizationException $e) {
            return BaseResponse::error($e->getMessage(), 403);
        }
        // get all loan data
        $loans = Loan::with(['accepted_bank', 'accepted_bank.bank', 'user']);

        // check if there is type query parameter
        if ($request->query('status')) {
            // show rejected loans
            if ($request->get('status') == 'rejected') $loans = $loans->where('status', 0);
            // show rejected loans
            if ($request->get('status') == 'accepted') $loans = $loans->where('status', 1);
        }
        return BaseResponse::success($loans->get());
    }
}

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
use App\Helpers\Countries;

class LoanController extends Controller
{


    private function getRejectionReason($reasons, $loan_id)
    {
        // filter banks to rejected banks only
        $rejected_banks = $reasons->filter(function ($reason) {
            if (count($reason['reasons']) > 0) return $reason;
        });

        // collecting reasons into array
        $rejection_reasons = [];
        foreach ($rejected_banks as $rejected_bank) {
            foreach ($rejected_bank['reasons'] as $reason) {
                // push data to rejection_reasons array
                array_push($rejection_reasons, [
                    'loan_id' => $loan_id,
                    'bank_id' => $rejected_bank['bank_id'],
                    'rejection_reason' => $reason,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        return $rejection_reasons;
    }

    // TODO: handle loan submission
    public function loan(Request $request)
    {
        // validate user input
        $validate = Validator::make($request->all(), [
            'loan_amount' => 'required|numeric|min:100',
        ]);

        // check validation status
        if ($validate->fails()) return BaseResponse::error($validate->messages()->all());

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

        // get country name with country_id in env
        $country_name = collect(Countries::getCountries())->filter(fn ($country) => $country['id'] == env('COUNTRY_ID'));
        // check country data
        // give error message if country not found
        if (count($country_name) == 0) return BaseResponse::error('No country data found!. Please check ENV file', 500);
        // get country name
        $country_name = $country_name->first()['name'];

        foreach ($banks as $idx => $bank) {
            $reasons[$bank->id] = collect([
                'bank_id'   => $bank->id,
                'max_loan'  => $bank->loaning_percentage,
                'bank_name' => $bank->name,
                'reasons' => collect([])
            ]);

            // check user profile, and store the reason if requirements is not met
            // check user marital status with bank requirement
            if ($bank->marital_status != $user->user_profile->marital_status && $bank->marital_status != 2) $reasons[$bank->id]['reasons']->push($bank->name . ' only accept ' . $marital_statuses[$bank->marital_status] . ' Person!');
            // check max user age with bank requirement
            if (Carbon::parse($user->user_profile->dob)->age > $bank->max_age) $reasons[$bank->id]['reasons']->push($bank->name . ' only accept person below ' . $bank->max_age . ' years old');
            // check min user age with bank requirement
            if (Carbon::parse($user->user_profile->dob)->age < $bank->min_age) $reasons[$bank->id]['reasons']->push($bank->name . ' only accept person above ' . $bank->min_age . ' years old');
            // check user employment status with bank requirement
            if ($bank->employment != $user->user_profile->employement && $bank->employment != 2) $reasons[$bank->id]['reasons']->push($bank->name . ' only accept person who has ' . $employments[$bank->employment] . ' employment');
            // check user nationality with bank requirement
            if ($bank->nationality == 0 && $user->user_profile->country->id != env('COUNTRY_ID')) $reasons[$bank->id]['reasons']->push($bank->name . ' only accept ' . $country_name . ' citizen');
        }

        // filter banks to accepted banks only
        $accepted_banks = $reasons->filter(function ($reason) {
            if (count($reason['reasons']) == 0) return $reason;
        });


        // if no bank accept
        if (count($accepted_banks) == 0) {

            // insert loan data
            $loan = Loan::create(
                [
                    'user_id' => $user->id,
                    'loan_amount' => $request->input('loan_amount'),
                    'status' => 0,
                ]
            );
            // get rejection reason
            $rejection_reasons = $this->getRejectionReason($reasons, $loan->id);
            // insert loan reasons
            LoanReason::insert($rejection_reasons);
            return BaseResponse::success(data: ['reasons' => $rejection_reasons], message: 'Your loan has been rejected!');
        } else {
            // insert loan data
            $loan = Loan::create(
                [
                    'user_id' => $user->id,
                    'loan_amount' => $request->input('loan_amount'),
                    'status' => 1,
                ]
            );
            // if there is bank accepted the loaning
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
            // get rejection reason
            $rejection_reasons = $this->getRejectionReason($reasons, $loan->id);
            // insert loan reasons
            LoanReason::insert($rejection_reasons);
            AcceptedBank::insert($accepted);
            return BaseResponse::success(['loan' => $loan, 'banks' => $accepted], 'Your loan has been accepted!');
        }
    }

    // TODO: show all loan data
    public function list_loan(Request $request)
    {
        // get current user login id
        $user_id = Auth::user()->id;
        // check if there is query user_id passed
        // replace user_id with value from query
        if ($request->query('user_id')) $user_id = $request->query('user_id');

        // get loan data with accepted_bank, loan_reason, and bank data
        $loans = Loan::with(['accepted_bank', 'loan_reason', 'loan_reason.bank' => fn ($query) => $query->withTrashed(),  'accepted_bank.bank' => fn ($query) => $query->withTrashed()])->where('user_id', $user_id)->get();

        // check loan data
        if (count($loans) == 0) return BaseResponse::error('No loan data found for userid = ' . $user_id, 404);

        return BaseResponse::success(data: [
            'loans' => $loans
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
            // show accepted loans
            if ($request->get('status') == 'accepted') $loans = $loans->where('status', 1);
        }
        return BaseResponse::success($loans->get());
    }

    public function rejection_reason($loan_id)
    {
        // check loan by current user login
        $loan = Loan::where('user_id', Auth::user()->id)->where('id', $loan_id)->get();
        if (count($loan) == 0) return BaseResponse::error('No loan data found!');

        // get rejection rason by loan id
        $rejection_reasons = LoanReason::where('loan_id', $loan_id)->get();
        // check rejection reason availability
        if (count($rejection_reasons) == 0) return BaseResponse::error('No Loan Reason data found!');
        $data = collect([]);
        $banks = Bank::withTrashed()->get();
        foreach ($rejection_reasons as $reason) {
            // filter bank data 
            $bank = $banks->filter(fn ($bank) => $reason->bank_id == $bank->id)->first();
            $temp = [];
            // filter rejection reason
            $reasons = $rejection_reasons->filter(fn ($r) => $r->bank_id == $reason->bank_id);
            // loop reasons and push it into temporary array vairable
            foreach ($reasons as $reason) {
                array_push($temp, $reason->rejection_reason);
            }
            $bank['loan_reason'] = $temp;
            if (!$data->where('id', $bank->id)->first()) $data->push($bank);
        }
        return BaseResponse::success($data);
    }
}

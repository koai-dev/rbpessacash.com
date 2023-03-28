<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\helpers;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WithdrawalMethod;
use App\Models\WithdrawRequest;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Rap2hpoutre\FastExcel\FastExcel;

class WithdrawController extends Controller
{
    private $withdraw_request;
    private $withdrawal_method;
    private $user;

    public function __construct(WithdrawRequest $withdraw_request, WithdrawalMethod $withdrawal_method, User $user)
    {
        $this->withdraw_request = $withdraw_request;
        $this->withdrawal_method = $withdrawal_method;
        $this->user = $user;
    }

    public function index(Request $request)
    {
        $query_param = [];
        $search = $request['search'];

        $method = $request->withdrawal_method;
        $withdraw_requests = $this->withdraw_request
            ->when($request->has('search'), function ($query) use ($request) {
                $key = explode(' ', $request['search']);

                $user_ids = User::where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('id', 'like', "%{$value}%")
                            ->orWhere('phone', 'like', "%{$value}%")
                            ->orWhere('f_name', 'like', "%{$value}%")
                            ->orWhere('l_name', 'like', "%{$value}%")
                            ->orWhere('email', 'like', "%{$value}%");
                    }
                })->get()->pluck('id')->toArray();

                $query_param = ['search' => $request['search']];
                return $query->whereIn('user_id', $user_ids);
            })
            ->with('user', 'withdrawal_method')
            ->when($request->has('withdrawal_method') && $request->withdrawal_method != 'all', function ($query) use ($request) {
                return $query->where('withdrawal_method_id', $request->withdrawal_method);
            })
            ->latest()
            ->paginate(Helpers::pagination_limit())
            ->appends($query_param);

        $withdrawal_methods = $this->withdrawal_method->latest()->get();

        return view('admin-views.withdraw.index', compact('withdraw_requests', 'withdrawal_methods', 'method', 'search'));
    }

    public function status_update(Request $request)
    {
        $request->validate([
            'request_id' => 'required',
            'request_status' => 'required|in:approve,deny',
        ]);

        $withdraw_request = $this->withdraw_request->with(['user'])->find($request['request_id']);

        if (!isset($withdraw_request->user)) {
            Toastr::error(translate('The request sender is unavailable'));
            return back();
        }

        //record in withdraw_requests table
        $withdraw_request->request_status = $request->request_status == 'approve' ? 'approved' : 'denied';
        $withdraw_request->is_paid = 1;
        $withdraw_request->admin_note = $request->admin_note ?? null;
        $withdraw_request->save();

        if ($request->request_status == 'approve')
        {
            $admin = $this->user->with(['emoney'])->where('type', 0)->first();
            if ($admin->emoney->current_balance < $withdraw_request['amount']) {
                Toastr::success(translate('You do not have enough balance. Please generate eMoney first.'));
                return back();
            }

            accept_withdraw_transaction($withdraw_request->user->id, $withdraw_request['amount']);
        }

        $data = [
            'title' => $request->request_status == 'approve' ? translate('Withdraw_request_accepted') : translate('Withdraw_request_denied'),
            'description' => '',
            'image' => '',
            'order_id'=>'',
        ];
        send_push_notification_to_device($withdraw_request->user->fcm_token, $data);

        Toastr::success(translate('The request has been successfully updated'));
        return back();
    }

    public function download(Request $request)
    {
        $withdraw_requests = $this->withdraw_request
            ->when($request->has('search'), function ($query) use ($request) {
                $key = explode(' ', $request['search']);

                $user_ids = User::where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('id', 'like', "%{$value}%")
                            ->orWhere('phone', 'like', "%{$value}%")
                            ->orWhere('f_name', 'like', "%{$value}%")
                            ->orWhere('l_name', 'like', "%{$value}%")
                            ->orWhere('email', 'like', "%{$value}%");
                    }
                })->get()->pluck('id')->toArray();

                return $query->whereIn('user_id', $user_ids);
            })
            ->with('user', 'withdrawal_method')
            ->when($request->has('withdrawal_method') && $request->withdrawal_method != 'all', function ($query) use ($request) {
                return $query->where('withdrawal_method_id', $request->withdrawal_method);
            })->get();
        $storage = [];

        foreach ($withdraw_requests as $key=>$withdraw_request) {
            if (!is_null($withdraw_request->user) && !is_null($withdraw_request->withdrawal_method_fields)) {
                $data = [
                    'No' => $key+1,
                    'UserName' => $withdraw_request->user->f_name . ' ' . $withdraw_request->user->l_name,
                    'UserPhone' => $withdraw_request->user->phone,
                    'UserEmail' => $withdraw_request->user->email,
                    'method_name' => $withdraw_request->withdrawal_method->method_name??'',
                ];

                $storage[] = array_merge($data, $withdraw_request->withdrawal_method_fields);
            }
        }

        return (new FastExcel($storage))->download(time() . '-file.xlsx');
    }
}

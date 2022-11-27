<?php

namespace App\Http\Controllers;

use App\AutoBank;
use App\AutoCash;
use App\Paymnet;
use App\Studant;
use App\StudantAccount;
use App\StudantInstallment;
use App\StudantTolls;
use App\SystemLog;
use App\Transactions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class StudantInstallmentsController extends Controller
{

    const MODEL = "App\StudantInstallment";

    public function all()
    {
        $Payment = Paymnet::select("id", "amount", "PaymentFrom", "PaymentType", "StatmentNumber", "StatmentDate", "created_at")->get();
        return $this->respond(Response::HTTP_OK, $Payment);
    }

    public function get($id)
    {
        $m = self::MODEL;
        $model = $m::find($id);
        if (is_null($model)) {
            return $this->respond(Response::HTTP_NOT_FOUND);
        }

        return $this->respond(Response::HTTP_OK, $model);
    }

    public function getAccountInfo(Request $request, $id)
    {
        $studant = Studant::find($id);
        $stdaccount = StudantAccount::where('studantId', '=', $studant['id'])->first();
        $studant['account'] = $stdaccount;
    }

    public function add(Request $request)
    {
        $m = self::MODEL;
        $payments = array();
        $installments = array();
        $payment = $request->input('Payment');
        $installment = $request->input('StudantInstallment');
        $payment['PaymentFrom'] = $installment['StudentId'];
        $payment['PaymentTo'] = "acmst";
        $payment['PaymentDate'] = Carbon::today();
        $payment['StatmentDate'] = Carbon::parse($payment['StatmentDate']);
        $stdacount = StudantAccount::where('studantId', '=', $installment['StudentId'])->first();
        $std = Studant::where('id', '=', $installment['StudentId'])->first();
        $tolls = StudantTolls::where('year', '=', Carbon::parse($std['applyDate'])->year)->where('program', '=', $std['program'])->where('currency', '=', $stdacount['currency'])->first();
        $amount = ((int) $stdacount['tolls'] * (float) $stdacount['scolarship']) / (int) $tolls['LoanNumber'];
        $restpayment =  (int) $payment['amount'] + $stdacount['amount'];
        $transaction = new Transactions();
        $transaction['amount'] = $payment['amount'];
        $transaction['userId'] = $payment['userId'];
        $transaction['studantId'] = $installment['StudentId'];
        $transaction['StatmentDate'] = $payment['StatmentDate'];
        $transaction['StatmentNumber'] = $payment['StatmentNumber'];
        $transaction['PaymentMethod'] = $payment['PaymentMethod'];
        $transaction['payments'] = '';
        $transaction['stdYear'] = $std['stdYear'];
        $receipt = 0;

        if (($tolls['LoanNumber'] == $stdacount['loan'] && (int) $restpayment > (int) $amount) || ((int) $stdacount['tolls'] + (int) $stdacount['registration']) < $restpayment)
            return response()->json('الدفعية اكثر من الرسوم المستحقة');

        if ($std['status'] == 'غير مسجل') {

            if ($stdacount['registration'] > $restpayment)
                return response()->json('الدفعية اقل من الرسوم المستحقة');

            $p = $payment;
            $p['amount'] = $stdacount['registration'];
            $p['PaymentType'] = 'تسجيل';
            $restpayment = $restpayment - $p['amount'];
            $p = Paymnet::create($p);
            $transaction['payments'] = $transaction['payments'] . $p['id'] . '|';
            $std['status'] = 'مسجل';
            $std->update();
        }

        if ($tolls['LoanNumber'] >= $stdacount['loan'] && $std['status'] == 'مسجل') {

            while ((int) $restpayment >= (int) $amount && (int) $stdacount['loan'] <= (int) $tolls['LoanNumber']) {
                $payment['amount'] = $amount;
                $payment['PaymentType'] = "loan." . $stdacount['loan'];
                $pay = Paymnet::create($payment);
                $install = new StudantInstallment();
                $install['PaymentId'] =  $std['id'];
                $install['StudentId'] = $pay['id'];
                $install['checked'] = '0';
                $install['checkedBy'] = '0';
                $install['year'] = $std['year'];
                $install->save();
                $restpayment = $restpayment - $amount;
                $transaction['payments'] = $transaction['payments'] . $pay['id'] . '|';
                $stdacount['loan'] = $stdacount['loan'] + 1;
                $payments[] = $pay;
                $installments[] = $install;
            }

            if ($payment['PaymentMethod'] == 'نقدي') {
                $receiptNumber = AutoCash::create();
                $receipt = $receiptNumber['id'];
                $transaction['StatmentNumber'] = $receipt;
            } else {
                $receiptNumber = AutoBank::create();
                $receipt = $receiptNumber['id'];
            }
        } else {
            return response()->json("تم دفع الرسوم مسبقا");
        }
        $stdacount['amount'] = (int) $restpayment;
        $transaction['leftover'] = (int) $restpayment;
        $transaction['ReceiptNumber'] = (int) $receipt;
        $transaction['userId'] = $request->auth["id"];
        $stdacount->update();
        $transaction->save();
        $sysLog = new SystemLogs();
        
        $sysLog->createLog("transaction", $transaction['id'], $std['id'], Carbon::now(),  $transaction['ReceiptNumber'] . "|" . $transaction['amount'] . "|" . $transaction['leftover'] . "|" . $transaction['PaymentMethod'] . "|" . $transaction['stdYear']);
        return response()->json("تم بنجاح، رقم الايصال " . $receipt);
    }

    public function put(Request $request, $id)
    {
        $m = self::MODEL;
        $this->validate($request, $m::$rules);
        $model = $m::find($id);
        if (is_null($model)) {
            return $this->respond(Response::HTTP_NOT_FOUND);
        }
        $model->update($request->all());
        return $this->respond(Response::HTTP_OK, $model);
    }

    public function remove($id)
    {
        $m = self::MODEL;
        if (is_null($m::find($id))) {
            return $this->respond(Response::HTTP_NOT_FOUND);
        }
        $m::destroy($id);
        return $this->respond(Response::HTTP_NO_CONTENT);
    }

    public function viewInvoices($id)
    {
        $m = self::MODEL;
        if (is_null($m::find($id))) {
            return $this->respond(Response::HTTP_NOT_FOUND);
        }
        $data = array();
        $std = Studant::find($id);
        $installment = $m::where("StudentId", '=', $id)->get();
        $payments = array();

        foreach ($installment as $in) {
            $payments[] = Paymnet::find($in['PaymentId']);
        }
        $loans = array();
        $i = 1;
        foreach ($payments as $pay) {
            $loans["Loan" . $i] = $pay;

            $i += 1;
        }
    }

    public function getYears(Request $request, $id)
    {
        $years = Transactions::where("studantId", $id)->select("stdYear")->distinct()->get();
        $data = array();
        foreach ($years as $year) {
            $data[] = $year['stdYear'];
        }
        return response()->json($data);
    }
    protected function respond($status, $data = [])
    {
        return response()->json($data, $status);
    }
}

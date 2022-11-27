<?php

namespace App\Http\Controllers;

use App\Paymnet;
use App\StudantInstallment;
use App\Studant;
use App\Transactions;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use PDF;

class PaymnetsController extends Controller
{

    const MODEL = "App\Paymnet";

    public function all()
    {
        $m = self::MODEL;
        return $this->respond(Response::HTTP_OK, $m::all());
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

    public function add(Request $request)
    {
        $m = self::MODEL;
        $this->validate($request, $m::$rules);
        return $this->respond(Response::HTTP_CREATED, $m::create($request->all()));
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

    protected function respond($status, $data = [])
    {
        return response()->json($data, $status);
    }

    public function printInvoice(Request $request, $id)
    {
        $installment = StudantInstallment::find($id);
        $payment = Paymnet::find($installment['PaymentId']);
        $studant = Studant::find($installment['StudentId']);

        $search = array(
            '$stdName',
            '$program',
            '$class',
            '$collegenbr',
            '$PaymentMethod',
            '$amount',
            '$stmnbr',
            '$PaymentType',
            '$stmdate',
            '$awriten',
            '$bank',
            '$user',
            '$sgntrue',
            '$invoice',
            '$date'
        );
        $replacement  = array(
            $studant['arabicFullName'],
            $studant['program'],
            $studant['class'],
            $studant['collegeNumber'],
            $payment['PaymentMethod'],
            $payment['amount'],
            $payment['StatmentNumber'],
            $payment['PaymentType'],
            $payment['StatmentDate'],
            'القيمة كتابة',
            'bank',
            $payment['userId'],
            '',
            $payment['id'],
            $payment['PaymentDate']
        );
        $html = File::get(storage_path() . '/assest/invoice.html');
        $html = str_replace($search, $replacement, $html);
        return response($html, 200);
    }

    public function ReportInstallmentPage(Request $request, $id)
    {
        $installments = StudantInstallment::where('StudentId', 'LIKE', $id)->get();
        $payments = Paymnet::where('PaymentFrom', '=', $id)->get();
        error_log(json_encode($installments));
        $transactions = Transactions::where('studantId', $id)->get();
        $studant = Studant::find($id);
        $response = array();
        $response['installments'] = $installments;
        $response['payments'] = $payments;
        $response['transactions'] = $transactions;
        $response['studant'] = $studant;
        return response()->json($response);
    }
}

<?php

namespace App\Http\Controllers;

use App\CommityLog;
use App\Paymnet;
use App\Studant;
use App\StudantAccount;
use App\StudantTolls;
use App\Transactions;
use App\ExtraTransactions;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class StudantAccountsController extends Controller
{

    const MODEL = "App\StudantAccount";

    use RESTActions;
    public function getAccountInfo(Request $request, $id)
    {
        $studant = Studant::find($id);
        $stdaccount = StudantAccount::where('studantId', '=', $studant['id'])->first();
        $stdaccount['tolls'] = $stdaccount['tolls'] *  $stdaccount['scolarship'];
        $payed = Transactions::where('studantId', $id)->where('stdYear', $studant['stdYear'])->sum('amount');
        $left = ($payed - ($stdaccount['tolls'] + $stdaccount['registration']) - $stdaccount['amount']);
        $stdaccount['left'] = $left;
        $stdaccount['payed'] = $payed;
        $stdaccount['arabicFullName'] = $studant['arabicFullName'];
        $stdaccount['collegeNumber'] = $studant['collegeNumber'];
        $stdaccount['class'] = $studant['class'];
        $stdaccount['semester'] = $studant['semester'];
        $stdaccount['program'] = $studant['program'];
        $stdaccount['stdYear'] = $studant['stdYear'];
        $stdaccount['applyDate'] = $studant['applyDate'];
        if ($stdaccount['scolarshipType'] == null) {
            $stdaccount['scolarshipType'] = 'لايوجد';
        }
        $stdaccount['transactions'] = Transactions::where('studantId', $id)->orderBy('id', 'DESC')->get();
        $stdaccount['extratransactions'] = ExtraTransactions::where('studantId', $id)->orderBy('id', 'DESC')->get();
        $stdaccount['payments'] = Paymnet::where('PaymentFrom', $id)->orderBy('id', 'ASC')->get();
        return response()->json($stdaccount);
    }
}

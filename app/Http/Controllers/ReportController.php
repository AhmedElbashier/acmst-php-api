<?php

namespace App\Http\Controllers;

use App\CommityLog;
use App\Paymnet;
use App\Studant;
use App\StudantAccount;
use App\StudantTolls;
use App\Transactions;
use App\ExtraTransactions;
use App\Settings;
use App\Users;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

use Firebase\JWT\JWT;

class ReportController extends Controller
{

    public function StdAccountReport(Request $request, $id, $year)
    {
        $std = Studant::findorFail($id);
        $stdaccount = StudantAccount::where('studantId', $std['id'])->first();
        $fullName = \str_replace(",", "", $std['arabicFullName']);
        $StdClass = $std['class'];
        $semester = $std['semester'];
        $collegeNumber = $std['collegeNumber'];
        $program = $std['program'];
        $stdYear = $year; //$std['stdYear'];
        $currency = $stdaccount['currency'];
        $tolls = StudantTolls::where('year', '=', Carbon::parse($std['applyDate'])->year)->where('program', '=', $std['program'])->first();
        $loans = $tolls['LoanNumber'];

        $payed = array();
        $payments = array();
        $pays = array();

        $transactions = Transactions::where("studantId", $std['id'])->where('stdYear', $year)->get();
        foreach ($transactions as $trans) {
            $pays[] = \explode("|", $trans['payments']);
        }
        if (isset($pays[0])) {
            foreach ($pays[0] as $pay) {
                $payments[] = Paymnet::find($pay);
            }
        }
        for ($i = 0; $i <= $loans; $i++) {
            if (isset($payments[$i])) {
                $payed[$i] = $payments[$i];

                $payments[$i]['StatmentNumber'] == NULL ? $payed[$i]['StatmentNumber'] = '' : $payed[$i]['StatmentNumber'] = $payments[$i]['StatmentNumber'];
                $payed[$i]['payed'] = $payments[$i]['amount'];
                $payed[$i]['left'] = 0;
            } else {
                $payed[$i]['PaymentType'] = 'loan.' . ((int) $i + 1);
                $payed[$i]['StatmentDate'] = '';
                $payed[$i]['StatmentNumber'] = '';
                $payed[$i]['amount'] = ($tolls['amount'] / $loans);
                $payed[$i]['payed'] = 0;
                $payed[$i]['left'] = $payed[$i]['amount'];
                $payed[$i]['PaymentMethod'] = "";
            }
            $payed[$i]['PaymentType'] = \str_replace(".", " ", $payed[$i]['PaymentType']);
            $payed[$i]['PaymentType'] = \str_replace("loan", "القسط", $payed[$i]['PaymentType']);
            $payed[$i]['PaymentType'] = \str_replace("1", "الاول", $payed[$i]['PaymentType']);
            $payed[$i]['PaymentType'] = \str_replace("2", "الثاني", $payed[$i]['PaymentType']);
            $payed[$i]['PaymentType'] = \str_replace("3", "الثالث", $payed[$i]['PaymentType']);
            $payed[$i]['PaymentType'] = \str_replace("4", "الرابع", $payed[$i]['PaymentType']);
            $payed[$i]['PaymentType'] = \str_replace("registration", "التسجيل", $payed[$i]['PaymentType']);
            $payed[$i]['PaymentMethod'] = \str_replace("cash", "نقدي", $payed[$i]['PaymentMethod']);
            $payed[$i]['PaymentMethod'] = \str_replace("bank", "بنك", $payed[$i]['PaymentMethod']);
        }
        $report = array();
        $report['StdName'] = $fullName;
        $report['StdClass'] = $StdClass;
        $report['semester'] = $semester;
        $report['collegeNumber'] = $collegeNumber;
        $report['program'] = $program;
        $report['currency'] = $currency;
        $report['loans'] = $loans;
        $report['stdYear'] = $stdYear;
        $report['payments'] = $payed;
        $report['transactions'] = $transactions;
        return response()->json($report, 200);
    }

    public function InvoiceReport(Request $request, $id)
    {
        $payment = Paymnet::findorFail($id);
        $std = Studant::find($payment['PaymentFrom']);
        $stdaccount = StudantAccount::where('studantId', $std['id'])->first();
        $user = Users::find($payment['userId']);
        $fullName = \str_replace(",", "", $std['arabicFullName']);
        $StdClass = $std['class'];
        $semester = $std['semester'];
        $collegeNumber = $std['collegeNumber'];
        $program = $std['program'];
        $stdYear = $std['stdYear'];
        $currency = $stdaccount['currency'];

        $payment['PaymentType'] = \str_replace(".", " ", $payment['PaymentType']);
        $payment['PaymentType'] = \str_replace("loan", "القسط", $payment['PaymentType']);
        $payment['PaymentType'] = \str_replace("1", "الاول", $payment['PaymentType']);
        $payment['PaymentType'] = \str_replace("2", "الثاني", $payment['PaymentType']);
        $payment['PaymentType'] = \str_replace("3", "الثالث", $payment['PaymentType']);
        $payment['PaymentType'] = \str_replace("4", "الرابع", $payment['PaymentType']);
        $payment['PaymentType'] = \str_replace("registration", "التسجيل", $payment['PaymentType']);

        $payment['PaymentMethod'] = \str_replace("cash", "نقدي", $payment['PaymentMethod']);
        $payment['PaymentMethod'] = \str_replace("bank", "بنك", $payment['PaymentMethod']);

        $payment['StatmentNumber'] == NULL ? $payment['StatmentNumber'] = '' : $payment['StatmentNumber'] = $payment['StatmentNumber'];

        $payment['PaymentTo'] == 'acmst' ? $payment['PaymentTo'] = 'الكلية - نقدي' : $payment['PaymentTo'] = $payment['PaymentTo'];

        $report = array();
        $report['StdName'] = $fullName;
        $report['StdClass'] = $StdClass;
        $report['semester'] = $semester;
        $report['collegeNumber'] = $collegeNumber;
        $report['program'] = $program;
        $report['currency'] = $currency;
        $report['stdYear'] = $stdYear;
        $report['userName'] = $user['name'];
        $report['payment'] = $payment;
        return response()->json($report, 200);
    }

    public function TransactionReport(Request $request, $id)
    {
        $transactions = Transactions::findorFail($id);
        $std = Studant::find($transactions['studantId']);
        $stdaccount = StudantAccount::where('studantId', $std['id'])->first();
        $user = Users::find($transactions['userId']);
        $fullName = \str_replace(",", "", $std['arabicFullName']);
        $StdClass = $std['class'];
        $semester = $std['semester'];
        $collegeNumber = $std['collegeNumber'];
        $program = $std['program'];
        $stdYear = $std['stdYear'];
        $currency = $stdaccount['currency'];

        $transactions['StatmentNumber'] == NULL ? $transactions['StatmentNumber'] = '' : $transactions['StatmentNumber'] = $transactions['StatmentNumber'];

        $report = array();
        $transactions['PaymentMethod'] == "نقدي" ? $report['PaymentTo'] =  'الكلية - نقدي' : $report['PaymentTo'] = 'بنك فيصل الاسلامي حساب رقم 4549';
        $report['StdName'] = $fullName;
        $report['StdClass'] = $StdClass;
        $report['semester'] = $semester;
        $report['collegeNumber'] = $collegeNumber;
        $report['program'] = $program;
        $report['currency'] = $currency;
        $report['stdYear'] = $stdYear;
        $report['userName'] = $user['name'];
        $report['transaction'] = $transactions;
        return response()->json($report, 200);
    }

    public function ExtraTransactionReport(Request $request, $id)
    {
        $extra = ExtraTransactions::findorFail($id);
        $std = Studant::find($extra['studantId']);
        $user = Users::find($extra['userId']);
        $report['userName'] = $user['name'];
        $fullName = \str_replace(",", "", $std['arabicFullName']);
        $StdClass = $std['class'];
        $semester = $std['semester'];
        $collegeNumber = $std['collegeNumber'];
        $program = $std['program'];
        $extra['StatmentNumber'] == NULL ? $extra['StatmentNumber'] = '' : $extra['StatmentNumber'] = $extra['StatmentNumber'];
        $report['StdName'] = $fullName;
        $report['StdClass'] = $StdClass;
        $report['semester'] = $semester;
        $report['collegeNumber'] = $collegeNumber;
        $report['program'] = $program;
        $report['ExtraPayment'] = $extra;
        return response()->json($report, 200);


    }


    public function RegistrationReport(Request $request, $id)
    {
        $std = Studant::find($id);
        $stdaccount = StudantAccount::where('studantId', $std['id'])->first();
        $fullName = \explode(",", $std['arabicFullName']);
        $StdClass = $std['class'];
        $semester = $std['semester'];
        $collegeNumber = $std['collegeNumber'];
        $program = $std['program'];
        $nationality = $std['nationality'];
        $phoneNumber = $std['phoneNumber1'];
        $religion = $std['religion'];
        $pvType = $std['pvType'];
        $pvNumber = $std['pvNumber'];
        $academicStand = $std['academicStand'];
        $AcceptanceType = $std['status'];
        $currency = $stdaccount['currency'];
        $address = $std['address'];
        $pic = $std['pic'];
        $pic = \str_replace("data:image/jpeg;base64,", "", $pic);
        $ScholarshipType =  $stdaccount['scolarshipType'] != '' ? $stdaccount['scolarshipType'] : 'لايوجد';
        $ScholarshipPer = 100 - ($stdaccount['scolarship'] * 100) . "%";

        $report = array();
        $report['StdName1'] = \str_replace("\n", "", $fullName[0]);
        $report['StdName2'] = $fullName[1];
        $report['StdName3'] = $fullName[2];
        $report['StdName4'] = $fullName[3];
        $report['StdClass'] = $StdClass;
        $report['PhoneNumber'] = $phoneNumber;
        $report['Nationality'] = $nationality;
        $report['Religon'] = $religion;
        $report['PvType'] = $pvType;
        $report['PvNumber'] = $pvNumber;
        $report['AcceptanceType'] = $academicStand;
        $report['PvNumber'] = $pvNumber;
        $report['StdSemester'] = $semester;
        $report['AcademicStand'] = $AcceptanceType;
        $report['StdCollegeNumber'] = $collegeNumber;
        $report['StdProgram'] = $program;
        $report['currency'] = $currency;
        $report['Address'] = $address;
        $report['Pic'] = $pic;
        $report['ScholarshipType'] = $ScholarshipType;
        $report['ScholarshipPer'] = $ScholarshipPer;
        $report['UserName'] = ''; //$user['name'];
        $report['FormDate'] = Carbon::now()->format("Y-m-d");
        return response()->json($report, 200);
    }

    public function CardReport(Request $request, $id)
    {
        $std = Studant::findorFail($id);
        $fullName = \str_replace(",", "", $std['arabicFullName']);
        $StdClass = $std['class'];
        $semester = $std['semester'];
        $settings = Settings::where('field1', '=', $semester)->first();
        $startDate = $settings['field2'];
        $endDate = $settings['field3'];
        $collegeNumber = $std['collegeNumber'];
        $program = $std['program'];
        $stdYear = $std['stdYear'];
        $pic = $std['pic'];
        $pic = \str_replace("data:image/jpeg;base64,", "", $pic);

        $report['StdName'] = $fullName;
        $report['StdClass'] = $StdClass;
        $report['semester'] = $semester;
        $report['collegeNumber'] = $collegeNumber;
        $report['startDate'] = $startDate;
        $report['startDate'] = $startDate;
        $report['endDate'] = $endDate;
        $report['program'] = $program;
        $report['stdYear'] = $stdYear;
        $report['pic'] = $pic;

        return response()->json($report, 200);
    }

    public function StdsAccountReport(Request $request)
    {
        $studants = Studant::all();
        $data = array();
        foreach ($studants as $std) {
            $stdaccount = StudantAccount::where('studantId', $std['id'])->first();
            $fullName = \str_replace(",", "", $std['arabicFullName']);
            $StdClass = $std['class'];
            $semester = $std['semester'];
            $collegeNumber = $std['collegeNumber'];
            $program = $std['program'];
            // $currentYear = \explode("-", $std['stdYear'])[0];
            $currentYear = Carbon::now()->year;
            $year = 2016;
            $tolls = StudantTolls::where('year', '=', Carbon::parse($std['applyDate'])->year)->where('program', '=', $std['program'])->first();
            while ($year <= $currentYear) {
                $stdYear = $year . '-' . ($year + 1);
                $currency = $stdaccount['currency'];
                $loans = $tolls['LoanNumber'];
                $payed = array();
                $payments = array();
                $pays = array();
                $transactions = Transactions::where("studantId", $std['id'])->where('stdYear', $stdYear)->get();
                foreach ($transactions as $trans) {
                    $pays[] = \explode("|", $trans['payments']);
                }
                if (isset($pays[0])) {
                    foreach ($pays[0] as $pay) {
                        $payments[] = Paymnet::find($pay);
                    }
                }
                for ($i = 0; $i <= $loans; $i++) {
                    if (isset($payments[$i])) {
                        $payed[$i] = $payments[$i];

                        $payments[$i]['StatmentNumber'] == NULL ? $payed[$i]['StatmentNumber'] = '' : $payed[$i]['StatmentNumber'] = $payments[$i]['StatmentNumber'];
                        $payed[$i]['payed'] = $payments[$i]['amount'];
                        $payed[$i]['left'] = 0;
                    } else {
                        $payed[$i]['PaymentType'] = 'loan.' . ((int) $i + 1);
                        $payed[$i]['StatmentDate'] = '';
                        $payed[$i]['StatmentNumber'] = '';
                        $payed[$i]['amount'] = ($tolls['amount'] / $loans);
                        $payed[$i]['payed'] = 0;
                        $payed[$i]['left'] = $payed[$i]['amount'];
                        $payed[$i]['PaymentMethod'] = "";
                    }
                    $payed[$i]['PaymentType'] = \str_replace(".", " ", $payed[$i]['PaymentType']);
                    $payed[$i]['PaymentType'] = \str_replace("loan", "القسط", $payed[$i]['PaymentType']);
                    $payed[$i]['PaymentType'] = \str_replace("1", "الاول", $payed[$i]['PaymentType']);
                    $payed[$i]['PaymentType'] = \str_replace("2", "الثاني", $payed[$i]['PaymentType']);
                    $payed[$i]['PaymentType'] = \str_replace("3", "الثالث", $payed[$i]['PaymentType']);
                    $payed[$i]['PaymentType'] = \str_replace("4", "الرابع", $payed[$i]['PaymentType']);
                    $payed[$i]['PaymentType'] = \str_replace("registration", "التسجيل", $payed[$i]['PaymentType']);
                    $payed[$i]['PaymentMethod'] = \str_replace("cash", "نقدي", $payed[$i]['PaymentMethod']);
                    $payed[$i]['PaymentMethod'] = \str_replace("bank", "بنك", $payed[$i]['PaymentMethod']);
                }
                $report = array();
                $report['StdName'] = $fullName;
                $report['StdClass'] = $StdClass;
                $report['semester'] = $semester;
                $report['collegeNumber'] = $collegeNumber;
                $report['program'] = $program;
                $report['currency'] = $currency;
                $report['loans'] = $loans;
                $report['stdYear'] = $stdYear;
                $report['payments'] = $payed;
                $report['transactions'] = $transactions;
                $data[$stdYear][$fullName] = (array) $report;
                $year++;
            }
        }
        return response()->json($data, 200);
    }

    public function Print(Request $request)
    {
     
        return response()->json(self::getPdf($request), 200);
    }

    public function getPdf(Request $request)
    {
        # code...
        $host    = "127.0.0.1";
        $port    = 5030;
        $std = array();
        $std['Id'] = $request->input("id");
        $std['type'] = $request->input("type");
        $std['year'] = $request->input("year");
        $message = json_encode($std);
        $socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");
        $result = socket_connect($socket, $host, $port) or die("Could not connect to server\n");
        socket_write($socket, $message, strlen($message)) or die("Could not send data to server\n");
        $result = socket_read($socket, 1024) or die("Could not read server response\n");
        socket_close($socket);
        return env('APP_URL') . "/api/storage/pdf/" . $result;
    }
}

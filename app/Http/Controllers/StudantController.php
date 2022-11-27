<?php

namespace App\Http\Controllers;

use App\CommityLog;
use App\Paymnet;
use App\Studant;
use App\StudantAccount;
use App\StudantInstallment;
use App\StudantTolls;
use App\SystemLog;
use App\Transactions;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Cards;


class StudantController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function getAll()
    {
        $studants = Studant::all();
        foreach ($studants as $studant) {
            $stdaccount = StudantAccount::where('studantId', $studant['id'])->first();
            $studant['currency'] = $stdaccount['currency'];
        }
        return response()->json($studants);
    }

    public function getScolarship()
    {
        $studants = Studant::select('id', 'arabicFullName', 'englishFullName', 'status', 'stdYear', 'nationality', 'applyDate', 'program', 'collegeNumber', 'status', 'class', 'semester')->get();
        $data = array();
        foreach ($studants as $studant) {
            $stdaccount = StudantAccount::where('studantId', $studant['id'])->first();
            if ($stdaccount['scolarship'] != "1") {
                $studant['currency'] = $stdaccount['currency'];
                $studant['scolarship'] = $stdaccount['scolarship'];
                $studant['scolarshipType'] = $stdaccount['scolarshipType'];
                $data[] = $studant;
            }
        }
        return response()->json($data);
    }

    public function getAllBasic()
    {
        $studants = Studant::select('id', 'arabicFullName', 'englishFullName', 'status', 'stdYear', 'nationality', 'applyDate', 'program', 'collegeNumber', 'status', 'class', 'semester')->get();
        foreach ($studants as $studant) {
            $stdaccount = StudantAccount::where('studantId', $studant['id'])->first();
            $studant['currency'] = $stdaccount['currency'];
            $studant['scolarship'] = $stdaccount['scolarship'];
            $studant['scolarshipType'] = $stdaccount['scolarshipType'];
        }
        return response()->json($studants);
    }

    public function getStudant($id)
    {
        $studant = Studant::findOrFail($id);
        return response()->json($studant);
    }


    public function create(Request $request)
    {
        $studant = Studant::create($request->all());

        return response()->json($studant, 201);
    }

    public function update($id, Request $request)
    {
        $studant = Studant::findOrFail($id);
        $data = array();
        $data = $request->all();
        $data['applyDate'] = Carbon::parse($data['applyDate'])->format("Y-m-d");
        $data['birthday'] = Carbon::parse($data['birthday'])->format("Y-m-d");
        $year = Carbon::parse($data['applyDate'])->year;
Log::info($request->auth);
        if (!Carbon::parse($data['applyDate'])->equalTo(Carbon::parse($studant['applyDate']))) {

            $new = false;
            $stdaccount = StudantAccount::where('studantId', $id)->first();
            if (!$stdaccount) {
                $stdaccount = new StudantAccount();
                $new = true;
            }
            $currency = $data['nationality'] == 'سوداني' ? 'جنيه' : 'دولار';
            $amount = 0;
            $registration = 0;
            if ($studant['status'] === 'قبول نظامي') {
                $data['class'] = 'الاول';
                $tolls = StudantTolls::where('year', '=',  Carbon::parse($studant['applyDate'])->year)->where('program', '=', $studant['program'])->where('currency', '=', $currency)->first();
                $amount = $tolls['amount'];
                $registration = $tolls['registration'];
            } else {
                $years = ['الاول' => 0, 'الثاني' => 1, 'الثالث' => 2, 'الرابع' => 3];
                $year = Carbon::parse($studant['applyDate'])->year - $years[$studant['class']];
                $query = "select a.* from studant_accounts a
                INNER JOIN studants s
                 ON(a.studantId = s.id)
                where year(s.applyDate) = " . $year . " and a.scolarship = '1' and a.scolarshipType = '' and s.class = '" . $studant['class'] . "' and s.program = '" . $studant['program'] . "' and a.currency = '" . $currency . "' and s.status = 'قبول نظامي'  limit 1";
                $acc =  DB::select(DB::raw($query));
                $amount = $acc[0]->tolls;
                $registration = $acc[0]->registration;
            }
            $data['stdYear'] = $year  . '-' . ((int) $year + 1);
            $data['semester'] = 'الاول';
            $stdaccount['currency'] = $currency;
            $stdaccount['studantId'] = $studant['id'];
            $stdaccount['amount'] = '0';
            $stdaccount['tolls'] =  $amount;
            $stdaccount['registration'] =  $registration;
            if ($stdaccount['scolarshipType'] == 'اعادة' || $stdaccount['scolarshipType'] == 'اعادة 2' || $stdaccount['scolarshipType'] == 'من الخارج') {
                $stdaccount['scolarship'] =  1;
                $stdaccount['scolarshipType'] =  '';
            }
            $stdaccount['loan'] = '1';
            $data['status'] = 'غير مسجل';
            Transactions::whereIn('studantId', [$id])->delete();
            Paymnet::whereIn('PaymentFrom', [$id])->delete();
            StudantInstallment::whereIn('StudentId', [$id])->delete();
            if (!$new) $stdaccount->update();
            else $stdaccount->save();
            $sysLog = new SystemLogs();
            $sysLog->createLog("restStudant", null, $id, Carbon::now(), "user:" . $request->auth["id"]);
        }
        $studant->update($data);
        return response()->json($studant, 200);
    }


    public function delete($id)
    {
        Studant::findOrFail($id)->delete();
        return response()->json('Deleted Successfully', 200);
    }

    public function accaptance(Request $request)
    {
        $studant = $request->input('studant');
        $studant['birthday'] = Carbon::parse($studant['birthday']);
        $studant['applyDate'] = Carbon::parse($studant['applyDate']);
        $studant['semester'] = 'الاول';
        $studant['status'] = 'غير معتمد';
        $studant['stdYear'] = Carbon::parse($studant['applyDate'])->year . '-' . (Carbon::parse($studant['applyDate'])->addYear()->year);
        $studant = Studant::create($studant);
        $currency = $request->input('studant')['nationality'] == 'سوداني' ? 'جنيه' : 'دولار';
        $amount = 0;
        $registration = 0;
        if ($studant['status'] === 'قبول نظامي') {
            $tolls = StudantTolls::where('year', '=',  Carbon::parse($studant['applyDate'])->year)->where('program', '=', $studant['program'])->where('currency', '=', $currency)->first();
            $amount = $tolls['amount'];
            $registration = $tolls['registration'];
        } else {
            $years = ['الاول' => 0, 'الثاني' => 1, 'الثالث' => 2, 'الرابع' => 3];
            $year = Carbon::parse($studant['applyDate'])->year - $years[$studant['class']];
            $query = "select a.* from studant_accounts a
            INNER JOIN studants s
             ON(a.studantId = s.id)
            where year(s.applyDate) = " . $year . " and a.scolarship = '1' and a.scolarshipType = '' and s.class = '" . $studant['class'] . "' and s.program = '" . $studant['program'] . "' and a.currency = '" . $currency . "' and s.status = 'قبول نظامي'  limit 1";
            $acc =  DB::select(DB::raw($query));
            
            $amount = $acc[0]->tolls;
            $registration = $acc[0]->registration;
        }
        $stdaccount = new StudantAccount();
        $stdaccount['currency'] = $currency;
        $stdaccount['studantId'] = $studant['id'];
        $stdaccount['amount'] = '0';
        $stdaccount['tolls'] =  $amount;
        $stdaccount['registration'] =  $registration;
        $stdaccount['loan'] = '1';
        $stdaccount->save();
        return response()->json("done");
    }


    public function genAccounts(Request $request)
    {
        $studants = Studant::all();
        $count = 0;
        foreach ($studants as $studant) {
            if ((StudantAccount::where("studantId", $studant['id'])->get()->count() >= 1)) {
                $stdaccount = StudantAccount::where("studantId", $studant['id'])->first();
                if ($studant['nationality'] == 'سوداني' && $stdaccount['currency'] == "دولار") {
                    $year = Carbon::parse($studant['applyDate'])->year;
                    $currency = 'جنيه';
                    $tolls = StudantTolls::where('year', '=',  Carbon::parse($studant['applyDate'])->year)->where('program', '=', $studant['program'])->where('currency', '=', $currency)->first();
                    switch ($studant['class']) {
                        case 'الاول':
                            $stdaccount['registration'] =  $tolls['registration'];
                            break;
                        case 'الثاني':
                            if (Carbon::parse($studant['applyDate'])->year != 2016) {
                                $stdaccount['registration'] = (int) $tolls['registration'] * 1.1;
                            } else {
                                $stdaccount['registration'] =  $tolls['registration'];
                            }
                            break;
                        case 'الثالث':
                            if (Carbon::parse($studant['applyDate'])->year != 2016) {
                                $stdaccount['registration'] = (int) $tolls['registration'] * 1.1 * 1.1;
                            } else {
                                $stdaccount['registration'] =  $tolls['registration'] * 1.1;
                            }
                            break;
                        case 'الرابع':
                            if (Carbon::parse($studant['applyDate'])->year != 2016) {
                                $stdaccount['registration'] = (int) $tolls['registration'] * 1.1 * 1.1 * 1.1;
                            } else {
                                $stdaccount['registration'] =  $tolls['registration'] * 1.1 * 1.1;
                            }
                            break;


                        default:
                            $stdaccount['registration'] =  $tolls['registration'];
                            break;
                    }
                    $stdaccount['currency'] = $currency;
                    $stdaccount['tolls'] =  $tolls['amount'];
                    $stdaccount->save();
                    $count++;
                }
            }
        }
        return response('all good:' . $count);
    }

    public function accepted(Request $request)
    {
        $studants = $request->input('studants');
        foreach ($studants as $studant) {
            $std = Studant::findorFail($studant['id']);
            $std['status'] = 'غير مسجل';
            $std->update();
        }
        return response()->json('تم بنجاح');
    }

    public function registration(Request $request, $id)
    {
        $studant = Studant::findOrFail($id);
        $studant['status'] = $request->input('status');
        if ($request->input('status') == 'newAcceptance')
            $studant['collegeNumber'] = $request->input('collegeNumber');
        $studant->update();
        return response()->json($studant);
    }

    public function scolarship(Request $request, $id)
    {
        $studant = Studant::find($id);
        $stdaccount = StudantAccount::where('studantId', 'like', $id)->first();

        $scolarshipType = $request->input('scolarshipType');
        if ($stdaccount) {
            if ($scolarshipType == 'تخفيض مستمر' || $scolarshipType == 'تخفيض رسوم لمدة عام') {
                $stdaccount['scolarship'] = $request->input('scolarship');
                $stdaccount['scolarshipType'] = $scolarshipType;
            } elseif ($scolarshipType == 'تخفيض أشقاء') {
                $brotherAccount = StudantAccount::where("studantId", $request->input("brother"))->first();

                $stdaccount['scolarship'] = "0.9";
                $brotherAccount['scolarship'] = "0.9";
                $stdaccount['scolarshipType'] = $scolarshipType;
                $brotherAccount['scolarshipType'] = $scolarshipType;
                $brotherAccount->update();
                $sysLog = new SystemLogs();
                $sysLog->createLog("scolarship", null, $request->input("brother"), Carbon::now(), $scolarshipType . "|" . $brotherAccount['scolarship'] . "|" . $studant['stdYear'] . "|user:" . $request->auth["id"]);
            } elseif ($scolarshipType == 'منحة وزارة' || $scolarshipType == 'منحة داخلية إجتماعية' || $scolarshipType == 'منحة داخلية تفوق أكاديمي') {
                $stdaccount['scolarship'] = '0';
                $stdaccount['scolarshipType'] = $scolarshipType;
            }
            $stdaccount->update();
            $sysLog = new SystemLogs();
            $sysLog->createLog("scolarship", null, $id, Carbon::now(), $scolarshipType . "|" . $stdaccount['scolarship'] . "|" . $studant['stdYear'] . "|user:" . $request->auth["id"]);
            return response()->json($stdaccount);
        }
        return response()->json("studant not found", 404);
    }

    public function removeScolarship(Request $request, $id)
    {
        $stdaccount = StudantAccount::where('studantId', 'like', $id)->first();
        $stdaccount['scolarship'] = '1';
        $stdaccount['scolarshipType'] = '';
        $stdaccount->update();
        $sysLog = new SystemLogs();
        $sysLog->createLog("removeScolarship", null, $id, Carbon::now(), $id . "|user:" . $request->auth["id"]);
        return response()->json("done");
    }

    public function passExams(Request $request)
    {


        $studants = $request->input('studants');
        $studant = Studant::find($studants[0]['id']);
        $stdaccount =  StudantAccount::where('studantId', $studant['id'])->first();
        $tolls = StudantTolls::where('year', '=',  Carbon::parse($studant['applyDate'])->year)->where('program', '=', $studant['program'])->where('currency', '=', $stdaccount['currency'])->first();
        $class = $studant['class'];

        if ((int) $tolls['LoanNumber'] < (int) $stdaccount['loan']) {
            $studant['semester'] = 'الاول';
            switch ($class) {
                case "الاول":
                    $studant['class'] = 'الثاني';
                    break;
                case "الثاني":
                    $studant['class'] = 'الثالث';
                    break;
                case "الثالث":
                    $studant['class'] = 'الرابع';
                    break;
                case "الرابع":
                    $studant['class'] = 'الخامس';
                    break;
                case "الخامس":
                    $studant['class'] = 'الخامس';
                    break;
                default:
                    $studant['class'] = 'الاول';
            }

            $stdaccount['loan'] = 1;

            if ($stdaccount['scolarshipType'] == 'تخفيض رسوم لمدة عام') {
                $stdaccount['scolarshipType'] = '';
                $stdaccount['scolarship'] = '1';
            } elseif ($stdaccount['scolarshipType'] == 'اعادة' || $stdaccount['scolarshipType'] == 'اعادة 2') {
                $stdaccount['scolarshipType'] = '';
                $stdaccount['scolarship'] = '1';
            }

            $year = (int) explode('-', $studant['stdYear'])[0];

            if ($year !== 2016)
                $stdaccount['registration'] = (int) $stdaccount['registration'] * 1.1;

            $studant['stdYear'] = ((int) $year + 1) . '-' . ((int) $year + 2);
            $studant['status'] = 'غير مسجل';
        } else {
            return response()->json("هذا الطالب لم يقم بدفع الرسوم كاملة:" . $studant['arabicFullName'], 200);
        }

        $sysLog = new SystemLogs();
        $sysLog->createLog("passexam", null, $studant['id'], Carbon::now(), "class:" . $class . "|" . $studant['class'] . "|" . $studant['stdYear'] . "|user:" . $request->auth["id"]);

        $stdaccount->update();
        $studant->update();

        return response()->json("تم نقل الطالب الى المستوى: " . $studant['class'], 200);
    }

    public function repeatYear(Request $request)
    {
        $studants = $request->input('studants');
        $studant = Studant::find($studants[0]['id']);
        $stdaccount =  StudantAccount::where('studantId', $studant['id'])->first();
        $tolls = StudantTolls::where('year', '=',  Carbon::parse($studant['applyDate'])->year)->where('program', '=', $studant['program'])->where('currency', '=', $stdaccount['currency'])->first();

        if ((int) $tolls['LoanNumber'] < (int) $stdaccount['loan']) {
            $studant['semester'] = 'الاول';
            $stdaccount['loan'] = 1;

            if ($stdaccount['scolarshipType'] == 'اعادة') {

                $stdaccount['scolarshipType'] = 'اعادة 2';
                $stdaccount['scolarship'] = '0.5';
            } elseif ($stdaccount['scolarshipType'] == 'اعادة 2') {
                $stdaccount['scolarshipType'] = 'من الخارج';
                $stdaccount['scolarship'] = '1';
            } else {

                $stdaccount['scolarshipType'] = 'اعادة';
                $stdaccount['scolarship'] = '0.5';
            }
            $year = (int) explode('-', $studant['stdYear'])[0];
            if ($year !== 2016)
                $stdaccount['registration'] = (int) $stdaccount['registration'] * 1.1;
            $studant['stdYear'] = ((int) $year + 1) . '-' . ((int) $year + 2);
            $studant['status'] = 'غير مسجل';
        } else {
            return response()->json("هذا الطالب لم يقم بدفع الرسوم كاملة:" . $studant['arabicFullName'], 200);
        }

        $sysLog = new SystemLogs();
        $sysLog->createLog("repeatYear", null, $studant['id'], Carbon::now(),  $studant['stdYear'] . "|" . $studant['class'] . "|" . $stdaccount['scolarshipType'] . "|user:" . $request->auth["id"]);

        $stdaccount->update();
        $studant->update();

        return response()->json("تم اعادة الطالب في المستوى: " . $studant['class'], 200);
    }


    public function freeze(Request $request)
     {
       
        $studant = Studant::findOrFail($request['id']);
        $stdaccount =  StudantAccount::where('studantId', $studant['id'])->first();
        $tolls = StudantTolls::where('year', '=',  Carbon::parse($studant['applyDate'])->year)->where('program', '=', $studant['program'])->where('currency', '=', $stdaccount['currency'])->first();
       

        if ((int) $tolls['LoanNumber'] > ((int) $stdaccount['loan'] / 2)) {

        $studant['status']="تجميد";
        $studant->update();
        $request['type']="freezeStatement";
        $report =new ReportController();
        $pdf=$report->getPdf($request);
        return response()->json($pdf,200);
        $sysLog = new SystemLogs();
        
        // $sysLog->createLog("transaction", $transaction['id'], $std['id'], Carbon::now(),  $transaction['ReceiptNumber'] . "|" . $transaction['amount'] . "|" . $transaction['leftover'] . "|" . $transaction['PaymentMethod'] . "|" . $transaction['stdYear']);
        }
        else
        return response()->json('الدفعية اقل من الرسوم المستحقة');
        
        return response()->json("تم تجميد الطالب : ", 200);
    }
    
    public function unfreeze(Request $request)
    {
        $studant = Studant::findOrFail($request['id']);
        $studant['status']="مسجل";
        $studant['stdYear']="2019-2020";
        $studant->update();
        return response()->json("تم فك التجميد بنجاح : ", 200);
          
    }
    
    public function resign(Request $request)
    {
        
        $studant = Studant::findOrFail($request['id']);
        $stdaccount =  StudantAccount::where('studantId', $studant['id'])->first();
        $studant['status']="إستقالة";
        $studant->update();
        $request['type']="resignationStatement";
       
        $report =new ReportController();
        $pdf=$report->getPdf($request);
        return response()->json($pdf,200);
    }
    
    public function transfare(Request $request)
    {
        $studant = Studant::findOrFail($request['id']);
        $studant['program']=$request['program'];
        $studant['class']=$request['class'];
        $studant['semester']="الاول";
        $studant['status']='غير مسجل';
        $stdaccount =  StudantAccount::where('studantId', $studant['id'])->first();
        $stdaccount['loan']="1";
        $stdaccount['scolarship']="1";
        $stdaccount['scolarshipType']=NULL;
        
        $exampleStd = Studant::where("program", $request['program'])->where('class', $request['class'])->where('status', "مسجل")->first();

        $tolls = StudantTolls::where('year', '=',  Carbon::parse($exampleStd['applyDate'])->year)->where('program', '=', $request['program'])->where('currency', '=', $stdaccount['currency'])->first();
        if($tolls == null)
            return response()->json('لا توجد دفعة لهذا المستوى',200);

        
        $stdaccount['registration'] = $tolls['registration'];
        $stdaccount['tolls'] = $tolls['amount'];
        $stdaccount->update();
        $studant->update();
        return response()->json('تم تحويل الطالب',200);
    }
    
    public function cardReplacement(Request $request)
    {
        $card = Cards::where("studantId",$request['id'])->first();
        $card->delete();
        return response()->json("تم إستخراج بدل فاقد : ", 200);
    }
   

    
}

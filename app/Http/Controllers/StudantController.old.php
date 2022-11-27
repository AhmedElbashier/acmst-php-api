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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;


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
        $studants = Studant::select('id', 'arabicFullName', 'englishFullName', 'academicStand', 'stdYear', 'nationality', 'applyDate', 'program', 'collegeNumber', 'status', 'class', 'semester')->get();
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
        $studants = Studant::select('id', 'arabicFullName', 'englishFullName', 'academicStand', 'stdYear', 'nationality', 'applyDate', 'program', 'collegeNumber', 'status', 'class', 'semester')->get();
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
        $data = $request->all();
        $data['applyDate'] = Carbon::parse($data['applyDate']);
        $studant['birthday'] = Carbon::parse($data['birthday']);
        $year = Carbon::parse($data['applyDate'])->year;

        $stdaccount = StudantAccount::where('studantId', $id)->first();
        $currency = $data['nationality'] == 'سوداني' ? 'جنيه' : 'دولار';
        $tolls = StudantTolls::where('year', '=',  $year)->where('program', '=', $data['program'])->where('currency', '=', $currency)->first();
        $stdaccount['currency'] = $currency;
        $stdaccount['tolls'] =  $tolls['amount'];
        $stdaccount['registration'] =  $tolls['registration'];
        if (!Carbon::parse($data['applyDate'])->equalTo(Carbon::parse($studant['applyDate']))) {
            $data['stdYear'] = $year  . '-' . ((int) $year + 1);
            $data['class'] = 'الاول';
            $data['semester'] = 'الاول';
            $stdaccount['currency'] = $currency;
            $stdaccount['studantId'] = $studant['id'];
            $stdaccount['amount'] = '0';
            $stdaccount['tolls'] =  $tolls['amount'];
            $stdaccount['registration'] =  $tolls['registration'];
            if ($stdaccount['scolarshipType'] == 'اعادة' || $stdaccount['scolarshipType'] == 'اعادة 2' || $stdaccount['scolarshipType'] == 'من الخارج') {
                $stdaccount['scolarship'] =  1;
                $stdaccount['scolarshipType'] =  '';
            }
            $stdaccount['loan'] = '1';
            $data['status'] = 'غير مسجل';
            Transactions::whereIn('studantId', [$id])->delete();
            Paymnet::whereIn('PaymentFrom', [$id])->delete();
            StudantInstallment::whereIn('StudentId', [$id])->delete();
        }
        $stdaccount->update();
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
        // $studant['class'] = 'الاول';
        $studant['status'] = 'غير معتمد';
        $studant['stdYear'] = Carbon::parse($studant['applyDate'])->year . '-' . (Carbon::parse($studant['applyDate'])->addYear()->year);
        $studant = Studant::create($studant);
        $commity = $request->input('commity');
        $commity = CommityLog::create($commity);
        $commity['studant'] = $studant['id'];
        $commity->update();
        $currency = $request->input('studant')['nationality'] == 'سوداني' ? 'جنيه' : 'دولار';
        $tolls = StudantTolls::where('year', '=',  Carbon::parse($studant['applyDate'])->year)->where('program', '=', $studant['program'])->where('currency', '=', $currency)->first();
        $stdaccount = new StudantAccount();
        $stdaccount['currency'] = $currency;
        $stdaccount['studantId'] = $studant['id'];
        $stdaccount['amount'] = '0';
        $stdaccount['tolls'] =  $tolls['amount'];
        $stdaccount['registration'] =  $tolls['registration'];
        $stdaccount['loan'] = '1';
        $stdaccount->save();
        return response()->json(['studant' => $studant, 'commity' => $commity]);
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
                    $stdaccount['loan'] = '1';
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
        $studant['academicStand'] = $request->input('academicStand');
        if ($request->input('academicStand') == 'newAcceptance')
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
                // $payments = Paymnet::where('PaymentFrom', $id)->where('PaymentType', '!=', 'تسجيل')->sum('amount');
                // $stdaccount['tolls'] = (int) ($stdaccount['tolls'] * $stdaccount['scolarship']) - $payments;
            } elseif ($scolarshipType == 'تخفيض أشقاء') {
                $brotherAccount = StudantAccount::where("studantId", $request->input("brother"))->first();

                $stdaccount['scolarship'] = "0.1";
                $brotherAccount['scolarship'] = "0.1";
                $stdaccount['scolarshipType'] = $scolarshipType;
                $brotherAccount['scolarshipType'] = $scolarshipType;
                $brotherAccount->update();
                $sysLog = new SystemLogs();
                $sysLog->createLog("scolarship", null, $request->input("brother"), Carbon::now(), $scolarshipType . "|" . $brotherAccount['scolarship'] . "|" . $studant['stdYear']);
                // $payments = Paymnet::where('PaymentFrom', $id)->where('PaymentType', '!=', 'تسجيل')->sum('amount');
                // $stdaccount['tolls'] = (int) ($stdaccount['tolls'] * 0.9) - $payments;
            } elseif ($scolarshipType == 'منحة وزارة' || $scolarshipType == 'منحة داخلية إجتماعية' || $scolarshipType == 'منحة داخلية تفوق أكاديمي') {
                $stdaccount['scolarship'] = '0';
                $stdaccount['scolarshipType'] = $scolarshipType;
                // $payments = Paymnet::where('PaymentFrom', $id)->where('PaymentType', '!=', 'تسجيل')->sum('amount');
                // $stdaccount['tolls'] = '0';
            }
            $stdaccount->update();
            $sysLog = new SystemLogs();
            $sysLog->createLog("scolarship", null, $id, Carbon::now(), $scolarshipType . "|" . $stdaccount['scolarship'] . "|" . $studant['stdYear']);
            return response()->json($stdaccount);
        }
        return response()->json("studant not found", 404);
    }

    public function passExams(Request $request)
    {


        $studants = $request->input('studants');
        // foreach ($studants as $studant) {
        $studant = Studant::find($studants[0]['id']);
        $stdaccount =  StudantAccount::where('studantId', $studant['id'])->first();
        $tolls = StudantTolls::where('year', '=',  Carbon::parse($studant['applyDate'])->year)->where('program', '=', $studant['program'])->where('currency', '=', $stdaccount['currency'])->first();
        $semester = $studant['semester'];
        $class = $studant['class'];
        // if ($semester == 'الاول') {
        //     $studant['semester'] = 'الثاني';
        // } elseif ($semester == 'الثاني') {
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
            }
            if ($stdaccount['scolarshipType'] == 'اعادة' || $stdaccount['scolarshipType'] == 'اعادة 2') {
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
        // }
        // }
        $sysLog = new SystemLogs();
        $sysLog->createLog("passexam", null, $studant['id'], Carbon::now(), "class:" . $class . "|" . $studant['class'] . "|" . $studant['stdYear']);
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
        $semester = $studant['semester'];
        // if ($semester == 'الاول') {
        //     $studant['semester'] = 'الثاني';
        // } elseif ($semester == 'الثاني') {
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
        // }
        $sysLog = new SystemLogs();
        $sysLog->createLog("repeatYear", null, $studant['id'], Carbon::now(),  $studant['stdYear'] . "|" . $studant['class'] . "|" . $stdaccount['scolarshipType']);
        $stdaccount->update();
        $studant->update();

        return response()->json("تم اعادة الطالب في المستوى: " . $studant['class'], 200);
    }
}

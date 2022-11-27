<?php


namespace App\Http\Controllers;

use App\Paymnet;
use App\Studant;
use App\StudantAccount;
use Carbon\Carbon;
use Crabbly\Fpdf\Fpdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use App\ExtraTransactions;
use Illuminate\Support\Facades\DB;
use App\Cards;




class ExtraTransactionsController extends Controller
{
    const MODEL = "App\Transactions";

    use RESTActions;
    public function all()
    {
        $m = self::MODEL;
        $data = $m::orderBy('id', 'DESC')->take(100)->get();
        $transactions = array();
        foreach ($data as $trans) {
            $std = Studant::find($trans['studantId']);
            $trans['stdName'] = $std['arabicFullName'];
            $transactions[] = $trans;
        }
        return $this->respond(Response::HTTP_OK, $transactions);
    }
    public function create(Request $request)
    {
        $extraTransaction = ExtraTransactions::create($request->all());

        return response()->json($extraTransaction, 201);
    }

    public function add(Request $request)
    {
        switch($request['PaymentType'])
        {
            case "unfreezeStatement":
                            
                    $studant = Studant::findOrFail($request['studantId']);
                    $studant['status']="مسجل";
                    $studant['stdYear']="2019-2020";

                    $extra =$request->input('extra_transactions');
                    $extra['studantId']= $request['studantId'];
                    $extra['amount']= $request['amount'];
                    $extra['userId']=$request->auth["id"];
                    $extra['PaymentType']= "فك تجميد";
                    $extra['PaymentName'] = "unfreezeStatement";
                    $extra['PaymentMethod'] = "نقدي";
                    $receiptNumber = AutoCash::create();
                    $extra['StatmentNumber'] = $receiptNumber['id'];
                    $extra['StatmentDate'] =  Carbon::parse($request['StatmentDate']);
                    $req=$extra;
                    $req=ExtraTransactions::create($req);


                    return response()->json("تم فك التجميد بنجاح : ", 200);

                break;
                
                case "certificationStatement":
                          
                    $extra =$request->input('extra_transactions');
                    $extra['studantId']= $request['studantId'];
                    $extra['amount']= $request['amount'];
                    $extra['userId']=$request->auth["id"];
                    $extra['PaymentType']= "شهادة قيد";
                    $extra['PaymentName'] = "certificationStatement";
                    $extra['PaymentMethod'] = "نقدي";
                    $receiptNumber = AutoCash::create();
                    $extra['StatmentNumber'] = $receiptNumber['id'];
                    $extra['StatmentDate'] =  Carbon::parse($request['StatmentDate']);
                    $req=$extra;
                    $req=ExtraTransactions::create($req);
                
                return response()->json("تم  بنجاح : ", 200); 
                break;
                case "lateRegistrateStatement":
                        
                    $extra =$request->input('extra_transactions');
                    $extra['studantId']= $request['studantId'];
                    $extra['amount']= $request['amount'];
                    $extra['userId']=$request->auth["id"];
                    $extra['PaymentType']= "تسجيل متأخر";
                    $extra['PaymentName'] = "lateRegistrateStatement";
                    $extra['PaymentMethod'] = "نقدي";
                    $receiptNumber = AutoCash::create();
                    $extra['StatmentNumber'] = $receiptNumber['id'];
                    $extra['StatmentDate'] =  Carbon::parse($request['StatmentDate']);
                    $req=$extra;
                    $req=ExtraTransactions::create($req);
                    
                    return response()->json("تم  بنجاح : ", 200);
                break;
                case "recorrection":
                     
                    $extra =$request->input('extra_transactions');
                    $extra['studantId']= $request['studantId'];
                    $extra['amount']= $request['amount'];
                    $extra['userId']=$request->auth["id"];
                    $extra['PaymentType']= "إعادة تصحيح";
                    $extra['PaymentName'] = "recorrection";
                    $extra['PaymentMethod'] = "نقدي";
                    $receiptNumber = AutoCash::create();
                    $extra['StatmentNumber'] = $receiptNumber['id'];
                    $extra['StatmentDate'] =  Carbon::parse($request['StatmentDate']);
                    $req=$extra;
                    $req=ExtraTransactions::create($req);
                    
                    return response()->json("تم  بنجاح : ", 200);
                break;
                case "failureRemovalStatement":
      
                    $extra =$request->input('extra_transactions');
                    $extra['studantId']= $request['studantId'];
                    $extra['amount']= $request['amount'];
                    $extra['userId']=$request->auth["id"];
                    $extra['PaymentType']= "إزالة رسوب";
                    $extra['PaymentName'] = "failureRemovalStatement";
                    $extra['PaymentMethod'] = "نقدي";
                    $receiptNumber = AutoCash::create();
                    $extra['StatmentNumber'] = $receiptNumber['id'];
                    $extra['StatmentDate'] =  Carbon::parse($request['StatmentDate']);
                    $req=$extra;
                    $req=ExtraTransactions::create($req);
                    
                    return response()->json("تم  بنجاح : ", 200);
                break;
                case "cardReplacementStatement":
                    $card = Cards::where("studantId",$request['studantId'])->first();
                    $card->delete();
                          
                    $extra =$request->input('extra_transactions');
                    $extra['studantId']= $request['studantId'];
                    $extra['amount']= $request['amount'];
                    $extra['userId']=$request->auth["id"];
                    $extra['PaymentType']= "بدل فاقد بطاقة";
                    $extra['PaymentName'] = "cardReplacementStatement";
                    $extra['PaymentMethod'] = "نقدي";
                    $receiptNumber = AutoCash::create();
                    $extra['StatmentNumber'] = $receiptNumber['id'];
                    $extra['StatmentDate'] =  Carbon::parse($request['StatmentDate']);
                    $req=$extra;
                    $req=ExtraTransactions::create($req);
                
                    return response()->json("تم إستخراج بدل فاقد : ", 200);
                break;
                case "detailStatement":
      
                    
                    $extra =$request->input('extra_transactions');
                    $extra['studantId']= $request['studantId'];
                    $extra['amount']= $request['amount'];
                    $extra['userId']=$request->auth["id"];
                    $extra['PaymentType']= "شهادة تفاصيل";
                    $extra['PaymentName'] = "detailStatement";
                    $extra['PaymentMethod'] = "نقدي";
                    $receiptNumber = AutoCash::create();
                    $extra['StatmentNumber'] = $receiptNumber['id'];
                    $extra['StatmentDate'] =  Carbon::parse($request['StatmentDate']);
                    $req=$extra;
                    $req=ExtraTransactions::create($req);
                    
                    return response()->json("تم  بنجاح : ", 200);
                break;
                case "graduationCertificateStatement":
                      
                    $extra =$request->input('extra_transactions');
                    $extra['studantId']= $request['studantId'];
                    $extra['amount']= $request['amount'];
                    $extra['userId']=$request->auth["id"];
                    $extra['PaymentType']= "شهادة تخرج ورق مؤمن";
                    $extra['PaymentName'] = "graduationCertificateStatement";
                    $extra['PaymentMethod'] = "نقدي";
                    $receiptNumber = AutoCash::create();
                    $extra['StatmentNumber'] = $receiptNumber['id'];
                    $extra['StatmentDate'] =  Carbon::parse($request['StatmentDate']);
                    $req=$extra;
                    $req=ExtraTransactions::create($req);
                    
                    return response()->json("تم  بنجاح : ", 200);
                break;
                case "graduationCertificateStatement2":
                    $extra =$request->input('extra_transactions');
                    $extra['studantId']= $request['studantId'];
                    $extra['amount']= $request['amount'];
                    $extra['userId']=$request->auth["id"];
                    $extra['PaymentType']= "شهادة تخرج نسخة كرتونية";
                    $extra['PaymentName'] = "graduationCertificateStatement2";
                    $extra['PaymentMethod'] = "نقدي";
                    $receiptNumber = AutoCash::create();
                    $extra['StatmentNumber'] = $receiptNumber['id'];
                    $extra['StatmentDate'] =  Carbon::parse($request['StatmentDate']);
                    $req=$extra;
                    $req=ExtraTransactions::create($req);
                
                return response()->json("تم  بنجاح : ", 200);
                break;
                default:

            break;
        }
     
    }


}

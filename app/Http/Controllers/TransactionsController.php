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
use TCPDF;
use TCPDF_FONTS;

class TransactionsController extends Controller
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

    public function report(Request $request)
    {
        $m = self::MODEL;
        $trans = array();
        $date = $request->input("date");
        $program = $request->input("program");
        $currency = $request->input("currency");
        $method = $request->input("method");
        Log::alert($date[0] == $date[1]);
        if ($date[0] == $date[1])
            // Log::alert('hey');
            $data = $m::where("StatmentDate", "=", Carbon::parse($date[0])->format('Y-m-d'))->orderBy('StatmentDate', 'ASC')->get();
        else
            $data = $m::where("StatmentDate", ">=", Carbon::parse($date[0]))->where("StatmentDate", "<=", Carbon::parse($date[1]))->orderBy('StatmentDate', 'ASC')->get();

        Log::alert($data);
        foreach ($data as $value) {
            if ($method == "الكل" || $value['PaymentMethod'] == $method) {
                $std = Studant::find($value['studantId']);
                $stdacc = StudantAccount::where("studantId", $std["id"])->first();
                $value['fullName'] = \str_replace(",", "", $std['arabicFullName']);
                $value['program'] = $std['program'];
                $value['currency']  = $stdacc['currency'];
                if (($std['program'] == $program || $program  == "الكل") && ($stdacc['currency'] == $currency || $currency == "الكل")) {
                    $trans[] = $value;
                }
            }
        }

        // array_multisort(array_column($trans, 'currency'), SORT_ASC, $trans);


        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $header = '  تقرير مالي   ' . Carbon::parse($date[0])->format("Y-m-d") . ' الى ' . Carbon::parse($date[1])->format("Y-m-d") . '    ' . "المستخدم: " . $request->auth['name'] . '    ' . "التاريخ: " . Carbon::now()->format("Y-m-d");
        // set default header data
        $pdf->SetHeaderData('PDF_HEADER_LOGO', PDF_HEADER_LOGO_WIDTH, 'كلية المدائن للعلوم والتكنولوجيا', $header);

        // set header and footer fonts
        $pdf->setHeaderFont(array('freeserif', '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(array('freeserif', '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language dependent data:
        $lg = array();
        $lg['a_meta_charset'] = 'UTF-8';
        $lg['a_meta_dir'] = 'rtl';
        $lg['a_meta_language'] = 'ar';
        $lg['w_page'] = 'page';

        $pdf->setLanguageArray($lg);


        if ($method != 'الكل') {
            // Log::alert($currency);
            if (array_search('جنيه', array_column($trans, 'currency')) !== false) {
                Log::alert('here');
                $pdf->AddPage();
                $width_cell = array(24, 20, 50, 45, 18, 18, 15);
                $pdf->SetFont('freeserif', 'B', 16, '', true);

                //Background color of header//
                $pdf->SetFillColor(250, 250, 250);

                $pdf->Cell(50, 10, $method, 1, 1, 'C', true);
                $pdf->Cell(50, 10, 'جنيه', 1, 1, 'C', true);

                $pdf->SetFont('freeserif', 'B', 12, '', true);
                //Background color of header//
                $pdf->SetFillColor(193, 229, 252);

                // Header starts ///
                $pdf->Cell($width_cell[0], 10, 'تاريخ الأشعار', 1, 0, 'C', true);
                $pdf->Cell($width_cell[1], 10, 'رقم الإشعار', 1, 0, 'C', true);
                $pdf->Cell($width_cell[2], 10, 'اسم الطالب', 1, 0, 'C', true);
                $pdf->Cell($width_cell[3], 10, 'البرنامج', 1, 0, 'C', true);
                $pdf->Cell($width_cell[4], 10, 'العملة', 1, 0, 'C', true);
                $pdf->Cell($width_cell[5], 10, 'المبلغ', 1, 1, 'C', true);
                //// header ends ///////

                $pdf->SetFont('freeserif', '', 10, '', true);
                //Background color of header//
                $pdf->SetFillColor(235, 236, 236);
                //to give alternate background fill color to rows//
                $fill = false;
                $pound = 0;
                $usd = 0;
                /// each record is one row  ///
                foreach ($trans as $row) {
                    if ($row['currency'] == 'جنيه') {
                        $pdf->Cell($width_cell[0], 10, $row['StatmentDate'], 1, 0, 'C', $fill);
                        $pdf->Cell($width_cell[1], 10, $row['StatmentNumber'], 1, 0, 'C', $fill);
                        $pdf->Cell($width_cell[2], 10, $row['fullName'], 1, 0, 'C', $fill);
                        $pdf->Cell($width_cell[3], 10, $row['program'], 1, 0, 'C', $fill);
                        $pdf->Cell($width_cell[4], 10, $row['currency'], 1, 0, 'C', $fill);
                        $pdf->Cell($width_cell[5], 10, $row['amount'], 1, 1, 'C', $fill);
                        //to give alternate background fill  color to rows//
                        $fill = !$fill;
                        $pound += $row['amount'];
                    }
                }

                $fmt = numfmt_create('us_US', \NumberFormatter::CURRENCY);
                $pdf->SetFont('freeserif', 'B', 10, '', true);
                $pdf->SetFillColor(250, 250, 250);
                $pdf->Cell(50, 10, 'مجموع الجنيه:' . numfmt_format_currency($fmt, $pound, "SDG"), 1, 1, 'C', true);
            }

            if (array_search('دولار', array_column($trans, 'currency')) !== false) {
                $pdf->AddPage();
                $width_cell = array(24, 20, 50, 45, 18, 18, 15);
                $pdf->SetFont('freeserif', 'B', 16, '', true);

                // $pdf->AddPage();

                $pdf->SetFillColor(250, 250, 250);
                $pdf->Cell(50, 10, 'دولار', 1, 1, 'C', true);

                $pdf->SetFont('freeserif', 'B', 12, '', true);
                //Background color of header//
                $pdf->SetFillColor(193, 229, 252);

                // Header starts ///
                $pdf->Cell($width_cell[0], 10, 'تاريخ الأشعار', 1, 0, 'C', true);
                $pdf->Cell($width_cell[1], 10, 'رقم الإشعار', 1, 0, 'C', true);
                $pdf->Cell($width_cell[2], 10, 'اسم الطالب', 1, 0, 'C', true);
                $pdf->Cell($width_cell[3], 10, 'البرنامج', 1, 0, 'C', true);
                $pdf->Cell($width_cell[4], 10, 'العملة', 1, 0, 'C', true);
                $pdf->Cell($width_cell[5], 10, 'المبلغ', 1, 1, 'C', true);
                //// header ends ///////

                $pdf->SetFont('freeserif', '', 10, '', true);
                //Background color of header//
                $pdf->SetFillColor(235, 236, 236);
                //to give alternate background fill color to rows//
                $fill = false;

                $pound = 0;
                $usd = 0;
                /// each record is one row  ///
                foreach ($trans as $row) {
                    if ($row['currency'] != 'جنيه') {
                        $pdf->Cell($width_cell[0], 10, $row['StatmentDate'], 1, 0, 'C', $fill);
                        $pdf->Cell($width_cell[1], 10, $row['StatmentNumber'], 1, 0, 'C', $fill);
                        $pdf->Cell($width_cell[2], 10, $row['fullName'], 1, 0, 'C', $fill);
                        $pdf->Cell($width_cell[3], 10, $row['program'], 1, 0, 'C', $fill);
                        $pdf->Cell($width_cell[4], 10, $row['currency'], 1, 0, 'C', $fill);
                        $pdf->Cell($width_cell[5], 10, $row['amount'], 1, 1, 'C', $fill);
                        //to give alternate background fill  color to rows//
                        $fill = !$fill;
                        $usd += $row['amount'];
                    }
                }

                $fmt = numfmt_create('us_US', \NumberFormatter::CURRENCY);
                $pdf->SetFont('freeserif', 'B', 10, '', true);
                $pdf->SetFillColor(250, 250, 250);
                $pdf->Cell(50, 10, 'مجموع الدولار:' . numfmt_format_currency($fmt, $usd, "USD"), 1, 1, 'C', true);
            }
        } else {

            if (array_search('نقدي', array_column($trans, 'PaymentMethod')) !== false) {
                $pdf->AddPage();
                $width_cell = array(24, 20, 50, 45, 18, 18, 15);
                $pdf->SetFont('freeserif', 'B', 16, '', true);

                //Background color of header//
                $pdf->SetFillColor(250, 250, 250);

                $pdf->Cell(50, 10, 'نقدي', 1, 1, 'C', true);

                $pdf->SetFont('freeserif', 'B', 12, '', true);
                //Background color of header//
                $pdf->SetFillColor(193, 229, 252);

                // Header starts ///
                $pdf->Cell($width_cell[0], 10, 'تاريخ الأشعار', 1, 0, 'C', true);
                $pdf->Cell($width_cell[1], 10, 'رقم الإشعار', 1, 0, 'C', true);
                $pdf->Cell($width_cell[2], 10, 'اسم الطالب', 1, 0, 'C', true);
                $pdf->Cell($width_cell[3], 10, 'البرنامج', 1, 0, 'C', true);
                $pdf->Cell($width_cell[4], 10, 'العملة', 1, 0, 'C', true);
                $pdf->Cell($width_cell[5], 10, 'المبلغ', 1, 1, 'C', true);
                //// header ends ///////

                $pdf->SetFont('freeserif', '', 10, '', true);
                //Background color of header//
                $pdf->SetFillColor(235, 236, 236);
                //to give alternate background fill color to rows//
                $fill = false;
                $pound = 0;
                $usd = 0;
                /// each record is one row  ///
                foreach ($trans as $row) {
                    if ($row['PaymentMethod'] == 'نقدي') {
                        $pdf->Cell($width_cell[0], 10, $row['StatmentDate'], 1, 0, 'C', $fill);
                        $pdf->Cell($width_cell[1], 10, $row['StatmentNumber'], 1, 0, 'C', $fill);
                        $pdf->Cell($width_cell[2], 10, $row['fullName'], 1, 0, 'C', $fill);
                        $pdf->Cell($width_cell[3], 10, $row['program'], 1, 0, 'C', $fill);
                        $pdf->Cell($width_cell[4], 10, $row['currency'], 1, 0, 'C', $fill);
                        $pdf->Cell($width_cell[5], 10, $row['amount'], 1, 1, 'C', $fill);
                        //to give alternate background fill  color to rows//
                        $fill = !$fill;
                        if ($row['currency'] == 'جنيه') {
                            $pound += $row['amount'];
                        } else {
                            $usd += $row['amount'];
                        }
                    }
                }

                $fmt = numfmt_create('us_US', \NumberFormatter::CURRENCY);
                $pdf->SetFont('freeserif', 'B', 10, '', true);
                $pdf->SetFillColor(250, 250, 250);
                $pdf->Cell(50, 10, 'مجموع الجنيه:' . numfmt_format_currency($fmt, $pound, "SDG"), 1, 0, 'C', true);
                $pdf->Cell(50, 10, 'مجموع الدولار:' . numfmt_format_currency($fmt, $usd, "USD"), 1, 1, 'C', true);
            }


            if (array_search('بنكي', array_column($trans, 'PaymentMethod')) !== false) {
                $pdf->AddPage();
                $width_cell = array(24, 20, 50, 45, 18, 18, 15);
                $pdf->SetFont('freeserif', 'B', 16, '', true);


                $pdf->SetFillColor(250, 250, 250);
                $pdf->Cell(50, 10, 'بنكي', 1, 1, 'C', true);

                $pdf->SetFont('freeserif', 'B', 12, '', true);
                //Background color of header//
                $pdf->SetFillColor(193, 229, 252);

                // Header starts ///
                $pdf->Cell($width_cell[0], 10, 'تاريخ الأشعار', 1, 0, 'C', true);
                $pdf->Cell($width_cell[1], 10, 'رقم الإشعار', 1, 0, 'C', true);
                $pdf->Cell($width_cell[2], 10, 'اسم الطالب', 1, 0, 'C', true);
                $pdf->Cell($width_cell[3], 10, 'البرنامج', 1, 0, 'C', true);
                $pdf->Cell($width_cell[4], 10, 'العملة', 1, 0, 'C', true);
                $pdf->Cell($width_cell[5], 10, 'المبلغ', 1, 1, 'C', true);
                //// header ends ///////

                $pdf->SetFont('freeserif', '', 10, '', true);
                //Background color of header//
                $pdf->SetFillColor(235, 236, 236);
                //to give alternate background fill color to rows//
                $fill = false;

                $pound = 0;
                $usd = 0;
                /// each record is one row  ///
                foreach ($trans as $row) {
                    if ($row['PaymentMethod'] == 'بنكي') {
                        $pdf->Cell($width_cell[0], 10, $row['StatmentDate'], 1, 0, 'C', $fill);
                        $pdf->Cell($width_cell[1], 10, $row['StatmentNumber'], 1, 0, 'C', $fill);
                        $pdf->Cell($width_cell[2], 10, $row['fullName'], 1, 0, 'C', $fill);
                        $pdf->Cell($width_cell[3], 10, $row['program'], 1, 0, 'C', $fill);
                        $pdf->Cell($width_cell[4], 10, $row['currency'], 1, 0, 'C', $fill);
                        $pdf->Cell($width_cell[5], 10, $row['amount'], 1, 1, 'C', $fill);
                        //to give alternate background fill  color to rows//
                        $fill = !$fill;
                        if ($row['currency'] == 'جنيه') {
                            $pound += $row['amount'];
                        } else {
                            $usd += $row['amount'];
                        }
                    }
                }

                $fmt = numfmt_create('us_US', \NumberFormatter::CURRENCY);
                $pdf->SetFont('freeserif', 'B', 10, '', true);
                $pdf->SetFillColor(250, 250, 250);
                $pdf->Cell(50, 10, 'مجموع الجنيه:' . numfmt_format_currency($fmt, $pound, "SDG"), 1, 0, 'C', true);
                $pdf->Cell(50, 10, 'مجموع الدولار:' . numfmt_format_currency($fmt, $usd, "USD"), 1, 1, 'C', true);
            }
        }

        $pdfName = 'daily-' . $request->auth['name'] . '-' . Carbon::now()->format("Y-m-d-H") . ".pdf";
        ob_clean();

        $data = array();
        $data['url'] = env('APP_URL') . "/api/storage/pdf/" . $pdfName;
        $data['trans'] = $trans;

        $pdf->Output("C:\\laragon\\www\\api\\storage\\pdf\\" . $pdfName, 'F');
        return response()->json($data, 200);
    }
}

<?php

namespace App\Http\Controllers;

use App\Cards;
use App\Settings;
use App\Studant;
use App\StudantAccount;
use App\StudantTolls;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use mikehaertl\pdftk\Pdf;

class CardsController extends Controller
{

    const MODEL = "App\Cards";

    use RESTActions;

    public function testPrintCard(Request $request)
    {
        $studant = $request->input('studantId');
        $studant = Studant::find($studant);
        $semester = $request->input('semester');
        $semester = Settings::where('field1', '=', $semester)->first();

        $search = array('$name', '$program', '$class', '$startdate', '$enddate');
        $replacement  = array($studant['arabicFullName'], $studant['program'], $studant['class'], $semester['field2'], $semester['field3']);
        $html = File::get(storage_path() . '/assest/card.html');
        $html = str_replace($search, $replacement, $html);
        // $pdf = new \PDF();
        // $content = $pdf->load($html)->obutput();
        // File::put(storage_path() . '/pdf/' . $card);
        // PDF::setOptions(['dpi' => 150, 'defaultPaperSize' => 'sans-serif']);
        // return \PDF::loadHTML($html)->setPaper('a4', 'landscape')->stream('card.pdf');
        return response($html, 200);
    }

    public function printReport(Request $request)
    {
        $host    = "127.0.0.1";
        $port    = 5030;
        $std = array();
        $std['Id'] = $request->input("id");
        $std['type'] = $request->input("type");
        $message = json_encode($std);
        $socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");
        $result = socket_connect($socket, $host, $port) or die("Could not connect to server\n");
        socket_write($socket, $message, strlen($message)) or die("Could not send data to server\n");
        $result = socket_read($socket, 1024) or die("Could not read server response\n");
        socket_close($socket);
        return response($result, 200);
    }

    public function Reader(Request $request, $id)
    {
        $studant = Studant::where("cardId", $id)->first();
        if ($studant['status'] === "مسجل")
            return response('1');
        return response('0');
    }
}

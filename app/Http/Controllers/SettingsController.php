<?php

namespace App\Http\Controllers;

use App\Role;
use App\StudantTolls;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Users;
use App\Services;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{

    const MODEL = "App\Settings";

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

    public function addUser(Request $request)
    {
        $user = $request->all();
        $role = Role::create();
        $role['acceptance'] = $user['role']['acceptance'];
        $role['approval'] = $user['role']['approval'];
        $role['registration'] = $user['role']['registration'];
        $role['scolarship'] = $user['role']['scolarship'];
        $role['installment'] = $user['role']['installment'];
        $role['exams'] = $user['role']['exams'];
        $role['settings'] = $user['role']['settings'];
        $role->save();
        $user['role'] = $role['id'];
        $user['lastLogin'] = null;
        $password = Hash::make($user['password']);
        $user['password'] = $password;
        $user = Users::create($user);
        return $this->respond(Response::HTTP_OK, $user);
    }

    public function addToll(Request $request)
    {
        $toll = $request->all();
        if (StudantTolls::where("year", $toll['year'])->where("program", $toll['program'])->where("currency", $toll['currency'])->get()->count())
            return $this->respond(Response::HTTP_NOT_FOUND);
        $toll = StudantTolls::create($toll);
        return $this->respond(Response::HTTP_OK, $toll);
    }
}

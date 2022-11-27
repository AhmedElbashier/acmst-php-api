<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Studant extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'arabicFullName',
        'englishFullName',
        'gender',
        'academicStand',
        'stdYear',
        'pvType',
        'pvNumber',
        'religion',
        'birthCountry',
        'birthday',
        'address',
        'nationality',
        'phoneNumber1',
        'phoneNumber2',
        'residencynumber',
        'residencyExpire',
        'parentName',
        'parentPhoneNumber1',
        'parentPhoneNumber2',
        'relation',
        'applyDate',
        'CertType',
        'CertPercentage',
        'program',
        'collegeNumber',
        'studentID',
        'cardId',
        'status',
        'year',
        'class',
        'semester',
        'pic',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
}

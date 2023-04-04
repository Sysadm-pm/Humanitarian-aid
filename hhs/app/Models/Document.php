<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//use \DB;
use Illuminate\Support\Facades\DB;

class Document extends Model
{
    protected $table = 'Documents';

    // Add your validation rules here
    public static $rules = [
    ];

    protected $fillable = [];
    protected $primaryKey = 'Id';
    const UPDATED_AT = 'ModifiedOn';
    const CREATED_AT = 'CreatedOn';


    public function scopeGetApplications($query,$user_id)
    {
        $query
        ->select('RegNumber')
        ->where('RecordState', '<>', 4)
        ->where('CitizenCardId','=',$user_id)
        ;
    }



}

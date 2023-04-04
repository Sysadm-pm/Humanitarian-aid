<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//use \DB;
use Illuminate\Support\Facades\DB;

class Person extends Model
{
    protected $table = 'Persons';

    // Add your validation rules here
    public static $rules = [
    ];

    protected $fillable = [];
    protected $primaryKey = 'Id';

    const UPDATED_AT = 'ModifiedOn';
    const CREATED_AT = 'CreatedOn';


    public function scopeWithDoc($query)
    {
        $query
        ->addSelect('p.Id as p_id') //93886
        ->addSelect('p.FullName as p_lastname')
        ->addSelect('p.Name as p_name')
        ->addSelect('p.MiddleName as p_middlename')
        ->addSelect( DB::raw("(to_char((\"p\".\"Birthday\"::date), 'DD.MM.YYYY'::text)) as p_bithday") )
        ->addSelect( DB::raw("(CASE
                                WHEN p.\"Gender\" = 'Male'   THEN 'Ч'
                                WHEN p.\"Gender\" = 'Female' THEN 'Ж'
                                ELSE p.\"Gender\"::text
                            END) p_gender"))
        ->addSelect('p.IPN as p_rnokpp')
        ->addSelect('p.IdentityDocumentType as p_doc_type')
        ->addSelect('p.DocPrefix as p_doc_prefix')
        ->addSelect('p.PersonalDocumentNumber as p_doc_number')
        ->addSelect( DB::raw("(to_char((\"p\".\"DocumentIssueDate\"::date), 'DD.MM.YYYY'::text)) as p_doc_issue_date") )
        ->addSelect( DB::raw("(to_char((\"p\".\"ExpirationDate\"::date), 'DD.MM.YYYY'::text)) as p_doc_expiration_date") )
        ->addSelect('p.PersonalDocumentAuthority as p_doc_issue_by')

        ->addSelect('dc.Id as dc_id')
        ->addSelect('dc.CitizenCardStatus as dc_card_status')
        ->addSelect('dc.RecordState as dc_record_state')
        ->addSelect('dc.PostIndex as dc_post_index')
        ->addSelect('dc.AtuDistrictId as dc_district_id')
        ->addSelect('dc_ad.Name as dc_district_name')
        ->addSelect('dc.Street as dc_street')
        ->addSelect('dc.BuildingTypeId as dc_building_type_id')
        ->addSelect('dc_mbt.Name as dc_building_type_name')
        ->addSelect('dc.BuildingNum as BuildingNum')
        ->addSelect('dc.AppartmentNum as dc_appartment')
        ->addSelect('dc.RegNumber as dc_reg_number')
        ->addSelect( DB::raw("(to_char((\"dc\".\"RegDate\"::date), 'DD.MM.YYYY'::text)) as dc_reg_date") )
//        картка заяви
        ->addSelect('dz.Id as dz_id')
        ->addSelect('dz.RecordState as dz_record_state')
        ->addSelect('dz.RegNumber as dz_reg_number')
        ->addSelect( DB::raw("(to_char((\"dz\".\"RegDate\"::date), 'DD.MM.YYYY'::text)) as dz_reg_date") )
        ->addSelect('dz.DescriptionOfReason as dz_description_of_reason')

        ->addSelect('dz.ApplicantTypeId as dz_ApplicantTypeId')
        ->addSelect(DB::raw("(to_char((\"dz\".\"ConsiderationDate\"::date), 'YYYY-MM-DD'::text)) as dz_ConsiderationDate"))
        ->addSelect('dz.ReasonOfSubmissionId as dz_ReasonOfSubmissionId')
        ->addSelect('dz.ResponsibleExecutorId as dz_ResponsibleExecutorId')
        ->addSelect('dz.Signed as dz_Signed')
        ->addSelect('dz.StatusId as dz_StatusId')
        ->addSelect('dz.SubmissionTypeId as dz_SubmissionTypeId')
        ->addSelect('dz.SubmitApplicationToId as dz_SubmitApplicationToId')
        ->addSelect( DB::raw("(to_char((\"dz\".\"RegDate\"::date), 'YYYY-MM-DD'::text)) as dz_RegDate") )
        ->addSelect('dz.CitizenCardId as dz_CitizenCardId')
        ->addSelect('dz.DescriptionOfReason as dz_DescriptionOfReason')
        ->addSelect('dz.PersonalDataPermission as dz_PersonalDataPermission')
        ->addSelect('dz.AverageMonthlyIncome as dz_AverageMonthlyIncome')
        // картка прийняття рішення
        ->addSelect('ds.Id as ds_id')
        ->addSelect('ds.ProtocolCardId as dp_id')
        ->addSelect('ds.DecisionTypeId as des_type_id')
        ->addSelect('ds.RecordState as ds_record_state')
        ->addSelect('ds.ApplicationId as ds_application_id')
        ->addSelect('ds.Amount as ds_amount')
        ->addSelect('ds.DecisionText as ds_decision_text')
        ->addSelect('ds.Notices as ds_notices')
        ->addSelect('ds.RegNumber as ds_reg_number')
        ->addSelect( DB::raw("(to_char((\"ds\".\"RegDate\"::date), 'DD.MM.YYYY'::text)) as ds_reg_date") )

        ->from('Persons AS p')//Особа
        ->whereNotNull('dc.Id')

        ->leftjoin('public.Documents as dc','dc.CitizenId', '=', 'p.Id')//Картка Громадянина
        ->leftjoin('AtuDistricts as dc_ad','dc_ad.Id', '=', 'dc.AtuDistrictId')//район
        ->leftjoin('MtaBuildingType as dc_mbt','dc_mbt.Id', '=', 'dc.BuildingTypeId')//тип будинку

        ->leftjoin('public.Documents as dz','dz.CitizenCardId', '=', 'dc.Id')//Картка Заяви
        ->leftjoin('public.Documents as ds','ds.ApplicationId', '=', 'dz.Id')//Картка Рішення

            ;
    }
    public function scopeGetOwner($query,$user_id)
    {
        $query
        ->addSelect('ou.Id as OwnerId')
        ->from('Persons AS p')//Особа
        ->join('public.OrgEmployee AS oe','oe.PersonId', '=', 'p.Id')
        ->join('public.OrgUnitPositionEmployees AS oupe','oupe.OrgEmployeeId', '=', 'oe.Id')
        ->join('public.OrgUnitPosition AS oup','oup.Id', '=', 'oupe.OrgUnitPositionId')
        ->join('public.OrgUnit AS ou','ou.Id', '=', 'oup.OrgUnitId')
        ->where('oe.RecordState', '<>', 4)//Картка Громадянина
        ->where('oupe.RecordState', '<>', 4)//Картка Громадянина
        ->where('oup.RecordState', '<>', 4)//Картка Громадянина
        ->where('ou.RecordState', '<>', 4)//Картка Громадянина
        ->where('p.UserId','=',$user_id)
        ->first()
        ;
    }

    public function scopeGetEmployee($query,$user_id=null)
    {
        $query
        ->addSelect(['oe.Id as oe_id'
        ,'p.UserId'
        ])
        ->from('Persons AS p')//Особа
        ->join('public.OrgEmployee AS oe','oe.PersonId', '=', 'p.Id')
        ->join('public.OrgUnitPositionEmployees AS oupe','oupe.OrgEmployeeId', '=', 'oe.Id')
        ->join('public.OrgUnitPosition AS oup','oup.Id', '=', 'oupe.OrgUnitPositionId')
        ->join('public.OrgUnit AS ou','ou.Id', '=', 'oup.OrgUnitId')
        ->where('oe.RecordState', '<>', 4)
        ->where('oupe.RecordState', '<>', 4)
        ->where('oup.RecordState', '<>', 4)
        ->where('ou.RecordState', '<>', 4)
        ->orderBy('ou.Id','asc')
        ->where('p.UserId','=',$user_id)
        ;
    }

}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Person;
use App\Models\Document;
use App\Models\DocumentPerson;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class PersonController extends Controller
{
    public function __construct()
    {
        session_id('monitor041122');
        session_start();

        if (!isset($_SESSION["select"])) {
            $_SESSION["select"] = 0;
        }
        if (!isset($_SESSION["select_helpers"])) {
            $_SESSION["select_helpers"] = 0;
        }

        if (!isset($_SESSION["update_person"])) {
            $_SESSION["update_person"] = 0;
        }
        if (!isset($_SESSION["update_request"])) {
            $_SESSION["update_request"] = 0;
        }
        if (!isset($_SESSION["update_decision"])) {
            $_SESSION["update_decision"] = 0;
        }

        if (!isset($_SESSION["insert_person"])) {
            $_SESSION["insert_person"] = 0;
        }
        if (!isset($_SESSION["insert_request"])) {
            $_SESSION["insert_request"] = 0;
        }
        if (!isset($_SESSION["insert_decision"])) {
            $_SESSION["insert_decision"] = 0;
        }

        if (!isset($_SESSION["curent_date"])) {
            $_SESSION["curent_date"] = Carbon::now()->timezone('Europe/Kiev')->format('Y-m-d');
        }

        if ($_SESSION["curent_date"] !== Carbon::now()->timezone('Europe/Kiev')->format('Y-m-d')) {
            error_log(json_encode($_SESSION, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ",\n", 3, "/var/www//count.json");
            $_SESSION["select"] = 0;
            $_SESSION["select_helpers"] = 0;

            $_SESSION["insert_person"] = 0;
            $_SESSION["insert_request"] = 0;
            $_SESSION["insert_decision"] = 0;

            $_SESSION["update_person"] = 0;
            $_SESSION["update_request"] = 0;
            $_SESSION["update_decision"] = 0;

            $_SESSION["curent_date"] = Carbon::now()->timezone('Europe/Kiev')->format('Y-m-d');
        }
    }
    public function __destruct()
    {
        session_write_close();
    }


    /**
     * Get my ip.
     *
     * @param  \Illuminate\Http\Request  $request
     */

    public function mirror(Request $request)
    {
        return $request;
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
        $result = Person::OrderBy("dc.Id")
            ->withDoc()
            ->paginate(15);

        return $result;
    }

    /**
     * Function for count request by type
     *
     * @param  $type
     */
    public static function countRequsets($type)
    {
        if (!is_numeric($_SESSION["{$type}"])) {
            $_SESSION["{$type}_error"] = "\"{$type}\" count error!";
        }
        switch ($type) {
            case "select":
                $_SESSION["select"] += 1;
                break;
            case "select_helpers":
                $_SESSION["select_helpers"] += 1;
                break;

            case "update_person":
                $_SESSION["update_person"] += 1;
                break;
            case "update_request":
                $_SESSION["update_request"] += 1;
                break;
            case "update_decision":
                $_SESSION["update_decision"] += 1;
                break;

            case "insert_person":
                $_SESSION["insert_person"] += 1;
                break;
            case "insert_request":
                $_SESSION["insert_request"] += 1;
                break;
            case "insert_decision":
                $_SESSION["insert_decision"] += 1;
                break;
            default:
                $_SESSION["switch_error"] = "Switch has been fault! Type value is: " . $type;
        }
    }



    public static function getDistrictPrefix($district_id)
    {
        $dictonary = [
            5   => 'ГОЛ',
            1   => 'ДАР',
            16  => 'ДЕС',
            6   => 'ДПР',
            14  => 'ОБЛ',
            2   => 'ПЕЧ',
            4   => 'ПОД',
            15  => 'КСШ',
            3   => 'СОЛ',
            8   => 'ШЕВ',
        ];

        return $dictonary[$district_id] ?? 'ХХХ';
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        if ($request->page >= 2) {
            return response()->json(['error' => ['message' => 'Limit']], 403);
        }
        $result = Person::where('p.FullName', 'ILIKE', '' . $request->p_lastname . '%')
            ->where('p.Name', 'ILIKE', '' . $request->p_name . '%')
            ->where('p.MiddleName', 'ILIKE', '' . $request->p_middlename . '%')
            ->where(function ($query) use ($request) {
                if ($request->p_rnokpp) {
                    $query->where('p.IPN', 'ILIKE', '' . $request->p_rnokpp . '%');
                } else {
                    $query->whereNotNull('p.IPN') //, 'ILIKE', '' . $request->p_rnokpp . '%')
                        ->orWhereNull('p.IPN');
                }
            })
            ->where('p.PersonalDocumentNumber', 'ILIKE', '' . $request->p_doc_number . '%')
            ->withDoc()
            ->OrderBy("dc.Id", 'desc')
            ->OrderBy("dz.Id", 'ASC')
            ->paginate(15)
            ->appends([
                'p_lastname' => $request->p_lastname,
                'p_name' => $request->p_name,
                'p_middlename' => $request->p_middlename,
                'p_rnokpp' => $request->p_rnokpp,
                'p_doc_number' => $request->p_doc_number,
            ]);

        self::countRequsets('select');

        return $result;
    }

    /**
     * Get Counts.
     *
     * @return \Illuminate\Http\Response
     */

    public function getCounts()
    {
        return $_SESSION;
    }

    /**
     * Get Type of desision.
     *
     * @return \Illuminate\Http\Response
     */

    public function getTypeDesision()
    {

        $result = \DB::table('MtaDecisionType')
            ->select(['Id', 'Name', 'Code'])
            ->where('RecordState', '<>', 4)
            ->orderBy('Id', 'asc')
            ->get();

        self::countRequsets('select_helpers');

        return $result;
    }

    /**
     * Get Applicant Category.
     *
     * @return \Illuminate\Http\Response
     */
    public function getApplicantCategory()
    {
        $result = \DB::table('MtaApplicantCategory')
            ->select(['Id', 'Name'])
            ->where('RecordState', '<>', 4)
            ->orderBy('Id', 'asc')
            ->get();

        self::countRequsets('select_helpers');

        return $result;
    }

    /**
     * Get Districts.
     *
     * @return \Illuminate\Http\Response
     */
    public function getDistricts()
    {
        $result = \DB::table('AtuDistricts')
            ->select(['Id', 'Name'])
            ->where('RecordState', '<>', 4)
            ->where('CityId', '=', 1)
            ->orderBy('Id', 'asc')
            ->get();

        self::countRequsets('select_helpers');

        return $result;
    }
    /**
     * Get Building Type.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBuildingType()
    {
        $result = \DB::table('MtaBuildingType')
            ->select(['Id', 'Name'])
            ->where('RecordState', '<>', 4)
            ->orderBy('Id', 'asc')
            ->get();

        self::countRequsets('select_helpers');

        return $result;
    }
    /**
     * Get Reason Of Submission.
     *
     * @return \Illuminate\Http\Response
     */
    public function getReasonOfSubmission()
    {
        $result = \DB::table('MtaReasonOfSubmission')
            ->select(['Id', 'Name'])
            ->where('RecordState', '<>', 4)
            ->orderBy('Id', 'asc')
            ->get();

        self::countRequsets('select_helpers');

        return $result;
    }

    /**
     * Get Applicant Type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getApplicantType(Request $request)
    {
        $result = \DB::table('MtaApplicantType')
            ->select(['Id', 'Name']);

        if (!empty($request->Id)) {
            $result->where('Id', '=', $request->Id);
        }

        $result = $result->where('RecordState', '<>', 4)
            ->orderBy('Id', 'asc')
            ->get();

        self::countRequsets('select_helpers');

        return $result;
    }

    /**
     * Get Application Status
     *
     * @return \Illuminate\Http\Response
     */
    public function getApplicationStatus()
    {
        $result = \DB::table('MtaApplicationStatus')
            ->select(['Id', 'Name'])
            ->where('RecordState', '<>', 4)
            ->orderBy('Id', 'asc')
            ->get();

        self::countRequsets('select_helpers');

        return $result;
    }

    /**
     * Get Submission Type
     *
     * @return \Illuminate\Http\Response
     */
    public function getSubmissionType()
    {
        $result = \DB::table('MtaSubmissionType')
            ->select(['Id', 'Name'])
            ->where('RecordState', '<>', 4)
            ->orderBy('Id', 'asc')
            ->get();

        self::countRequsets('select_helpers');

        return $result;
    }


    /**
     * Get Org Unit
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function getOrgUnit(Request $request)
    {
        if (isset($request->id)) {
            $result = \DB::table('OrgUnit')
                ->select(['Id', 'Name'])
                ->where('Id', $request->id)
                ->orderBy('Id', 'asc')
                ->first();

            self::countRequsets('select_helpers');

            return response()->json($result);
        } else {
            $result = \DB::table('OrgUnit')
                ->select(['Id', 'Name'])
                ->where('RecordState', '<>', 4)
                ->where(function ($query) {
                    $query
                        ->where(function ($query) {
                            $query
                                ->where('ParentId', '=', 7)
                                ->where('Id', '<', 19);
                        })
                        ->orWhere('Id', '=', 7);
                })
                ->orderBy('Id', 'asc')
                ->get();

            self::countRequsets('select_helpers');

            return $result;
        }
    }

    /**
     * Get Org Employee
     *
     * @param  $user_id
     * @return \Illuminate\Http\Response
     */
    public static function getOrgEmployee($user_id = null)
    {
        $result = \DB::table('OrgEmployee')
            ->select(
                [
                    'OrgEmployee.Id', 'p.UserId', 'p.UserName'
                    //,'p.*'
                    //,'OrgEmployee.*'
                ]
                //     [
                ,
                'p.FullName',
                'p.Name',
                'p.MiddleName'
                // ]
            )
            ->where('OrgEmployee.RecordState', '<>', 4)
            ->where('p.UserId', '=', '7ed2c678-c145-4cc8-a74b-b6ec405e7a03') //$user_id)
            //->join('PersonId', '=', $user_id)
            ->join('public.Persons AS p', 'OrgEmployee.PersonId', '=', 'p.Id')
            ->orderBy('p.FullName', 'asc')
            ->orderBy('p.Name', 'asc')
            //->orderBy('p.MiddleName','asc')
            ->get();

        self::countRequsets('select_helpers');
        return $result;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function getProtocol(Request $request)
    {

        $result = Document::where('Discriminator', '=',  'MtaProtocolCard')
            ->where('RecordState', '<>',  4)
            ->where('RegNumber', 'ILIKE',  $request->protocolSearch . '%')
            ->from('public.Documents AS dp')
            ->addSelect('dp.Id AS dp_id')
            ->addSelect('dp.RegNumber AS dp_reg_number')
            ->addSelect(\DB::raw("(to_char((\"dp\".\"RegDate\"::date), 'DD.MM.YYYY'::text)) AS dp_reg_date"))
            ->orderBy('dp.RegDate', 'desc')
            ->get();

        self::countRequsets('select_helpers');

        return $result;
    }

    /**
     * Display the specified resource.
     *
     * @param  App\Models\Person  $person
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
    }

    /**
     * Insert the specified resource in storage.
     *
     * @param  App\Models\Document  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function createRequest(Request $request, $id)
    {
        $this->validate(
            $request,
            [
                'CreatedBy' => 'required',
                'CreatedBy_Id' => 'required|uuid',
                'CitizenId' => 'required',
                'ApplicantTypeId' => 'required',
                'ConsiderationDate' => 'required',
                'ReasonOfSubmissionId' => 'required',
                'Signed' => 'required',
                'StatusId' => 'required',
                'SubmissionTypeId' => 'required',
                'SubmitApplicationToId' => 'required',
                'RegDate' => 'required',
                'PersonalDataPermission' => 'required',
            ],
            [],
            [
                'ApplicantTypeId' => '"Тип заявника"',
                'ConsiderationDate' => '"Дата розгляду"',
                'ReasonOfSubmissionId' => '"Причина звернення"',
                'Signed' => '"Підписаний"',
                'StatusId' => '"Статус заяви"',
                'SubmissionTypeId' => '"Тип подання"',
                'SubmitApplicationToId' => '"Подати заяву в"',
                'RegDate' => '"Дата реєстрації"',
                'PersonalDataPermission' => '"З дозволом на обробку персональних даних"',
            ]
        );

        if ($request->CreatedBy_Id) {
            $owner_id = Person::getOwner($request->CreatedBy_Id)->first()->OwnerId;
        } else {
            $owner_id = null;
        }


        $parent = Document::find($id);

        $document = new Document();
        $document->ApplicantTypeId = $request->ApplicantTypeId;
        $document->ConsiderationDate = Carbon::parse($request->ConsiderationDate);
        $document->ReasonOfSubmissionId = $request->ReasonOfSubmissionId;
        $document->ResponsibleExecutorId = Person::getEmployee($request->CreatedBy_Id)->first()->oe_id; //$request->OrgEmployee;
        $document->Signed = $request->Signed;
        $document->SignedECP = false;
        $document->TransferredFunds = false;
        $document->StatusId = $request->StatusId;
        $document->SubmissionTypeId = $request->SubmissionTypeId;
        $document->SubmitApplicationToId = $request->SubmitApplicationToId;
        $document->CreatedBy = $request->CreatedBy;
        $document->CreatedBy_Id = $request->CreatedBy_Id;
        $document->DocTypes = 8;
        $document->ModifiedBy = null;
        $document->ModifiedBy_Id = null;
        $document->ModifiedOn = null;
        $document->RecordState = 2;
        $document->PaymentIsMade = false;
        $document->Discriminator = "MtaApplicationCard";
        $document->RegDate = Carbon::now(); //$request->RegDate;
        $prefix1 = count(Document::getApplications($id)->get()) + 1;
        $prefix2 = count(Document::getApplications($id)->whereBetween('RegDate', [
            Carbon::now()->startOfYear(),
            Carbon::now()->endOfYear(),
        ])->get()) + 1;
        $document->RegNumber = $prefix1 . '/' . $parent->RegNumber . '/' . Carbon::now()->format('y') . '/' . self::getDistrictPrefix($parent->AtuDistrictId) . '/' . $prefix2;
        $document->CitizenCardId = $parent->Id;
        $document->DescriptionOfReason = $request->DescriptionOfReason;
        $document->PersonalDataPermission = $request->PersonalDataPermission;
        $document->OwnerId = $owner_id;
        $document->AverageMonthlyIncome = $request->AverageMonthlyIncome;

        $document->save();

        self::countRequsets('insert_request');
        return $document;
    }

    /**
     * Insert the specified resource in storage.
     *
     * @param  App\Models\Document  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function updateRequest(Request $request, $id)
    {

        $this->validate($request, [
            'CreatedBy' => 'required',
            'CreatedBy_Id' => 'required|uuid',
            'CitizenId' => 'required',
            'ApplicantTypeId' => 'required',
            'ConsiderationDate' => 'required',
            'ReasonOfSubmissionId' => 'required',
            'StatusId' => 'required',
            'SubmissionTypeId' => 'required',
            'SubmitApplicationToId' => 'required',
            'RegDate' => 'required',
        ]);


        $document = Document::find($id);

        $document->ApplicantTypeId = $request->ApplicantTypeId;
        $document->ConsiderationDate = Carbon::parse($request->ConsiderationDate);
        $document->ReasonOfSubmissionId = $request->ReasonOfSubmissionId;
        $document->StatusId = $request->StatusId;
        $document->SubmissionTypeId = $request->SubmissionTypeId;
        $document->SubmitApplicationToId = $request->SubmitApplicationToId;
        $document->ModifiedBy = $request->CreatedBy;
        $document->ModifiedBy_Id = $request->CreatedBy_Id;
        $document->ModifiedOn = Carbon::now();
        $document->RegDate = $request->RegDate;
        $document->DescriptionOfReason = $request->DescriptionOfReason;
        $document->AverageMonthlyIncome = $request->AverageMonthlyIncome;

        $document->save();

        self::countRequsets('update_request');
        return $document;
    }

    /**
     * Insert the Person.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function createPerson(Request $request)
    {
        $this->validate(
            $request,
            [
                'CreatedBy_Id' => 'required|uuid',
                'FullName' => 'required',
                'Name' => 'required',
                'Birthday' => 'required',
                'Gender' => 'required|in:Male,Female',
                'AtuDistrictId' => 'required',
                'Street' => 'required',
                'BuildingNum' => 'required',
                'NoIPN' => 'nullable|bool',
                'DocPrefix' => 'nullable|size:2',
                'IPN' => 'required_if:NoIPN,0,false|nullable|sometimes|unique:Persons|digits:10',
                'CitizenCardStatus' => 'required',
                'CitizenCardType' => 'required',
                'MtaApplicantCategoryList' => 'required',
                'BuildingTypeId' => 'required',
                'DocumentIssueDate' => 'required',
                'IdentityDocumentType' => 'required',
                'PersonalDocumentNumber' => "unique:Persons,PersonalDocumentNumber,NULL,NULL,DocPrefix,{$request->DocPrefix}",
            ],
            [
                'IPN.required_if' => 'Якщо поле "РНОКПП відсутній" не вибране, необхідно ввести 10 цифр РНОКПП. ',
                'IPN.unique' => 'Такий РНОКПП вже існує в системі! ',
                'IPN.digits' => 'Поле РНОКПП має містити 10 цифр! ',
                'PersonalDocumentNumber.unique' => 'Вказаний документ вже існує в системі!',
            ],
            [
                'FullName' => '"Прізвище"',
                'Name' => '"Ім`я"',
                'Birthday' => '"День Народження"',
                'Gender' => '"Стать"',
                'AtuDistrictId' => '"Район"',
                'Street' => '"Вулиця"',
                'BuildingNum' => '"Номер будівлі"',
                'DocPrefix' => '"Серія документу"',
                'NoIPN' => '"РНОКПП відсутній"',
                'BuildingTypeId' => '"Тип приміщення"',
                'CitizenCardStatus' => '"Статус картки громадянина"',
                'CitizenCardType' => '"Тип картки громадянина"',
                'DocumentIssueDate' => '"Дата видачі"',
                'MtaApplicantCategoryList' => '"Категорії"',
                'IdentityDocumentType' => '"Документ що посвідчує особу"',
            ]
        );



        if ($request->CreatedBy_Id) {
            $owner_id = Person::getOwner($request->CreatedBy_Id)->first()->OwnerId;
        } else {
            $owner_id = null;
        }

        $person = new Person();

        $person->Birthday = Carbon::parse($request->Birthday);
        $person->Caption = null;
        $person->IdentityDocumentType = $request->IdentityDocumentType;
        $person->DocPrefix = strtoupper($request->DocPrefix);
        $person->PersonalDocumentNumber = $request->PersonalDocumentNumber;
        $person->PersonalDocumentAuthority = $request->PersonalDocumentAuthority;
        $person->DocumentIssueDate = Carbon::parse($request->DocumentIssueDate);
        $person->ExpirationDate = $request->ExpirationDate ? Carbon::parse($request->ExpirationDate) : null;
        $person->Phone = $request->Phone;
        $person->Email = $request->Email;
        $person->Name = $request->Name;
        $person->MiddleName = $request->MiddleName;
        $person->FullName = $request->FullName;
        $person->UserId = null;
        $person->UserName = null;
        $person->Gender = $request->Gender;
        $person->NoIPN = $request->NoIPN;
        if (!$request->NoIPN) {
            $person->IPN = $request->IPN;
        } else {
            $person->IPN = null;
        }
        $person->Location = null;
        $person->CreatedBy = $request->CreatedBy;
        $person->CreatedBy_Id = $request->CreatedBy_Id;
        $person->ModifiedBy = $request->ModifiedBy;
        $person->ModifiedBy_Id = $request->ModifiedBy_Id;
        $person->ModifiedOn = null;
        $person->RecordState = 2;
        $person->OwnerId = $owner_id;
        $person->save();
        if ($person->Id) {

            $document = new Document();
            $document->CitizenId = $person->Id;
            $document->DocTypes = 0;
            $document->CitizenCardStatus = $request->CitizenCardStatus;
            $document->CitizenCardType = $request->CitizenCardType;

            $document->PostIndex = $request->PostIndex;
            $document->AtuDistrictId = $request->AtuDistrictId;
            $document->Street = $request->Street;
            $document->BuildingNum = $request->BuildingNum;
            $document->BuildingTypeId = $request->BuildingTypeId;
            $document->AppartmentNum = $request->AppartmentNum;
            $document->Description = $request->Description;

            $document->RegDate = Carbon::now();
            $document->Discriminator = "CitizenCard";
            $document->RecordState = 0;
            $doc_original = Document::select(["RegNumber"])
                ->where("Discriminator", "CitizenCard")
                ->latest()
                ->first();
            $doc_result = $doc_original->RegNumber + 1;
            if ($doc_result > 9999999) {
                $document->RegNumber =  "" . $doc_result;
            } elseif ($doc_result > 999999) {
                $document->RegNumber =  "0" . $doc_result;
            } else {
                $document->RegNumber =  "00" . $doc_result;
            }

            $document->IncomeSize = 0;
            $document->ParentId = null;
            $document->OwnerId = $owner_id;
            $document->CreatedBy = $request->CreatedBy;
            $document->CreatedBy_Id = $request->CreatedBy_Id;
            $document->ModifiedBy = null;
            $document->ModifiedBy_Id = null;
            $document->ModifiedOn = null;

            $document->save();
            if (is_array($request->MtaApplicantCategoryList) && isset($request->MtaApplicantCategoryList[0]) && isset($document->Id)) {
                if ($request->MtaApplicantCategoryList) {
                    foreach ($request->MtaApplicantCategoryList as $value) {
                        \DB::table('MtaApplicantCategoryListItem')
                            ->insert([
                                "CitizenCardId" =>  $document->Id,
                                "CreatedBy" => $request->CreatedBy,
                                "CreatedBy_Id" => $request->CreatedBy_Id,
                                "CreatedOn" => Carbon::now(),
                                "MtaApplicantCategoryId" => $value,
                                "RecordState" => 0,
                            ]);
                    }
                }
            }
            if (isset($document->Id)) {
                self::countRequsets('insert_person');
                return [$person, $document];
            }
            return "error";
        }

        self::countRequsets('insert_person');

        return [$person, $document];
    }



    /**
     * Update the Person.
     *
     * @param  App\Models\Person  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updatePerson(Request $request, $id)
    {
        if (!$id) {
            return response()->json(array(
                'code'      =>  431,
                'message'   =>  'Id not found - <' . $id  . '>'
            ), 404);
        }

        $this->validate(
            $request,
            [
                'CreatedBy_Id' => 'required|uuid',
                'FullName' => 'required',
                'Name' => 'required',
                'Birthday' => 'required',
                'Gender' => 'required',
                'NoIPN' => 'nullable|bool',
                'DocPrefix' => 'nullable|size:2',
                'IPN' => 'required_if:NoIPN,0,false|nullable|sometimes|unique:Persons,IPN,' . $id . ',Id|digits:10',
                'DocumentIssueDate' => 'required',
                'PersonalDocumentAuthority' => 'required',
                'IdentityDocumentType' => 'required',
                'PersonalDocumentNumber' => "unique:Persons,PersonalDocumentNumber,{$id},Id,DocPrefix,{$request->DocPrefix}",
            ],
            [
                'IPN.required_if' => 'Якщо поле "РНОКПП відсутній" не вибране, необхідно ввести 10 цифр РНОКПП. ',
                'IPN.unique' => 'Такий РНОКПП вже існує в системі! ',
                'IPN.digits' => 'Поле РНОКПП має містити 10 цифр! ',
                'PersonalDocumentNumber.unique' => 'Вказаний документ вже існує в системі!',
            ],
            [
                'FullName' => '"Прізвище"',
                'Name' => '"Ім`я"',
                'Birthday' => '"День Народження"',
                'Gender' => '"Стать"',
                'DocPrefix' => '"Серія документу"',
                'NoIPN' => '"РНОКПП відсутній"',
                'DocumentIssueDate' => '"Дата видачі"',
                'PersonalDocumentAuthority' => '"Ким виданий"',
                'IdentityDocumentType' => '"Документ що посвідчує особу"',
            ]
        );



        if ($request->CreatedBy_Id) {
            $owner_id = Person::getOwner($request->CreatedBy_Id)->first()->OwnerId;
        } else {
            $owner_id = null;
        }

        $person = Person::find($id);

        $person->Birthday = Carbon::parse($request->Birthday);
        $person->IdentityDocumentType = $request->IdentityDocumentType;
        $person->DocPrefix = strtoupper($request->DocPrefix);
        $person->PersonalDocumentNumber = $request->PersonalDocumentNumber;
        $person->PersonalDocumentAuthority = $request->PersonalDocumentAuthority;
        $person->DocumentIssueDate = Carbon::parse($request->DocumentIssueDate);
        $person->ExpirationDate = $request->ExpirationDate ? Carbon::parse($request->ExpirationDate) : null;
        $person->Phone = $request->Phone;
        $person->Email = $request->Email;
        $person->Name = $request->Name;
        $person->MiddleName = $request->MiddleName;
        $person->FullName = $request->FullName;
        $person->Gender = $request->Gender;
        $person->NoIPN = $request->NoIPN;
        if (!$request->NoIPN) {
            $person->IPN = $request->IPN;
        } else {
            $person->IPN = null;
        }
        $person->ModifiedBy = $request->ModifiedBy;
        $person->ModifiedBy_Id = $request->ModifiedBy_Id;
        $person->ModifiedOn = Carbon::now();
        $person->save();
        return $person;
        if ($person->Id) {
            $document = Document::find();
            $document->CitizenId = $person->Id;
            $document->DocTypes = 0;
            $document->CitizenCardStatus = $request->CitizenCardStatus;
            $document->CitizenCardType = $request->CitizenCardType;
            $document->PostIndex = $request->PostIndex;
            $document->AtuDistrictId = $request->AtuDistrictId;
            $document->Street = $request->Street;
            $document->BuildingNum = $request->BuildingNum;
            $document->BuildingTypeId = $request->BuildingTypeId;
            $document->AppartmentNum = $request->AppartmentNum;
            $document->Description = $request->Description;
            $document->RegDate = Carbon::now();
            $document->Discriminator = "CitizenCard";
            $document->RecordState = 0;
            $doc_original = Document::select(["RegNumber"])
                ->where("Discriminator", "CitizenCard")
                ->latest()
                ->first();
            $doc_result = $doc_original->RegNumber + 1;
            if ($doc_result > 9999999) {
                $document->RegNumber =  "" . $doc_result;
            } elseif ($doc_result > 999999) {
                $document->RegNumber =  "0" . $doc_result;
            } else {
                $document->RegNumber =  "00" . $doc_result;
            }
            $document->IncomeSize = 0;
            $document->ParentId = null;
            $document->OwnerId = $owner_id;
            $document->CreatedBy = $request->CreatedBy;
            $document->CreatedBy_Id = $request->CreatedBy_Id;
            $document->ModifiedBy = null;
            $document->ModifiedBy_Id = null;
            $document->ModifiedOn = null;

            $document->save();
            if (is_array($request->MtaApplicantCategoryList) && isset($request->MtaApplicantCategoryList[0]) && isset($document->Id)) {
                if ($request->MtaApplicantCategoryList) {
                    foreach ($request->MtaApplicantCategoryList as $value) {
                        \DB::table('MtaApplicantCategoryListItem')
                            ->insert([
                                "CitizenCardId" =>  $document->Id,
                                "CreatedBy" => $request->CreatedBy,
                                "CreatedBy_Id" => $request->CreatedBy_Id,
                                "CreatedOn" => Carbon::now(),
                                "MtaApplicantCategoryId" => $value,
                                "RecordState" => 0,
                            ]);
                    }
                }
            }
            if (isset($document->Id)) {
                self::countRequsets('insert_person');
                return [$person, $document];
            }
            return "error";
        }

        self::countRequsets('insert_person');

        return [$person, $document];
    }

    /**
     * Show the Person.
     *
     * @param  App\Models\Person  $id
     * @return \Illuminate\Http\Response
     */
    public function showPerson($id)
    {
        $person = Person::select([
                'Id',
                \DB::raw("(to_char((\"Birthday\"::date), 'YYYY-MM-DD'::text)) \"Birthday\""),
                'IdentityDocumentType',
                'DocPrefix',
                'PersonalDocumentNumber',
                'PersonalDocumentAuthority',
                \DB::raw("(to_char((\"DocumentIssueDate\"::date), 'YYYY-MM-DD'::text)) \"DocumentIssueDate\""),
                \DB::raw("(to_char((\"ExpirationDate\"::date), 'YYYY-MM-DD'::text)) \"ExpirationDate\""),
                'Phone',
                'Email',
                'Name',
                'MiddleName',
                'FullName',
                'Gender',
                'NoIPN',
                'IPN',
                'CreatedBy',
                'CreatedBy_Id',
                'CreatedOn',
                'ModifiedBy',
                'ModifiedBy_Id',
                'ModifiedOn',
                'RecordState',
                'OwnerId',
            ])
            ->find($id);

        $document = DocumentPerson::
            addSelect('Id as d_id')
            ->addSelect('CitizenCardStatus')
            ->addSelect('CitizenCardType')
            ->addSelect('PostIndex')
            ->addSelect('AtuDistrictId')
            ->addSelect('Street')
            ->addSelect('BuildingTypeId')
            ->addSelect('BuildingNum')
            ->addSelect('AppartmentNum')
            ->addSelect('RegNumber')
            ->addSelect('Description')
            ->addSelect('RegDate')
            ->addSelect('Discriminator')
            ->addSelect('OwnerId')
            ->addSelect('CreatedBy')
            ->addSelect('CreatedBy_Id')
            ->addSelect('CreatedOn')
            ->addSelect('ModifiedBy')
            ->addSelect('ModifiedBy_Id')
            ->addSelect('ModifiedOn')
            ->addSelect(\DB::raw("(to_char((\"RegDate\"::date), 'DD.MM.YYYY'::text))"))
            ->find($person->Id);

        return array_merge($person->toArray(), $document->toArray());

    }

    /**
     * Insert the desision card
     *
     * @param  App\Models\Document  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function createDecision(Request $request, $id)
    {

        $this->validate(
            $request,
            [
                'des_type_id' => 'required',
                'dp_id' => 'required',
                'ds_amount' => 'required',
                'ds_decision_text' => 'required',
                'sub' => 'required|uuid',
                'name' => 'required',
            ],
            [
            ],
            [
                'des_type_id' => '"Тип рішення"',
                'ds_amount' => '"Розмір допомоги, грн"',
                'dp_id' => '"Протокол документу"',
                'ds_decision_text' => '"Текст рішення"',
            ]
        );

        $camelDocument = Document::find($id);
        $document = new Document();
        $document->RegNumber =  "РІШ_" . $camelDocument->RegNumber; //$request->ds_reg_number;
        $document->Discriminator = 'MtaDecisionCard';
        $document->RecordState = 2;
        $document->CreatedBy = $request->name;
        $document->CreatedBy_Id = $request->sub;
        $document->MtaDecisionCard_CitizenCardId = $camelDocument->CitizenCardId;
        $document->ApplicationId = $id;
        $document->ProtocolCardId = $request->dp_id;
        $document->DecisionTypeId = $request->des_type_id;
        $document->RegDate = Carbon::now();
        $document->ModifiedOn = null;
        $document->Amount = $request->ds_amount ? $request->ds_amount : 0;
        if ($request->des_type_id !== 1) {
            $document->DecisionText = $request->ds_decision_text;
        } else {
            $document->DecisionText = "Рішення прийнято, призначена допомога в розмірі: " . $request->ds_amount . " шт.";
        }
        $document->Notices = $request->ds_notices;
        $document->DocTypes = 0;
        $document->OwnerId = null; //$owner_id;
        $document->save();

        $camelDocument->StatusId = 6;
        $camelDocument->save();

        self::countRequsets('insert_decision');


        return $camelDocument;
    }

    /**
     * Update desision card.
     *
     * @param  App\Models\Document  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateDecision(Request $request, $id)
    {
        $this->validate(
            $request,
            [
                'des_type_id' => 'required',
                'dp_id' => 'required',
                'ds_amount' => 'required',
                'ds_decision_text' => 'required',
                'sub' => 'required|uuid',
                'name' => 'required',
            ],
            [
            ],
            [
                'des_type_id' => '"Тип рішення"',
                'ds_amount' => '"Розмір допомоги, грн"',
                'dp_id' => '"Протокол документу"',
                'ds_decision_text' => '"Текст рішення"',
            ]
        );

        $document = Document::find($id);
        $document->RegDate = Carbon::parse($request->reg_date) ?? $document->RegDate;
        $document->ModifiedOn = Carbon::now();
        $document->ModifiedBy = $request->name;
        $document->ModifiedBy_Id = $request->sub;
        $document->ProtocolCardId = $request->dp_id; // ? $request->dp_id : null ;
        $document->DecisionTypeId = $request->des_type_id; // ? $request->des_type_id : null ;
        $document->DecisionText = $request->ds_decision_text;
        $document->Amount = $request->ds_amount;
        $document->Notices = $request->ds_notices;

        $document->save();

        self::countRequsets('update_decision');

        return $document;
    }

}

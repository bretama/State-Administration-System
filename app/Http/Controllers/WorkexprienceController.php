<?php

namespace App\Http\Controllers;
use Kamaln7\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;


use App\Http\Requests;
use  App\CareerInformation;
use App\ApprovedHitsuy;
use App\DateConvert;
use DB;

use Illuminate\Http\Request;

class WorkexprienceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()    //if not authenticated redirect to login
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $data = CareerInformation::all ();
        return view ('membership.careerinfo')->withData ( $data );
    }
    public function exprienceIndex()
    {
        $value = Auth::user()->area;
        if(array_search(Auth::user()->usertype, ['woreda','woredaadmin']) !== false){
            $value = '__'.$value;
        }
        $data = CareerInformation::whereIn('hitsuyID', function($query) use($value){
            $query->select('hitsuyID')
            ->from(with(new ApprovedHitsuy)->getTable())
            ->where('zoneworedaCode', 'LIKE', $value.'%');
        })->get();
        // $data = CareerInformation::all ();
        $zobadatas = DB::table("zobatats")->pluck("zoneName","zoneCode");
        return view ('membership.careerinfo',compact('data','zobadatas'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'hitsuyID' => 'required',
            'career' => 'required',
            'exprienceType' => 'required|in:ሞያዊ,ፖለቲካዊ',
            'position' => 'required',
            'startDate' => 'required|ethiopian_date',
            'institute' => 'required',
            'address' => 'required'
            ],
            [
            'ethiopian_date' => 'ዝጀመረሉ/ትሉ መዓልቲ/ወርሒ/ዓመተምህረት ክኸውን ኣለዎ',
            'required' => ':attribute ኣይተመልአን',
            'in' => ':attribute ካብቶም ዘለዉ መማረፅታት ክኸውን ኣለዎ',
            ]);
        $fieldNames = [
        "hitsuyID" => "መለለዩ ሕፁይ",
        "career" => "ስራሕ መደብ",
        "exprienceType" => "ዓይነት",
        "position" => "ሓላፍነት",
        "startDate" => "ዝጀመረሉ/ትሉ",
        "institute" => "ትካል",
        "address" => "ኣድራሻ"
        ];
        $validator->setAttributeNames($fieldNames);
        if($validator->fails()){
            return [false, $validator->errors()->all()];
        }
        if(!ApprovedHitsuy::where('hitsuyID', $request->hitsuyID)->count()){
            $validator->errors()->add('duplicate', 'ኣባል ኣብ መዝገብ የለን');
            return [false, $validator->errors()->all()];
        }
        if(CareerInformation::where('hitsuyID', $request->hitsuyID)->where('exprienceType', $request->exprienceType)->where('position', $request->position)->where('career', $request->career)->where('institute', $request->institute)->count()){
            $validator->errors()->add('duplicate', 'መረዳእታ ልምዲ ስራሕ ኣብ መዝገብ ኣሎ');
            return [false,  $validator->errors()->all()];
        }
        $exp = new CareerInformation;
        $exp->hitsuyID = $request->hitsuyID;
        $exp->exprienceType = $request->exprienceType;
        $exp->career = $request->career;
        $exp->position = $request->position;
        $exp->institute = $request->institute;
        $exp->address = $request->address;
        $exp->startDate = DateConvert::correctDate($request->startDate);
        $exp->save();  
        return [true, "ስራሕ ልምዲ ብትኽክል ተመዝጊቡ ኣሎ"];

    }
    public function editexprience(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'id' => 'required',
            'hitsuyID' => 'required',
            'career' => 'required',
            'exprienceType' => 'required|in:ሞያዊ,ፖለቲካዊ',
            'position' => 'required',
            'startDate' => 'required|ethiopian_date',
            'institute' => 'required',
            'address' => 'required'
            ],
            [
            'ethiopian_date' => 'ዝጀመረሉ/ትሉ መዓልቲ/ወርሒ/ዓመተምህረት ክኸውን ኣለዎ',
            'required' => ':attribute ኣይተመልአን',
            'in' => ':attribute ካብቶም ዘለዉ መማረፅታት ክኸውን ኣለዎ',
            ]);
        $fieldNames = [
        "id" => "መለለዪ መረዳእታ ትምህርቲ",
        "hitsuyID" => "መለለዩ ሕፁይ",
        "career" => "ስራሕ መደብ",
        "exprienceType" => "ዓይነት",
        "position" => "ሓላፍነት",
        "startDate" => "ዝጀመረሉ/ትሉ",
        "institute" => "ትካል",
        "address" => "ኣድራሻ"
        ];
        $validator->setAttributeNames($fieldNames);
        if($validator->fails()){
            return [false, 'error', $validator->errors()->all()];
        }
        if(!ApprovedHitsuy::where('hitsuyID', $request->hitsuyID)->count()){
            $validator->errors()->add('duplicate', 'ኣባል ኣብ መዝገብ የለን');
            return [false, 'error', $validator->errors()->all()];
        }
        if(CareerInformation::where('hitsuyID', $request->hitsuyID)->where('exprienceType', $request->exprienceType)->where('position', $request->position)->where('career', $request->career)->where('institute', $request->institute)->count()){
            $validator->errors()->add('duplicate', 'መረዳእታ ልምዲ ስራሕ ኣብ መዝገብ ኣሎ');
            return [false,  'error', $validator->errors()->all()];
        }
       $expr = CareerInformation::find ( $request->id );             
        $expr->exprienceType = $request->exprienceType;
        $expr->career = $request->career;
        $expr->position = $request->position;
        $expr->institute = $request->institute;
        $expr->address = $request->address;
        $expr->startDate = DateConvert::correctDate($request->startDate);
        $expr->save();
        
        return [true, 'info', "ስራሕ ልምዲ ብትኽክል ተስተኻኺሉ ኣሎ"];
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }
    public function deletexprience(Request $request)
    {
        $expres =CareerInformation::find($request->id)->delete();
        return [true, "ስራሕ ልምዲ ብትኽክል ተሰሪዙ ኣሎ"];
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

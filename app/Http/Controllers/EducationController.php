<?php

namespace App\Http\Controllers;
use Kamaln7\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;


use App\Http\Requests;
use  App\EducationInformation;
use App\ApprovedHitsuy;
use App\Hitsuy;

use DB;
use Illuminate\Http\Request;

class EducationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $data = EducationInformation::all ();
        return view ('membership.educationinfo')->withData ( $data );
    }
    public function educationIndex()
    {
        $value = Auth::user()->area;
        if(array_search(Auth::user()->usertype, ['woreda','woredaadmin']) !== false){
            $value = '__'.$value;
        }
        // $data = DB::table('education_informations')->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'education_informations.hitsuyID')->where('approved_hitsuys.zoneworedaCode', 'LIKE', $value.'%')->get();
        $data = EducationInformation::whereIn('hitsuyID', function($query) use($value){
            $query->select('hitsuyID')
            ->from(with(new ApprovedHitsuy)->getTable())
            ->where('zoneworedaCode', 'LIKE', $value.'%');
        })->get();
        // $data = EducationInformation::where('hitsuyID', 'LIKE', $value.'%')->get();
        $zobadatas = DB::table("zobatats")->pluck("zoneName","zoneCode");
        return view ('membership.educationinfo',compact('data','zobadatas'));
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
            'educationType' => 'required',
            'educationLevel' => 'required|in:1,2,3,4,5,6,7,8,9,10,11,12,??????????????????,????????????,?????????,???????????????,???.??????.???',
            'institute' => 'required',
            'graduationDate' => 'required|digits:4|integer|min:1950|max:'.(date('Y')-7),
            ],
            [
            'required' => ':attribute ?????????????????????',
            'digits' => '?????????????????? ?????? ????????? ???????????? ???????????? ?????????',
            'min' => ':attribute ????????? 1950 ???????????? ?????????',
            'max' => ':attribute ????????? ????????? '.(date('Y')-7).' ???????????? ?????????',
            'integer' => '?????????????????? ?????? ????????? ???????????? ???????????? ?????????',
            'in' => ':attribute ???????????? ????????? ?????????????????? ???????????? ?????????',
            ]);
        $fieldNames = [
        "hitsuyID" => "???????????? ?????????",
        "educationType" => "???????????? ???????????????",
        "educationLevel" => "????????? ???????????????",
        "institute" => "????????? ?????????",
        "graduationDate" => "?????????????????? ?????????",];
        $validator->setAttributeNames($fieldNames);
        if($validator->fails()){
            return [false, $validator->errors()->all()];
        }
        if(!ApprovedHitsuy::where('hitsuyID', $request->hitsuyID)->count()){
            $validator->errors()->add('duplicate', '????????? ?????? ???????????? ?????????');
            return [false,  $validator->errors()->all()];
        }
        if(EducationInformation::where('hitsuyID', $request->hitsuyID)->where('educationType', $request->educationType)->where('educationLevel', $request->educationLevel)->count()){
            $validator->errors()->add('duplicate', '??????????????? ??????????????? ?????? ???????????? ??????');
            return [false,  $validator->errors()->all()];
        }
        $edu = new EducationInformation;
        $edu->hitsuyID = $request->hitsuyID;
        $edu->educationType = $request->educationType;
        $edu->educationLevel = $request->educationLevel;
        $edu->institute = $request->institute;
        $edu->graduationDate = $request->graduationDate;
        $edu->save();  
        $eduArray = ['1','2','3','4','5','6','7','8','9','10','11','12','??????????????????','????????????','?????????','???????????????','???.??????.???'];
        $hitsuy = Hitsuy::find($request->hitsuyID);
        if(array_search($request->educationLevel, $eduArray) > array_search($hitsuy->educationlevel, $eduArray)){
            $hitsuy->educationlevel = $request->educationLevel;
            $hitsuy->save();
        }
        return [true, "?????? ??????????????? ??????????????? ??????????????? ??????????????? ??????"];

    }
    public function editEducation(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'id' => 'required',
            'hitsuyID' => 'required',
            'educationType' => 'required',
            'educationLevel' => 'required|in:1,2,3,4,5,6,7,8,9,10,11,12,??????????????????,????????????,?????????,???????????????,???.??????.???',
            'institute' => 'required',
            'graduationDate' => 'required|digits:4|integer|min:1950|max:'.(date('Y')-7),
            ],
            [
            'required' => ':attribute ?????????????????????',
            'digits' => '?????????????????? ?????? ????????? ???????????? ???????????? ?????????',
            'min' => ':attribute ????????? 1950 ???????????? ?????????',
            'max' => ':attribute ????????? '.(date('Y')-7).' ???????????? ?????????',
            'integer' => '?????????????????? ?????? ????????? ???????????? ???????????? ?????????',
            'in' => ':attribute ???????????? ????????? ?????????????????? ???????????? ?????????',
            ]);
        $fieldNames = [
        "id" => "???????????? ??????????????? ???????????????",
        "hitsuyID" => "???????????? ?????????",
        "educationType" => "???????????? ???????????????",
        "educationLevel" => "????????? ???????????????",
        "institute" => "????????? ?????????",
        "graduationDate" => "?????????????????? ?????????",];
        $validator->setAttributeNames($fieldNames);
        if($validator->fails()){
            return [false, 'error', $validator->errors()->all()];
        }
        if(!ApprovedHitsuy::where('hitsuyID', $request->hitsuyID)->count()){
            $validator->errors()->add('duplicate', '????????? ?????? ???????????? ?????????');
            return [false,  'error', $validator->errors()->all()];
        }
        if(EducationInformation::where('hitsuyID', $request->hitsuyID)->where('educationType', $request->educationType)->where('educationLevel', $request->educationLevel)->count()){
            $validator->errors()->add('duplicate', '??????????????? ??????????????? ?????? ???????????? ??????');
            return [false, 'error', $validator->errors()->all()];
        }

       $educ = EducationInformation::find( $request->id );
        $educ->educationType = $request->educationType;
        $educ->educationLevel = $request->educationLevel;
        $educ->institute = $request->institute;
        $educ->graduationDate = $request->graduationDate;
        $educ->save();
        return [true, 'info', "?????? ??????????????? ??????????????? ??????????????? ?????????????????? ??????"];
    }
    public function deleteducation(Request $request)
    {
        $expres =EducationInformation::find($request->id)->delete();
        return [true, "???????????? ?????? ??????????????? ??????????????? ??????????????? ???????????? ??????"];
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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kamaln7\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests;
use App\Individualplan;
use  App\ApprovedHitsuy;
use DB;

class IndividualplanController extends Controller
{
    public function __construct()    //if not authenticated, redirect to login
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $data = ApprovedHitsuy::where('approved_status','1')->get();
        $individualdata = DB::table("individualplans")->pluck("year");
        $tempdata = ApprovedHitsuy::all()->pluck("hitsuyID");
        $collectionyear = collect([]);
        foreach ($tempdata as $value) {
            $lastYear = Individualplan::where('hitsuyID',$value)->orderBy('year', 'desc')->pluck('year')->first();
            $collectionyear->prepend($lastYear, $value);
        }
        return view ('planing.individualplan',compact('data','collectionyear'));
    }
    public function store(Request $request)
    {
        $messages = [
            'required' => ':attribute ኣይተመልአን',
            'integer' => ':attribute ቑፅሪ ክኸውን ኣለዎ',
            'in' => ':attribute ካብቶም ዘለዉ መማረፅታት ክኸውን ኣለዎ',
            'min' => ':attribute ድሕሪ 1950 ክኸውን ኣለዎ',
            'max' => ':attribute ክሳብ '.(date('Y')-7).' ክኸውን ኣለዎ',
        ];
        //
        $validator = \Validator::make($request->all(),[
            'memeberID' => 'required',
            'year' => 'required|integer|digits:4|min:1950|max:'.(date('Y')-7),
        ],$messages);
        $fieldNames = [
            'memeberID' => 'መለለዩ ቑፅሪ ኣባላት',
            'year' => 'ዓመት',
        ];
        $validator->setAttributeNames($fieldNames);
        if($validator->fails()){
            return [false, 'error', $validator->errors()->all()];
        }
        //
        $memeberIDs=json_decode($request->memeberID);
        DB::beginTransaction();
        foreach($memeberIDs as $hID) {   
            if(!ApprovedHitsuy::where('hitsuyID', $hID)->count()){
                $validator->errors()->add('duplicate', 'ትልሚ ኣይተመዝገበን መለለዪ ኣባል '.$hID.' ኣብ መዝገብ ኣይተረኸበን');
                DB::rollback();
                return [false,  'error', $validator->errors()->all()];
            }
            if(Individualplan::where('hitsuyID', $hID)->where('year', $request->year)->count()) {
                $validator->errors()->add('duplicate', 'ትልሚ ኣይተመዝገበን [ትልሚ ኣብ መዝገብ ኣሎ]');
                DB::rollback();
                return [false,  'error', $validator->errors()->all()];   
            }
            $data = new Individualplan;
            $data->hitsuyID = $hID; 
            $data->year = $request->year;
            $data->save();    
        }
        DB::commit();
        return [true, "info", "ዓመታዊ ትልሚ ውልቀ ሰብ ብትክክል ተመዝጊቡ ኣሎ"];
    }
}

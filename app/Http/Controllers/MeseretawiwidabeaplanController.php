<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kamaln7\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests;
use App\meseretawiWdabe;
use App\Meseretawiwidabeaplan;
use DB;

class MeseretawiwidabeaplanController extends Controller
{
    //
    public function __construct()    //if not authenticated, redirect to login
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $data = meseretawiWdabe::get();
        $individualdata = DB::table("meseretawiwidabeaplans")->pluck("planyear");
        $tempdata = meseretawiWdabe::all()->pluck("widabeCode");
        $collectionyear = collect([]);
        foreach ($tempdata as $value) {
            $lastYear = Meseretawiwidabeaplan::where('widabecode',$value)->orderBy('planyear', 'desc')->orderBy('quarter', 'desc')->pluck('planyear')->first();
            $quarter = Meseretawiwidabeaplan::where('widabecode',$value)->orderBy('planyear', 'desc')->orderBy('quarter', 'desc')->pluck('quarter')->first();
            if($quarter)
                $collectionyear->prepend($lastYear. '(' . $quarter . ')', $value);
            else
                $collectionyear->prepend($lastYear, $value);
        }
        return view ('planing.meseretawiwidabeplan',compact('data','collectionyear'));
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
            'quarter' => 'required|in:3 ወርሒ,6 ወርሒ,9 ወርሒ,ዓመት'
        ],$messages);
        $fieldNames = [
            'memeberID' => 'መለለዩ ቑፅሪ ኣባላት',
            'year' => 'ዓመት',
            'quarter' => 'ርብዒ ዓመት'
        ];
        $validator->setAttributeNames($fieldNames);
        if($validator->fails()){
            return [false, 'error', $validator->errors()->all()];
        }
        //
        $memeberIDs=json_decode($request->memeberID);
        DB::beginTransaction();
        foreach($memeberIDs as $hID) {   
            if(!meseretawiWdabe::where('widabeCode', $hID)->count()){
                $validator->errors()->add('duplicate', 'ትልሚ ኣይተመዝገበን መለለዪ መሰረታዊ ውዳበ '.$hID.' ኣብ መዝገብ ኣይተረኸበን');
                DB::rollback();
                return [false,  'error', $validator->errors()->all()];
            }
            if(Meseretawiwidabeaplan::where('widabecode', $hID)->where('planyear', $request->year)->where('quarter', $request->quarter)->count()){
                $validator->errors()->add('duplicate', 'ትልሚ ኣይተመዝገበን [ትልሚ ኣብ መዝገብ ኣሎ]');
                DB::rollback();
                return [false,  'error', $validator->errors()->all()];   
            }
            $data = new Meseretawiwidabeaplan;
            $data->widabecode = $hID; 
            $data->planyear = $request->year;
            $data->quarter = $request->quarter;
            $data->save();    
        }
        DB::commit();
        return [true, "info", "ትልሚ መሰረታዊ ውዳበ ብትክክል ተመዝጊቡ ኣሎ"];
    }
    // 
}

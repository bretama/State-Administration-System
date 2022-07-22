<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kamaln7\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests;
use App\meseretawiWdabe;
use App\Meseretawiwidabeaplan;
use App\Wahio;
use App\Wahioplan;
use DB;

class WahioplanController extends Controller
{
    public function __construct()    //if not authenticated, redirect to login
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $data = Wahio::get();
        $individualdata = DB::table("wahioplans")->pluck("planyear");
        $tempdata = Wahio::all()->pluck("id");
        $collectionyear = collect([]);
        foreach ($tempdata as $value) {
            $lastYear = Wahioplan::where('wahioid',$value)->orderBy('planyear', 'desc')->orderBy('quarter', 'desc')->pluck('planyear')->first();
            $quarter = Wahioplan::where('wahioid',$value)->orderBy('planyear', 'desc')->orderBy('quarter', 'desc')->pluck('quarter')->first();
            if($quarter)
                $collectionyear->prepend($lastYear. '(' . $quarter . ')', $value);
            else
                $collectionyear->prepend($lastYear, $value);
        }
        return view ('planing.wahioplan',compact('data','collectionyear'));
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
            if(!Wahio::where('id', $hID)->count()){
                $validator->errors()->add('duplicate', 'ትልሚ ኣይተመዝገበን ዋህዮ '.$hID.' ኣብ መዝገብ ኣይተረኸበን');
                DB::rollback();
                return [false,  'error', $validator->errors()->all()];
            }
            if(Wahioplan::where('id', $hID)->where('planyear', $request->planyear)->where('quarter', $request->quarter)->count()){
                $validator->errors()->add('duplicate', 'ትልሚ ኣይተመዝገበን [ትልሚ ኣብ መዝገብ ኣሎ]');
                DB::rollback();
                return [false,  'error', $validator->errors()->all()];   
            }
            $data = new Wahioplan;
            $data->wahioid = $hID; 
            $data->planyear = $request->year;
            $data->quarter = $request->quarter;
            $data->save();    
        }
        DB::commit();
        return [true, "info", "ትልሚ ዋህዮ ብትክክል ተመዝጊቡ ኣሎ"];
    }
}

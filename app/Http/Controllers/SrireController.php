<?php

namespace App\Http\Controllers;

use Kamaln7\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests;
use  App\Srire;
use  App\Zobatat;
use  App\Woreda;
use  App\meseretawiWdabe;
use  App\Wahio;
use DB;

use Illuminate\Http\Request;

class SrireController extends Controller
{   
    public function __construct()    //if not authenticated redirect to login
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $zobadatas = DB::table("zobatats")->pluck("zoneName","zoneCode");
        $data = Woreda::all ();	   	

        $tempdata = Woreda::all()->pluck("name","woredacode");           
        $collectionyear = collect([]);       
        foreach ($tempdata as $key => $value) {
            $lastYear = Srire::where('code',$key)->where('type','ወረዳ')->orderBy('year', 'desc')->pluck('year')->first();
            $collectionyear->prepend($lastYear, $key);
        }

	   	return view ('rank.rankworeda',compact('data','zobadatas','collectionyear'));
    } 

    public function rankmwidabeindex()
    { 
        $zobadatas = DB::table("zobatats")->pluck("zoneName","zoneCode");
        $tabiadata = DB::table("tabias")->pluck("woredacode","tabiaCode");        
        $woredadata = DB::table("woredas")->pluck("zoneCode","woredacode");
        $data = meseretawiWdabe::all ();	   	

        $tempdata = meseretawiWdabe::all()->pluck("widabeName","widabeCode");           
        $collectionyear = collect([]);       
        foreach ($tempdata as $key => $value) {
            $lastYear = Srire::where('code',$key)->where('type','መሰረታዊ ውዳበ')->orderBy('year', 'desc')->pluck('year')->first();
            $collectionyear->prepend($lastYear, $key);
        }

	   	return view ('rank.rankmwidabe',compact('data','zobadatas','collectionyear','tabiadata','woredadata'));
    }

    public function rankwahioindex()
    { 
        $zobadatas = DB::table("zobatats")->pluck("zoneName","zoneCode");
        $mwidabedata = DB::table("meseretawi_wdabes")->pluck("tabiaCode","widabeCode"); 
        $tabiadata = DB::table("tabias")->pluck("woredacode","tabiaCode");        
        $woredadata = DB::table("woredas")->pluck("zoneCode","woredacode");
        $data = Wahio::all ();	   	

        $tempdata = Wahio::all()->pluck("wahioName","id");           
        $collectionyear = collect([]);       
        foreach ($tempdata as $key => $value) {
            $lastYear = Srire::where('code',$key)->where('type','ዋህዮ')->orderBy('year', 'desc')->pluck('year')->first();
            $collectionyear->prepend($lastYear, $key);
        }

	   	return view ('rank.rankwahio',compact('data','zobadatas','collectionyear','mwidabedata','tabiadata','woredadata'));
    }

    public function storerankWoreda(Request $request)
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
            'type' => 'required|in:መሰረታዊ ውዳበ,ዋህዮ,ወረዳ',
            'result' => 'required|in:ቅድሚት,ማእኸላይ,ድሕሪት'
        ],$messages);
        $fieldNames = [
            'memeberID' => 'መለለዩ ቑፅሪ ኣባላት',
            'year' => 'ዓመት',
            'result' => 'ውፅኢት ስርርዕ'
        ];
        $validator->setAttributeNames($fieldNames);
        if($validator->fails()){
            return [false, 'error', $validator->errors()->all()];
        }

        $memeberIDs=json_decode($request->memeberID);
        
        DB::beginTransaction();
        foreach($memeberIDs as $mycode) {
            if(!Woreda::where('woredacode', $mycode)->count()){
                DB::rollback();
                return [false, "error", "መለለዪ ቑፅሪ ወረዳ " . $mycode . " ኣይተረኸበን<br> ስርርዕ ወረዳታት ኣይተመዝገበን"];
            }
            if(Srire::where('code', $mycode)->where('type', 'ወረዳ')->where('year', $request->year)->count()){
                DB::rollback();
                return [false, "error", "መለለዪ ቑፅሪ ወረዳ " . $mycode . " ናይ " . $request->year . "<br> ስርርዕ ኣብ መዝገብ ኣሎ እዩ"];
            }
            $data = new Srire;                   
            $data->code = $mycode; 
            $data->type = $request->type;
            $data->result = $request->result;
            $data->year = $request->year; 
            $data->save();    
        }
        DB::commit();
        return [true, "info", "ስርርዕ ወረዳታት ብትክክል ተመዝጊቡ ኣሎ"];
    }

    public function storerankMwidabe(Request $request)
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
            'type' => 'required|in:መሰረታዊ ውዳበ,ዋህዮ,ወረዳ',
            'result' => 'required|in:ቅድሚት,ማእኸላይ,ድሕሪት'
        ],$messages);
        $fieldNames = [
            'memeberID' => 'መለለዩ ቑፅሪ ኣባላት',
            'year' => 'ዓመት',
            'result' => 'ውፅኢት ስርርዕ'
        ];
        $validator->setAttributeNames($fieldNames);
        if($validator->fails()){
            return [false, 'error', $validator->errors()->all()];
        }
        $memeberIDs=json_decode($request->memeberID);
        DB::beginTransaction();
        foreach($memeberIDs as $mycode) {   
            if(!meseretawiWdabe::where('widabeCode', $mycode)->count()){
                DB::rollback();
                return [false, "error", "መለለዪ ቑፅሪ መሰረታዊ ውዳበ " . $mycode . " ኣይተረኸበን<br> ስርርዕ መሰረታዊ ውዳበታት ኣይተመዝገበን"];
            }
            if(Srire::where('code', $mycode)->where('type', 'መሰረታዊ ውዳበ')->where('year', $request->year)->count()){
                DB::rollback();
                return [false, "error", "መለለዪ ቑፅሪ መሰረታዊ ውዳበ " . $mycode . " ናይ " . $request->year . "<br> ስርርዕ ኣብ መዝገብ ኣሎ እዩ"];
            }
            $data = new Srire;                   
            $data->code = $mycode; 
            $data->type = $request->type;
            $data->result = $request->result;
            $data->year = $request->year; 
            $data->save();    
        }
        DB::commit();
        return [true, "info", "ስርርዕ መሰረታዊ ውዳበታት ብትክክል ተመዝጊቡ ኣሎ"];
    }

    public function storerankWahio(Request $request)
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
            'type' => 'required|in:መሰረታዊ ውዳበ,ዋህዮ,ወረዳ',
            'result' => 'required|in:ቅድሚት,ማእኸላይ,ድሕሪት'
        ],$messages);
        $fieldNames = [
            'memeberID' => 'መለለዩ ቑፅሪ ኣባላት',
            'year' => 'ዓመት',
            'result' => 'ውፅኢት ስርርዕ'
        ];
        $validator->setAttributeNames($fieldNames);
        if($validator->fails()){
            return [false, 'error', $validator->errors()->all()];
        }
        $memeberIDs=json_decode($request->memeberID);
        
        DB::beginTransaction();
        foreach($memeberIDs as $mycode) {
            if(!Wahio::where('id', $mycode)->count()){
                DB::rollback();
                return [false, "error", "መለለዪ ቑፅሪ ዋህዮ " . $mycode . " ኣይተረኸበን<br> ስርርዕ ዋህዮታት ኣይተመዝገበን"];
            }
            if(Srire::where('code', $mycode)->where('type', 'ዋህዮ')->where('year', $request->year)->count()){
                DB::rollback();
                return [false, "error", "መለለዪ ቑፅሪ ዋህዮ " . $mycode . " ናይ " . $request->year . "<br> ስርርዕ ኣብ መዝገብ ኣሎ እዩ"];
            }
            $data = new Srire;                   
            $data->code = $mycode; 
            $data->type = $request->type;
            $data->result = $request->result;
            $data->year = $request->year; 
            $data->save();    
        }
        DB::commit();
        return [true, "info", "ስርርዕ ዋህዮታት ብትክክል ተመዝጊቡ ኣሎ"];
    }
}

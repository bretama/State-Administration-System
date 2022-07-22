<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kamaln7\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests;
use  App\Hitsuy;
use  App\ApprovedHitsuy;
use  App\Zobatat;
use  App\meseretawiWdabe;
use  App\Wahio;
use App\Yearly;
use App\Monthly;
use App\Mewacho;
use DB;

class PaymentController extends Controller
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
        $value = Auth::user()->area;
        if(array_search(Auth::user()->usertype, ['woreda','woredaadmin']) !== false){
            $value = '__'.$value;
        }
        $data = ApprovedHitsuy::where('zoneworedaCode','LIKE', $value.'%')->get();
        $zobadatas = DB::table("zobatats")->pluck("zoneName","zoneCode");
        $mwidabedata = DB::table("meseretawi_wdabes")->pluck("widabeName","widabeCode");
        $wahiodata = DB::table("wahios")->pluck("wahioName","id");
        $yearlydata = DB::table("yearly_settings")->pluck("amount","type");

        $tempdata = ApprovedHitsuy::all()->pluck("membershipType","hitsuyID");
        $collectionyear = collect([]);       
        foreach ($tempdata as $key => $value) {
            $lastYear = Yearly::where('hitsuyID',$key)->orderBy('year', 'desc')->pluck('year')->first();
            $collectionyear->prepend($lastYear, $key);
        }
        
        return view ('payment.yearly',compact('data','zobadatas','wahiodata','mwidabedata','collectionyear','yearlydata'));
    }
    public function indexformonthly()
    {
        $value = Auth::user()->area;
        if(array_search(Auth::user()->usertype, ['woreda','woredaadmin']) !== false){
            $value = '__'.$value;
        }
        $data = ApprovedHitsuy::where('zoneworedaCode','LIKE', $value.'%')/*->where('approved_status','1')*/->get();
        $zobadatas = DB::table("zobatats")->pluck("zoneName","zoneCode");
        $mwidabedata = DB::table("meseretawi_wdabes")->pluck("widabeName","widabeCode");
        $wahiodata = DB::table("wahios")->pluck("wahioName","id");
        $monthlydata = DB::table("monthly_settings")->select("percent","from","to")->get()->toArray();

        $tempdata = ApprovedHitsuy::where('zoneworedaCode','LIKE', $value.'%')->pluck("membershipType","hitsuyID");
        $collectionyear = collect([]);       
        $collectionmonth = collect([]);
        $collectionamount = collect([]);
        foreach ($tempdata as $key => $value) {
            $lastYear = Monthly::where('hitsuyID',$key)->orderBy('updated_at', 'desc')->pluck('year')->first();
            $lastmonth = Monthly::where('hitsuyID',$key)->orderBy('updated_at', 'desc')->pluck('month')->first();
            $lastamount = Monthly::where('hitsuyID',$key)->orderBy('updated_at', 'desc')->pluck('amount')->first();
            $collectionmonth->prepend($lastmonth, $key);
            $collectionyear->prepend($lastYear, $key);
            $collectionamount->prepend($lastamount, $key);
        }
        
        return view ('payment.monthly',compact('data','zobadatas','wahiodata','mwidabedata','collectionyear','collectionmonth','collectionamount','monthlydata')); 
    }
    public function indexformewacho(Request $request)
    {
        // if member types are fixed improve the code
        $mwname=$request->mewacho;              
        $zobadatas = DB::table("zobatats")->pluck("zoneName","zoneCode");
        $mwidabedata = DB::table("meseretawi_wdabes")->pluck("widabeName","widabeCode");
        $wahiodata = DB::table("wahios")->pluck("wahioName","id");
        
        $membertype = DB::table("mewacho_settings")->where('name',$mwname)->pluck("mtype","mtype");
        $mewachoamount = DB::table("mewacho_settings")->where('name',$mwname)->pluck("amount","mtype");        
        // $membertypearr=json_decode($membertypearr); 
        // $membertype=$membertypearr[0];             
        // $mewachoamountarr=json_decode($mewachoamountarr);
        // $mewachoamount=$mewachoamountarr[0];                    
        // $data = ApprovedHitsuy::whereHas('hitsuy', function ($query) use($membertype) {
        //     $query->where('occupation', $membertype);
        // })->get();          
        // $tempdata = ApprovedHitsuy::whereHas('hitsuy', function ($query) use($membertype) {
        //     $query->where('occupation', $membertype);
        // })->pluck("membershipType","hitsuyID");  
        $collectionmewacho =[];    
        $data = ApprovedHitsuy::where('approved_status','1')->get();
        $tempdata = ApprovedHitsuy::all()->pluck("membershipType","hitsuyID");
        foreach ($tempdata as $key => $value) {
            $lasthitsuy = Mewacho::where('hitsuyID',$key)->where('mewacho_name', $mwname)->pluck('hitsuyID')->first();            
            if($lasthitsuy)
                $collectionmewacho[$lasthitsuy]="ተኸፊሉ";                
        }
        
        return view ('payment.mewachodetail',compact('data','zobadatas','wahiodata','mwidabedata','collectionmewacho','membertype','mewachoamount','mwname')); 
    }
    public function indexformewachomain()
    {
        $currentdate='2010-03-15';
        $mewachodata = DB::table("mewacho_settings")->where('deadline','>=',$currentdate)->pluck("mtype","name");
        return view ('payment.mewacho',compact('mewachodata')); 
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
            'max' => ':attribute ክሳብ '.(date('Y')-((date('m')>10||(date('m')==9 && date('d')>11)) ? 7 :8)).' ክኸውን ኣለዎ',
        ];
        //
        $validator = \Validator::make($request->all(),[
            'memeberID' => 'required',
            'year' => 'required|integer|digits:4|min:1950|max:'.(date('Y')-((date('m')>10||(date('m')==9 && date('d')>11)) ? 7 :8)),
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
        $yearlydata = DB::table("yearly_settings")->pluck("amount","type");

        DB::beginTransaction();
        foreach($memeberIDs as $hID){
            if(!ApprovedHitsuy::where('hitsuyID', $hID)->count()){
                $validator->errors()->add('duplicate', 'ክፍሊት ኣይተመዝገበን መለለዪ ኣባል '.$hID.'  ኣብ መዝገብ ኣይተረኸበን');
                DB::rollback();
                return [false,  'error', $validator->errors()->all()];
            }
            if(Yearly::where('hitsuyID', $hID)->where('year', $request->year)->count()){
                $validator->errors()->add('duplicate', 'ክፍሊት ኣይተመዝገበን መለለዪ ኣባል '.$hID. ' ናይ ' . $request->year . ' ክፍሊት ከፊሉ ነይሩ እዩ');
                DB::rollback();
                return [false,  'error', $validator->errors()->all()];
            }
            $data = new Yearly;                   
            $data->hitsuyID = $hID; 
            $data->year = $request->year;            
            $myType = Hitsuy::where('hitsuyID',$hID)->pluck('occupation')->first();
            $data->amount = $yearlydata[$myType];   
            $data->save();    
        }
        DB::commit();
        return [true, "info", "ዓመታዊ ክፍሊት ብትክክል ተመዝጊቡ ኣሎ"];
    }

    public function storeMonthly(Request $request)
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
            'month' => 'required|in:መስከረም,ጥቅምቲ,ሕዳር,ታሕሳስ,ጥሪ,ለካቲት,መጋቢት,ሚያዝያ,ግንቦት,ሰነ,ሓምለ,ነሓሰ',
        ],$messages);
        $fieldNames = [
            'memeberID' => 'መለለዩ ቑፅሪ ኣባላት',
            'year' => 'ዓመት',
            'month' => 'ክፍሊት ወርሒ',
        ];
        $validator->setAttributeNames($fieldNames);
        if($validator->fails()){
            return [false, 'error', $validator->errors()->all()];
        }
        //
        $memeberIDs=json_decode($request->memeberID);
        $monthlydata = DB::table("monthly_settings")->select("percent","from","to")->get()->toArray();
        foreach($memeberIDs as $hID) {   
            if(!ApprovedHitsuy::where('hitsuyID', $hID)->count()){
                $validator->errors()->add('duplicate', 'ክፍሊት ኣይተመዝገበን መለለዪ ኣባል '.$hID.' ኣብ መዝገብ ኣይተረኸበን');
                return [false,  'error', $validator->errors()->all()];
            }
            if(Monthly::where('hitsuyID', $hID)->where('year', $request->year)->where('month', $request->month)->count()){
                $validator->errors()->add('duplicate', 'ክፍሊት ኣይተመዝገበን መለለዪ ኣባል '.$hID.' ወርሒ ' . $request->month . ' ' . $request->year . ' ከፊሉ ነይሩ እዩ');
                return [false,  'error', $validator->errors()->all()];   
            }
        }
        foreach($memeberIDs as $hID) {   
            $data = new Monthly;                   
            $data->hitsuyID = $hID; 
            $data->year = $request->year; 
            $data->month = $request->month;

            $myNet = ApprovedHitsuy::where('hitsuyID',$hID)->pluck('netSalary')->first();
            $mypercent=0.0;
            foreach ($monthlydata as $monthly) {
                if(($myNet >= $monthly->from) && ($myNet < $monthly->to)){
                    $mypercent = $monthly->percent;
                    break;
                }
            }
                            
            $data->amount = $myNet*$mypercent;   
            $data->save();    
        }

        return [true, "info", "ወርሓዊ ክፍሊት ብትክክል ተመዝጊቡ ኣሎ"];
    }
    public function storeMewacho(Request $request)
    {
        //
        $memeberIDs=json_decode($request->memeberID);
        $myamount=json_decode($request->amount);
        
        foreach($memeberIDs as $key => $hID) {   
            $data = new Mewacho;                   
            $data->hitsuyID = $hID; 
            $data->payday = $request->payday; 
            $data->mewacho_name = $request->mewacho;                                        
            $data->amount = $myamount[$key];   
            $data->save();    
        }

        Toastr::info("መዋጮ ክፍሊት ብትክክል ተመዝጊቡ ኣሎ");
        return back();
        // return response ()->json ( $request->mewacho );
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

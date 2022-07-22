<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use  App\Siltena;
use  App\Hitsuy;
use  App\ApprovedHitsuy;
use App\DateConvert;
use DB;

use Kamaln7\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;

class TrainingController extends Controller
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
        //
        // $data = Hitsuy::where('hitsuy_status','ኣባል')->get(); 
        $value = Auth::user()->area;
        if(array_search(Auth::user()->usertype, ['woreda','woredaadmin']) !== false){
            $value = '__'.$value;
        }
        $data = ApprovedHitsuy::where('approved_status','1')->where('zoneworedaCode', 'LIKE', $value.'%')->get();
        $zobadatas = DB::table("zobatats")->pluck("zoneName","zoneCode");
        $mwidabedata = DB::table("meseretawi_wdabes")->pluck("widabeName","widabeCode");
        $wahiodata = DB::table("wahios")->pluck("wahioName","id");
        
        return view ('membership.training',compact('data','zobadatas','wahiodata','mwidabedata'));
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
            'ethiopian_date' => ':attribute: መዓልቲ/ወርሒ/ዓመተምህረት ክኸውን ኣለዎ',
            'required' => ':attribute ኣይተመልአን',
            'integer' => ':attribute ቑፅሪ ክኸውን ኣለዎ',
            'in' => ':attribute ካብቶም ዘለዉ መማረፅታት ክኸውን ኣለዎ'
        ];
        $validator = \Validator::make($request->all(), [  
            'memeberID' => 'required',
            'trainingLevel' => 'required|in:ጀማሪ ኣመራርሓ ስልጠና,ታሕተዋይ ኣመራርሓ ስልጠና,ማእኸላይ ኣመራርሓ ስልጠና,ላዕለዋይ ኣመራርሓ ስልጠና',
            'trainer' => 'required',
            'trainingPlace' => 'required',
            'trainingType' => 'required|in:ናይ ውድብ,ናይ መንግስቲ',
            'zoneDecision' => 'required',  
            'woredaApproved' => 'required',                     
            'numDays' => 'required|integer',
            'startDate' => 'required|ethiopian_date',
            'endDate' => 'required|ethiopian_date'

        ],$messages);
        $fieldNames = [
        'memeberID' => 'መፍለዪ ቑፅሪ ኣባላት',
        'trainingLevel' => 'ዝወሰዶ ስልጠና ',
        'trainer' => 'ስልጠና ዝሃበ ኣካል',
        'trainingPlace' => 'ናይ ስልጠና ቦታ',
        'trainingType' => 'ዝተውሃበ ስልጠና',
        'zoneDecision' => 'ናይ ዞባ ውሳነ ቐሪቡ',  
        'woredaApproved' => 'ናይ ወረዳ ውሳነ ቐሪብ',                     
        'numDays' => 'ጠቕላላ ናይ ስልጠና መዓልትታት',
        'startDate' => 'ስልጠና ዝጀመረሉ ዕለት',
        'endDate' => 'ስልጠና ዝተወደአሉ ዕለት'
        ];
        $validator->setAttributeNames($fieldNames);
        if($validator->fails()){
            return [false, 'error', $validator->errors()->all()];
        }
        if(!Hitsuy::where('hitsuyID', $request->hitsuyID)->count()){
            $validator->errors()->add('duplicate', 'ኣባል ኣብ መዝገብ የለን');
            return [false, 'error', $validator->errors()->all()];
        }
        $memeberIDs=json_decode($request->memeberID);
        // update approved based on type of training, ow don't update status
        foreach($memeberIDs as $hID) {   
            $data = new Siltena;                   
            $data->hitsuyID = $hID; 
            $data->trainingLevel = $request->trainingLevel;
            $data->trainer = $request->trainer;
            $data->startDate = $this->correctDate($request->startDate);
            $data->endDate = $this->correctDate($request->endDate);
            $data->numDays = $request->numDays;
            $data->trainingPlace = $request->trainingPlace;
            $data->trainingType = $request->trainingType;
            $data->zoneDecision = $request->zoneDecision;
            $data->woredaApproved =$request->woredaApproved;
            $data->zoneApproved =$request->zoneApproved;
            $data->officeApproved =$request->officeApproved;
            $data->isDocumented =$request->isDocumented;
            $data->save();                           

            if($request->membertype=="ተራ ኣባል" && $request->trainingLevel=="ጀማሪ ኣመራርሓ ስልጠና"){
                ApprovedHitsuy::where('hitsuyID', $hID)->update(['memberType' => 'ጀማሪ ኣመራርሓ']);

            }else if($request->membertype=="ሰብ ሞያ" && $request->trainingLevel=="ጀማሪ ኣመራርሓ ስልጠና"){
                ApprovedHitsuy::where('hitsuyID', $hID)->update(['memberType' => 'ጀማሪ ኣመራርሓ']);

            }else if($request->membertype=="ጀማሪ ኣመራርሓ" && $request->trainingLevel=="ታሕተዋይ ኣመራርሓ ስልጠና"){
                ApprovedHitsuy::where('hitsuyID', $hID)->update(['memberType' => 'ታሕተዋይ ኣመራርሓ']);

            }else if($request->membertype=="ታሕተዋይ ኣመራርሓ" && $request->trainingLevel=="ማእኸላይ ኣመራርሓ ስልጠና"){
                ApprovedHitsuy::where('hitsuyID', $hID)->update(['memberType' => 'ማእኸላይ ኣመራርሓ']);

            }else if($request->membertype=="ማእኸላይ ኣመራርሓ" && $request->trainingLevel=="ላዕለዋይ ኣመራርሓ ስልጠና"){
                ApprovedHitsuy::where('hitsuyID', $hID)->update(['memberType' => 'ላዕለዋይ ኣመራርሓ']);

            }else{

            }
            
        }
        return [true, "info", "ስልጠና ብትክክል ተመዝጊቡ ኣሎ"];
        // return response ()->json ( $hID ); 
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

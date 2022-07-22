<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Kamaln7\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests;
use DB;
use  App\ApprovedHitsuy;
use App\LowerLeader;
use  App\Zobatat;



class LowerLeadersController extends Controller
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
        $data = ApprovedHitsuy::where('approved_status','1')->get(); 
        $zobadatas = DB::table("zobatats")->pluck("zoneName","zoneCode");
        return view ('membership.lowerleaderform',compact('data','zobadatas')); 
    }
    public function lowerleadersIndex()
    {
        $value = Auth::user()->area;
        if(array_search(Auth::user()->usertype, ['woreda','woredaadmin']) !== false){
            $value = '__'.$value;
        }
        $data = LowerLeader::whereIn('hitsuyID', function($query) use($value){
            $query->select('hitsuyID')
            ->from(with(new ApprovedHitsuy)->getTable())
            ->where('zoneworedaCode', 'LIKE', $value.'%');
        })->get();
        // $data=LowerLeader::all();       
        $tabianame = DB::table("tabias")->pluck("tabiaName","tabiaCode");
        $tabiadata = DB::table("tabias")->pluck("woredacode","tabiaCode");
        $woredaname = DB::table("woredas")->pluck("name","woredacode");
        $woredadata = DB::table("woredas")->pluck("zoneCode","woredacode");
        $zobadatas = DB::table("zobatats")->pluck("zoneName","zoneCode");
        return view ('membership.lowleaderslist',compact('data','zobadatas','tabiadata','tabianame','woredaname','woredadata'));
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
    {   //
        $messages = [            
            'required' => ':attribute ብትኽክል ኣይኣተወን',
            'in' => ':attribute ካብቶም ዘለዉ መማረፅታት ክኸውን ኣለዎ',
            'max' => 'ዓመተምህረት ቅድሚ '.(date('Y')-7).' ክኸውን ኣለዎ',
        ];
        //
        $validator = \Validator::make($request->all(),[
            'hitsuyID' => 'required',
            'model' => 'required|in:ሞዴል,ዘይሞዴል',            
            'evaluation' => 'required|in:A,B,C',
            'remark' => 'required',
            'year' => 'required|digits:4|integer|min:1950|max:'.(date('Y')-7),
            'half' => 'required|in:6 ወርሒ,ዓመት',
        ],$messages);
        $fieldNames = [
            'hitsuyID' => 'መለለዩ ኣባል',
            'model' => 'ዓይነት ኣባል',
            'evaluation' => 'ውፅኢት ገምጋም',
            'remark' => 'ናይ በዓል ዋና ርኢቶ',
            'year' => 'ዓመተ ምህረት',
            'half' => 'እዋን ገምጋም',
        ];
        $validator->setAttributeNames($fieldNames);
        $validator->validate();
        if(!ApprovedHitsuy::where('hitsuyID', $request->hitsuyID)->count()){
            $validator->errors()->add('duplicate', 'ኣባል ኣብ መዝገብ የለን');
            return redirect()->back()->withErrors($validator)->withInput();
        }
        if(!ApprovedHitsuy::where('hitsuyID', $request->hitsuyID)->where('memberType','ታሕተዋይ ኣመራርሓ')->count()){
            $validator->errors()->add('duplicate', 'ኣባል ታሕተዋይ ኣመራርሓ ኣይኮነን');
            return redirect()->back()->withErrors($validator)->withInput();
        }
        if(LowerLeader::where('hitsuyID', $request->hitsuyID)->where('year', $request->year)->where('half', $request->half)->count()){
            $validator->errors()->add('duplicate', $request->hitsuyID . ' ኣብ ዓመት ' . $request->year . '(' . $request->half . ') ተገምጊሙ እዩ');
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $data = new LowerLeader;
        $data->hitsuyID = $request->hitsuyID;
        $data->model = $request->model;
        $data->evaluation = $request->evaluation;
        $data->remark = $request->remark;     
        $data->year = $request->year;
        $data->half = $request->half;
        $data->save();  
  
        Toastr::info("ናይ ታሕተዋይ ኣመራርሓ ኣባላት ህወሓት ማህደር ብትኽክል ተመዝጊቡ ኣሎ");
        return back();
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

<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Kamaln7\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests;
use DB;
use  App\ApprovedHitsuy;
use App\SuperLeader;
use  App\Zobatat;

class SuperLeaderController extends Controller
{
    //
    public function __construct()    //if not authenticated redirect to login
    {
        $this->middleware('auth');
    }
    public function index()
    {
       $data = ApprovedHitsuy::where('approved_status','1')->get();
       $zobadatas = DB::table("zobatats")->pluck("zoneName","zoneCode");
        
        return view ('leadership.topleadershipform',compact('data','zobadatas')); 
        
    }
     public function topleadersIndex()
    {
        $value = Auth::user()->area;
        if(array_search(Auth::user()->usertype, ['woreda','woredaadmin']) !== false){
            $value = '__'.$value;
        }
        $data = SuperLeader::whereIn('hitsuyID', function($query) use($value){
            $query->select('hitsuyID')
            ->from(with(new ApprovedHitsuy)->getTable())
            ->where('zoneworedaCode', 'LIKE', $value.'%');
        })->get();
        // $data=SuperLeader::all();
        $tabiadata = DB::table("tabias")->pluck("woredacode","tabiaCode");
        $woredaname = DB::table("woredas")->pluck("name","woredacode");
        $woredadata = DB::table("woredas")->pluck("zoneCode","woredacode");
        $zobadatas = DB::table("zobatats")->pluck("zoneName","zoneCode");   
        return view ('leadership.tleaderslist',compact('data','zobadatas','tabiadata','woredaname','woredadata') );
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
        //
        $messages = [            
            'required' => '*:attribute',
            'integer' => '*:attribute',
            'digits' => 'ዓመተምህረት 4 ኣሃዛት ክኸውን ኣለዎ',
            'min' => 'ዓመተምህረት ድሕሪ :min ክኸውን ኣለዎ',
            'max' => 'ዓመተምህረት ቅድሚ '.(date('Y')-7).' ክኸውን ኣለዎ',
            'between' => '*:attribute',
            'in' => '*:attribute',
        ];
        //
        $validator = \Validator::make($request->all(),[
            'answer1' => 'required',            
            'answer2' => 'required',
            'answer3' => 'required',
            'answer4' => 'required',            
            'answer5' => 'required',
            'answer6' => 'required',
            'answer7' => 'required',            
            'answer8' => 'required',
            'answer9' => 'required',
            'answer10' => 'required',  
            'answer11' => 'required',
            'answer12' => 'required',            
            'answer13' => 'required',
            'answer14' => 'required',
            'answer15' => 'required',              
            'result1' => 'required|integer|between:0,11',
            'result2' => 'required|integer|between:0,10',
            'result3' => 'required|integer|between:0,10',
            'result4' => 'required|integer|between:0,4',
            'result5' => 'required|integer|between:0,2',
            'result6' => 'required|integer|between:0,2',
            'result7' => 'required|integer|between:0,2',
            'result8' => 'required|integer|between:0,2',
            'result9' => 'required|integer|between:0,11',
            'result10' => 'required|integer|between:0,10',
            'result11' => 'required|integer|between:0,14',
            'result12' => 'required|integer|between:0,10',
            'result13' => 'required|integer|between:0,10',
            'remark' => 'required',
            'year' => 'required|digits:4|integer|min:1950|max:'.(date('Y')-7),
            'half' => 'required|in:6 ወርሒ,ዓመት',
        ],$messages);
        if($validator->fails()){
            return [false, $validator->errors()->all()];
        }
        if(!ApprovedHitsuy::where('hitsuyID', $request->hitsuyID)->count()){
            $validator->errors()->add('duplicate', 'ኣባል ኣብ መዝገብ የለን');
            return [false, $validator->errors()->all()];
        }
        if(!ApprovedHitsuy::where('hitsuyID', $request->hitsuyID)->where('memberType','ላዕለዋይ ኣመራርሓ')->count()){
            $validator->errors()->add('duplicate', 'ኣባል ላዕለዋይ ኣመራርሓ ኣይኮነን');
            return [false, $validator->errors()->all()];
        }
        if(SuperLeader::where('hitsuyID', $request->hitsuyID)->where('year', $request->year)->where('half', $request->half)->count()){
            $validator->errors()->add('duplicate', $request->hitsuyID . ' ኣብ ዓመት ' . $request->year . '(' . $request->half . ') ተገምጊሙ እዩ');
            return [false, $validator->errors()->all()];   
        }
        $data = new SuperLeader;
        $data->hitsuyID = $request->hitsuyID;
        $data->answer1 = $request->answer1;
        $data->answer2 = $request->answer2;
        $data->answer3 = $request->answer3;
        $data->answer4 = $request->answer4;
        $data->answer5 = $request->answer5;
        $data->answer6 = $request->answer6;
        $data->answer7 = $request->answer7;
        $data->answer8 = $request->answer8;
        $data->answer9 = $request->answer9;
        $data->answer10 = $request->answer10;
        $data->answer11 = $request->answer11;
        $data->answer12 = $request->answer12;
        $data->answer13 = $request->answer13;
        $data->answer14 = $request->answer14;
        $data->answer15 = $request->answer15;
        //$data->answer16 = $request->answer16;
        $data->result1 = $request->result1;
        $data->result2 = $request->result2;
        $data->result3 = $request->result3;
        $data->result4 = $request->result4;
        $data->result5 = $request->result5;
        $data->result6 = $request->result6;
        $data->result7 = $request->result7;
        $data->result8 = $request->result8;
        $data->result9 = $request->result9;
        $data->result10 = $request->result10;
        $data->result11 = $request->result11;
        $data->result12 = $request->result12;
        $data->result13 = $request->result13;
       // $data->result14 = $request->result14;
        $data->sum = $data->result1 + $data->result2 + $data->result3 + $data->result4 + $data->result5 + $data->result6 + $data->result7 + $data->result8 + $data->result9 + $data->result10 + $data->result11 + $data->result12 + $data->result13;
        $data->remark = $request->remark;
        $data->year = $request->year;
        $data->half = $request->half;
        $data->save();  
        return [true, "ናይ ላዕለዋይ ኣመራርሓ ማህደር ብትኽክል ተመዝጊቡ ኣሎ"];
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

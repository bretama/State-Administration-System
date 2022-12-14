<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Kamaln7\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;

use  App\Zobatat;
use  App\Woreda; 
use  App\Tabia;
use  App\Wahio;
use  App\meseretawiWdabe;
use  App\ApprovedHitsuy;
use  App\Hitsuy;
use DB;

class wahioController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
      public function index(Request $request)
    {

  	 $data = Wahio::with('wahiosmw')->get();
	   $zobadatas = DB::table("zobatats")->pluck("zoneName","zoneCode");
	   return view ( 'kebabyawi.wahio', compact('data','zobadatas'));	
	   
    } 
	        
	public function searchmeseretawidabe($tid)
    {
	  $data = DB::table("meseretawi_wdabes")

                    ->where("tabiaCode",$tid)

                    ->pluck("widabeName","widabeCode");

        return json_encode($data);
    }

	public function searchwahio($mid)
    {
	  $data = DB::table("wahios")

                    ->where("widabeCode",$mid)

                    ->pluck("wahioName","id");

        return json_encode($data);
    }
    public function searchhitsuy($wid)
    {
      $data = DB::table("approved_hitsuys")

                    ->where("assignedWahio",$wid)

                    ->where('approved_status','1')

                    ->pluck("membershipType","hitsuyID");
        $collection = collect([]);         
        foreach ($data as $key => $value) {
            $fn = collect([]);
            $kv = DB::table("hitsuys")

                    ->where("hitsuyID",$key)->pluck("name");
            $kvf = DB::table("hitsuys")

                    ->where("hitsuyID",$key)->pluck("fname");   
            $kvg = DB::table("hitsuys")

                    ->where("hitsuyID",$key)->pluck("gfname");                                     

               $fn=$fn->prepend($kvg);
               $fn=$fn->prepend($kvf);
               $fn=$fn->prepend($kv);
               $fn=$fn->collapse();
               $fn=$fn->all();
               
               $collection->prepend($fn, $key);                      
        }                            
        return json_encode($collection);
    }
    public function searchallhitsuy($wid)
    {
      $data = DB::table("hitsuys")

                    ->where("proposerWahio",$wid)

                    ->whereIn('hitsuy_status',['?????????','??????????????? ????????????','?????????'])

                    ->pluck("occupation","hitsuyID");
        $collection = collect([]);         
        foreach ($data as $key => $value) {
            $fn = collect([]);
            $kv = DB::table("hitsuys")

                    ->where("hitsuyID",$key)->pluck("name");
            $kvf = DB::table("hitsuys")

                    ->where("hitsuyID",$key)->pluck("fname");   
            $kvg = DB::table("hitsuys")

                    ->where("hitsuyID",$key)->pluck("gfname");                                     

               $fn=$fn->prepend($kvg);
               $fn=$fn->prepend($kvf);
               $fn=$fn->prepend($kv);
               $fn=$fn->collapse();
               $fn=$fn->all();
               
               $collection->prepend($fn, $key);                      
        }                            
        return json_encode($collection);
    }

    public function searchhitsuymeseretawi($wid)
    {
      $data = DB::table("approved_hitsuys")

                    ->where("assignedWudabe",$wid)

                    ->where('approved_status','1')

                    ->pluck("membershipType","hitsuyID");
        $collection = collect([]);         
        foreach ($data as $key => $value) {
            $fn = collect([]);
            $kv = DB::table("hitsuys")

                    ->where("hitsuyID",$key)->pluck("name");
            $kvf = DB::table("hitsuys")

                    ->where("hitsuyID",$key)->pluck("fname");   
            $kvg = DB::table("hitsuys")

                    ->where("hitsuyID",$key)->pluck("gfname");                                     

               $fn=$fn->prepend($kvg);
               $fn=$fn->prepend($kvf);
               $fn=$fn->prepend($kv);
               $fn=$fn->collapse();
               $fn=$fn->all();
               
               $collection->prepend($fn, $key);                      
        }                            
        return json_encode($collection);
    }

    public function searchmeduimleader($wid)
    {
      $data = DB::table("approved_hitsuys")

                    ->where("assignedWahio",$wid)
                    ->where('approved_status','1')
                    ->where("memberType","??????????????? ???????????????")
                    ->pluck("membershipType","hitsuyID");
        $collection = collect([]);         
        foreach ($data as $key => $value) {
            $fn = collect([]);
            $kv = DB::table("hitsuys")

                    ->where("hitsuyID",$key)->pluck("name");
            $kvf = DB::table("hitsuys")

                    ->where("hitsuyID",$key)->pluck("fname");   
            $kvg = DB::table("hitsuys")

                    ->where("hitsuyID",$key)->pluck("gfname");                                     

               $fn=$fn->prepend($kvg);
               $fn=$fn->prepend($kvf);
               $fn=$fn->prepend($kv);
               $fn=$fn->collapse();
               $fn=$fn->all();
               
               $collection->prepend($fn, $key);                      
        }                            
        return json_encode($collection);
    }
	public function searchtopleader($wid)
    {
      $data = DB::table("approved_hitsuys")

                    ->where("assignedWahio",$wid)
                    ->where('approved_status','1')
                    ->where("memberType","??????????????? ???????????????")
                    ->pluck("membershipType","hitsuyID");
        $collection = collect([]);         
        foreach ($data as $key => $value) {
            $fn = collect([]);
            $kv = DB::table("hitsuys")

                    ->where("hitsuyID",$key)->pluck("name");
            $kvf = DB::table("hitsuys")

                    ->where("hitsuyID",$key)->pluck("fname");   
            $kvg = DB::table("hitsuys")

                    ->where("hitsuyID",$key)->pluck("gfname");                                     

               $fn=$fn->prepend($kvg);
               $fn=$fn->prepend($kvf);
               $fn=$fn->prepend($kv);
               $fn=$fn->collapse();
               $fn=$fn->all();
               
               $collection->prepend($fn, $key);                      
        }                            
        return json_encode($collection);
    }
     public function searchfirstinstantleader($wid)
    {
      $data = DB::table("approved_hitsuys")

                    ->where("assignedWahio",$wid)
                    ->where('approved_status','1')
                    ->where("memberType","????????? ???????????????")
                    ->pluck("membershipType","hitsuyID");
        $collection = collect([]);         
        foreach ($data as $key => $value) {
            $fn = collect([]);
            $kv = DB::table("hitsuys")

                    ->where("hitsuyID",$key)->pluck("name");
            $kvf = DB::table("hitsuys")

                    ->where("hitsuyID",$key)->pluck("fname");   
            $kvg = DB::table("hitsuys")

                    ->where("hitsuyID",$key)->pluck("gfname");                                     

               $fn=$fn->prepend($kvg);
               $fn=$fn->prepend($kvf);
               $fn=$fn->prepend($kv);
               $fn=$fn->collapse();
               $fn=$fn->all();
               
               $collection->prepend($fn, $key);                      
        }                            
        return json_encode($collection);
    }  
    public function searchexpert($wid)
    {
      $data = DB::table("approved_hitsuys")
                      ->where("assignedWahio",$wid)
                      ->where('approved_status','1')
                    ->where("memberType","?????? ??????")
                    //->where([["assignedWahio",$wid],["memberType","?????? ?????????"],["approved_hitsuys.occupation",\App\ApprovedHitsuy::?????????]["approved_hitsuys.occupation",\App\ApprovedHitsuy::????????????]])
                    ->pluck("membershipType","hitsuyID");
        $collection = collect([]);         
        foreach ($data as $key => $value) {
            $fn = collect([]);

            $kv = DB::table("hitsuys")
                    
                    ->where("hitsuyID",$key)->pluck("name");
            $kvf = DB::table("hitsuys")
                    
                    ->where("hitsuyID",$key)->pluck("fname");   
            $kvg = DB::table("hitsuys")
                    
                    ->where("hitsuyID",$key)->pluck("gfname"); 
                                                 

               $fn=$fn->prepend($kvg);
               $fn=$fn->prepend($kvf);
               $fn=$fn->prepend($kv);
               $fn=$fn->collapse();
               $fn=$fn->all();
               
               $collection->prepend($fn, $key);                      
        }                            
        return json_encode($collection);
    } 
 

        public function searchlowleader($wid)
        {
          $data = DB::table("approved_hitsuys")

                        ->where("assignedWahio",$wid)
                        ->where('approved_status','1')
                        ->where("memberType","??????????????? ???????????????")
                        ->pluck("membershipType","hitsuyID");
            $collection = collect([]);         
            foreach ($data as $key => $value) {
                $fn = collect([]);

                $kv = DB::table("hitsuys")
                        
                        ->where("hitsuyID",$key)->pluck("name");
                $kvf = DB::table("hitsuys")
                        
                        ->where("hitsuyID",$key)->pluck("fname");   
                $kvg = DB::table("hitsuys")
                        
                        ->where("hitsuyID",$key)->pluck("gfname"); 
                                                     

                   $fn=$fn->prepend($kvg);
                   $fn=$fn->prepend($kvf);
                   $fn=$fn->prepend($kv);
                   $fn=$fn->collapse();
                   $fn=$fn->all();
                   
                   $collection->prepend($fn, $key);                      
            }                            
            return json_encode($collection);
        }  
        public function searchtaramember($wid)
        {
          $data = DB::table("approved_hitsuys")

                        ->where("assignedWahio",$wid)
                        ->where('approved_status','1')
                        ->where("memberType","?????? ?????????")
                        ->pluck("membershipType","hitsuyID");
            $collection = collect([]);         
            foreach ($data as $key => $value) {
                $fn = collect([]);

                $kv = DB::table("hitsuys")                    
                        ->where("hitsuyID",$key)->pluck("name");
                $kvf = DB::table("hitsuys")                    
                        ->where("hitsuyID",$key)->pluck("fname");   
                $kvg = DB::table("hitsuys")                    
                        ->where("hitsuyID",$key)->pluck("gfname");                                                 
                   $fn=$fn->prepend($kvg);
                   $fn=$fn->prepend($kvf);
                   $fn=$fn->prepend($kv);
                   $fn=$fn->collapse();
                   $fn=$fn->all();
                   
                   $collection->prepend($fn, $key);                      
            }                            
            return json_encode($collection);
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
    
	 public function addWahio(Request $request)
    {       	
      $messages = [            
            'required' => ':attribute ??????????????? ?????????????????????',
            'numeric' => ':attribute ????????? ???????????? ????????????',
            'min' => ':attribute ????????? :min ???????????? ?????????',
            'max' => ':attribute ????????? :max ???????????? ?????????',
            'in' => ':attribute ???????????? ????????? ?????????????????? ???????????? ?????????',
            'date_format' => '?????????: ????????????/?????????/????????????????????? ???????????? ?????????',
        ];
        //
        $validator = \Validator::make($request->all(), [
            'whname' => 'required',
            'widabeCode' => 'required',
            'wtype' => 'in:???????????????,???????????????,?????? ???????????????,????????????,???????????????,?????? ??????'
        ],$messages);
        $fieldNames = [
            'whname' => '?????? ?????????',
            'widabeCode' => '??????????????? ?????????',
            'wtype' => '???????????? ?????????'
        ];
        $validator->setAttributeNames($fieldNames);
        if($validator->fails()){
            return [false, 'error', $validator->errors()->all()];
        }
        else if(!meseretawiWdabe::where('widabeCode',$request->widabeCode)->count()){
            $validator->errors()->add('not found', '??????????????? ????????? ?????? ???????????? ?????????');
            return [false, 'error', $validator->errors()->all()];
        }
        else if(Wahio::where('wahioName',$request->whname)->where('widabeCode',$request->widabeCode)->count()){
            $validator->errors()->add('duplicate', '????????? '.$request->tname.' ?????? ???????????? ?????? ??????');
            return [false, 'error', $validator->errors()->all()];
        }
		$dataWahio= new Wahio ;
		$dataWahio->wahioName=($request->whname);	
		$dataWahio->widabeCode=($request->widabeCode);
    $dataWahio->parentcode = (meseretawiWdabe::where('widabeCode',$request->widabeCode)->first()->parentcode . $request->widabeCode);
    $dataWahio->type = ($request->wtype);
		        
		$dataWahio->save();
		return [true, 'info', "????????? ??????????????? ???????????? ??????", $dataWahio->id];
		
    }
	 public function editWahio(Request $request)
    {
      $messages = [            
            'required' => ':attribute ??????????????? ?????????????????????',
            'numeric' => ':attribute ????????? ???????????? ????????????',
            'min' => ':attribute ????????? :min ???????????? ?????????',
            'max' => ':attribute ????????? :max ???????????? ?????????',
            'in' => ':attribute ???????????? ????????? ?????????????????? ???????????? ?????????',
            'date_format' => '?????????: ????????????/?????????/????????????????????? ???????????? ?????????',
        ];
        //
        $validator = \Validator::make($request->all(), [
            'wcode' => 'required',
            'whname' => 'required',
            'widabeCode' => 'required',
            'wtype' => 'in:???????????????,???????????????,?????? ???????????????,????????????,???????????????,?????? ??????'
        ],$messages);
        $fieldNames = [
            'wcode' => '????????? ??????',
            'whname' => '?????? ?????????',
            'widabeCode' => '??????????????? ?????????',
            'wtype' => '???????????? ?????????'
        ];
        $validator->setAttributeNames($fieldNames);
        if($validator->fails()){
            return [false, 'error', $validator->errors()->all()];
        }
        else if(!meseretawiWdabe::where('widabeCode',$request->widabeCode)->count()){
            $validator->errors()->add('not found', '??????????????? ????????? ?????? ???????????? ?????????');
            return [false, 'error', $validator->errors()->all()];
        }
        else if(Wahio::where('wahioName',$request->whname)->where('type', $request->type)->where('widabeCode',$request->widabeCode)->count()){
            $validator->errors()->add('duplicate', '????????? '.$request->tname.' ?????? ???????????? ?????? ??????');
            return [false, 'error', $validator->errors()->all()];
        }
    $dataWahio = Wahio::find ( $request->wcode );
    $dataWahio->wahioName=($request->whname);   
    $dataWahio->widabeCode = ($request->widabeCode); 
    $dataWahio->parentcode = (meseretawiWdabe::where('widabeCode',$request->widabeCode)->first()->parentcode . $request->widabeCode);
    $dataWahio->type = ($request->wtype);
    $dataWahio->save ();
    return [true, 'info', "????????? ?????????????????? ??????"];
  }
  public function deleteWahio(Request $request)
    {
        $data = Wahio::find($request->code);
        if($data){
            $data->delete();
            return [true, '????????? ????????? ??????'];
        }
        return [true, "????????? ???????????? ??????????????????"];
    }


   

   
   
		 
	}
    


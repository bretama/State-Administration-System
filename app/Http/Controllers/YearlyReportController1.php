<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Auth;
use  App\Zobatat;
use  App\Woreda; 
use  App\Tabia;
use  App\Wahio;
use  App\meseretawiWdabe;
use  App\ApprovedHitsuy;
use  App\Hitsuy;
use  App\Yearly;
use DB;
class YearlyReportController1 extends Controller
{
 public function index(Request $request)
    {
        $zoneCode = Auth::user()->area;


        $zobadatas = '';
        if(Auth::user()->usertype == 'admin'){
            $zobadatas = DB::table("zobatats")->pluck("zoneName","zoneCode");
            $woredadatas = DB::table("woredas")->pluck("name","woredacode");
            
            if(!$request->zone){
                $zoneCode = DB::table("zobatats")->pluck("zoneCode")->first();

               
            }
            else
                $zoneCode = $request->zone;
        }
        //$fm = DB::table('approved_hitsuys')->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
        $dd=DB::table("yearlies")->join('hitsuys','yearlies.hitsuyID','=','hitsuys.hitsuyID')->join('meseretawi_Wdabes','yearlies.')->get()->toArray();
        dd($dd);
         //$woredaCode = DB::table("woredas")->where('zoneCode',$zoneCode)->pluck("woredacode")->first();
         //$tabiaCode = DB::table("tabias")->where('woredacode','004')->pluck("tabiaCode")->first();
        // $woredaCode = DB::table("woredas")->where('zoneCode',$zoneCode)->pluck("woredacode","name");
        // $meseretawi_Wdabes = DB::table("meseretawi_Wdabes")->where('tabiaCode',$tabiaCode)->pluck("widabeCode","widabeName");
       // $year = Yearly::where();
         
        //$woredaCode = DB::table("woredas")->pluck("woredaCode")->first();
        
        $zoneName = Zobatat::where('zoneCode', $zoneCode)->select(['zoneName'])->first()->toArray()['zoneName'];//tabias.parentcode

        $woredas = Woreda::where('zoneCode', $zoneCode)->select(['woredaCode', 'isUrban', 'name'])->get()->toArray();
         
         
         
        $woredaCode ='';
        $meseretawiWidabes = meseretawiWdabe::where('parentcode', 'LIKE', '__'.$woredaCode.'____')->select('widabeName')->get()->toArray();
        
       // $woredas = meseretawiWdabe::where('', $zoneCode)->select(['woredaCode', 'isUrban', 'name'])->get()->toArray();

        
        // $user=DB::table('meseretawi_Wdabes')->get()->toArray();
        // $name=DB::table('meseretawi_Wdabes')->pluck('widabeName');
       // echo $name;
        //ocupation from hitsuys
        //
        
        
              
        $ketemageter = [];
        $geterwidabe = [];
        $ketemawidabe = [];
        $deant = [];
        foreach ($woredas as $woreda) {
            $row_ketema_geter = ['name' => $woreda['name'], 'rm' => 0, 'rf' => 0, 'rs' => 0, 'um' => 0, 'uf' => 0, 'us' => 0, 'sm' => 0, 'sf' => 0, 'ss' => 0];
            $row_geter_widabe = ['name' => $woreda['name'], 'fm' => 0, 'ff' => 0, 'fs' => 0, 'cm' => 0, 'cf' => 0, 'cs' => 0, 'tm' => 0, 'tf' => 0, 'ts' => 0, 'stm' => 0, 'stf' => 0, 'sts' => 0, 'sm' => 0, 'sf' => 0, 'ss' => 0];
            $row_ketema_widabe = ['name' => $woreda['name'], 'dm' => 0, 'df' => 0, 'ds' => 0, 'lm' => 0, 'lf' => 0, 'ls' => 0, 'cm' => 0, 'cf' => 0, 'cs' => 0, 'tm' => 0, 'tf' => 0, 'ts' => 0, 'stm' => 0, 'stf' => 0, 'sts' => 0, 'sm' => 0, 'sf' => 0, 'ss' => 0];
            $row_deant = ['name' => $woreda['name'], 'mm' => 0, 'mf' => 0, 'ms' => 0, 'khm' => 0, 'khf' => 0, 'khs' => 0, 'cm' => 0, 'cf' => 0, 'cs' => 0, 'tm' => 0, 'tf' => 0, 'ts' => 0, 'sem' => 0, 'sef' => 0, 'ses' => 0, 'sm' => 0, 'sf' => 0, 'ss' => 0];
            $tabias = Tabia::where('woredacode', $woreda['woredaCode'])->select(['tabiaCode', 'isUrban'])->get()->toArray();
            foreach ($tabias as $tabia) {
                if($tabia['isUrban'] == 'ገጠር'){
                    //Geter total count
                    $row_ketema_geter['rm'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('gender', 'ተባ')->count();
                    $row_ketema_geter['rf'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('gender', 'ኣን')->count();
                    $row_ketema_geter['rs'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                    //Geter farmer count
                    $row_geter_widabe['fm'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ገባር')->where('gender', 'ተባ')->count();
                    $row_geter_widabe['ff'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ገባር')->where('gender', 'ኣን')->count();
                    $row_geter_widabe['fs'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ገባር')->count();

                    //Geter civil servant count
                    $row_geter_widabe['cm'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ሲቪል ሰርቫንት')->where('gender', 'ተባ')->count();
                    $row_geter_widabe['cf'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ሲቪል ሰርቫንት')->where('gender', 'ኣን')->count();
                    $row_geter_widabe['cs'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ሲቪል ሰርቫንት')->count();

                    //Geter teacher count
                    $row_geter_widabe['tm'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'መምህር')->where('gender', 'ተባ')->count();
                    $row_geter_widabe['tf'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'መምህር')->where('gender', 'ኣን')->count();
                    $row_geter_widabe['ts'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'መምህር')->count();

                    //Geter student count
                    $row_geter_widabe['stm'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ተምሃራይ')->where('gender', 'ተባ')->count();
                    $row_geter_widabe['stf'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ተምሃራይ')->where('gender', 'ኣን')->count();
                    $row_geter_widabe['sts'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ተምሃራይ')->count();
                }
                else{
                    //Ketema total count
                    $row_ketema_geter['um'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('gender', 'ተባ')->count();
                    $row_ketema_geter['uf'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('gender', 'ኣን')->count();
                    $row_ketema_geter['us'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                    //Ketema deant count
                    $row_ketema_widabe['dm'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->whereIn('occupation',['መፍረዪ','ንግዲ','ግልጋሎት','ኮስንትራክሽን','ከተማ ሕርሻ'])->where('gender', 'ተባ')->count();
                    $row_ketema_widabe['df'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->whereIn('occupation',['መፍረዪ','ንግዲ','ግልጋሎት','ኮስንትራክሽን','ከተማ ሕርሻ'])->where('gender', 'ኣን')->count();
                    $row_ketema_widabe['ds'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->whereIn('occupation',['መፍረዪ','ንግዲ','ግልጋሎት','ኮስንትራክሽን','ከተማ ሕርሻ'])->count();

                    //Ketema labour count
                    $row_ketema_widabe['lm'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ሸቃላይ')->where('gender', 'ተባ')->count();
                    $row_ketema_widabe['lf'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ሸቃላይ')->where('gender', 'ኣን')->count();
                    $row_ketema_widabe['ls'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ሸቃላይ')->count();

                    //Ketema civil servant count
                    $row_ketema_widabe['cm'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ሲቪል ሰርቫንት')->where('gender', 'ተባ')->count();
                    $row_ketema_widabe['cf'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ሲቪል ሰርቫንት')->where('gender', 'ኣን')->count();
                    $row_ketema_widabe['cs'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ሲቪል ሰርቫንት')->count();

                    //Ketema teacher count
                    $row_ketema_widabe['tm'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'መምህር')->where('gender', 'ተባ')->count();
                    $row_ketema_widabe['tf'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'መምህር')->where('gender', 'ኣን')->count();
                    $row_ketema_widabe['ts'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'መምህር')->count();

                    //Ketema student count
                    $row_ketema_widabe['stm'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ተምሃራይ')->where('gender', 'ተባ')->count();
                    $row_ketema_widabe['stf'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ተምሃራይ')->where('gender', 'ኣን')->count();
                    $row_ketema_widabe['sts'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ተምሃራይ')->count();

                    //deant manufacturing count
                    $row_deant['mm'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation','መፍረዪ')->where('gender', 'ተባ')->count();
                    $row_deant['mf'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation','መፍረዪ')->where('gender', 'ኣን')->count();
                    $row_deant['ms'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation','መፍረዪ')->count();

                    //deant ketema hirsha count
                    $row_deant['khm'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ከተማ ሕርሻ')->where('gender', 'ተባ')->count();
                    $row_deant['khf'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ከተማ ሕርሻ')->where('gender', 'ኣን')->count();
                    $row_deant['khs'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ከተማ ሕርሻ')->count();

                    //deant construction count
                    $row_deant['cm'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ኮስንትራክሽን')->where('gender', 'ተባ')->count();
                    $row_deant['cf'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ኮስንትራክሽን')->where('gender', 'ኣን')->count();
                    $row_deant['cs'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ኮስንትራክሽን')->count();

                    //deant trade count
                    $row_deant['tm'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ንግዲ')->where('gender', 'ተባ')->count();
                    $row_deant['tf'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ንግዲ')->where('gender', 'ኣን')->count();
                    $row_deant['ts'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ንግዲ')->count();

                    //deant service count
                    $row_deant['sem'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ግልጋሎት')->where('gender', 'ተባ')->count();
                    $row_deant['sef'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ግልጋሎት')->where('gender', 'ኣን')->count();
                    $row_deant['ses'] += ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('occupation', 'ግልጋሎት')->count();
                }
            }
            $row_ketema_geter['sm'] = $row_ketema_geter['um'] + $row_ketema_geter['uf'];
            $row_ketema_geter['sf'] = $row_ketema_geter['rm'] + $row_ketema_geter['rf'];
            $row_ketema_geter['ss'] = $row_ketema_geter['us'] + $row_ketema_geter['rs'];
            $ketemageter[] = array_values($row_ketema_geter);

            $row_geter_widabe['sm'] = $row_geter_widabe['fm'] + $row_geter_widabe['cm'] + $row_geter_widabe['tm'] + $row_geter_widabe['stm'];
            $row_geter_widabe['sf'] = $row_geter_widabe['ff'] + $row_geter_widabe['cf'] + $row_geter_widabe['tf'] + $row_geter_widabe['stf'];
            $row_geter_widabe['ss'] = $row_geter_widabe['fs'] + $row_geter_widabe['cs'] + $row_geter_widabe['ts'] + $row_geter_widabe['sts'];
            $geterwidabe[] = array_values($row_geter_widabe);

            $row_ketema_widabe['sm'] = $row_ketema_widabe['dm'] + $row_ketema_widabe['lm'] + $row_ketema_widabe['cm'] + $row_ketema_widabe['tm']+ $row_ketema_widabe['stm'];
            $row_ketema_widabe['sf'] = $row_ketema_widabe['df'] + $row_ketema_widabe['lf'] + $row_ketema_widabe['cf'] + $row_ketema_widabe['tf']+ $row_ketema_widabe['stf'];
            $row_ketema_widabe['ss'] = $row_ketema_widabe['ds'] + $row_ketema_widabe['ls'] + $row_ketema_widabe['cs'] + $row_ketema_widabe['ts']+ $row_ketema_widabe['sts'];
            $ketemawidabe[] = array_values($row_ketema_widabe);

            $row_deant['sm'] = $row_deant['mm'] + $row_deant['khm'] + $row_deant['cm'] + $row_deant['tm']+ $row_deant['sem'];
            $row_deant['sf'] = $row_deant['mf'] + $row_deant['khf'] + $row_deant['cf'] + $row_deant['tf']+ $row_deant['sef'];
            $row_deant['ss'] = $row_deant['ms'] + $row_deant['khs'] + $row_deant['cs'] + $row_deant['ts']+ $row_deant['ses'];
            $deant[] = array_values($row_deant);
        }
        // $ketemageter = [['ወ/ይት', '6819', '2133', '8952', '1405', '675', '2080', '8224', '2808', '11032'],['ወ/ይት', '6819', '2133', '8952', '1405', '675', '2080', '8224', '2808', '11032']];
        return view('report.yearly1', compact('zoneName', 'ketemageter', 'geterwidabe', 'ketemawidabe', 'deant', 'zobadatas', 'zoneCode'));
       
    } 
}

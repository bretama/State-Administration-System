<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use  App\Zobatat;
use  App\Woreda; 
use  App\Tabia;
use  App\Wahio;
use  App\meseretawiWdabe;
use  App\ApprovedHitsuy;
use  App\Hitsuy;
use App\Penalty;
use App\Transfer;
use App\Dismiss;
use App\DateConvert;
use DB;

use Carbon\Carbon;

class GeterReport extends Controller
{
    public function index(Request $request){
        $A_UPPER = 100;
        $A_LOWER = 80;
        $B_UPPER = 79;
        $B_LOWER = 50;
        $C_UPPER = 49;
        $C_LOWER = 0;
        $zoneCode = '01';
        $zoneName = Zobatat::where('zoneCode', $zoneCode)->select(['zoneName'])->first()->toArray()['zoneName'];
        $woredas = Woreda::where('zoneCode', $zoneCode)->select(['woredaCode', 'isUrban', 'name'])->get()->toArray();
        $now = Carbon::today();
        $then=$now->subMonths(3);
        $weseking_gudletin = [];
        $abalat_age_education = [];
        $abalat_mahberawi_bota = [];
        $abalat_deant = [];
        $wahio_count = [];
        $tabia_count = [];
        $plan_deant = [];
        $plan_non_deant = [];
        $plan_all = [];
        $model_members = [];
        $new_members_non_deant = [];
        $new_members_deant = [];
        $approved_new_members = [];
        $grades = [];
        foreach ($woredas as $woreda) {
            $row_weseking_gudletin = [$woreda['name'], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            $row_abalat_age_education = [$woreda['name'], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            $row_abalat_mahberawi_bota = [$woreda['name'], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            $row_abalat_deant = [$woreda['name'], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            $row_wahio_count = [$woreda['name'], 0, 0, 0, 0, 0, 0];
            $row_tabia_count = [$woreda['name'], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            $row_plan_deant = [$woreda['name'], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,];
            $row_plan_non_deant = [$woreda['name'], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,];
            $row_plan_all = [$woreda['name'], 0, 0, 0, 0, 0, 0, 0, 0, 0, ''];
            $row_model_members = [$woreda['name'], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, ''];
            $row_model_members = [$woreda['name'], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, ''];
            $row_new_members_non_deant = [$woreda['name'], 0, 0, 0, 0, 0, 0, 0, 0, 0];
            $row_new_members_deant = [$woreda['name'], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            $row_approved_new_members = [$woreda['name'], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            $row_grades = [$woreda['name'], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            $tabias = Tabia::where('woredacode', $woreda['woredaCode'])->select(['tabiaCode', 'isUrban'])->get()->toArray();
            $row_tabia_count[1] = count($tabias);
            foreach ($tabias as $tabia) {
                //wesekin gudletin
                if($tabia['isUrban'] == '?????????'){
                    $row_weseking_gudletin[2] += Hitsuy::where('hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')
                    ->whereBetween('created_at', [Carbon::createFromDate(2019,1,1), Carbon::createFromDate(2019,4,1)])
                    ->count();

                    $row_weseking_gudletin[3] += Transfer::where('zone', $zoneCode)
                    ->where('woreda', $woreda['woredaCode'])
                    ->where('tabia', $tabia['tabiaCode'])
                    ->whereBetween('created_at', [Carbon::createFromDate(2019,1,1), Carbon::createFromDate(2019,4,1)])
                    ->count();

                    // TODO ???????????? ????????????
                    $row_weseking_gudletin[4] += 0;

                    $row_weseking_gudletin[6] += DB::table('dismisses')
                    ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'dismisses.hitsuyID')
                    ->where('dismissReason', '?????????')
                    ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])
                    ->whereBetween('dismisses.created_at', [Carbon::createFromDate(2019,1,1), Carbon::createFromDate(2019,4,1)])
                    ->count();   

                    $row_weseking_gudletin[7] += DB::table('dismisses')
                    ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'dismisses.hitsuyID')
                    ->where('dismissReason', '???????????????')
                    ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])
                    ->whereBetween('dismisses.created_at', [Carbon::createFromDate(2019,1,1), Carbon::createFromDate(2019,4,1)])
                    ->count();   

                    $row_weseking_gudletin[8] += DB::table('penalties')
                    ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'penalties.hitsuyID')
                    ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    ->where('penaltyGiven', '?????? ??????????????? ???????????? ?????? ????????????')
                    ->where('hitsuys.hitsuy_status', '???????????????')
                    ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])
                    ->whereBetween('penalties.created_at', [Carbon::createFromDate(2019,1,1), Carbon::createFromDate(2019,4,1)])
                    ->count();


                    //TODO University
                    $row_weseking_gudletin[9] += Transfer::where('oldzone', $zoneCode)
                    ->where('oldworeda', $woreda['woredaCode'])
                    ->where('oldtabia', $tabia['tabiaCode'])
                    ->whereBetween('created_at', [Carbon::createFromDate(2019,1,1), Carbon::createFromDate(2019,4,1)])
                    ->count();

                    $row_weseking_gudletin[10] += Transfer::where('oldzone', $zoneCode)
                    ->where('oldworeda', $woreda['woredaCode'])
                    ->where('oldtabia', $tabia['tabiaCode'])
                    ->where('zone', $zoneCode)
                    ->whereBetween('created_at', [Carbon::createFromDate(2019,1,1), Carbon::createFromDate(2019,4,1)])
                    ->count();

                    $row_weseking_gudletin[11] += Transfer::where('oldzone', $zoneCode)
                    ->where('oldworeda', $woreda['woredaCode'])
                    ->where('oldtabia', $tabia['tabiaCode'])
                    ->where('zone','NOT', $zoneCode)
                    ->whereBetween('created_at', [Carbon::createFromDate(2019,1,1), Carbon::createFromDate(2019,4,1)])
                    ->count();

                    //TODO out of region
                    $row_weseking_gudletin[12] += Transfer::where('oldzone', $zoneCode)
                    ->where('oldworeda', $woreda['woredaCode'])
                    ->where('oldtabia', $tabia['tabiaCode'])
                    ->where('zone','NOT', $zoneCode)
                    ->whereBetween('created_at', [Carbon::createFromDate(2019,1,1), Carbon::createFromDate(2019,4,1)])
                    ->count();

                    $row_weseking_gudletin[13] += DB::table('dismisses')
                    ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'dismisses.hitsuyID')
                    ->whereIn('dismissReason', ['?????? ????????? ?????? ??????', '????????????' , '?????????'])
                    ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])
                    ->whereBetween('dismisses.created_at', [Carbon::createFromDate(2019,1,1), Carbon::createFromDate(2019,4,1)])
                    ->count();

                    $row_weseking_gudletin[16] += (ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count() + Hitsuy::where('hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->count());
                }

                //abalat age, education
                if($tabia['isUrban'] == '?????????'){
                    $fm = ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                    $cd = Hitsuy::where('hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->count();
                    $row_abalat_age_education[1] += $fm;
                    $row_abalat_age_education[2] += $cd;
                    $row_abalat_age_education[3] += ($fm + $cd);

                    // 18 - 35
                    $fm = DB::table('approved_hitsuys')->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->whereBetween('hitsuys.dob', [Carbon::today()->subYears(35), Carbon::today()->subYears(18)]) ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                    $cd = Hitsuy::whereBetween('hitsuys.dob', [Carbon::today()->subYears(35), Carbon::today()->subYears(18)]) ->where('hitsuys.hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->count();
                    $row_abalat_age_education[4] += ($fm + $cd);

                    //36 - 60
                    $fm = DB::table('approved_hitsuys')
                    ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->whereBetween('hitsuys.dob', [Carbon::today()->subYears(60), Carbon::today()->subYears(35)])
                    ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                    $cd = Hitsuy::whereBetween('hitsuys.dob', [Carbon::today()->subYears(60), Carbon::today()->subYears(35)]) ->where('hitsuys.hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->count();
                    $row_abalat_age_education[5] += ($fm + $cd);

                    //above 60
                    $fm = DB::table('approved_hitsuys')->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->whereBetween('hitsuys.dob', [Carbon::today()->subYears(150), Carbon::today()->subYears(60)]) ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                    $cd = Hitsuy::whereBetween('hitsuys.dob', [Carbon::today()->subYears(150), Carbon::today()->subYears(60)]) ->where('hitsuys.hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->count();
                    $row_abalat_age_education[6] += ($fm + $cd);


                    // illiterate [education]
                    $fm = DB::table('approved_hitsuys')
                    ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->whereIn('hitsuys.educationlevel', ['', null])
                    ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                    $cd = Hitsuy::whereIn('hitsuys.educationlevel', ['', null]) ->where('hitsuys.hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->count();
                    $row_abalat_age_education[7] += ($fm + $cd);

                    //1 - 8 [education]
                    $fm = DB::table('approved_hitsuys')
                    ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->whereBetween('hitsuys.educationlevel', [1, 8])
                    ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                    $cd = Hitsuy::whereBetween('hitsuys.educationlevel', [1, 8]) ->where('hitsuys.hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->count();
                    $row_abalat_age_education[8] += ($fm + $cd);

                    //9 - 12 [education]
                    $fm = DB::table('approved_hitsuys')
                    ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->whereBetween('hitsuys.educationlevel', [9, 12])
                    ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                    $cd = Hitsuy::whereBetween('hitsuys.educationlevel', [9, 12]) ->where('hitsuys.hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->count();
                    $row_abalat_age_education[9] += ($fm + $cd);

                    //certificate [education]
                    $fm = DB::table('approved_hitsuys')
                    ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->where('hitsuys.educationlevel', '??????????????????')
                    ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                    $cd = Hitsuy::where('hitsuys.educationlevel', '??????????????????') ->where('hitsuys.hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->count();
                    $row_abalat_age_education[10] += ($fm + $cd);

                    //diploma [education]
                    $fm = DB::table('approved_hitsuys')
                    ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->where('hitsuys.educationlevel', '????????????')
                    ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                    $cd = Hitsuy::where('hitsuys.educationlevel', '????????????') ->where('hitsuys.hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->count();
                    $row_abalat_age_education[11] += ($fm + $cd);

                    //1st degree [education]
                    $fm = DB::table('approved_hitsuys')
                    ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->where('hitsuys.educationlevel', '?????????')
                    ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                    $cd = Hitsuy::where('hitsuys.educationlevel', '?????????') ->where('hitsuys.hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->count();
                    $row_abalat_age_education[12] += ($fm + $cd);

                    //master's [education]
                    $fm = DB::table('approved_hitsuys')
                    ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->where('hitsuys.educationlevel', '????????????')
                    ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                    $cd = Hitsuy::where('hitsuys.educationlevel', '????????????') ->where('hitsuys.hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->count();
                    $row_abalat_age_education[13] += ($fm + $cd);

                    //PhD [education]
                    $fm = DB::table('approved_hitsuys')
                    ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->where('hitsuys.educationlevel', '????????????')
                    ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                    $cd = Hitsuy::where('hitsuys.educationlevel', '????????????') ->where('hitsuys.hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->count();
                    $row_abalat_age_education[14] += ($fm + $cd);

                }
                //abalat mahberawi bota
                if($tabia['isUrban'] == '?????????'){

                    // ?????????
                    $fm = DB::table('approved_hitsuys')
                    ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->where('hitsuys.occupation', '?????????')
                    ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                    $cd = Hitsuy::where('hitsuys.occupation', '?????????') ->where('hitsuys.hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->count();
                    $row_abalat_mahberawi_bota[2] += ($fm + $cd);

                    // ???????????? ?????? ??????
                    $fm = DB::table('approved_hitsuys')
                    ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->where('hitsuys.occupation', '????????? ???????????????')
                    ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                    $cd = Hitsuy::where('hitsuys.occupation', '????????? ???????????????') ->where('hitsuys.hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->count();
                    $row_abalat_mahberawi_bota[3] += ($fm + $cd);

                    // teacher
                    $fm = DB::table('approved_hitsuys')
                    ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->where('hitsuys.occupation', '????????????')
                    ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                    $cd = Hitsuy::where('hitsuys.occupation', '????????????') ->where('hitsuys.hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->count();
                    $row_abalat_mahberawi_bota[4] += ($fm + $cd);

                    // student
                    $fm = DB::table('approved_hitsuys')
                    ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->where('hitsuys.occupation', '???????????????')
                    ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                    $cd = Hitsuy::where('hitsuys.occupation', '???????????????') ->where('hitsuys.hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->count();
                    $row_abalat_mahberawi_bota[5] += ($fm + $cd);


                    // 67 - 83
                    $fm = DB::table('approved_hitsuys')->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->whereBetween('approved_hitsuys.membershipDate', [Carbon::createFromDate(1967,1,1), Carbon::createFromDate(1983,1,1)]) ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                    $cd = 0;
                    // $cd = Hitsuy::whereBetween('approved_hitsuys.membershipDate', [Carbon::today()->subYears(35), Carbon::today()->subYears(18)]) ->where('hitsuys.hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->count();
                    $row_abalat_mahberawi_bota[6] += ($fm + $cd);

                    // 84 - 93
                    $fm = DB::table('approved_hitsuys')->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->whereBetween('approved_hitsuys.membershipDate', [Carbon::createFromDate(1983,1,1), Carbon::createFromDate(1993,1,1)]) ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                    // $cd = Hitsuy::whereBetween('approved_hitsuys.membershipDate', [Carbon::today()->subYears(35), Carbon::today()->subYears(18)]) ->where('hitsuys.hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->count();
                    $row_abalat_mahberawi_bota[7] += ($fm + $cd);

                    // 94 - 2000
                    $fm = DB::table('approved_hitsuys')->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->whereBetween('approved_hitsuys.membershipDate', [Carbon::createFromDate(1993,1,1), Carbon::createFromDate(2000,1,1)]) ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                    // $cd = Hitsuy::whereBetween('approved_hitsuys.membershipDate', [Carbon::today()->subYears(35), Carbon::today()->subYears(18)]) ->where('hitsuys.hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->count();
                    $row_abalat_mahberawi_bota[8] += ($fm + $cd);

                    // after 2001
                    $fm = DB::table('approved_hitsuys')->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->whereBetween('approved_hitsuys.membershipDate', [Carbon::createFromDate(2000,1,1), Carbon::createFromDate(9000,1,1)]) ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                    // $cd = Hitsuy::whereBetween('approved_hitsuys.membershipDate', [Carbon::today()->subYears(35), Carbon::today()->subYears(18)]) ->where('hitsuys.hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->count();
                    $row_abalat_mahberawi_bota[9] += ($fm + $cd);


                    // ?????? ???????????????
                    $fm = DB::table('approved_hitsuys')->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->where('approved_hitsuys.assignedAssoc','?????? ???????????????') ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                    // $cd = Hitsuy::whereBetween('approved_hitsuys.membershipDate', [Carbon::today()->subYears(35), Carbon::today()->subYears(18)]) ->where('hitsuys.hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->count();
                    $row_abalat_mahberawi_bota[10] += ($fm + $cd);

                    // ???????????????
                    $fm = DB::table('approved_hitsuys')->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->where('approved_hitsuys.assignedAssoc','???????????????') ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                    // $cd = Hitsuy::whereBetween('approved_hitsuys.membershipDate', [Carbon::today()->subYears(35), Carbon::today()->subYears(18)]) ->where('hitsuys.hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->count();
                    $row_abalat_mahberawi_bota[11] += ($fm + $cd);

                    // ???????????????
                    $fm = DB::table('approved_hitsuys')->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->where('approved_hitsuys.assignedAssoc','???????????????') ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                    // $cd = Hitsuy::whereBetween('approved_hitsuys.membershipDate', [Carbon::today()->subYears(35), Carbon::today()->subYears(18)]) ->where('hitsuys.hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->count();
                    $row_abalat_mahberawi_bota[12] += ($fm + $cd);

                    // ??????????????? ???????????????
                    $fm = DB::table('approved_hitsuys')
                    ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->where('hitsuys.occupation', '????????? ???????????????')
                    ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                    $cd = Hitsuy::where('hitsuys.occupation', '????????? ???????????????') ->where('hitsuys.hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->count();
                    $row_abalat_mahberawi_bota[13] += ($fm + $cd);

                    // ????????????????????????
                    $fm = DB::table('approved_hitsuys')
                    ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->where('hitsuys.occupation', '????????????????????????')
                    ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                    $cd = Hitsuy::where('hitsuys.occupation', '????????? ???????????????') ->where('hitsuys.hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->count();
                    $row_abalat_mahberawi_bota[14] += ($fm + $cd);

                    // ?????????
                    $fm = DB::table('approved_hitsuys')
                    ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->where('hitsuys.occupation', '?????????')
                    ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                    $cd = Hitsuy::where('hitsuys.occupation', '????????? ???????????????') ->where('hitsuys.hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->count();
                    $row_abalat_mahberawi_bota[15] += ($fm + $cd);

                    // ?????? ???????????????
                    $fm = ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->where('gender', '??????')->count();
                    $cd = Hitsuy::where('hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->where('gender', '??????')->count();
                    $row_abalat_mahberawi_bota[16] += ($fm + $cd);

                    //?????????
                    $fm = ApprovedHitsuy::where('zoneworedaCode',$zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                    $cd = Hitsuy::where('hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->count();
                    $row_abalat_mahberawi_bota[17] += ($fm + $cd);

                }
                //tabia count
                if($tabia['isUrban'] == '?????????'){

                    if(ApprovedHitsuy::where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count()+
                        Hitsuy::where('hitsuy_status', '?????????')->where('hitsuyID', 'LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'%')->count()>500){
                        $row_tabia_count[2] += 1;
                    }
                    else{
                        $row_tabia_count[3] += 1;
                    }

                    // ???????????????
                    $row_tabia_count[5] += meseretawiWdabe::where('tabiaCode', $tabia['tabiaCode'])->where('type', '???????????????')->count();
                    // ????????? ?????????
                    $row_tabia_count[6] += meseretawiWdabe::where('tabiaCode', $tabia['tabiaCode'])->where('type', '????????? ?????????')->count();
                    // ????????????????????????
                    $row_tabia_count[7] += meseretawiWdabe::where('tabiaCode', $tabia['tabiaCode'])->where('type', '????????????????????????')->count();
                    // ?????????
                    $row_tabia_count[8] += meseretawiWdabe::where('tabiaCode', $tabia['tabiaCode'])->where('type', '?????????')->count();
                    // ???????????????
                    $row_tabia_count[9] += meseretawiWdabe::where('tabiaCode', $tabia['tabiaCode'])->where('type', '???????????????')->count();
                    // ?????????
                    $row_tabia_count[11] += meseretawiWdabe::where('tabiaCode', $tabia['tabiaCode'])->where('type', '?????????')->count();
                    // ????????????
                    $row_tabia_count[12] += meseretawiWdabe::where('tabiaCode', $tabia['tabiaCode'])->where('type', '????????????')->count();
                    // ???????????????
                    $row_tabia_count[13] += meseretawiWdabe::where('tabiaCode', $tabia['tabiaCode'])->where('type', '???????????????')->count();
                    // ???/???????????????
                    $row_tabia_count[14] += meseretawiWdabe::where('tabiaCode', $tabia['tabiaCode'])->where('type', '???/???????????????')->count();

                    //???/?????????
                    $row_tabia_count[16] += meseretawiWdabe::where('tabiaCode', $tabia['tabiaCode'])->count();                    
                }
                //deant
                // if($tabia['isUrban'] == '?????????'){
                //     // manufacturing
                //     $fm = ApprovedHitsuy::
                //     // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                //     where('wudabeType', '???????????????')
                //     ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                //     $cd = 0;
                //     $row_abalat_deant[1] += ($fm + $cd);

                //     // ketema hrisha
                //     $fm = ApprovedHitsuy::
                //     // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                //     where('wudabeType', '????????? ?????????')
                //     ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                //     $cd = 0;
                //     $row_abalat_deant[2] += ($fm + $cd);

                //     // construction
                //     $fm = ApprovedHitsuy::
                //     // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                //     where('wudabeType', '????????????????????????')
                //     ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                //     $cd = 0;
                //     $row_abalat_deant[3] += ($fm + $cd);

                //     // trade
                //     $fm = ApprovedHitsuy::
                //     // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                //     where('wudabeType', '?????????')
                //     ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                //     $cd = 0;
                //     $row_abalat_deant[4] += ($fm + $cd);

                //     // service
                //     $fm = ApprovedHitsuy::
                //     // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                //     where('wudabeType', '???????????????')
                //     ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();
                //     $cd = 0;
                //     $row_abalat_deant[5] += ($fm + $cd);

                //     // 67 - 83
                //     $fm = ApprovedHitsuy::
                //     // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                //     whereIn('wudabeType', ['???????????????', '????????? ?????????', '????????????????????????', '?????????', '???????????????'])
                //     ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])
                //     ->whereBetween('membershipDate', [Carbon::createFromDate(1967,1,1), Carbon::createFromDate(1983,1,1)])
                //     ->count();
                //     $cd = 0;
                //     $row_abalat_deant[6] += ($fm + $cd);

                //     // 83 - 93
                //     $fm = ApprovedHitsuy::
                //     // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                //     whereIn('wudabeType', ['???????????????', '????????? ?????????', '????????????????????????', '?????????', '???????????????'])
                //     ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])
                //     ->whereBetween('membershipDate', [Carbon::createFromDate(1983,1,1), Carbon::createFromDate(1993,1,1)])
                //     ->count();
                //     $cd = 0;
                //     $row_abalat_deant[7] += ($fm + $cd);

                //     // 93 - 2000
                //     $fm = ApprovedHitsuy::
                //     // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                //     whereIn('wudabeType', ['???????????????', '????????? ?????????', '????????????????????????', '?????????', '???????????????'])
                //     ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])
                //     ->whereBetween('membershipDate', [Carbon::createFromDate(1993,1,1), Carbon::createFromDate(2000,1,1)])
                //     ->count();
                //     $cd = 0;
                //     $row_abalat_deant[8] += ($fm + $cd);

                //     // 2000 - 
                //     $fm = ApprovedHitsuy::
                //     // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                //     whereIn('wudabeType', ['???????????????', '????????? ?????????', '????????????????????????', '?????????', '???????????????'])
                //     ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])
                //     ->whereBetween('membershipDate', [Carbon::createFromDate(2000,1,1), Carbon::createFromDate(9000,1,1)])
                //     ->count();
                //     $cd = 0;
                //     $row_abalat_deant[9] += ($fm + $cd);

                //     // ?????? ???????????????
                //     $fm = ApprovedHitsuy::
                //     // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                //     whereIn('wudabeType', ['???????????????', '????????? ?????????', '????????????????????????', '?????????', '???????????????'])
                //     ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])
                //     ->where('assignedAssoc', '?????? ???????????????')
                //     ->count();
                //     $cd = 0;
                //     $row_abalat_deant[10] += ($fm + $cd);

                //     // ????????????
                //     $fm = ApprovedHitsuy::
                //     // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                //     whereIn('wudabeType', ['???????????????', '????????? ?????????', '????????????????????????', '?????????', '???????????????'])
                //     ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])
                //     ->where('assignedAssoc', '????????????')
                //     ->count();
                //     $cd = 0;
                //     $row_abalat_deant[11] += ($fm + $cd);

                //     // ???????????????
                //     $fm = ApprovedHitsuy::
                //     // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                //     whereIn('wudabeType', ['???????????????', '????????? ?????????', '????????????????????????', '?????????', '???????????????'])
                //     ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])
                //     ->where('assignedAssoc', '???????????????')
                //     ->count();
                //     $cd = 0;
                //     $row_abalat_deant[12] += ($fm + $cd);

                //     // ??????????????? ???????????????
                //     $fm = ApprovedHitsuy::
                //     // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                //     whereIn('wudabeType', ['???????????????', '????????? ?????????', '????????????????????????', '?????????', '???????????????'])
                //     ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])
                //     ->where('occupation', '????????? ???????????????')
                //     ->count();
                //     $cd = 0;
                //     $row_abalat_deant[13] += ($fm + $cd);

                //     // ????????????????????????
                //     $fm = ApprovedHitsuy::
                //     // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                //     whereIn('wudabeType', ['???????????????', '????????? ?????????', '????????????????????????', '?????????', '???????????????'])
                //     ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])
                //     ->where('occupation', '')
                //     ->count();
                //     $cd = 0;
                //     $row_abalat_deant[14] += ($fm + $cd);

                //     // ?????????
                //     $fm = ApprovedHitsuy::
                //     // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                //     whereIn('wudabeType', ['???????????????', '????????? ?????????', '????????????????????????', '?????????', '???????????????'])
                //     ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])
                //     ->where('occupation', '')
                //     ->count();
                //     $cd = 0;
                //     $row_abalat_deant[15] += ($fm + $cd);

                //     // girls
                //     $fm = ApprovedHitsuy::
                //     // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                //     whereIn('wudabeType', ['???????????????', '????????? ?????????', '????????????????????????', '?????????', '???????????????'])
                //     ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])
                //     ->where('gender', '??????')
                //     ->count();
                //     $cd = 0;
                //     $row_abalat_deant[16] += ($fm + $cd);

                //     // ?????????
                //     $fm = ApprovedHitsuy::
                //     // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                //     whereIn('wudabeType', ['???????????????', '????????? ?????????', '????????????????????????', '?????????', '???????????????'])
                //     ->where('zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])
                //     ->count();
                //     $cd = 0;
                //     $row_abalat_deant[17] += ($fm + $cd);

                // }
                //wahio count
                if($tabia['isUrban'] == '?????????'){

                    // ????????????
                    foreach (meseretawiWdabe::where('tabiaCode', $tabia['tabiaCode'])->where('type', '????????????')->get()->toArray() as $widabe){
                        $row_wahio_count[1] += Wahio::where('widabeCode', $widabe['widabeCode'])->count();
                    }

                    // ???????????????
                    foreach (meseretawiWdabe::where('tabiaCode', $tabia['tabiaCode'])->where('type', '???????????????')->get()->toArray() as $widabe){
                        $row_wahio_count[1] += Wahio::where('widabeCode', $widabe['widabeCode'])->count();
                    }

                    // ???/???????????????
                    foreach (meseretawiWdabe::where('tabiaCode', $tabia['tabiaCode'])->where('type', '???/???????????????')->get()->toArray() as $widabe){
                        $row_wahio_count[1] += Wahio::where('widabeCode', $widabe['widabeCode'])->count();
                    }

                    // ?????????
                    foreach (meseretawiWdabe::where('tabiaCode', $tabia['tabiaCode'])->where('type', '?????????')->get()->toArray() as $widabe){
                        $row_wahio_count[1] += Wahio::where('widabeCode', $widabe['widabeCode'])->count();
                    }

                    // ?????????
                    foreach (meseretawiWdabe::where('tabiaCode', $tabia['tabiaCode'])->get()->toArray() as $widabe){
                        $row_wahio_count[6] += Wahio::where('widabeCode', $widabe['widabeCode'])->count();
                    }
                }
                //plan deant
                // if($tabia['isUrban'] == '?????????'){
                //     //???????????????
                //     $row_plan_deant[2] += DB::table('meseretawi_wdabes')
                //     ->join('meseretawiwidabeaplans', 'meseretawi_wdabes.widabeCode', '=', 'meseretawiwidabeaplans.widabecode')
                //     ->where('meseretawi_wdabes.type', '???????????????')
                //     ->where('meseretawi_wdabes.tabiaCode', $tabia['tabiaCode'])
                //     ->where('meseretawiwidabeaplans.planyear',2011)->count();

                //     //????????? ?????????
                //     $row_plan_deant[4] += DB::table('meseretawi_wdabes')
                //     ->join('meseretawiwidabeaplans', 'meseretawi_wdabes.widabeCode', '=', 'meseretawiwidabeaplans.widabecode')
                //     ->where('meseretawi_wdabes.type', '????????? ?????????')
                //     ->where('meseretawi_wdabes.tabiaCode', $tabia['tabiaCode'])
                //     ->where('meseretawiwidabeaplans.planyear',2011)->count();

                //     //????????????????????????
                //     $row_plan_deant[6] += DB::table('meseretawi_wdabes')
                //     ->join('meseretawiwidabeaplans', 'meseretawi_wdabes.widabeCode', '=', 'meseretawiwidabeaplans.widabecode')
                //     ->where('meseretawi_wdabes.type', '????????????????????????')
                //     ->where('meseretawi_wdabes.tabiaCode', $tabia['tabiaCode'])
                //     ->where('meseretawiwidabeaplans.planyear',2011)->count();

                //     //?????????
                //     $row_plan_deant[8] += DB::table('meseretawi_wdabes')
                //     ->join('meseretawiwidabeaplans', 'meseretawi_wdabes.widabeCode', '=', 'meseretawiwidabeaplans.widabecode')
                //     ->where('meseretawi_wdabes.type', '?????????')
                //     ->where('meseretawi_wdabes.tabiaCode', $tabia['tabiaCode'])
                //     ->where('meseretawiwidabeaplans.planyear',2011)->count();

                //     //???????????????
                //     $row_plan_deant[10] += DB::table('meseretawi_wdabes')
                //     ->join('meseretawiwidabeaplans', 'meseretawi_wdabes.widabeCode', '=', 'meseretawiwidabeaplans.widabecode')
                //     ->where('meseretawi_wdabes.type', '???????????????')
                //     ->where('meseretawi_wdabes.tabiaCode', $tabia['tabiaCode'])
                //     ->where('meseretawiwidabeaplans.planyear',2011)->count();

                // }
                //plan non deant
                if($tabia['isUrban'] == '?????????'){
                    //???????????????
                    $row_plan_deant[2] += DB::table('meseretawi_wdabes')
                    ->join('meseretawiwidabeaplans', 'meseretawi_wdabes.widabeCode', '=', 'meseretawiwidabeaplans.widabecode')
                    ->where('meseretawi_wdabes.type', '???????????????')
                    ->where('meseretawi_wdabes.tabiaCode', $tabia['tabiaCode'])
                    ->where('meseretawiwidabeaplans.planyear',2011)->count();

                    //????????????
                    $row_plan_deant[4] += DB::table('meseretawi_wdabes')
                    ->join('meseretawiwidabeaplans', 'meseretawi_wdabes.widabeCode', '=', 'meseretawiwidabeaplans.widabecode')
                    ->where('meseretawi_wdabes.type', '????????????')
                    ->where('meseretawi_wdabes.tabiaCode', $tabia['tabiaCode'])
                    ->where('meseretawiwidabeaplans.planyear',2011)->count();

                    //???????????????
                    $row_plan_deant[6] += DB::table('meseretawi_wdabes')
                    ->join('meseretawiwidabeaplans', 'meseretawi_wdabes.widabeCode', '=', 'meseretawiwidabeaplans.widabecode')
                    ->where('meseretawi_wdabes.type', '???????????????')
                    ->where('meseretawi_wdabes.tabiaCode', $tabia['tabiaCode'])
                    ->where('meseretawiwidabeaplans.planyear',2011)->count();

                    //???/???????????????
                    $row_plan_deant[8] += DB::table('meseretawi_wdabes')
                    ->join('meseretawiwidabeaplans', 'meseretawi_wdabes.widabeCode', '=', 'meseretawiwidabeaplans.widabecode')
                    ->where('meseretawi_wdabes.type', '???/???????????????')
                    ->where('meseretawi_wdabes.tabiaCode', $tabia['tabiaCode'])
                    ->where('meseretawiwidabeaplans.planyear',2011)->count();

                }
                //plan all
                if($tabia['isUrban'] == '?????????'){
                    //??????????????? ?????????
                    $row_plan_all[2] += DB::table('approved_hitsuys')
                    ->join('individualplans', 'approved_hitsuys.hitsuyID', '=', 'individualplans.hitsuyID')
                    // ->where('meseretawi_wdabes.type', '?????????')
                    ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])
                    ->where('individualplans.year',2011)->count();


                    //??????????????? ?????????
                    $row_plan_all[5] += DB::table('meseretawi_wdabes')
                    ->join('meseretawiwidabeaplans', 'meseretawi_wdabes.widabeCode', '=', 'meseretawiwidabeaplans.widabecode')
                    // ->where('meseretawi_wdabes.type', '?????????')
                    ->where('meseretawi_wdabes.tabiaCode', $tabia['tabiaCode'])
                    ->where('meseretawiwidabeaplans.planyear',2011)->count();

                }
                //model members
                if($tabia['isUrban'] == '?????????'){

                }
                //new candidates non deant
                if($tabia['isUrban'] == '?????????'){
                    // ????????? ???????????????
                    $row_new_members_non_deant[2] += Hitsuy::where('hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->where('occupation', '????????? ???????????????')->count();

                    // ????????????
                    $row_new_members_non_deant[4] += Hitsuy::where('hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->where('occupation', '????????????')->count();

                    // ???????????????
                    $row_new_members_non_deant[6] += Hitsuy::where('hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->where('occupation', '???????????????')->count();

                    // ????????????
                    $row_new_members_non_deant[8] += Hitsuy::where('hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->where('occupation', '????????????')->count();
                }
                //new candidates deant
                if($tabia['isUrban'] == '?????????'){
                    // ???????????????
                    $row_new_members_deant[2] += Hitsuy::where('hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->where('occupation', '???????????????')->count();

                    // ????????? ?????????
                    $row_new_members_deant[4] += Hitsuy::where('hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->where('occupation', '????????? ?????????')->count();

                    // ????????????????????????
                    $row_new_members_deant[6] += Hitsuy::where('hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->where('occupation', '????????????????????????')->count();

                    // ?????????
                    $row_new_members_deant[8] += Hitsuy::where('hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->where('occupation', '?????????')->count();

                    // ???????????????
                    $row_new_members_deant[8] += Hitsuy::where('hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->where('hitsuy_status', '?????????')->where('occupation', '???????????????')->count();
                }
                //new approved members
                if($tabia['isUrban'] == '?????????'){
                    // ???????????? ???????????????
                    $row_approved_new_members[1] += Hitsuy::where('hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->whereBetween('regDate',[Carbon::createFromDate(2004,1,1)->subMonths(6), Carbon::createFromDate(2004,3,30)->subMonths(6)])->where('gender', '??????')->count();
                    $row_approved_new_members[2] += Hitsuy::where('hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->whereBetween('regDate',[Carbon::createFromDate(2004,1,1)->subMonths(6), Carbon::createFromDate(2004,3,30)->subMonths(6)])->where('gender', '??????')->count();
                    $row_approved_new_members[3] += Hitsuy::where('hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->whereBetween('regDate',[Carbon::createFromDate(2004,1,1)->subMonths(6), Carbon::createFromDate(2004,3,30)->subMonths(6)])->count();

                    // ?????????
                    $row_approved_new_members[4] += Hitsuy::where('hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->whereBetween('regDate',[Carbon::createFromDate(2004,1,1)->subMonths(6), Carbon::createFromDate(2004,3,30)->subMonths(6)])->where('hitsuy_status', '?????????')->where('gender', '??????')->count();
                    $row_approved_new_members[5] += Hitsuy::where('hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->whereBetween('regDate',[Carbon::createFromDate(2004,1,1)->subMonths(6), Carbon::createFromDate(2004,3,30)->subMonths(6)])->where('hitsuy_status', '?????????')->where('gender', '??????')->count();
                    $row_approved_new_members[6] += Hitsuy::where('hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->whereBetween('regDate',[Carbon::createFromDate(2004,1,1)->subMonths(6), Carbon::createFromDate(2004,3,30)->subMonths(6)])->where('hitsuy_status', '?????????')->count();

                    // ??????????????? TODO
                    $row_approved_new_members[8] += Hitsuy::where('hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->whereBetween('regDate',[Carbon::createFromDate(2004,1,1)->subMonths(6), Carbon::createFromDate(2004,3,30)->subMonths(6)])->where('hitsuy_status', '?????????')->where('gender', '??????')->count();
                    $row_approved_new_members[9] += Hitsuy::where('hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->whereBetween('regDate',[Carbon::createFromDate(2004,1,1)->subMonths(6), Carbon::createFromDate(2004,3,30)->subMonths(6)])->where('hitsuy_status', '?????????')->where('gender', '??????')->count();
                    $row_approved_new_members[10] += Hitsuy::where('hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->whereBetween('regDate',[Carbon::createFromDate(2004,1,1)->subMonths(6), Carbon::createFromDate(2004,3,30)->subMonths(6)])->where('hitsuy_status', '?????????')->count();

                    // ???????????? ???????????????
                    $row_approved_new_members[11] += Hitsuy::where('hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'].'/%')->whereBetween('regDate',[Carbon::createFromDate(2004,3,30)->subMonths(6), Carbon::createFromDate(9000,1,1)->subMonths(6)])->where('hitsuy_status', '?????????')->count();
                }
                //grades
                if($tabia['isUrban'] == '?????????'){

                    // deant
                    {
                        // total
                        $row_grades[1] += DB::table('approved_hitsuys')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->whereIn('hitsuys.occupation', ['????????????????????????','????????? ?????????', '???????????????', '?????????', '???????????????'])
                        ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])
                        ->count();
                        // deant A
                        {
                            $sl = DB::table('super_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->whereIn('hitsuys.occupation', ['????????????????????????','????????? ?????????', '???????????????', '?????????', '???????????????'])
                            ->whereBetween('super_leaders.sum', [$A_LOWER, $A_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ml = DB::table('middle_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->whereIn('hitsuys.occupation', ['????????????????????????','????????? ?????????', '???????????????', '?????????', '???????????????'])
                            ->whereBetween('middle_leaders.sum', [$A_LOWER, $A_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ll = DB::table('lower_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->whereIn('hitsuys.occupation', ['????????????????????????','????????? ?????????', '???????????????', '?????????', '???????????????'])
                            ->where('lower_leaders.evaluation', 'A')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $fil = DB::table('first_instant_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->whereIn('hitsuys.occupation', ['????????????????????????','????????? ?????????', '???????????????', '?????????', '???????????????'])
                            ->whereBetween('first_instant_leaders.sum', [$A_LOWER, $A_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ex = DB::table('experts')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'experts.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->whereIn('hitsuys.occupation', ['????????????????????????','????????? ?????????', '???????????????', '?????????', '???????????????'])
                            ->whereBetween('experts.sum', [$A_LOWER, $A_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $tm = DB::table('tara_members')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->whereIn('hitsuys.occupation', ['????????????????????????','????????? ?????????', '???????????????', '?????????', '???????????????'])
                            ->where('tara_members.evaluation', 'A')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $row_grades[2] += $sl + $ml + $ll + $fil + $ex + $tm;
                        }
                        // deant B
                        {
                            $sl = DB::table('super_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->whereIn('hitsuys.occupation', ['????????????????????????','????????? ?????????', '???????????????', '?????????', '???????????????'])
                            ->whereBetween('super_leaders.sum', [$B_LOWER, $B_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ml = DB::table('middle_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->whereIn('hitsuys.occupation', ['????????????????????????','????????? ?????????', '???????????????', '?????????', '???????????????'])
                            ->whereBetween('middle_leaders.sum', [$B_LOWER, $B_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ll = DB::table('lower_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->whereIn('hitsuys.occupation', ['????????????????????????','????????? ?????????', '???????????????', '?????????', '???????????????'])
                            ->where('lower_leaders.evaluation', 'B')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $fil = DB::table('first_instant_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->whereIn('hitsuys.occupation', ['????????????????????????','????????? ?????????', '???????????????', '?????????', '???????????????'])
                            ->whereBetween('first_instant_leaders.sum', [$B_LOWER, $B_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ex = DB::table('experts')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'experts.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->whereIn('hitsuys.occupation', ['????????????????????????','????????? ?????????', '???????????????', '?????????', '???????????????'])
                            ->whereBetween('experts.sum', [$B_LOWER, $B_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $tm = DB::table('tara_members')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->whereIn('hitsuys.occupation', ['????????????????????????','????????? ?????????', '???????????????', '?????????', '???????????????'])
                            ->where('tara_members.evaluation', 'B')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $row_grades[3] += $sl + $ml + $ll + $fil + $ex + $tm;
                        }
                        // deant C
                        {
                            $sl = DB::table('super_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->whereIn('hitsuys.occupation', ['????????????????????????','????????? ?????????', '???????????????', '?????????', '???????????????'])
                            ->whereBetween('super_leaders.sum', [$C_LOWER, $C_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ml = DB::table('middle_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->whereIn('hitsuys.occupation', ['????????????????????????','????????? ?????????', '???????????????', '?????????', '???????????????'])
                            ->whereBetween('middle_leaders.sum', [$C_LOWER, $C_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ll = DB::table('lower_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->whereIn('hitsuys.occupation', ['????????????????????????','????????? ?????????', '???????????????', '?????????', '???????????????'])
                            ->where('lower_leaders.evaluation', 'C')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $fil = DB::table('first_instant_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->whereIn('hitsuys.occupation', ['????????????????????????','????????? ?????????', '???????????????', '?????????', '???????????????'])
                            ->whereBetween('first_instant_leaders.sum', [$C_LOWER, $C_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ex = DB::table('experts')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'experts.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->whereIn('hitsuys.occupation', ['????????????????????????','????????? ?????????', '???????????????', '?????????', '???????????????'])
                            ->whereBetween('experts.sum', [$C_LOWER, $C_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $tm = DB::table('tara_members')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->whereIn('hitsuys.occupation', ['????????????????????????','????????? ?????????', '???????????????', '?????????', '???????????????'])
                            ->where('tara_members.evaluation', 'C')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $row_grades[4] += $sl + $ml + $ll + $fil + $ex + $tm;
                        }
                    }
                    // ?????????
                    {
                        // total
                        $row_grades[6] += DB::table('approved_hitsuys')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', '????????????')
                        ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])
                        ->count();
                        // ????????? A
                        {
                            $sl = DB::table('super_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->whereBetween('super_leaders.sum', [$A_LOWER, $A_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ml = DB::table('middle_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->whereBetween('middle_leaders.sum', [$A_LOWER, $A_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ll = DB::table('lower_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->where('lower_leaders.evaluation', 'A')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $fil = DB::table('first_instant_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->whereBetween('first_instant_leaders.sum', [$A_LOWER, $A_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ex = DB::table('experts')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'experts.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->whereBetween('experts.sum', [$A_LOWER, $A_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $tm = DB::table('tara_members')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->where('tara_members.evaluation', 'A')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $row_grades[7] += $sl + $ml + $ll + $fil + $ex + $tm;
                        }
                        // ????????? B
                        {
                            $sl = DB::table('super_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->whereBetween('super_leaders.sum', [$B_LOWER, $B_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ml = DB::table('middle_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->whereBetween('middle_leaders.sum', [$B_LOWER, $B_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ll = DB::table('lower_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->where('lower_leaders.evaluation', 'B')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $fil = DB::table('first_instant_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->whereBetween('first_instant_leaders.sum', [$B_LOWER, $B_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ex = DB::table('experts')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'experts.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->whereBetween('experts.sum', [$B_LOWER, $B_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $tm = DB::table('tara_members')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->where('tara_members.evaluation', 'B')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $row_grades[8] += $sl + $ml + $ll + $fil + $ex + $tm;
                        }
                        // ????????? C
                        {
                            $sl = DB::table('super_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->whereBetween('super_leaders.sum', [$C_LOWER, $C_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ml = DB::table('middle_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->whereBetween('middle_leaders.sum', [$C_LOWER, $C_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ll = DB::table('lower_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->where('lower_leaders.evaluation', 'C')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $fil = DB::table('first_instant_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->whereBetween('first_instant_leaders.sum', [$C_LOWER, $C_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ex = DB::table('experts')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'experts.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->whereBetween('experts.sum', [$C_LOWER, $C_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $tm = DB::table('tara_members')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->where('tara_members.evaluation', 'C')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $row_grades[9] += $sl + $ml + $ll + $fil + $ex + $tm;
                        }
                    }
                    // ?????? ??????
                    {
                        // total
                        $row_grades[11] += DB::table('approved_hitsuys')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', '????????? ???????????????')
                        ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])
                        ->count();
                        // ?????? ?????? A
                        {
                            $sl = DB::table('super_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????? ???????????????')
                            ->whereBetween('super_leaders.sum', [$A_LOWER, $A_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ml = DB::table('middle_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????? ???????????????')
                            ->whereBetween('middle_leaders.sum', [$A_LOWER, $A_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ll = DB::table('lower_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????? ???????????????')
                            ->where('lower_leaders.evaluation', 'A')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $fil = DB::table('first_instant_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????? ???????????????')
                            ->whereBetween('first_instant_leaders.sum', [$A_LOWER, $A_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ex = DB::table('experts')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'experts.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????? ???????????????')
                            ->whereBetween('experts.sum', [$A_LOWER, $A_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $tm = DB::table('tara_members')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????? ???????????????')
                            ->where('tara_members.evaluation', 'A')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $row_grades[12] += $sl + $ml + $ll + $fil + $ex + $tm;
                        }
                        // ?????? ?????? B
                        {
                            $sl = DB::table('super_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????? ???????????????')
                            ->whereBetween('super_leaders.sum', [$B_LOWER, $B_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ml = DB::table('middle_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????? ???????????????')
                            ->whereBetween('middle_leaders.sum', [$B_LOWER, $B_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ll = DB::table('lower_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????? ???????????????')
                            ->where('lower_leaders.evaluation', 'B')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $fil = DB::table('first_instant_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????? ???????????????')
                            ->whereBetween('first_instant_leaders.sum', [$B_LOWER, $B_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ex = DB::table('experts')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'experts.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????? ???????????????')
                            ->whereBetween('experts.sum', [$B_LOWER, $B_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $tm = DB::table('tara_members')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????? ???????????????')
                            ->where('tara_members.evaluation', 'B')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $row_grades[13] += $sl + $ml + $ll + $fil + $ex + $tm;
                        }
                        // ?????? ?????? C
                        {
                            $sl = DB::table('super_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????? ???????????????')
                            ->whereBetween('super_leaders.sum', [$C_LOWER, $C_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ml = DB::table('middle_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????? ???????????????')
                            ->whereBetween('middle_leaders.sum', [$C_LOWER, $C_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ll = DB::table('lower_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????? ???????????????')
                            ->where('lower_leaders.evaluation', 'C')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $fil = DB::table('first_instant_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????? ???????????????')
                            ->whereBetween('first_instant_leaders.sum', [$C_LOWER, $C_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ex = DB::table('experts')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'experts.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????? ???????????????')
                            ->whereBetween('experts.sum', [$C_LOWER, $C_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $tm = DB::table('tara_members')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????? ???????????????')
                            ->where('tara_members.evaluation', 'C')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $row_grades[14] += $sl + $ml + $ll + $fil + $ex + $tm;
                        }
                    }
                    // ???????????????
                    {
                        // total
                        $row_grades[16] += DB::table('approved_hitsuys')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', '????????????')
                        ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])
                        ->count();
                        // ??????????????? A
                        {
                            $sl = DB::table('super_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->whereBetween('super_leaders.sum', [$A_LOWER, $A_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ml = DB::table('middle_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->whereBetween('middle_leaders.sum', [$A_LOWER, $A_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ll = DB::table('lower_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->where('lower_leaders.evaluation', 'A')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $fil = DB::table('first_instant_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->whereBetween('first_instant_leaders.sum', [$A_LOWER, $A_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ex = DB::table('experts')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'experts.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->whereBetween('experts.sum', [$A_LOWER, $A_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $tm = DB::table('tara_members')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->where('tara_members.evaluation', 'A')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $row_grades[17] += $sl + $ml + $ll + $fil + $ex + $tm;
                        }
                        // ??????????????? B
                        {
                            $sl = DB::table('super_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->whereBetween('super_leaders.sum', [$B_LOWER, $B_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ml = DB::table('middle_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->whereBetween('middle_leaders.sum', [$B_LOWER, $B_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ll = DB::table('lower_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->where('lower_leaders.evaluation', 'B')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $fil = DB::table('first_instant_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->whereBetween('first_instant_leaders.sum', [$B_LOWER, $B_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ex = DB::table('experts')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'experts.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->whereBetween('experts.sum', [$B_LOWER, $B_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $tm = DB::table('tara_members')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->where('tara_members.evaluation', 'B')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $row_grades[18] += $sl + $ml + $ll + $fil + $ex + $tm;
                        }
                        // ??????????????? C
                        {
                            $sl = DB::table('super_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->whereBetween('super_leaders.sum', [$C_LOWER, $C_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ml = DB::table('middle_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->whereBetween('middle_leaders.sum', [$C_LOWER, $C_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ll = DB::table('lower_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->where('lower_leaders.evaluation', 'C')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $fil = DB::table('first_instant_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->whereBetween('first_instant_leaders.sum', [$C_LOWER, $C_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ex = DB::table('experts')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'experts.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->whereBetween('experts.sum', [$C_LOWER, $C_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $tm = DB::table('tara_members')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '????????????')
                            ->where('tara_members.evaluation', 'C')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $row_grades[19] += $sl + $ml + $ll + $fil + $ex + $tm;
                        }
                    }
                    // ????????????
                    {
                        // total
                        $row_grades[21] += DB::table('approved_hitsuys')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', '???????????????')
                        ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])
                        ->count();
                        // ???????????? A
                        {
                            $sl = DB::table('super_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '???????????????')
                            ->whereBetween('super_leaders.sum', [$A_LOWER, $A_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ml = DB::table('middle_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '???????????????')
                            ->whereBetween('middle_leaders.sum', [$A_LOWER, $A_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ll = DB::table('lower_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '???????????????')
                            ->where('lower_leaders.evaluation', 'A')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $fil = DB::table('first_instant_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '???????????????')
                            ->whereBetween('first_instant_leaders.sum', [$A_LOWER, $A_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ex = DB::table('experts')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'experts.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '???????????????')
                            ->whereBetween('experts.sum', [$A_LOWER, $A_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $tm = DB::table('tara_members')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '???????????????')
                            ->where('tara_members.evaluation', 'A')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $row_grades[22] += $sl + $ml + $ll + $fil + $ex + $tm;
                        }
                        // ???????????? B
                        {
                            $sl = DB::table('super_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '???????????????')
                            ->whereBetween('super_leaders.sum', [$B_LOWER, $B_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ml = DB::table('middle_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '???????????????')
                            ->whereBetween('middle_leaders.sum', [$B_LOWER, $B_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ll = DB::table('lower_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '???????????????')
                            ->where('lower_leaders.evaluation', 'B')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $fil = DB::table('first_instant_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '???????????????')
                            ->whereBetween('first_instant_leaders.sum', [$B_LOWER, $B_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ex = DB::table('experts')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'experts.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '???????????????')
                            ->whereBetween('experts.sum', [$B_LOWER, $B_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $tm = DB::table('tara_members')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '???????????????')
                            ->where('tara_members.evaluation', 'B')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $row_grades[23] += $sl + $ml + $ll + $fil + $ex + $tm;
                        }
                        // ???????????? C
                        {
                            $sl = DB::table('super_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '???????????????')
                            ->whereBetween('super_leaders.sum', [$C_LOWER, $C_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ml = DB::table('middle_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '???????????????')
                            ->whereBetween('middle_leaders.sum', [$C_LOWER, $C_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ll = DB::table('lower_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '???????????????')
                            ->where('lower_leaders.evaluation', 'C')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $fil = DB::table('first_instant_leaders')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '???????????????')
                            ->whereBetween('first_instant_leaders.sum', [$C_LOWER, $C_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $ex = DB::table('experts')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'experts.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '???????????????')
                            ->whereBetween('experts.sum', [$C_LOWER, $C_UPPER])
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $tm = DB::table('tara_members')
                            ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                            ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                            // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                            ->where('hitsuys.occupation', '???????????????')
                            ->where('tara_members.evaluation', 'C')
                            ->where('approved_hitsuys.zoneworedaCode', $zoneCode.$woreda['woredaCode'].$tabia['tabiaCode'])->count();

                            $row_grades[24] += $sl + $ml + $ll + $fil + $ex + $tm;
                        }
                    }
                }
            }
            $row_abalat_age_education[15] = $row_abalat_age_education[7] + $row_abalat_age_education[8] + $row_abalat_age_education[9] + $row_abalat_age_education[10] + $row_abalat_age_education[11] + $row_abalat_age_education[12] + $row_abalat_age_education[13] + $row_abalat_age_education[14];
            $row_wahio_count[5] = $row_wahio_count[1] + $row_wahio_count[2] + $row_wahio_count[3] + $row_wahio_count[4];
            $row_tabia_count[4] = $row_tabia_count[2] + $row_tabia_count[3];
            $row_tabia_count[10] = $row_tabia_count[5] + $row_tabia_count[6] + $row_tabia_count[7] + $row_tabia_count[8] + $row_tabia_count[9];
            $row_tabia_count[15] = $row_tabia_count[11] + $row_tabia_count[12] + $row_tabia_count[13] + $row_tabia_count[14];
            $row_grades[5] =  $row_grades[1] - ($row_grades[2] + $row_grades[3] + $row_grades[4]);
            $row_grades[10] =  $row_grades[6] - ($row_grades[7] + $row_grades[8] + $row_grades[9]);
            $row_grades[15] =  $row_grades[11] - ($row_grades[12] + $row_grades[13] + $row_grades[14]);
            $row_grades[20] =  $row_grades[16] - ($row_grades[17] + $row_grades[18] + $row_grades[19]);
            $row_grades[25] =  $row_grades[21] - ($row_grades[22] + $row_grades[23] + $row_grades[24]);

            $abalat_age_education[] = $row_abalat_age_education;
            $abalat_mahberawi_bota[] = $row_abalat_mahberawi_bota;
            $abalat_deant[] = $row_abalat_deant;
            $wahio_count[] = $row_wahio_count;
            $tabia_count[] = $row_tabia_count;
            $plan_deant[] = $row_plan_deant;
            $plan_non_deant[] = $row_plan_non_deant;
            $plan_all[] = $row_plan_all;
            $model_members[] = $row_model_members;
            $new_members_non_deant[] = $row_new_members_non_deant;
            $new_members_deant[] = $row_new_members_deant;
            $approved_new_members[] = $row_approved_new_members;
            $grades[] = $row_grades;
            
        }
        return view('report.geter', compact('zoneName', 'abalat_age_education', 'abalat_mahberawi_bota', 'abalat_deant', 'wahio_count', 'tabia_count', 'plan_deant', 'plan_non_deant', 'plan_all', 'model_members', 'new_members_non_deant', 'new_members_deant', 'approved_new_members', 'grades'));
    }
}

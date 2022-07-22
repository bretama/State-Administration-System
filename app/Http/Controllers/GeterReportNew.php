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
use App\Penalty;
use App\Transfer;
use App\Dismiss;
use App\DateConvert;
use DB;

use Carbon\Carbon;

class GeterReportNew extends Controller
{
    public function index(Request $request){

        $zoneCode = Auth::user()->area;
        $zobadatas = '';
        if(Auth::user()->usertype == 'admin'){
            $zobadatas = DB::table("zobatats")->pluck("zoneName","zoneCode");
            
            if(!$request->zone){
                $zoneCode = DB::table("zobatats")->pluck("zoneCode")->first();
            }
            else
                $zoneCode = $request->zone;
        }

        $today = DateConvert::toEthiopian(date('d/m/Y'));
        $quarter = [];
        $st = (int)(((int)explode("/", $today)[1]-1)/3)*3+1;
        $quarter_names = ['3 ወርሒ', '6 ወርሒ', '9 ወርሒ', 'ዓመት'];
        $this_quarter = $quarter_names[(($st-1)/3)];
        $quarter[] = DateConvert::toGregorian('1/' . $st . '/' .explode("/", $today)[2]);
        $quarter[] = DateConvert::toGregorian('1/' . ($st+3) . '/' . explode("/", $today)[2]);
        $A_UPPER = 100;
        $A_LOWER = 80;
        $B_UPPER = 79;
        $B_LOWER = 50;
        $C_UPPER = 49;
        $C_LOWER = 0;
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
        $plan_non_deant = [];
        $plan_all = [];
        $model_members = [];
        $new_members_non_deant = [];
        $approved_new_members = [];
        $grades = [];
        $punishment = [];
        foreach ($woredas as $woreda) {
            $row_weseking_gudletin = [$woreda['name'], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            $row_abalat_age_education = [$woreda['name'], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            $row_abalat_mahberawi_bota = [$woreda['name'], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            $row_abalat_deant = [$woreda['name'], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            $row_wahio_count = [$woreda['name'], 0, 0, 0, 0, 0, 0];
            $row_tabia_count = [$woreda['name'], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,];
            $row_plan_non_deant = [$woreda['name'], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,];
            $row_plan_all = [$woreda['name'], 0, 0, 0, 0, 0, 0, 0, 0, 0, ''];
            $row_model_members = [$woreda['name'], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, ''];
            $row_new_members_non_deant = [$woreda['name'], 0, 0, 0, 0, 0, 0, 0, 0, 0];
            $row_approved_new_members = [$woreda['name'], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            $row_grades = [$woreda['name'], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,];
            $row_punishment = [$woreda['name'], 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            // Wesekin gudletin table
            {
                $row_weseking_gudletin[2] = DB::table('hitsuys')
                    ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT('tabias.parentcode', tabias.tabiaCode, '%')"))
                    ->where('tabias.isUrban', '=', 'ገጠር')
                    ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                    ->where('hitsuy_status', 'ሕፁይ')
                    ->whereBetween('hitsuys.created_at', [Carbon::createFromDate(explode("/", $quarter[0])[2], explode("/", $quarter[0])[1], explode("/", $quarter[0])[0]), Carbon::createFromDate(explode("/", $quarter[1])[2], explode("/", $quarter[1])[1], explode("/", $quarter[1])[0])])
                    ->count();

                $row_weseking_gudletin[3] = DB::table('transfers')
                    ->where('transfers.zone', $zoneCode)
                    ->where('transfers.woreda', $woreda['woredaCode'])
                    ->join('tabias', 'transfers.tabia', '=', 'tabias.tabiaCode')
                    ->where('tabias.isUrban', '=', 'ገጠር')
                    ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                    ->whereBetween('transfers.created_at', [Carbon::createFromDate(explode("/", $quarter[0])[2], explode("/", $quarter[0])[1], explode("/", $quarter[0])[0]), Carbon::createFromDate(explode("/", $quarter[1])[2], explode("/", $quarter[1])[1], explode("/", $quarter[1])[0])])
                    ->count();

                // TODO ተኣጊዱ ዝነበረ
                $row_weseking_gudletin[4] = 0;

                $row_weseking_gudletin[5] = $row_weseking_gudletin[2] + $row_weseking_gudletin[3] + $row_weseking_gudletin[4];

                $row_weseking_gudletin[6] = DB::table('dismisses')
                    ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'dismisses.hitsuyID')
                    ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                    // ->where('approved_hitsuys.zoneworedaCode', '=', '010080034')
                    ->where('dismissReason', 'ብሞት')
                    ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                    ->where('tabias.isUrban', '=', 'ገጠር')
                    ->whereBetween('dismisses.created_at', [Carbon::createFromDate(explode("/", $quarter[0])[2], explode("/", $quarter[0])[1], explode("/", $quarter[0])[0]), Carbon::createFromDate(explode("/", $quarter[1])[2], explode("/", $quarter[1])[1], explode("/", $quarter[1])[0])])
                    ->count();


                $row_weseking_gudletin[7] = DB::table('dismisses')
                    ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'dismisses.hitsuyID')
                    ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                    // ->where('approved_hitsuys.zoneworedaCode', '=', '010080034')
                    ->where('dismissReason', 'ብቕፅዓት')
                    ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                    ->where('tabias.isUrban', '=', 'ገጠር')
                    ->whereBetween('dismisses.created_at', [Carbon::createFromDate(explode("/", $quarter[0])[2], explode("/", $quarter[0])[1], explode("/", $quarter[0])[0]), Carbon::createFromDate(explode("/", $quarter[1])[2], explode("/", $quarter[1])[1], explode("/", $quarter[1])[0])])
                    ->count();

                $row_weseking_gudletin[8] = DB::table('penalties')
                    ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'penalties.hitsuyID')
                    ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                    ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                    // ->where('approved_hitsuys.zoneworedaCode', '=', '010080034')
                    ->where('penaltyGiven', 'ካብ ኣባልነት ንውሱን ጊዜ ምእጋድ')
                    ->where('hitsuys.hitsuy_status', 'ዝተኣገደ')
                    ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                    ->where('tabias.isUrban', '=', 'ገጠር')
                    ->whereBetween('penalties.created_at', [Carbon::createFromDate(explode("/", $quarter[0])[2], explode("/", $quarter[0])[1], explode("/", $quarter[0])[0]), Carbon::createFromDate(explode("/", $quarter[1])[2], explode("/", $quarter[1])[1], explode("/", $quarter[1])[0])])
                    ->count();

                //TODO University
                $row_weseking_gudletin[9] = Transfer::where('oldzone', $zoneCode)
                    ->where('oldworeda', $woreda['woredaCode'])
                    ->whereBetween('created_at', [Carbon::createFromDate(explode("/", $quarter[0])[2], explode("/", $quarter[0])[1], explode("/", $quarter[0])[0]), Carbon::createFromDate(explode("/", $quarter[1])[2], explode("/", $quarter[1])[1], explode("/", $quarter[1])[0])])
                    ->count();

                $row_weseking_gudletin[10] = Transfer::where('oldzone', $zoneCode)
                    ->where('oldworeda', $woreda['woredaCode'])
                    ->whereBetween('created_at', [Carbon::createFromDate(explode("/", $quarter[0])[2], explode("/", $quarter[0])[1], explode("/", $quarter[0])[0]), Carbon::createFromDate(explode("/", $quarter[1])[2], explode("/", $quarter[1])[1], explode("/", $quarter[1])[0])])
                    ->count();

                $row_weseking_gudletin[11] = Transfer::where('oldzone', $zoneCode)
                    ->where('oldworeda', $woreda['woredaCode'])
                    ->where('zone','NOT', $zoneCode)
                    ->whereBetween('created_at', [Carbon::createFromDate(explode("/", $quarter[0])[2], explode("/", $quarter[0])[1], explode("/", $quarter[0])[0]), Carbon::createFromDate(explode("/", $quarter[1])[2], explode("/", $quarter[1])[1], explode("/", $quarter[1])[0])])
                    ->count();

                //TODO out of region
                $row_weseking_gudletin[12] = Transfer::where('oldzone', $zoneCode)
                ->where('oldworeda', $woreda['woredaCode'])
                ->where('zone','NOT', $zoneCode)
                ->whereBetween('created_at', [Carbon::createFromDate(explode("/", $quarter[0])[2], explode("/", $quarter[0])[1], explode("/", $quarter[0])[0]), Carbon::createFromDate(explode("/", $quarter[1])[2], explode("/", $quarter[1])[1], explode("/", $quarter[1])[0])])
                ->count();

                $row_weseking_gudletin[13] = DB::table('dismisses')
                    ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'dismisses.hitsuyID')
                    ->whereIn('dismissReason', ['ናይ ውልቀ ሰብ ሕቶ', 'ብኽብሪ' , 'ካሊእ'])
                    ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                    ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                    ->where('tabias.isUrban', '=', 'ገጠር')
                    ->whereBetween('dismisses.created_at', [Carbon::createFromDate(explode("/", $quarter[0])[2], explode("/", $quarter[0])[1], explode("/", $quarter[0])[0]), Carbon::createFromDate(explode("/", $quarter[1])[2], explode("/", $quarter[1])[1], explode("/", $quarter[1])[0])])
                    ->count();

                $row_weseking_gudletin[14] = $row_weseking_gudletin[6] + $row_weseking_gudletin[7] + $row_weseking_gudletin[8] + $row_weseking_gudletin[9] + $row_weseking_gudletin[10] + $row_weseking_gudletin[11] + $row_weseking_gudletin[12] + $row_weseking_gudletin[13];
                $row_weseking_gudletin[15] = $row_weseking_gudletin[5] - $row_weseking_gudletin[14];

                $row_weseking_gudletin[16] = DB::table('approved_hitsuys')
                    ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                    ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    ->where('hitsuys.hitsuy_status', 'ኣባል')
                    ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                    ->where('tabias.isUrban', '=', 'ገጠር')
                    ->count()
                + DB::table('hitsuys')
                    ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode, '/%')"))
                    ->where('hitsuy_status', 'ሕፁይ')
                    ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                    ->where('tabias.isUrban', '=', 'ገጠር')
                    ->count();
            }
            //abalat age, education
            {
                $fm = ApprovedHitsuy::where('zoneworedaCode', 'LIKE', $zoneCode.$woreda['woredaCode'].'%')->count();
                $cd = Hitsuy::where('hitsuyID','LIKE', $zoneCode.$woreda['woredaCode'].'%')->where('hitsuy_status', 'ሕፁይ')->count();
                $row_abalat_age_education[1] = $fm;
                $row_abalat_age_education[2] = $cd;
                $row_abalat_age_education[3] = ($fm + $cd);

                // 18 - 35
                $fm = DB::table('approved_hitsuys')->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->whereBetween('hitsuys.dob', [Carbon::today()->subYears(35), Carbon::today()->subYears(18)])
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->count();
                $cd = Hitsuy::whereBetween('hitsuys.dob', [Carbon::today()->subYears(35), Carbon::today()->subYears(18)])
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode,'/%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('hitsuy_status', 'ሕፁይ')
                ->count();
                $row_abalat_age_education[4] = ($fm + $cd);

                // 36 - 60
                $fm = DB::table('approved_hitsuys')->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->whereBetween('hitsuys.dob', [Carbon::today()->subYears(60), Carbon::today()->subYears(35)])
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->count();
                $cd = Hitsuy::whereBetween('hitsuys.dob', [Carbon::today()->subYears(60), Carbon::today()->subYears(35)])
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode,'/%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('hitsuy_status', 'ሕፁይ')
                ->count();
                $row_abalat_age_education[5] = ($fm + $cd);

                // above 60
                $fm = DB::table('approved_hitsuys')->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->whereBetween('hitsuys.dob', [Carbon::today()->subYears(150), Carbon::today()->subYears(60)])
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->count();
                $cd = Hitsuy::whereBetween('hitsuys.dob', [Carbon::today()->subYears(150), Carbon::today()->subYears(60)])
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode,'/%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('hitsuy_status', 'ሕፁይ')
                ->count();
                $row_abalat_age_education[6] = ($fm + $cd);

                // illiterate [education]
                $fm = DB::table('approved_hitsuys')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->where('hitsuys.educationlevel', 'ዘይተምሃረ')
                ->count();
                $cd = Hitsuy::where('hitsuys.educationlevel', 'ዘይተምሃረ')
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode,'/%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('hitsuy_status', 'ሕፁይ')
                ->count();
                $row_abalat_age_education[7] = ($fm + $cd);

                // 1-8 [education]
                $fm = DB::table('approved_hitsuys')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->whereIn('hitsuys.educationlevel', [1, 8])
                ->count();
                $cd = Hitsuy::whereIn('hitsuys.educationlevel', [1, 8])
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode,'/%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('hitsuy_status', 'ሕፁይ')
                ->count();
                $row_abalat_age_education[8] = ($fm + $cd);

                // 9-12 [education]
                $fm = DB::table('approved_hitsuys')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->whereIn('hitsuys.educationlevel', [9, 12])
                ->count();
                $cd = Hitsuy::whereIn('hitsuys.educationlevel', [9, 12])
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode,'/%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('hitsuy_status', 'ሕፁይ')
                ->count();
                $row_abalat_age_education[9] = ($fm + $cd);

                // certificate [education]
                $fm = DB::table('approved_hitsuys')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->where('hitsuys.educationlevel', 'ሰርቲፊኬት')
                ->count();
                $cd = Hitsuy::where('hitsuys.educationlevel', 'ሰርቲፊኬት')
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode,'/%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('hitsuy_status', 'ሕፁይ')
                ->count();
                $row_abalat_age_education[10] = ($fm + $cd);

                // diploma [education]
                $fm = DB::table('approved_hitsuys')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->where('hitsuys.educationlevel', 'ዲፕሎማ')
                ->count();
                $cd = Hitsuy::where('hitsuys.educationlevel', 'ዲፕሎማ')
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode,'/%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('hitsuy_status', 'ሕፁይ')
                ->count();
                $row_abalat_age_education[11] = ($fm + $cd);


                // degree [education]
                $fm = DB::table('approved_hitsuys')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->where('hitsuys.educationlevel', 'ዲግሪ')
                ->count();
                $cd = Hitsuy::where('hitsuys.educationlevel', 'ዲግሪ')
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode,'/%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('hitsuy_status', 'ሕፁይ')
                ->count();
                $row_abalat_age_education[12] = ($fm + $cd);

                // master's [education]
                $fm = DB::table('approved_hitsuys')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->where('hitsuys.educationlevel', 'ማስተርስ')
                ->count();
                $cd = Hitsuy::where('hitsuys.educationlevel', 'ማስተርስ')
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode,'/%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('hitsuy_status', 'ሕፁይ')
                ->count();
                $row_abalat_age_education[13] = ($fm + $cd);

                // PhD [education]
                $fm = DB::table('approved_hitsuys')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->where('hitsuys.educationlevel', 'ዶክተር')
                ->count();
                $cd = Hitsuy::where('hitsuys.educationlevel', 'ዶክተር')
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode,'/%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('hitsuy_status', 'ሕፁይ')
                ->count();
                $row_abalat_age_education[14] = ($fm + $cd);

                // Sum
                $row_abalat_age_education[15] = $row_abalat_age_education[7] + $row_abalat_age_education[8] + $row_abalat_age_education[9] + $row_abalat_age_education[10] + $row_abalat_age_education[11] + $row_abalat_age_education[12] + $row_abalat_age_education[13] + $row_abalat_age_education[14];
            }
            //abalat mahberawi bota
            {
                // ሓረስታይ
                $fm = DB::table('approved_hitsuys')
                ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->where('hitsuys.occupation', 'ሓረስታይ')
                ->count();
                $cd = Hitsuy::where('hitsuys.occupation', 'ሓረስታይ')
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode,'/%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('hitsuy_status', 'ሕፁይ')->count();
                $row_abalat_mahberawi_bota[1] = ($fm + $cd);

                // ካልኦት ሰብ ሞያ
                $fm = DB::table('approved_hitsuys')
                ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->where('hitsuys.occupation', 'ሲቪል ሰርቫንት')
                ->count();
                $cd = Hitsuy::where('hitsuys.occupation', 'ሲቪል ሰርቫንት')
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode,'/%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('hitsuy_status', 'ሕፁይ')->count();
                $row_abalat_mahberawi_bota[2] = ($fm + $cd);

                // መምህራን
                $fm = DB::table('approved_hitsuys')
                ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->where('hitsuys.occupation', 'መምህር')
                ->count();
                $cd = DB::table('hitsuys')->where('hitsuys.occupation', 'መምህር')
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode,'/%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('hitsuy_status', 'ሕፁይ')
                ->count();
                $row_abalat_mahberawi_bota[3] = ($fm + $cd);

                // ተምሃሮ
                $fm = DB::table('approved_hitsuys')
                ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->where('hitsuys.occupation', 'ተምሃራይ')
                ->count();
                $cd = Hitsuy::where('hitsuys.occupation', 'ተምሃራይ')
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode,'/%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('hitsuy_status', 'ሕፁይ')->count();
                $row_abalat_mahberawi_bota[4] = ($fm + $cd);

                // 67 - 83
                $fm = DB::table('approved_hitsuys')
                ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->whereBetween('approved_hitsuys.membershipDate', [Carbon::createFromDate(1967,1,1), Carbon::createFromDate(1983,1,1)])
                ->count();
                $cd = 0;
                $row_abalat_mahberawi_bota[5] = ($fm + $cd);

                // 84 - 93
                $fm = DB::table('approved_hitsuys')
                ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->whereBetween('approved_hitsuys.membershipDate', [Carbon::createFromDate(1983,1,1), Carbon::createFromDate(1993,1,1)])
                ->count();
                $cd = 0;
                $row_abalat_mahberawi_bota[6] = ($fm + $cd);

                // 94 - 2000
                $fm = DB::table('approved_hitsuys')
                ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->whereBetween('approved_hitsuys.membershipDate', [Carbon::createFromDate(1993,1,1), Carbon::createFromDate(2000,1,1)])
                ->count();
                $cd = 0;
                $row_abalat_mahberawi_bota[7] = ($fm + $cd);

                // after 2001
                $fm = DB::table('approved_hitsuys')
                ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->whereBetween('approved_hitsuys.membershipDate', [Carbon::createFromDate(2000,1,1), Carbon::createFromDate(9000,1,1)])
                ->count();
                $cd = 0;
                $row_abalat_mahberawi_bota[8] = ($fm + $cd);

                // ደቂ ኣንስትዮ
                $fm = DB::table('approved_hitsuys')
                ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->where('approved_hitsuys.assignedAssoc','ደቂ ኣንስትዮ')
                ->count();
                $row_abalat_mahberawi_bota[9] = ($fm + $cd);

                // ሓረስታይ
                $fm = DB::table('approved_hitsuys')
                ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->where('approved_hitsuys.assignedAssoc','ሓረስታይ')
                ->count();
                $row_abalat_mahberawi_bota[10] = ($fm + $cd);

                // መናእሰይ
                $fm = DB::table('approved_hitsuys')
                ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->where('approved_hitsuys.assignedAssoc','መናእሰይ')
                ->count();
                $row_abalat_mahberawi_bota[11] = ($fm + $cd);

                // መምህራን
                $fm = DB::table('approved_hitsuys')
                ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->where('approved_hitsuys.assignedAssoc','መምህራን')
                ->count();
                $row_abalat_mahberawi_bota[12] = ($fm + $cd);

                // መንግስቲ ሰራሕተኛ
                $fm = DB::table('approved_hitsuys')
                ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->where('hitsuys.occupation', 'ሲቪል ሰርቫንት')
                ->count();
                $cd = DB::table('hitsuys')->where('hitsuys.occupation', 'ሲቪል ሰርቫንት')
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode,'/%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('hitsuy_status', 'ሕፁይ')
                ->count();
                $row_abalat_mahberawi_bota[13] = ($fm + $cd);

                // ዘይመንግስታዊ
                $fm = DB::table('approved_hitsuys')
                ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->where('hitsuys.occupation', 'ዘይመንግስታዊ')
                ->count();
                $cd = DB::table('hitsuys')->where('hitsuys.occupation', 'ዘይመንግስታዊ')
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode,'/%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('hitsuy_status', 'ሕፁይ')
                ->count();
                $row_abalat_mahberawi_bota[14] = ($fm + $cd);

                // ውልቀ
                $fm = DB::table('approved_hitsuys')
                ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->where('hitsuys.occupation', 'ውልቀ')
                ->count();
                $cd = DB::table('hitsuys')
                ->where('hitsuys.occupation', 'ውልቀ')
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode,'/%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('hitsuy_status', 'ሕፁይ')
                ->count();
                $row_abalat_mahberawi_bota[15] = ($fm + $cd);

                // ደቂ ኣንስትዮ
                $fm = DB::table('approved_hitsuys')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('gender', 'ኣን')
                ->count();
                $cd = DB::table('hitsuys')
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode,'/%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('hitsuy_status', 'ሕፁይ')
                ->where('gender', 'ኣን')
                ->count();
                $row_abalat_mahberawi_bota[16] = ($fm + $cd);

                //ድምር
                $fm = DB::table('approved_hitsuys')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->count();
                $cd = DB::table('hitsuys')
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode,'/%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('hitsuy_status', 'ሕፁይ')
                ->count();
                $row_abalat_mahberawi_bota[17] = ($fm + $cd);
            }
            //wahio count
            {
                // ተምሃሮ
                $row_wahio_count[1] = DB::table('wahios')
                ->join('meseretawi_wdabes', 'wahios.widabeCode', '=', 'meseretawi_wdabes.widabeCode')
                ->where('meseretawi_wdabes.type', 'ተምሃሮ')
                ->join('tabias', 'meseretawi_wdabes.tabiaCode', '=', 'tabias.tabiaCode')
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->count();

                // መምህራን
                $row_wahio_count[2] = DB::table('wahios')
                ->join('meseretawi_wdabes', 'wahios.widabeCode', '=', 'meseretawi_wdabes.widabeCode')
                ->where('meseretawi_wdabes.type', 'መምህራን')
                ->join('tabias', 'meseretawi_wdabes.tabiaCode', '=', 'tabias.tabiaCode')
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->count();

                // ሲ/ሰርቫንት
                $row_wahio_count[3] = DB::table('wahios')
                ->join('meseretawi_wdabes', 'wahios.widabeCode', '=', 'meseretawi_wdabes.widabeCode')
                ->where('meseretawi_wdabes.type', 'ሲ/ሰርቫንት')
                ->join('tabias', 'meseretawi_wdabes.tabiaCode', '=', 'tabias.tabiaCode')
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->count();

                // ሸቃሎ
                $row_wahio_count[4] = DB::table('wahios')
                ->join('meseretawi_wdabes', 'wahios.widabeCode', '=', 'meseretawi_wdabes.widabeCode')
                ->where('meseretawi_wdabes.type', 'ሸቃሎ')
                ->join('tabias', 'meseretawi_wdabes.tabiaCode', '=', 'tabias.tabiaCode')
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->count();

                // ድምር
                $row_wahio_count[5] = $row_wahio_count[1] + $row_wahio_count[2] + $row_wahio_count[3] + $row_wahio_count[4];

                // ጠቕላላ ድምር
                $row_wahio_count[6] = DB::table('wahios')
                ->join('meseretawi_wdabes', 'wahios.widabeCode', '=', 'meseretawi_wdabes.widabeCode')
                ->join('tabias', 'meseretawi_wdabes.tabiaCode', '=', 'tabias.tabiaCode')
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->count();
            }

            //tabia count
            {
                $row_tabia_count[1] = DB::table('tabias')
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->count();

                $row_tabia_count[2] = DB::table('tabias')
                ->join('approved_hitsuys', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->groupBy('tabias.tabiaCode')
                ->havingRaw('COUNT(approved_hitsuys.hitsuyID)>500')
                ->pluck(DB::raw("COUNT(approved_hitsuys.hitsuyID)"))
                ->count();

                $row_tabia_count[3] = $row_tabia_count[1] - $row_tabia_count[2];

                $row_tabia_count[4] = $row_tabia_count[1];
                // ገባር
                $row_tabia_count[5] = DB::table('meseretawi_wdabes')
                ->join('tabias', 'tabias.tabiaCode', '=', 'meseretawi_wdabes.tabiaCode')
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('meseretawi_wdabes.type', 'ሓረስታይ')
                ->count();
                // ተምሃሮ
                $row_tabia_count[6] = DB::table('meseretawi_wdabes')
                ->join('tabias', 'tabias.tabiaCode', '=', 'meseretawi_wdabes.tabiaCode')
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('meseretawi_wdabes.type', 'ተምሃሮ')
                ->count();
                // መምህራን
                $row_tabia_count[7] = DB::table('meseretawi_wdabes')
                ->join('tabias', 'tabias.tabiaCode', '=', 'meseretawi_wdabes.tabiaCode')
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('meseretawi_wdabes.type', 'መምህራን')
                ->count();
                // ሲ/ሰርቫንት
                $row_tabia_count[8] = DB::table('meseretawi_wdabes')
                ->join('tabias', 'tabias.tabiaCode', '=', 'meseretawi_wdabes.tabiaCode')
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('meseretawi_wdabes.type', 'ሲ/ሰርቫንት')
                ->count();

                //ጠ/ድምር
                $row_tabia_count[9] = DB::table('meseretawi_wdabes')
                ->join('tabias', 'tabias.tabiaCode', '=', 'meseretawi_wdabes.tabiaCode')
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->count();
            }

            // TODO for all plan tables set quarter and year 

            //plan non deant
            {
                //ሓረስታይ
                $row_plan_non_deant[2] = DB::table('meseretawi_wdabes')
                ->join('meseretawiwidabeaplans', 'meseretawi_wdabes.widabeCode', '=', 'meseretawiwidabeaplans.widabecode')
                ->where('meseretawi_wdabes.type', 'ሓረስታይ')
                ->join('tabias', 'tabias.tabiaCode', '=', 'meseretawi_wdabes.tabiaCode')
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('meseretawiwidabeaplans.planyear',explode("/", $today)[2])
                ->where('meseretawiwidabeaplans.quarter', $this_quarter)
                ->count();

                //ተምሃሮ
                $row_plan_non_deant[4] = DB::table('meseretawi_wdabes')
                ->join('meseretawiwidabeaplans', 'meseretawi_wdabes.widabeCode', '=', 'meseretawiwidabeaplans.widabecode')
                ->where('meseretawi_wdabes.type', 'ተምሃሮ')
                ->join('tabias', 'tabias.tabiaCode', '=', 'meseretawi_wdabes.tabiaCode')
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('meseretawiwidabeaplans.planyear',explode("/", $today)[2])
                ->where('meseretawiwidabeaplans.quarter', $this_quarter)
                ->count();

                //መምህራን
                $row_plan_non_deant[6] = DB::table('meseretawi_wdabes')
                ->join('meseretawiwidabeaplans', 'meseretawi_wdabes.widabeCode', '=', 'meseretawiwidabeaplans.widabecode')
                ->where('meseretawi_wdabes.type', 'መምህራን')
                ->join('tabias', 'tabias.tabiaCode', '=', 'meseretawi_wdabes.tabiaCode')
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('meseretawiwidabeaplans.planyear',explode("/", $today)[2])
                ->where('meseretawiwidabeaplans.quarter', $this_quarter)
                ->count();

                //ሲ/ሰርቫንት
                $row_plan_non_deant[8] = DB::table('meseretawi_wdabes')
                ->join('meseretawiwidabeaplans', 'meseretawi_wdabes.widabeCode', '=', 'meseretawiwidabeaplans.widabecode')
                ->where('meseretawi_wdabes.type', 'ሲ/ሰርቫንት')
                ->join('tabias', 'tabias.tabiaCode', '=', 'meseretawi_wdabes.tabiaCode')
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('meseretawiwidabeaplans.planyear',explode("/", $today)[2])
                ->where('meseretawiwidabeaplans.quarter', $this_quarter)
                ->count();

            }

            //plan all
            {
                //ውልቀሰብ
                $row_plan_all[2] = DB::table('approved_hitsuys')
                ->join('individualplans', 'approved_hitsuys.hitsuyID', '=', 'individualplans.hitsuyID')
                // ->where('meseretawi_wdabes.type', 'ሸቃሎ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('individualplans.year',explode("/", $today)[2])
                ->count();

                //መሰረታዊ ውዳበ
                $row_plan_all[5] = DB::table('meseretawi_wdabes')
                ->join('meseretawiwidabeaplans', 'meseretawi_wdabes.widabeCode', '=', 'meseretawiwidabeaplans.widabecode')
                // ->where('meseretawi_wdabes.type', 'ሸቃሎ')
                ->join('tabias', 'tabias.tabiaCode', '=', 'meseretawi_wdabes.tabiaCode')
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('meseretawiwidabeaplans.planyear',explode("/", $today)[2])
                ->where('meseretawiwidabeaplans.quarter', $this_quarter)
                ->count();

                //ዋህዮ
                $row_plan_all[8] = DB::table('wahioplans')
                ->join('wahios', 'wahios.id', '=', 'wahioplans.wahioid')
                // ->where('meseretawi_wdabes.type', 'ሸቃሎ')
                ->join('tabias', 'wahios.parentcode', 'LIKE', DB::raw("CONCAT('_____', tabias.tabiaCode, '%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('wahioplans.planyear',explode("/", $today)[2])
                ->where('wahioplans.quarter', $this_quarter)
                ->count();
            }

            //TODO complete table
            //model members
            {
                $row_model_members[1] = 
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ላዕለዋይ አመራርሓ')
                ->where('approved_hitsuys.meseratawiposition', 'መ/ዉ/አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('super_leaders', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                ->whereBetween('super_leaders.sum', [80, 100])
                ->where('super_leaders.half', ($this_quarter == 'ዓመት' ? '6 ወርሒ':'ዓመት'))
                ->where('super_leaders.year', ($this_quarter == 'ዓመት' ? explode("/", $today)[2] : explode("/", $today)[2] - 1))
                ->count()
                 +
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ማእኸላይ አመራርሓ')
                ->where('approved_hitsuys.meseratawiposition', 'መ/ዉ/አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('middle_leaders', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                ->whereBetween('middle_leaders.sum', [80, 100])
                ->where('middle_leaders.half', ($this_quarter == 'ዓመት' ? '6 ወርሒ':'ዓመት'))
                ->where('middle_leaders.year', ($this_quarter == 'ዓመት' ? explode("/", $today)[2] : explode("/", $today)[2] - 1))
                ->count()
                +
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ታሕተዋይ አመራርሓ')
                ->where('approved_hitsuys.meseratawiposition', 'መ/ዉ/አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('lower_leaders', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                ->where('lower_leaders.model', 'ሞዴል')
                ->where('lower_leaders.half', ($this_quarter == 'ዓመት' ? '6 ወርሒ':'ዓመት'))
                ->where('lower_leaders.year', ($this_quarter == 'ዓመት' ? explode("/", $today)[2] : explode("/", $today)[2] - 1))
                ->count()
                +
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ጀማሪ አመራርሓ')
                ->where('approved_hitsuys.meseratawiposition', 'መ/ዉ/አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('first_instant_leaders', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                ->whereBetween('first_instant_leaders.sum', [80, 100])
                ->where('first_instant_leaders.half', ($this_quarter == 'ዓመት' ? '6 ወርሒ':'ዓመት'))
                ->where('first_instant_leaders.year', ($this_quarter == 'ዓመት' ? explode("/", $today)[2] : explode("/", $today)[2] - 1))
                ->count()
                +
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ተራ ኣባል')
                ->where('approved_hitsuys.meseratawiposition', 'መ/ዉ/አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('tara_members', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                ->where('tara_members.model', 'ሞዴል')
                ->where('tara_members.half', ($this_quarter == 'ዓመት' ? '6 ወርሒ':'ዓመት'))
                ->where('tara_members.year', ($this_quarter == 'ዓመት' ? explode("/", $today)[2] : explode("/", $today)[2] - 1))
                ->count();

                $row_model_members[2] = 
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ላዕለዋይ አመራርሓ')
                ->where('approved_hitsuys.meseratawiposition', 'መ/ዉ/አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('super_leaders', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                ->whereBetween('super_leaders.sum', [80, 100])
                ->where('super_leaders.half', $this_quarter)
                ->where('super_leaders.year', explode("/", $today)[2])
                ->count()
                 +
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ማእኸላይ አመራርሓ')
                ->where('approved_hitsuys.meseratawiposition', 'መ/ዉ/አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('middle_leaders', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                ->whereBetween('middle_leaders.sum', [80, 100])
                ->where('middle_leaders.half', $this_quarter)
                ->where('middle_leaders.year', explode("/", $today)[2])
                ->count()
                +
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ታሕተዋይ አመራርሓ')
                ->where('approved_hitsuys.meseratawiposition', 'መ/ዉ/አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('lower_leaders', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                ->where('lower_leaders.model', 'ሞዴል')
                ->where('lower_leaders.half', $this_quarter)
                ->where('lower_leaders.year', explode("/", $today)[2])
                ->count()
                +
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ጀማሪ አመራርሓ')
                ->where('approved_hitsuys.meseratawiposition', 'መ/ዉ/አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('first_instant_leaders', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                ->whereBetween('first_instant_leaders.sum', [80, 100])
                ->where('first_instant_leaders.half', $this_quarter)
                ->where('first_instant_leaders.year', explode("/", $today)[2])
                ->count()
                +
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ተራ ኣባል')
                ->where('approved_hitsuys.meseratawiposition', 'መ/ዉ/አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('tara_members', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                ->where('tara_members.model', 'ሞዴል')
                ->where('tara_members.half', $this_quarter)
                ->where('tara_members.year', explode("/", $today)[2])
                ->count();

                $row_model_members[3] = 
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ላዕለዋይ አመራርሓ')
                ->where('approved_hitsuys.meseratawiposition', 'መ/ዉ/አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('super_leaders', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                ->whereBetween('super_leaders.sum', [0, 80])
                ->where('super_leaders.half', $this_quarter)
                ->where('super_leaders.year', explode("/", $today)[2])
                ->count()
                 +
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ማእኸላይ አመራርሓ')
                ->where('approved_hitsuys.meseratawiposition', 'መ/ዉ/አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('middle_leaders', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                ->whereBetween('middle_leaders.sum', [0, 80])
                ->where('middle_leaders.half', $this_quarter)
                ->where('middle_leaders.year', explode("/", $today)[2])
                ->count()
                +
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ታሕተዋይ አመራርሓ')
                ->where('approved_hitsuys.meseratawiposition', 'መ/ዉ/አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('lower_leaders', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                ->where('lower_leaders.model', 'ዘይሞዴል')
                ->where('lower_leaders.half', $this_quarter)
                ->where('lower_leaders.year', explode("/", $today)[2])
                ->count()
                +
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ጀማሪ አመራርሓ')
                ->where('approved_hitsuys.meseratawiposition', 'መ/ዉ/አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('first_instant_leaders', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                ->whereBetween('first_instant_leaders.sum', [0, 80])
                ->where('first_instant_leaders.half', $this_quarter)
                ->where('first_instant_leaders.year', explode("/", $today)[2])
                ->count()
                +
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ተራ ኣባል')
                ->where('approved_hitsuys.meseratawiposition', 'መ/ዉ/አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('tara_members', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                ->where('tara_members.model', 'ዘይሞዴል')
                ->where('tara_members.half', $this_quarter)
                ->where('tara_members.year', explode("/", $today)[2])
                ->count();

                $row_model_members[5] = 
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ላዕለዋይ አመራርሓ')
                ->where('approved_hitsuys.meseratawiposition', 'ዋህዮ አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('super_leaders', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                ->whereBetween('super_leaders.sum', [80, 100])
                ->where('super_leaders.half', ($this_quarter == 'ዓመት' ? '6 ወርሒ':'ዓመት'))
                ->where('super_leaders.year', ($this_quarter == 'ዓመት' ? explode("/", $today)[2] : explode("/", $today)[2] - 1))
                ->count()
                 +
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ማእኸላይ አመራርሓ')
                ->where('approved_hitsuys.meseratawiposition', 'ዋህዮ አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('middle_leaders', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                ->whereBetween('middle_leaders.sum', [80, 100])
                ->where('middle_leaders.half', ($this_quarter == 'ዓመት' ? '6 ወርሒ':'ዓመት'))
                ->where('middle_leaders.year', ($this_quarter == 'ዓመት' ? explode("/", $today)[2] : explode("/", $today)[2] - 1))
                ->count()
                +
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ታሕተዋይ አመራርሓ')
                ->where('approved_hitsuys.meseratawiposition', 'ዋህዮ አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('lower_leaders', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                ->where('lower_leaders.model', 'ሞዴል')
                ->where('lower_leaders.half', ($this_quarter == 'ዓመት' ? '6 ወርሒ':'ዓመት'))
                ->where('lower_leaders.year', ($this_quarter == 'ዓመት' ? explode("/", $today)[2] : explode("/", $today)[2] - 1))
                ->count()
                +
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ጀማሪ አመራርሓ')
                ->where('approved_hitsuys.meseratawiposition', 'ዋህዮ አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('first_instant_leaders', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                ->whereBetween('first_instant_leaders.sum', [80, 100])
                ->where('first_instant_leaders.half', ($this_quarter == 'ዓመት' ? '6 ወርሒ':'ዓመት'))
                ->where('first_instant_leaders.year', ($this_quarter == 'ዓመት' ? explode("/", $today)[2] : explode("/", $today)[2] - 1))
                ->count()
                +
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ተራ ኣባል')
                ->where('approved_hitsuys.meseratawiposition', 'ዋህዮ አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('tara_members', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                ->where('tara_members.model', 'ሞዴል')
                ->where('tara_members.half', ($this_quarter == 'ዓመት' ? '6 ወርሒ':'ዓመት'))
                ->where('tara_members.year', ($this_quarter == 'ዓመት' ? explode("/", $today)[2] : explode("/", $today)[2] - 1))
                ->count();

                $row_model_members[6] = 
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ላዕለዋይ አመራርሓ')
                ->where('approved_hitsuys.meseratawiposition', 'ዋህዮ አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('super_leaders', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                ->whereBetween('super_leaders.sum', [80, 100])
                ->where('super_leaders.half', $this_quarter)
                ->where('super_leaders.year', explode("/", $today)[2])
                ->count()
                 +
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ማእኸላይ አመራርሓ')
                ->where('approved_hitsuys.meseratawiposition', 'ዋህዮ አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('middle_leaders', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                ->whereBetween('middle_leaders.sum', [80, 100])
                ->where('middle_leaders.half', $this_quarter)
                ->where('middle_leaders.year', explode("/", $today)[2])
                ->count()
                +
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ታሕተዋይ አመራርሓ')
                ->where('approved_hitsuys.meseratawiposition', 'ዋህዮ አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('lower_leaders', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                ->where('lower_leaders.model', 'ሞዴል')
                ->where('lower_leaders.half', $this_quarter)
                ->where('lower_leaders.year', explode("/", $today)[2])
                ->count()
                +
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ጀማሪ አመራርሓ')
                ->where('approved_hitsuys.meseratawiposition', 'ዋህዮ አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('first_instant_leaders', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                ->whereBetween('first_instant_leaders.sum', [80, 100])
                ->where('first_instant_leaders.half', $this_quarter)
                ->where('first_instant_leaders.year', explode("/", $today)[2])
                ->count()
                +
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ተራ ኣባል')
                ->where('approved_hitsuys.meseratawiposition', 'ዋህዮ አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('tara_members', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                ->where('tara_members.model', 'ሞዴል')
                ->where('tara_members.half', $this_quarter)
                ->where('tara_members.year', explode("/", $today)[2])
                ->count();

                $row_model_members[7] = 
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ላዕለዋይ አመራርሓ')
                ->where('approved_hitsuys.meseratawiposition', 'ዋህዮ አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('super_leaders', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                ->whereBetween('super_leaders.sum', [0, 80])
                ->where('super_leaders.half', $this_quarter)
                ->where('super_leaders.year', explode("/", $today)[2])
                ->count()
                 +
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ማእኸላይ አመራርሓ')
                ->where('approved_hitsuys.meseratawiposition', 'ዋህዮ አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('middle_leaders', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                ->whereBetween('middle_leaders.sum', [0, 80])
                ->where('middle_leaders.half', $this_quarter)
                ->where('middle_leaders.year', explode("/", $today)[2])
                ->count()
                +
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ታሕተዋይ አመራርሓ')
                ->where('approved_hitsuys.meseratawiposition', 'ዋህዮ አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('lower_leaders', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                ->where('lower_leaders.model', 'ዘይሞዴል')
                ->where('lower_leaders.half', $this_quarter)
                ->where('lower_leaders.year', explode("/", $today)[2])
                ->count()
                +
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ጀማሪ አመራርሓ')
                ->where('approved_hitsuys.meseratawiposition', 'ዋህዮ አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('first_instant_leaders', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                ->whereBetween('first_instant_leaders.sum', [0, 80])
                ->where('first_instant_leaders.half', $this_quarter)
                ->where('first_instant_leaders.year', explode("/", $today)[2])
                ->count()
                +
                DB::table('approved_hitsuys')
                ->where('approved_hitsuys.memberType', 'ተራ ኣባል')
                ->where('approved_hitsuys.meseratawiposition', 'ዋህዮ አመራርሓ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->join('tara_members', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                ->where('tara_members.model', 'ዘይሞዴል')
                ->where('tara_members.half', $this_quarter)
                ->where('tara_members.year', explode("/", $today)[2])
                ->count();

                // $row_model_members[10] = 
                // DB::table('approved_hitsuys')
                // ->where('approved_hitsuys.memberType', 'ላዕለዋይ አመራርሓ')
                // ->where('approved_hitsuys.meseratawiposition', 'ዋህዮ አመራርሓ')
                // ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                // ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                // ->where('tabias.isUrban', '=', 'ገጠር')
                // ->join('super_leaders', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                // ->whereBetween('super_leaders.sum', [80, 100])
                // ->where('super_leaders.half', $this_quarter)
                // ->where('super_leaders.year', explode("/", $today)[2])
                // ->count()
                //  +
                // DB::table('approved_hitsuys')
                // ->where('approved_hitsuys.memberType', 'ማእኸላይ አመራርሓ')
                // ->where('approved_hitsuys.meseratawiposition', 'ዋህዮ አመራርሓ')
                // ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                // ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                // ->where('tabias.isUrban', '=', 'ገጠር')
                // ->join('middle_leaders', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                // ->whereBetween('middle_leaders.sum', [80, 100])
                // ->where('middle_leaders.half', $this_quarter)
                // ->where('middle_leaders.year', explode("/", $today)[2])
                // ->count()
                // +
                // DB::table('approved_hitsuys')
                // ->where('approved_hitsuys.memberType', 'ታሕተዋይ አመራርሓ')
                // ->where('approved_hitsuys.meseratawiposition', 'ዋህዮ አመራርሓ')
                // ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                // ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                // ->where('tabias.isUrban', '=', 'ገጠር')
                // ->join('lower_leaders', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                // ->where('lower_leaders.model', 'ሞዴል')
                // ->where('lower_leaders.half', $this_quarter)
                // ->where('lower_leaders.year', explode("/", $today)[2])
                // ->count()
                // +
                // DB::table('approved_hitsuys')
                // ->where('approved_hitsuys.memberType', 'ጀማሪ አመራርሓ')
                // ->where('approved_hitsuys.meseratawiposition', 'ዋህዮ አመራርሓ')
                // ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                // ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                // ->where('tabias.isUrban', '=', 'ገጠር')
                // ->join('first_instant_leaders', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                // ->whereBetween('first_instant_leaders.sum', [80, 100])
                // ->where('first_instant_leaders.half', $this_quarter)
                // ->where('first_instant_leaders.year', explode("/", $today)[2])
                // ->count()
                // +
                // DB::table('approved_hitsuys')
                // ->where('approved_hitsuys.memberType', 'ተራ ኣባል')
                // ->where('approved_hitsuys.meseratawiposition', 'ዋህዮ አመራርሓ')
                // ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                // ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                // ->where('tabias.isUrban', '=', 'ገጠር')
                // ->join('tara_members', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                // ->where('tara_members.model', 'ሞዴል')
                // ->where('tara_members.half', $this_quarter)
                // ->where('tara_members.year', explode("/", $today)[2])
                // ->count();

                // $row_model_members[11] = 
                // DB::table('approved_hitsuys')
                // ->where('approved_hitsuys.memberType', 'ላዕለዋይ አመራርሓ')
                // ->where('approved_hitsuys.meseratawiposition', 'ዋህዮ አመራርሓ')
                // ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                // ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                // ->where('tabias.isUrban', '=', 'ገጠር')
                // ->join('super_leaders', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                // ->whereBetween('super_leaders.sum', [0, 80])
                // ->where('super_leaders.half', $this_quarter)
                // ->where('super_leaders.year', explode("/", $today)[2])
                // ->count()
                //  +
                // DB::table('approved_hitsuys')
                // ->where('approved_hitsuys.memberType', 'ማእኸላይ አመራርሓ')
                // ->where('approved_hitsuys.meseratawiposition', 'ዋህዮ አመራርሓ')
                // ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                // ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                // ->where('tabias.isUrban', '=', 'ገጠር')
                // ->join('middle_leaders', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                // ->whereBetween('middle_leaders.sum', [0, 80])
                // ->where('middle_leaders.half', $this_quarter)
                // ->where('middle_leaders.year', explode("/", $today)[2])
                // ->count()
                // +
                // DB::table('approved_hitsuys')
                // ->where('approved_hitsuys.memberType', 'ታሕተዋይ አመራርሓ')
                // ->where('approved_hitsuys.meseratawiposition', 'ዋህዮ አመራርሓ')
                // ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                // ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                // ->where('tabias.isUrban', '=', 'ገጠር')
                // ->join('lower_leaders', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                // ->where('lower_leaders.model', 'ዘይሞዴል')
                // ->where('lower_leaders.half', $this_quarter)
                // ->where('lower_leaders.year', explode("/", $today)[2])
                // ->count()
                // +
                // DB::table('approved_hitsuys')
                // ->where('approved_hitsuys.memberType', 'ጀማሪ አመራርሓ')
                // ->where('approved_hitsuys.meseratawiposition', 'ዋህዮ አመራርሓ')
                // ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                // ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                // ->where('tabias.isUrban', '=', 'ገጠር')
                // ->join('first_instant_leaders', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                // ->whereBetween('first_instant_leaders.sum', [0, 80])
                // ->where('first_instant_leaders.half', $this_quarter)
                // ->where('first_instant_leaders.year', explode("/", $today)[2])
                // ->count()
                // +
                // DB::table('approved_hitsuys')
                // ->where('approved_hitsuys.memberType', 'ተራ ኣባል')
                // ->where('approved_hitsuys.meseratawiposition', 'ዋህዮ አመራርሓ')
                // ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                // ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                // ->where('tabias.isUrban', '=', 'ገጠር')
                // ->join('tara_members', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                // ->where('tara_members.model', 'ዘይሞዴል')
                // ->where('tara_members.half', $this_quarter)
                // ->where('tara_members.year', explode("/", $today)[2])
                // ->count();
            }

            //new candidates non deant
            {
                // ሲቪል ሰርቫንት
                $row_new_members_non_deant[2] = DB::table('hitsuys')
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode, '%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('hitsuy_status', 'ሕፁይ')
                ->where('occupation', 'ሲቪል ሰርቫንት')
                ->count();

                // ሓረስታይ
                $row_new_members_non_deant[4] = DB::table('hitsuys')
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode, '%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('hitsuy_status', 'ሕፁይ')
                ->where('occupation', 'ሓረስታይ')
                ->count();

                // ተምሃራይ
                $row_new_members_non_deant[6] = DB::table('hitsuys')
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode, '%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('hitsuy_status', 'ሕፁይ')
                ->where('occupation', 'ተምሃራይ')
                ->count();

                // መምህር
                $row_new_members_non_deant[8] = DB::table('hitsuys')
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode, '%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->where('hitsuy_status', 'ሕፁይ')
                ->where('occupation', 'መምህር')
                ->count();
            }

            //new approved members
            {
                // ክሰግሩ ዝግበኦም
                $row_approved_new_members[1] = DB::table('hitsuys')
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode, '%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->whereBetween('regDate',[Carbon::createFromDate(2004,1,1)->subMonths(6), Carbon::createFromDate(2004,3,30)->subMonths(6)])
                ->where('gender', 'ተባ')
                ->count();
                $row_approved_new_members[2] = DB::table('hitsuys')
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode, '%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->whereBetween('regDate',[Carbon::createFromDate(2004,1,1)->subMonths(6), Carbon::createFromDate(2004,3,30)->subMonths(6)])
                ->where('gender', 'ኣን')
                ->count();
                $row_approved_new_members[3] = $row_approved_new_members[1] + $row_approved_new_members[2];

                // ፍፃመ
                $row_approved_new_members[4] = DB::table('hitsuys')
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode, '%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->whereBetween('regDate',[Carbon::createFromDate(2004,1,1)->subMonths(6), Carbon::createFromDate(2004,3,30)->subMonths(6)])
                ->where('gender', 'ተባ')
                ->where('hitsuy_status', 'ኣባል')
                ->count();
                $row_approved_new_members[5] = DB::table('hitsuys')
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode, '%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->whereBetween('regDate',[Carbon::createFromDate(2004,1,1)->subMonths(6), Carbon::createFromDate(2004,3,30)->subMonths(6)])
                ->where('gender', 'ኣን')
                ->where('hitsuy_status', 'ኣባል')
                ->count();
                $row_approved_new_members[6] = $row_approved_new_members[4] + $row_approved_new_members[5];

                // ዘይሰገሩ TODO check ዝተኣገደ part 
                $row_approved_new_members[8] = DB::table('hitsuys')
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode, '%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->whereBetween('regDate',[Carbon::createFromDate(2004,1,1)->subMonths(6), Carbon::createFromDate(2004,3,30)->subMonths(6)])
                ->where('gender', 'ተባ')
                ->whereIn('hitsuy_status', ['ሕፁይ', 'ዝተኣገደ'])
                ->count();
                $row_approved_new_members[9] = DB::table('hitsuys')
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode, '%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->whereBetween('regDate',[Carbon::createFromDate(2004,1,1)->subMonths(6), Carbon::createFromDate(2004,3,30)->subMonths(6)])
                ->where('gender', 'ኣን')
                ->whereIn('hitsuy_status', ['ሕፁይ', 'ዝተኣገደ'])
                ->count();
                $row_approved_new_members[10] = $row_approved_new_members[8] + $row_approved_new_members[9];

                // ግዚኦም ዘይኣኸለ
                $row_approved_new_members[11] = DB::table('hitsuys')
                ->join('tabias', 'hitsuys.hitsuyID', 'LIKE', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode, '%')"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                ->whereBetween('regDate',[Carbon::createFromDate(2004,3,30)->subMonths(6), Carbon::createFromDate(9000,1,1)->subMonths(6)])
                ->whereIn('hitsuy_status', ['ሕፁይ', 'ዝተኣገደ'])
                ->count();

            }

            //grades
            {
                // ሓረስታይ
                {
                    // total
                    $row_grades[1] += DB::table('approved_hitsuys')
                    ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                    ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                    ->where('tabias.isUrban', '=', 'ገጠር')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->where('hitsuys.occupation', 'ሓረስታይ')
                    ->count();
                    // ሓረስታይ A
                    {
                        $sl = DB::table('super_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሓረስታይ')
                        ->whereBetween('super_leaders.sum', [$A_LOWER, $A_UPPER])
                        ->count();

                        $ml = DB::table('middle_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሓረስታይ')
                        ->whereBetween('middle_leaders.sum', [$A_LOWER, $A_UPPER])
                        ->count();

                        $ll = DB::table('lower_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሓረስታይ')
                        ->where('lower_leaders.evaluation', 'A')
                        ->count();

                        $fil = DB::table('first_instant_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሓረስታይ')
                        ->whereBetween('first_instant_leaders.sum', [$A_LOWER, $A_UPPER])
                        ->count();

                        $ex = DB::table('experts')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'experts.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሓረስታይ')
                        ->whereBetween('experts.sum', [$A_LOWER, $A_UPPER])
                        ->count();

                        $tm = DB::table('tara_members')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሓረስታይ')
                        ->where('tara_members.evaluation', 'A')
                        ->count();

                        $row_grades[2] = $sl + $ml + $ll + $fil + $ex + $tm;
                    }
                    // ሓረስታይ B
                    {
                        $sl = DB::table('super_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሓረስታይ')
                        ->whereBetween('super_leaders.sum', [$B_LOWER, $B_UPPER])
                        ->count();

                        $ml = DB::table('middle_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሓረስታይ')
                        ->whereBetween('middle_leaders.sum', [$B_LOWER, $B_UPPER])
                        ->count();

                        $ll = DB::table('lower_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሓረስታይ')
                        ->where('lower_leaders.evaluation', 'B')
                        ->count();

                        $fil = DB::table('first_instant_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሓረስታይ')
                        ->whereBetween('first_instant_leaders.sum', [$B_LOWER, $B_UPPER])
                        ->count();

                        $ex = DB::table('experts')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'experts.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሓረስታይ')
                        ->whereBetween('experts.sum', [$B_LOWER, $B_UPPER])
                        ->count();

                        $tm = DB::table('tara_members')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሓረስታይ')
                        ->where('tara_members.evaluation', 'B')
                        ->count();

                        $row_grades[3] = $sl + $ml + $ll + $fil + $ex + $tm;
                    }
                    // ሓረስታይ C
                    {
                        $sl = DB::table('super_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሓረስታይ')
                        ->whereBetween('super_leaders.sum', [$C_LOWER, $C_UPPER])
                        ->count();

                        $ml = DB::table('middle_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሓረስታይ')
                        ->whereBetween('middle_leaders.sum', [$C_LOWER, $C_UPPER])
                        ->count();

                        $ll = DB::table('lower_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሓረስታይ')
                        ->where('lower_leaders.evaluation', 'C')
                        ->count();

                        $fil = DB::table('first_instant_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሓረስታይ')
                        ->whereBetween('first_instant_leaders.sum', [$C_LOWER, $C_UPPER])
                        ->count();

                        $ex = DB::table('experts')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'experts.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሓረስታይ')
                        ->whereBetween('experts.sum', [$C_LOWER, $C_UPPER])
                        ->count();

                        $tm = DB::table('tara_members')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሓረስታይ')
                        ->where('tara_members.evaluation', 'C')
                        ->count();

                        $row_grades[4] = $sl + $ml + $ll + $fil + $ex + $tm;
                    }
                }
                // ሰብ ሞያ
                {
                    // total
                    $row_grades[6] += DB::table('approved_hitsuys')
                    ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                    ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                    ->where('tabias.isUrban', '=', 'ገጠር')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->where('hitsuys.occupation', 'ሲቪል ሰርቫንት')
                    ->count();
                    // ሰብ ሞያ A
                    {
                        $sl = DB::table('super_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሲቪል ሰርቫንት')
                        ->whereBetween('super_leaders.sum', [$A_LOWER, $A_UPPER])
                        ->count();

                        $ml = DB::table('middle_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሲቪል ሰርቫንት')
                        ->whereBetween('middle_leaders.sum', [$A_LOWER, $A_UPPER])
                        ->count();

                        $ll = DB::table('lower_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሲቪል ሰርቫንት')
                        ->where('lower_leaders.evaluation', 'A')
                        ->count();

                        $fil = DB::table('first_instant_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሲቪል ሰርቫንት')
                        ->whereBetween('first_instant_leaders.sum', [$A_LOWER, $A_UPPER])
                        ->count();

                        $ex = DB::table('experts')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'experts.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሲቪል ሰርቫንት')
                        ->whereBetween('experts.sum', [$A_LOWER, $A_UPPER])
                        ->count();

                        $tm = DB::table('tara_members')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሲቪል ሰርቫንት')
                        ->where('tara_members.evaluation', 'A')
                        ->count();

                        $row_grades[7] = $sl + $ml + $ll + $fil + $ex + $tm;
                    }
                    // ሰብ ሞያ B
                    {
                        $sl = DB::table('super_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሲቪል ሰርቫንት')
                        ->whereBetween('super_leaders.sum', [$B_LOWER, $B_UPPER])
                        ->count();

                        $ml = DB::table('middle_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሲቪል ሰርቫንት')
                        ->whereBetween('middle_leaders.sum', [$B_LOWER, $B_UPPER])
                        ->count();

                        $ll = DB::table('lower_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሲቪል ሰርቫንት')
                        ->where('lower_leaders.evaluation', 'B')
                        ->count();

                        $fil = DB::table('first_instant_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሲቪል ሰርቫንት')
                        ->whereBetween('first_instant_leaders.sum', [$B_LOWER, $B_UPPER])
                        ->count();

                        $ex = DB::table('experts')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'experts.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሲቪል ሰርቫንት')
                        ->whereBetween('experts.sum', [$B_LOWER, $B_UPPER])
                        ->count();

                        $tm = DB::table('tara_members')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሲቪል ሰርቫንት')
                        ->where('tara_members.evaluation', 'B')
                        ->count();

                        $row_grades[8] = $sl + $ml + $ll + $fil + $ex + $tm;
                    }
                    // ሰብ ሞያ C
                    {
                        $sl = DB::table('super_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሲቪል ሰርቫንት')
                        ->whereBetween('super_leaders.sum', [$C_LOWER, $C_UPPER])
                        ->count();

                        $ml = DB::table('middle_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሲቪል ሰርቫንት')
                        ->whereBetween('middle_leaders.sum', [$C_LOWER, $C_UPPER])
                        ->count();

                        $ll = DB::table('lower_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሲቪል ሰርቫንት')
                        ->where('lower_leaders.evaluation', 'C')
                        ->count();

                        $fil = DB::table('first_instant_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሲቪል ሰርቫንት')
                        ->whereBetween('first_instant_leaders.sum', [$C_LOWER, $C_UPPER])
                        ->count();

                        $ex = DB::table('experts')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'experts.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሲቪል ሰርቫንት')
                        ->whereBetween('experts.sum', [$C_LOWER, $C_UPPER])
                        ->count();

                        $tm = DB::table('tara_members')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ሲቪል ሰርቫንት')
                        ->where('tara_members.evaluation', 'C')
                        ->count();

                        $row_grades[9] = $sl + $ml + $ll + $fil + $ex + $tm;
                    }
                }
                // መምህራን
                {
                    // total
                    $row_grades[11] += DB::table('approved_hitsuys')
                    ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                    ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                    ->where('tabias.isUrban', '=', 'ገጠር')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->where('hitsuys.occupation', 'መምህር')
                    ->count();
                    // መምህራን A
                    {
                        $sl = DB::table('super_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'መምህር')
                        ->whereBetween('super_leaders.sum', [$A_LOWER, $A_UPPER])
                        ->count();

                        $ml = DB::table('middle_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'መምህር')
                        ->whereBetween('middle_leaders.sum', [$A_LOWER, $A_UPPER])
                        ->count();

                        $ll = DB::table('lower_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'መምህር')
                        ->where('lower_leaders.evaluation', 'A')
                        ->count();

                        $fil = DB::table('first_instant_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'መምህር')
                        ->whereBetween('first_instant_leaders.sum', [$A_LOWER, $A_UPPER])
                        ->count();

                        $ex = DB::table('experts')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'experts.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'መምህር')
                        ->whereBetween('experts.sum', [$A_LOWER, $A_UPPER])
                        ->count();

                        $tm = DB::table('tara_members')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'መምህር')
                        ->where('tara_members.evaluation', 'A')
                        ->count();

                        $row_grades[12] = $sl + $ml + $ll + $fil + $ex + $tm;
                    }
                    // መምህራን B
                    {
                        $sl = DB::table('super_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'መምህር')
                        ->whereBetween('super_leaders.sum', [$B_LOWER, $B_UPPER])
                        ->count();

                        $ml = DB::table('middle_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'መምህር')
                        ->whereBetween('middle_leaders.sum', [$B_LOWER, $B_UPPER])
                        ->count();

                        $ll = DB::table('lower_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'መምህር')
                        ->where('lower_leaders.evaluation', 'B')
                        ->count();

                        $fil = DB::table('first_instant_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'መምህር')
                        ->whereBetween('first_instant_leaders.sum', [$B_LOWER, $B_UPPER])
                        ->count();

                        $ex = DB::table('experts')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'experts.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'መምህር')
                        ->whereBetween('experts.sum', [$B_LOWER, $B_UPPER])
                        ->count();

                        $tm = DB::table('tara_members')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'መምህር')
                        ->where('tara_members.evaluation', 'B')
                        ->count();

                        $row_grades[13] = $sl + $ml + $ll + $fil + $ex + $tm;
                    }
                    // መምህራን C
                    {
                        $sl = DB::table('super_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'መምህር')
                        ->whereBetween('super_leaders.sum', [$C_LOWER, $C_UPPER])
                        ->count();

                        $ml = DB::table('middle_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'መምህር')
                        ->whereBetween('middle_leaders.sum', [$C_LOWER, $C_UPPER])
                        ->count();

                        $ll = DB::table('lower_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'መምህር')
                        ->where('lower_leaders.evaluation', 'C')
                        ->count();

                        $fil = DB::table('first_instant_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'መምህር')
                        ->whereBetween('first_instant_leaders.sum', [$C_LOWER, $C_UPPER])
                        ->count();

                        $ex = DB::table('experts')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'experts.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'መምህር')
                        ->whereBetween('experts.sum', [$C_LOWER, $C_UPPER])
                        ->count();

                        $tm = DB::table('tara_members')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'መምህር')
                        ->where('tara_members.evaluation', 'C')
                        ->count();

                        $row_grades[14] = $sl + $ml + $ll + $fil + $ex + $tm;
                    }
                }
                // ተምሃሮ
                {
                    // total
                    $row_grades[16] += DB::table('approved_hitsuys')
                    ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                    ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                    ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                    ->where('tabias.isUrban', '=', 'ገጠር')
                    // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                    ->where('hitsuys.occupation', 'ተምሃራይ')
                    ->count();
                    // ተምሃሮ A
                    {
                        $sl = DB::table('super_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ተምሃራይ')
                        ->whereBetween('super_leaders.sum', [$A_LOWER, $A_UPPER])
                        ->count();

                        $ml = DB::table('middle_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ተምሃራይ')
                        ->whereBetween('middle_leaders.sum', [$A_LOWER, $A_UPPER])
                        ->count();

                        $ll = DB::table('lower_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ተምሃራይ')
                        ->where('lower_leaders.evaluation', 'A')
                        ->count();

                        $fil = DB::table('first_instant_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ተምሃራይ')
                        ->whereBetween('first_instant_leaders.sum', [$A_LOWER, $A_UPPER])
                        ->count();

                        $ex = DB::table('experts')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'experts.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ተምሃራይ')
                        ->whereBetween('experts.sum', [$A_LOWER, $A_UPPER])
                        ->count();

                        $tm = DB::table('tara_members')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ተምሃራይ')
                        ->where('tara_members.evaluation', 'A')
                        ->count();

                        $row_grades[17] = $sl + $ml + $ll + $fil + $ex + $tm;
                    }
                    // ተምሃሮ B
                    {
                        $sl = DB::table('super_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ተምሃራይ')
                        ->whereBetween('super_leaders.sum', [$B_LOWER, $B_UPPER])
                        ->count();

                        $ml = DB::table('middle_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ተምሃራይ')
                        ->whereBetween('middle_leaders.sum', [$B_LOWER, $B_UPPER])
                        ->count();

                        $ll = DB::table('lower_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ተምሃራይ')
                        ->where('lower_leaders.evaluation', 'B')
                        ->count();

                        $fil = DB::table('first_instant_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ተምሃራይ')
                        ->whereBetween('first_instant_leaders.sum', [$B_LOWER, $B_UPPER])
                        ->count();

                        $ex = DB::table('experts')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'experts.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ተምሃራይ')
                        ->whereBetween('experts.sum', [$B_LOWER, $B_UPPER])
                        ->count();

                        $tm = DB::table('tara_members')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ተምሃራይ')
                        ->where('tara_members.evaluation', 'B')
                        ->count();

                        $row_grades[18] = $sl + $ml + $ll + $fil + $ex + $tm;
                    }
                    // ተምሃሮ C
                    {
                        $sl = DB::table('super_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'super_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ተምሃራይ')
                        ->whereBetween('super_leaders.sum', [$C_LOWER, $C_UPPER])
                        ->count();

                        $ml = DB::table('middle_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'middle_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ተምሃራይ')
                        ->whereBetween('middle_leaders.sum', [$C_LOWER, $C_UPPER])
                        ->count();

                        $ll = DB::table('lower_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'lower_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ተምሃራይ')
                        ->where('lower_leaders.evaluation', 'C')
                        ->count();

                        $fil = DB::table('first_instant_leaders')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'first_instant_leaders.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ተምሃራይ')
                        ->whereBetween('first_instant_leaders.sum', [$C_LOWER, $C_UPPER])
                        ->count();

                        $ex = DB::table('experts')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'experts.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ተምሃራይ')
                        ->whereBetween('experts.sum', [$C_LOWER, $C_UPPER])
                        ->count();

                        $tm = DB::table('tara_members')
                        ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'tara_members.hitsuyID')
                        ->join('hitsuys', 'approved_hitsuys.hitsuyID', '=', 'hitsuys.hitsuyID')
                        ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                        ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                        // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                        ->where('hitsuys.occupation', 'ተምሃራይ')
                        ->where('tara_members.evaluation', 'C')
                        ->count();

                        $row_grades[19] = $sl + $ml + $ll + $fil + $ex + $tm;
                    }
                }
                $row_grades[5] =  $row_grades[1] - ($row_grades[2] + $row_grades[3] + $row_grades[4]);
                $row_grades[10] =  $row_grades[6] - ($row_grades[7] + $row_grades[8] + $row_grades[9]);
                $row_grades[15] =  $row_grades[11] - ($row_grades[12] + $row_grades[13] + $row_grades[14]);
                $row_grades[20] =  $row_grades[16] - ($row_grades[17] + $row_grades[18] + $row_grades[19]);
            }

            //punishments
            {
                $row_punishment[1] = DB::table('penalties')
                ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'penalties.hitsuyID')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->where('penaltyGiven', 'መጠንቀቕታ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('penalties.created_at', [Carbon::createFromDate(explode("/", $quarter[0])[2], explode("/", $quarter[0])[1], explode("/", $quarter[0])[0]), Carbon::createFromDate(explode("/", $quarter[1])[2], explode("/", $quarter[1])[1], explode("/", $quarter[1])[0])])
                ->count();

                $row_punishment[2] = DB::table('penalties')
                ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'penalties.hitsuyID')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->where('penaltyGiven', 'ናይ ሕፀ እዋን ምንዋሕ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('penalties.created_at', [Carbon::createFromDate(explode("/", $quarter[0])[2], explode("/", $quarter[0])[1], explode("/", $quarter[0])[0]), Carbon::createFromDate(explode("/", $quarter[1])[2], explode("/", $quarter[1])[1], explode("/", $quarter[1])[0])])
                ->count();

                $row_punishment[3] = DB::table('penalties')
                ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'penalties.hitsuyID')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->where('penaltyGiven', 'ካብ ሕፁይነት ምብራር')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('penalties.created_at', [Carbon::createFromDate(explode("/", $quarter[0])[2], explode("/", $quarter[0])[1], explode("/", $quarter[0])[0]), Carbon::createFromDate(explode("/", $quarter[1])[2], explode("/", $quarter[1])[1], explode("/", $quarter[1])[0])])
                ->count();

                $row_punishment[4] = DB::table('penalties')
                ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'penalties.hitsuyID')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->where('penaltyGiven', 'ካብ ሙሉእ ናብ ሕፁይ ኣባልነት ምውራድ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('penalties.created_at', [Carbon::createFromDate(explode("/", $quarter[0])[2], explode("/", $quarter[0])[1], explode("/", $quarter[0])[0]), Carbon::createFromDate(explode("/", $quarter[1])[2], explode("/", $quarter[1])[1], explode("/", $quarter[1])[0])])
                ->count();

                $row_punishment[5] = DB::table('penalties')
                ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'penalties.hitsuyID')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->where('penaltyGiven', 'ካብ ሓላፍነት ንውሱን ጊዜ ምእጋድ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('penalties.created_at', [Carbon::createFromDate(explode("/", $quarter[0])[2], explode("/", $quarter[0])[1], explode("/", $quarter[0])[0]), Carbon::createFromDate(explode("/", $quarter[1])[2], explode("/", $quarter[1])[1], explode("/", $quarter[1])[0])])
                ->count();

                $row_punishment[6] = DB::table('penalties')
                ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'penalties.hitsuyID')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->where('penaltyGiven', 'ካብ ሓላፍነት ምውራድ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('penalties.created_at', [Carbon::createFromDate(explode("/", $quarter[0])[2], explode("/", $quarter[0])[1], explode("/", $quarter[0])[0]), Carbon::createFromDate(explode("/", $quarter[1])[2], explode("/", $quarter[1])[1], explode("/", $quarter[1])[0])])
                ->count();

                $row_punishment[7] = DB::table('penalties')
                ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'penalties.hitsuyID')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->where('penaltyGiven', 'ካብ ኣባልነት ንውሱን ጊዜ ምእጋድ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('penalties.created_at', [Carbon::createFromDate(explode("/", $quarter[0])[2], explode("/", $quarter[0])[1], explode("/", $quarter[0])[0]), Carbon::createFromDate(explode("/", $quarter[1])[2], explode("/", $quarter[1])[1], explode("/", $quarter[1])[0])])
                ->count();

                $row_punishment[8] = DB::table('penalties')
                ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'penalties.hitsuyID')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->where('penaltyGiven', 'ካብ ኣባልነት ምብራር')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('penalties.created_at', [Carbon::createFromDate(explode("/", $quarter[0])[2], explode("/", $quarter[0])[1], explode("/", $quarter[0])[0]), Carbon::createFromDate(explode("/", $quarter[1])[2], explode("/", $quarter[1])[1], explode("/", $quarter[1])[0])])
                ->count();  

                //TODO chargeTypes not set!!
                $row_punishment[10] = DB::table('penalties')
                ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'penalties.hitsuyID')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->whereIn('chargeType', [])
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('penalties.created_at', [Carbon::createFromDate(explode("/", $quarter[0])[2], explode("/", $quarter[0])[1], explode("/", $quarter[0])[0]), Carbon::createFromDate(explode("/", $quarter[1])[2], explode("/", $quarter[1])[1], explode("/", $quarter[1])[0])])
                ->count();   

                $row_punishment[11] = DB::table('penalties')
                ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'penalties.hitsuyID')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->whereIn('chargeType', [])
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('penalties.created_at', [Carbon::createFromDate(explode("/", $quarter[0])[2], explode("/", $quarter[0])[1], explode("/", $quarter[0])[0]), Carbon::createFromDate(explode("/", $quarter[1])[2], explode("/", $quarter[1])[1], explode("/", $quarter[1])[0])])
                ->count();

                $row_punishment[12] = DB::table('penalties')
                ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'penalties.hitsuyID')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->whereIn('chargeType', [''])
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('penalties.created_at', [Carbon::createFromDate(explode("/", $quarter[0])[2], explode("/", $quarter[0])[1], explode("/", $quarter[0])[0]), Carbon::createFromDate(explode("/", $quarter[1])[2], explode("/", $quarter[1])[1], explode("/", $quarter[1])[0])])
                ->count();   

                $row_punishment[13] = DB::table('penalties')
                ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'penalties.hitsuyID')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->whereIn('chargeType', [''])
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('penalties.created_at', [Carbon::createFromDate(explode("/", $quarter[0])[2], explode("/", $quarter[0])[1], explode("/", $quarter[0])[0]), Carbon::createFromDate(explode("/", $quarter[1])[2], explode("/", $quarter[1])[1], explode("/", $quarter[1])[0])])
                ->count();   

                $row_punishment[14] = DB::table('penalties')
                ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'penalties.hitsuyID')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->whereIn('chargeType', [''])
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('penalties.created_at', [Carbon::createFromDate(explode("/", $quarter[0])[2], explode("/", $quarter[0])[1], explode("/", $quarter[0])[0]), Carbon::createFromDate(explode("/", $quarter[1])[2], explode("/", $quarter[1])[1], explode("/", $quarter[1])[0])])
                ->count();

                $row_punishment[15] = DB::table('penalties')
                ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'penalties.hitsuyID')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->where('approved_hitsuys.gender', 'ኣን')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('penalties.created_at', [Carbon::createFromDate(explode("/", $quarter[0])[2], explode("/", $quarter[0])[1], explode("/", $quarter[0])[0]), Carbon::createFromDate(explode("/", $quarter[1])[2], explode("/", $quarter[1])[1], explode("/", $quarter[1])[0])])
                ->count();

                $row_punishment[16] += DB::table('penalties')
                ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'penalties.hitsuyID')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->where('approved_hitsuys.gender', 'ተባ')
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('penalties.created_at', [Carbon::createFromDate(explode("/", $quarter[0])[2], explode("/", $quarter[0])[1], explode("/", $quarter[0])[0]), Carbon::createFromDate(explode("/", $quarter[1])[2], explode("/", $quarter[1])[1], explode("/", $quarter[1])[0])])
                ->count();

                $row_punishment[17] += DB::table('penalties')
                ->join('approved_hitsuys', 'approved_hitsuys.hitsuyID', '=', 'penalties.hitsuyID')
                // ->whereBetween('approved_hitsuys.updated_at', [$now,  $then])
                ->join('tabias', 'approved_hitsuys.zoneworedaCode', '=', DB::raw("CONCAT(tabias.parentcode, tabias.tabiaCode)"))
                ->where('tabias.woredaCode', '=', $woreda['woredaCode'])
                ->where('tabias.isUrban', '=', 'ገጠር')
                // ->whereBetween('penalties.created_at', [Carbon::createFromDate(explode("/", $quarter[0])[2], explode("/", $quarter[0])[1], explode("/", $quarter[0])[0]), Carbon::createFromDate(explode("/", $quarter[1])[2], explode("/", $quarter[1])[1], explode("/", $quarter[1])[0])])
                ->count();
                $row_punishment[9] =  $row_punishment[1] + $row_punishment[2] + $row_punishment[3] + $row_punishment[4] + $row_punishment[5] + $row_punishment[6] + $row_punishment[7] + $row_punishment[8];

            }

            $weseking_gudletin[] = $row_weseking_gudletin;
            $abalat_age_education[] = $row_abalat_age_education;
            $abalat_mahberawi_bota[] = $row_abalat_mahberawi_bota;
            $abalat_deant[] = $row_abalat_deant;
            $wahio_count[] = $row_wahio_count;
            $tabia_count[] = $row_tabia_count;
            $plan_non_deant[] = $row_plan_non_deant;
            $plan_all[] = $row_plan_all;
            $model_members[] = $row_model_members;
            $new_members_non_deant[] = $row_new_members_non_deant;
            $approved_new_members[] = $row_approved_new_members;
            $grades[] = $row_grades;
            $punishment[] = $row_punishment;
        }
        return view('report.geter_new', compact('zoneName', 'weseking_gudletin', 'abalat_age_education', 'abalat_mahberawi_bota', 'abalat_deant', 'wahio_count', 'tabia_count', 'plan_non_deant', 'plan_all', 'model_members', 'new_members_non_deant', 'approved_new_members', 'grades', 'punishment', 'zobadatas', 'zoneCode'));
    }
}

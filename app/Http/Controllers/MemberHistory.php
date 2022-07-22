<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

use  App\ApprovedHitsuy;
use  App\Transfer;
use  App\Siltena;
use  App\Penalty;
use App\SuperLeader;
use App\LowerLeader;
use  App\Mideba;
use  App\EducationInformation;
use  App\CareerInformation;

class MemberHistory extends Controller
{
    public function index($id, Request $request){
        $id = str_replace('_', '/', $id);
        $member = ApprovedHitsuy::where('hitsuyID','=', $id)->first();
        if(!$member){
            return abort(404);
        }
        $transfers = Transfer::where('hitsuyID', '=', $id)->orderBy('startDate', 'desc')->get();
        $midebas = Mideba::where('hitsuyID', '=', $id)->orderBy('startDate', 'desc')->get();
        $siltenas = Siltena::where('hitsuyID', '=', $id)->orderBy('startDate', 'desc')->get();
        $penalties = Penalty::where('hitsuyID', '=', $id)->orderBy('startDate', 'desc')->get();
        $educations = EducationInformation::where('hitsuyID', '=', $id)->orderBy('graduationDate', 'desc')->get();
        $expriences = CareerInformation::where('hitsuyID', '=', $id)->orderBy('startDate', 'desc')->get();
        $gemgams = [];
        if($member->memberType == 'ላዕለዋይ ኣመራርሓ'){
            $gemgams = SuperLeader::where('hitsuyID', $id)->orderBy('year', 'desc')->orderBy('half', 'desc')->get();
        }
        if($member->memberType == 'ታሕተዋይ ኣመራርሓ'){
            $gemgams = LowerLeader::where('hitsuyID', $id)->orderBy('year', 'desc')->orderBy('half', 'desc')->get();
        }
        return view('membership.history', compact('member', 'transfers', 'midebas', 'siltenas', 'penalties', 'gemgams', 'educations', 'expriences'));
    }
}

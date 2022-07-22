<?php
namespace App\Http\Controllers;
use Kamaln7\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Hitsuy;
use App\ApprovedHitsuy;
use App\Yearly;
use App\Monthly;
use App\CoreDegefti;
use App\LowerLeader;
use App\MediumLeader;
use App\MiddleLeader;
use App\SuperLeader;
use Carbon\Carbon;
use DateTime;

use Illuminate\Database\Eloquent\Model;
use DB;

class DashboardController extends Controller
{
    //
      //the top part of the dashboard
    public function totalpayment()
    {
        $abalat = Hitsuy::count();
        $cordgefti = CoreDegefti::count();
        $monthly = Monthly::count();
        $yearly = Yearly::count();
        $totalpayments = $monthly + $yearly;

        $lowerleader = LowerLeader::count();
        
        $middleleader = MiddleLeader::count();
        $superleader = SuperLeader::count();
        $amerarha = $lowerleader + $middleleader + $superleader;
        //$users=Hitsuy::all();
        $users2=Hitsuy::count();
        #sdd($users);
        $users=Hitsuy::where('hitsuy_status','ኣባል');
        $users1 = Hitsuy::where('hitsuy_status','ኣባል')->count();
        $ኣመራርሓዝኮኑ=Hitsuy::where('hitsuy_status','ኣመራርሓ');
        $ኣመራርበዝሒ  = Hitsuy::where('hitsuy_status','ኣመራርሓ')->count();
        $lowerleader1=LowerLeader::all();
        $middleleader1=MiddleLeader::all();
        $superleader1=SuperLeader::all();
        //
        $presenttime= date("Y-m-d");
        
        $presenttime1 = strtotime($presenttime);
        $threemonthsbeforenow = strtotime("-3 months",$presenttime1);
        
        //$bb=Hitsuy::where('created_at' '<=', $threemonthsbeforenow)->get();
        // $aa = Hitsuy::where('created_at', '<=', new \DateTime('-3 months'));
        // $created_a = strtotime($aa);
        // dd($created_a);

        $counter = 0;
        $ucounter=0;
        $middle=0;
        $lower = 0;
        $super = 0;
        dd($presenttime1);
        $ኣመመራርቅድሚ3=0;
        //to get htsuyat qdmi 3 werhi
        //dd($users);

        foreach($users as $user){
         
          $created_attime =  \Carbon\Carbon::parse($user->created_at)->format('Y-m-d');
          $created_attime1 = strtotime($created_attime);
  
            if ($created_attime1 <= $threemonthsbeforenow) {
                 $counter++;
                }
              $ucounter++;
          }
        // foreach($ኣመራርሓዝኮኑ as $use){
        //   $created_attime =  \Carbon\Carbon::parse($use->created_at)->format('Y-m-d');
        //   $created_attime1 = strtotime($created_attime);
        //     if ($created_attime1 <= $threemonthsbeforenow) {
        //          $ኣመመራርቅድሚ3++;
        //         }
              
        //   }
          //to get amerarha qdmi 3 werhi

          foreach($lowerleader1 as $lower1){
          $created_attime =  \Carbon\Carbon::parse($lower1->created_at)->format('Y-m-d');
          $created_attime1 = strtotime($created_attime);
            if ($created_attime1 <= $threemonthsbeforenow) {
                 $lower++;
                }
             
          }

          foreach($middleleader1 as $middle1){
          $created_attime =  \Carbon\Carbon::parse($middle1->created_at)->format('Y-m-d');
          $created_attime1 = strtotime($created_attime);
            if ($created_attime1 <= $threemonthsbeforenow) {
                 $middle++;
                }
             
          }

          foreach($superleader1 as $super1){
          $created_attime =  \Carbon\Carbon::parse($super1->created_at)->format('Y-m-d');
          $created_attime1 = strtotime($created_attime);
            if ($created_attime1 <= $threemonthsbeforenow) {
                 $super++;
                }
             
          }
          $amerarhakdmi3months = $lower + $middle + $super;
         return view('dashboard')->withtotalpayments($totalpayments)->withusers1($users1)->withcordgefti($cordgefti)->withኣመራርበዝሒ($ኣመራርበዝሒ)->withucounter($ucounter)->withcounter($counter)->withusers2($users2)->withኣመመራርቅድሚ3($ኣመመራርቅድሚ3);
    }
}

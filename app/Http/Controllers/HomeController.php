<?php

namespace App\Http\Controllers;

use  App\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use  App\Zobatat;
use  App\Woreda;
use App\Hitsuy;
use App\Monthly;
use App\Yearly;
use App\CoreDegefti;
use Carbon\Carbon;
use DateTime;
use  App\Documents;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
          

////////////////////////////
        $totaltablevalue = Hitsuy::count();
       
        $monthly = Monthly::count();
        $yearly = Yearly::count();
        $totalpayments = $monthly + $yearly;
       
        $abalat = Hitsuy::where('hitsuy_status','ኣባል')->count();
        $abalatbefore_threemonths = Hitsuy::whereIn('hitsuy_status',['ኣባል'])->where('created_at','<',Carbon::now()->subMonths(3))->count();
        
        
        $amerarha  = Hitsuy::where('hitsuy_status','ኣመራርሓ')->count();
        $amerarhabefore_threemonths = Hitsuy::where('hitsuy_status','ኣመራርሓ')->where('created_at','<',Carbon::now()->subMonths(3))->count();

        $cordgefti = CoreDegefti::count();
       

  ////////////////////////////      

        $data = null;
        if(Auth::user()->usertype == 'admin'){
            $data = Announcement::orderBy('created_at', 'DESC')
            ->get();
        }
        if(array_search(Auth::user()->usertype, ['zone', 'zoneadmin']) !== false){
            $data = Announcement::where('area', 'all')
            ->orWhere(function ($query){
                $query->where('area', 'zone')
                ->where('code', Auth::user()->area);
            })
            ->orderBy('created_at', 'DESC')
            ->get();
        }
        if(array_search(Auth::user()->usertype, ['woreda', 'woredaadmin']) !== false){
            $data = Announcement::where('area', 'all')
            ->orWhere(function ($query){
                $query->where('area', 'zone')
                ->where('code', Woreda::where('woredacode', Auth::user()->area)->first()->toArray()['zoneCode']);
            })
            ->orWhere(function ($query){
                $query->where('area', 'woreda')
                ->where('code', Auth::user()->area);
            })
            ->orderBy('created_at', 'DESC')
            ->get();
        }
        $doc = Documents::orderBy('created_at', 'DESC')->get();
        return view('dashboard', compact('data', 'doc','totaltablevalue','totalpayments','abalat','abalatbefore_threemonths','amerarha','amerarhabefore_threemonths','cordgefti'));
    }
}

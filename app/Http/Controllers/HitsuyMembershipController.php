<?php

namespace App\Http\Controllers;

require_once substr(dirname(__FILE__), 0, -17).'\PHPExcel-1.8\Classes\PHPExcel.php';

use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
// use PHPExcel_Style_Borders;
use PHPExcel_Shared_Font;
use App\DateConvert;

use  App\Hitsuy;
use  App\ApprovedHitsuy;
use  App\NotyetHitsuy;
use  App\RejectedHitsuy;
use  App\CareerInformation;
use App\meseretawiWdabe;
use App\Wahio;
use App\Zobatat;
use DB;

use Illuminate\Http\Request;
use Kamaln7\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;


use Carbon\Carbon;

class HitsuyMembershipController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $value = Auth::user()->area;
        if(array_search(Auth::user()->usertype, ['woreda','woredaadmin']) !== false){
            $value = '__'.$value;
        }
        $data = Hitsuy::whereIn('hitsuy_status',['ሕፁይ','ሕፁይነት ተናዊሑ','ሕፁይነት ተሰሪዙ'])->where('hitsuyID', 'LIKE', $value.'%')/*->where('regDate','<',Carbon::now()->subMonths(6))*/->orderBy('regDate','asc')->get();
        return view ( 'membership.membership' )->withData ( $data );
    }
    public function listMembers()
    {
        //
        $value = Auth::user()->area;
        if(array_search(Auth::user()->usertype, ['woreda','woredaadmin']) !== false){
            $value = '__'.$value;
        }
        $data = ApprovedHitsuy::where('zoneworedaCode','LIKE', $value.'%')->where('approved_status','1')->get();        
        $zobadatas = DB::table("zobatats")->pluck("zoneName","zoneCode");
        return view('membership.memberlist',compact('data','zobadatas'));
    }
    public function wahioleadersindex()
    {
        //
        $data = ApprovedHitsuy::where('approved_status','1')->get();      
        $zobadatas = DB::table("zobatats")->pluck("zoneName","zoneCode");
        return view ('leadership.wahioleaders',compact('data','zobadatas'));        
    }
    public function meseretawileadersindex()
    {
        //
        $data = ApprovedHitsuy::where('approved_status','1')->get();      
        $zobadatas = DB::table("zobatats")->pluck("zoneName","zoneCode");
        return view ('leadership.meseretawileaders',compact('data','zobadatas'));        
    }
    public function listHitsuy()
    {
        $value = Auth::user()->area;
        if(array_search(Auth::user()->usertype, ['woreda','woredaadmin']) !== false){
            $value = '__'.$value;
        }
        $data = Hitsuy::whereIn('hitsuy_status',['ሕፁይ', 'ሕፁይነት ተናዊሑ', 'ሕፁይነት ተሰሪዙ'])->where('hitsuyID', 'LIKE', $value.'%')->get();
        $zobadatas = DB::table("zobatats")->pluck("zoneName","zoneCode");
        return view ('membership.hitsuylist',compact('data','zobadatas'));        
    }

    public function listHitsuyExcel()
    {
        $value = Auth::user()->area;
        if(array_search(Auth::user()->usertype, ['woreda','woredaadmin']) !== false){
            $value = '__'.$value;
        }
        $data = Hitsuy::whereIn('hitsuy_status',['ሕፁይ', 'ሕፁይነት ተናዊሑ', 'ሕፁይነት ተሰሪዙ'])->where('hitsuyID', 'LIKE', $value.'%')->get();
        $zobadatas = DB::table("zobatats")->pluck("zoneName","zoneCode");
        $zoneCode = Auth::user()->area;
        $today = DateConvert::toEthiopian(date('d/m/Y'));
        if(Auth::user()->usertype == 'admin'){
            $zoneCode = DB::table("zobatats")->pluck("zoneCode")->first();
        }
        $zoneName = Zobatat::where('zoneCode', $zoneCode)->select(['zoneName'])->first()->toArray()['zoneName'];
        header('Content-Type: application/vvnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $zoneName . '_' . explode("/", $today)[2] . '_ሕፁያት.xlsx"');
        header('Cache-Control: max-age=0');
        $objPHPExcel = new PHPExcel();
        
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'መ.ቑ')
            ->setCellValue('B1', 'ሽም ሕፁይ')
            ->setCellValue('C1', 'ፆታ')
            ->setCellValue('D1', 'ዕድመ')
            ->setCellValue('E1', 'ትውልዲ ዓዲ')
            ->setCellValue('F1', 'ዝተመልመለሉ ዕለት')
            ->setCellValue('G1', 'ኩነታት ሕፁይነት')
            ->setCellValue('H1', 'ስራሕ')
            ->setCellValue('I1', 'ሓላፍነት');
        $i = 2;

        foreach ($data as $mydata) {
              $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $i , $mydata->hitsuyID)
                ->setCellValue('B' . $i , $mydata->name . $mydata->fname . $mydata->gfname)
                ->setCellValue('C' . $i , $mydata->gender)
                ->setCellValue('D' . $i , (date('Y') - date('Y',strtotime($mydata->dob)))-8)
                ->setCellValue('E' . $i , $mydata->birthPlace)
                ->setCellValue('F' . $i , DateConvert::toEthiopian(date('d/m/Y',strtotime($mydata->regDate))))
                ->setCellValue('G' . $i , $mydata->hitsuy_status)
                ->setCellValue('H' . $i , $mydata->occupation)
                ->setCellValue('I' . $i , $mydata->position);
              $i++;
        }

        $style = array(
            'alignment' => array('horizontal' =>PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
        //     'borders' => array(
        //         'allborders' => array(
        //             'style' => PHPExcel_Style_Borders::BORDER_THICK,
        //             'color' => array('rgb' => '000000')
        //         )
        // )
        );

        // PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
        foreach (range('A', 'Z') as $columnID) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
        }

        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->getDefaultStyle()->applyFromArray($style);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
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
        //add ApprovedHitsuy
        $validator = \Validator::make($request->all(), [
            'hitsuyID' => 'required',
            'membershipDate' => 'required|ethiopian_date',
            // 'membershipType' => 'required|in:ተጋዳላይ,ሲቪል',
            // 'grossSalary' => 'numeric',
            'netSalary' => 'numeric',
            'assignedWudabe' => 'required',
            'assignedWahio' => 'required',
            'assignedAssoc' => 'required',
            // 'fileNumber' => 'required',
            'isReported' => 'required',
            'hasRequested' => 'required',
            'isApproved' => 'required',
            ],
            [
            'ethiopian_date' => 'ዕለት: መዓልቲ/ወርሒ/ዓመተምህረት ክኸውን ኣለዎ',
            'required' => ':attribute ኣይተመልአን',
            'numeric' => ':attribute ቑፅሪ ክኸውን ኣለዎ',
            'in' => ':attribute ካብቶም ዘለዉ መማረፅታት ክኸውን ኣለዎ'
            ]);
        $fieldNames = [
        "hitsuyID" => "መለለዩ ሕፁይ",
        "membershipDate" => "ኣባል ዝኾነሉ ዕለት",
        "membershipType" => "ዓይነት ኣባል",
        "grossSalary" => "ጠቕላላ ደሞዝ",
        'netSalary' => 'ዝተፃረየ ደሞዝ',
        'assignedWudabe' => 'ዝተወደበሉ መሰረታዊ ውዳበ',
        'assignedWahio' => 'ዝተወደበሉ ዋህዮ',
        'assignedAssoc' => 'ዝተወደበሉ ማሕበር',
        'fileNumber' => 'ቑፅሪ ሰነድ',
        'isReported' => 'ሪፖርት ቐሪቡ',
        'hasRequested' => 'ሕፁይ ሓቲቱ',
        'isApproved' => 'ፀዲቑ'];
        $validator->setAttributeNames($fieldNames);
        if($validator->fails()){
            return [false, 'error', $validator->errors()->all()];
        }
        if(!Hitsuy::where('hitsuyID', $request->hitsuyID)->count()){
            $validator->errors()->add('duplicate', 'ሕፁይ ኣብ መዝገብ የለን');
            return [false, 'error', $validator->errors()->all()];
        }
        if(!meseretawiWdabe::where('widabeCode',$request->assignedWudabe)->count()){
            $validator->errors()->add('duplicate', 'መሰረታዊ ውዳበ ኣብ መዝገብ የለን');
            return [false, 'error', $validator->errors()->all()];   
        }
        if(!Wahio::where('id',$request->assignedWahio)->count()){
            $validator->errors()->add('duplicate', 'ዋህዮ ኣብ መዝገብ የለን');
            return [false, 'error', $validator->errors()->all()];   
        }
        $apprMem = new ApprovedHitsuy;
        $apprMem->hitsuyID = $request->hitsuyID;        
        $apprMem->membershipDate = DateConvert::correctDate($request->membershipDate);
        $apprMem->membershipType = '';

        $hitsuyoccup = Hitsuy::where('hitsuyID',$request->hitsuyID)->orderBy('updated_at', 'desc')->pluck('occupation')->first();
        if($hitsuyoccup=='መምህር'||$hitsuyoccup=='ሲቪል ሰርቫንት'){
            if(!$request->netSalary){
                $validator->errors()->add('duplicate', 'ዝተፃረየ ደሞዝ ኣይተመልአን');
                return [false, 'error', $validator->errors()->all()];          
            }
        }
        if($hitsuyoccup=="ምሁር"||$hitsuyoccup=="መምህር"){
            $apprMem->memberType = "ሰብ ሞያ";
        }
        
        $zoneworedaCodeVal = substr($request->hitsuyID,0, 9);
        // $zoneworedaCodeVal=intval($zoneworedaCodeVal);

        $apprMem->gender = Hitsuy::where('hitsuyID',$request->hitsuyID)->orderBy('updated_at', 'desc')->pluck('gender')->first();
        $apprMem->occupation = Hitsuy::where('hitsuyID',$request->hitsuyID)->orderBy('updated_at', 'desc')->pluck('occupation')->first();

        $apprMem->zoneworedaCode = $zoneworedaCodeVal;
        $apprMem->grossSalary = 0;
        if(!$request->netSalary)
            $apprMem->netSalary = 0;
        else
            $apprMem->netSalary = $request->netSalary;
        $apprMem->assignedWudabe = $request->assignedWudabe;
        $apprMem->wudabeType = meseretawiWdabe::where('widabeCode', $request->assignedWudabe)->pluck('type')->first();
        $apprMem->assignedWahio = $request->assignedWahio;
        $apprMem->assignedAssoc = $request->assignedAssoc;
        $apprMem->fileNumber = '';
        $apprMem->isReported = $request->isReported;
        $apprMem->hasRequested = $request->hasRequested;
        $apprMem->isApproved = $request->isApproved;        
        $apprMem->save();   

        //update Hitsuy
        $updHist = Hitsuy::find ( $request->hitsuyID );
        $updHist->hitsuy_status ='ኣባል';
        $updHist->save(); 
        return [true, "info", "ኣባልነት ብትክክል ፀዲቑ ኣሎ"];
                
    }
    public function editMember(Request $request)
    {
        //add ApprovedHitsuy
        $validator = \Validator::make($request->all(), [
            'hitsuyID' => 'required',
            'membershipDate' => 'required|ethiopian_date',
            'membershipType' => 'required|in:ተጋዳላይ,ሲቪል',
            'grossSalary' => 'numeric',
            'netSalary' => 'required|numeric',
            'assignedAssoc' => 'required|in:ደቂ ኣንስትዮ,መናእሰይ,ገባር,መምህራን',
            'fileNumber' => 'required',
            ],
            [
            'ethiopian_date' => 'ዕለት: መዓልቲ/ወርሒ/ዓመተምህረት ክኸውን ኣለዎ',
            'required' => ':attribute ኣይተመልአን',
            'numeric' => ':attribute ቑፅሪ ክኸውን ኣለዎ',
            'in' => ':attribute ካብቶም ዘለዉ መማረፅታት ክኸውን ኣለዎ'
            ]);
        $fieldNames = [
        "hitsuyID" => "መለለዩ ሕፁይ",
        "membershipDate" => "ኣባል ዝኾነሉ ዕለት",
        "membershipType" => "ዓይነት ኣባል",
        "grossSalary" => "ጠቕላላ ደሞዝ",
        'netSalary' => 'ዝተፃረየ ደሞዝ',
        'fileNumber' => 'ቑፅሪ ሰነድ'];
        $validator->setAttributeNames($fieldNames);
        if($validator->fails()){
            return [false, 'error', $validator->errors()->all()];
        }
        if(!ApprovedHitsuy::where('hitsuyID', $request->hitsuyID)->count()){
            $validator->errors()->add('duplicate', 'ኣባል ኣብ መዝገብ የለን');
            return [false, 'error', $validator->errors()->all()];
        }
        ApprovedHitsuy::where('hitsuyID',$request->hitsuyID)->update(['membershipDate' => DateConvert::correctDate($request->membershipDate),'membershipType' => $request->membershipType,'grossSalary' => $request->grossSalary,'netSalary' => $request->netSalary,'fileNumber' => $request->fileNumber]);
        return [true, "info", "ኣባል ተስተኻኺሉ ኣሎ"];
                
    }
    
    public function postponeHitsuy(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'hitsuyID' => 'required',
            'postponedDate' => 'required|ethiopian_date'],
            [
            'ethiopian_date' => 'ዕለት: መዓልቲ/ወርሒ/ዓመተምህረት ክኸውን ኣለዎ',
            'required' => ':attribute ኣይተመልአን',]);
        $fieldNames = [
        "hitsuyID1" => "መለለዩ ሕፁይ",
        "postponedDate" => "ዝተናውሐሉ ዕለት",];
        $validator->setAttributeNames($fieldNames);
        if($validator->fails()){
            return [false, 'error', $validator->errors()->all()];
        }
        if(!Hitsuy::where('hitsuyID', $request->hitsuyID)->count()){
            $validator->errors()->add('duplicate', 'ሕፁይ ኣብ መዝገብ የለን');
            return [false, 'error', $validator->errors()->all()];
        }
        //add NotyetHitsuy

        $postMem = new NotyetHitsuy;
        $postMem->hitsuyID = $request->hitsuyID; 
        $postMem->postponedDate = DateConvert::correctDate($request->postponedDate);
        $postMem->save();   

        //update Hitsuy
        $updHist = Hitsuy::find ( $request->hitsuyID );
        $updHist->hitsuy_status ='ሕፁይነት ተናዊሑ';
        $updHist->save(); 

        return [true, "info", "ሕፁይነት ብትክክል ተናዊሑ ኣሎ"];
    }

    public function rejectHitsuy(Request $request)
    {
        $validator = \Validator::make($request->all(),[
            'hitsuyID' => 'required',
            'rejectionReason' => 'required',
            'rejectionDate' => 'required|ethiopian_date'
        ],[
            'ethiopian_date' => 'ዕለት: መዓልቲ/ወርሒ/ዓመተምህረት ክኸውን ኣለዎ',
            'required' => ':attribute ኣይተመልአን'
        ]);
        $fieldNames = [
        "hitsuyID2" => "መለለዩ ሕፁይ",
        "rejectionReason" => "ዝተሰረዘሉ ምኽንያት",
        "rejectionDate" => "ዝተሰረዘሉ ዕለት"];
        $validator->setAttributeNames($fieldNames);
        if($validator->fails()){
            return [false, 'error', $validator->errors()->all()];
        }
        if(!Hitsuy::where('hitsuyID', $request->hitsuyID)->count()){
            $validator->errors()->add('duplicate', 'ሕፁይ ኣብ መዝገብ የለን');
            return [false, 'error', $validator->errors()->all()];
        }
        //add rejected
        $rejMem = new RejectedHitsuy;
        $rejMem->hitsuyID = $request->hitsuyID;        
        $rejMem->rejectionReason = $request->rejectionReason;
        $rejMem->rejectionDate = DateConvert::correctDate($request->rejectionDate);
        $rejMem->save();

        //update Hitsuy
        $updHist = Hitsuy::find( $request->hitsuyID );
        $updHist->hitsuy_status ='ሕፁይነት ተሰሪዙ';
        $updHist->save(); 

        return [true, "info", "ሕፁይነት ብትክክል ተሰሪዙ ኣሎ"];
    }
    
    public function wahioleadersupdate(Request $request)
    {
        //update CareerInformation

        $updCareer = new CareerInformation;
        $updCareer->hitsuyID = $request->hitsuyID;        
        $updCareer->exprienceType = "ፖለቲካ";
        $updCareer->career = "ዋህዮ ኣመራርሓ";      
        $updCareer->position = $request->leadertype;      
        $updCareer->institute = $request->woredaID;      
        $updCareer->address = $request->wahioID;
        $updCareer->startDate = DateConvert::correctDate($request->decisiondate);
        $updCareer->save();   

        //update Member's position 
         $rHID=$request->hitsuyID;
         ApprovedHitsuy::where('hitsuyID',$rHID)->update(['wahioposition' => $request->leadertype]);;
        

        if($request->hitsuyID1!="የለን"){
            ApprovedHitsuy::where('hitsuyID',$request->hitsuyID1)->update(['wahioposition' => 'ተራ ኣባል']);
            
            CareerInformation::where('hitsuyID',$request->hitsuyID1)->where('position',$request->leadertype)->update(['endDate' => $request->decisiondate]);
            
        }

        Toastr::info("ዋህዮ ኣመራርሓ ብትክክል ተመዝጊቡ ኣሎ");
        return back();                
    }

    public function meseretawileadersupdate(Request $request)
    {
        //update CareerInformation

        $updCareer = new CareerInformation;
        $updCareer->hitsuyID = $request->hitsuyID;        
        $updCareer->exprienceType = "ፖለቲካ";
        $updCareer->career = "መሰረታዊ ውዳበ ኣመራርሓ";      
        $updCareer->position = $request->leadertype;      
        $updCareer->institute = $request->woredaID;      
        $updCareer->address = $request->meseretawiID;
        $updCareer->startDate = DateConvert::correctDate($request->decisiondate);
        $updCareer->save();   

        //update Member's position 
         $rHID=$request->hitsuyID;
         ApprovedHitsuy::where('hitsuyID',$rHID)->update(['meseratawiposition' => $request->leadertype]);;
        

        if($request->hitsuyID1!="የለን"){
            ApprovedHitsuy::where('hitsuyID',$request->hitsuyID1)->update(['meseratawiposition' => 'ተራ ኣባል']);
            
            CareerInformation::where('hitsuyID',$request->hitsuyID1)->where('position',$request->leadertype)->update(['endDate' => $request->decisiondate]);
            
        }

        Toastr::info("መሰረታዊ ውዳበ ኣመራርሓ ብትክክል ተመዝጊቡ ኣሎ");
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

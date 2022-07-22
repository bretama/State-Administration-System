<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kamaln7\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;

use App\Http\Requests;
use  App\MewachoSetting;

class MewachoSettingsController extends Controller
{
    //
    public function __construct()    //if not authenticated redirect to login
    {
        $this->middleware('auth');
    }
    public function index()
    {
         $data = MewachoSetting::all ();
       return view ( 'settings.mewachosetting' )->withData ( $data );
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
    public function correctDate($date){
        return date('Y-m-d',strtotime(str_replace('/','-',$date)));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        $messages = [            
            'required' => ':attribute ብትኽክል ኣይኣተወን።',
            'numeric' => ':attribute ቑፅሪ ክኸውን ኣለዎ።',
            'min' => ':attribute ልዕሊ :min ክኸውን ኣለዎ',
            'max' => ':attribute ትሕቲ :max ክኸውን ኣለዎ',
            'in' => ':attribute ካብቶም ዘለዉ መማረፅታት ክኸውን ኣለዎ',
            'date_format' => 'ዕለት: መዓልቲ/ወርሒ/ዓመተምህረት ክኸውን ኣለዎ',
        ];
        //
        $validator = \Validator::make($request->all(), [
            'name' => 'required',
            'purpose' => 'required',            
            'mtype' => 'required|in:ገባር,ሸቃላይ,ምሁር,ደኣንት,መምህር,ተምሃራይ,ነጋዲይ',
            'amount' => 'required',
            'deadline' => 'required|date_format:d/m/Y'
        ],$messages);
        $fieldNames = [
        'name' => 'ሽም መዋጮ',
        'purpose' => 'ዕላማ',
        'mtype' => 'ዓይነት ኣባል',
        'amount' => 'መጠን',
        'deadline' => 'ዝኸፈለሉ ዕለት'
        ];
        $validator->setAttributeNames($fieldNames);
        if($validator->fails()){
            return [false, 'error', $validator->errors()->all()];
        }
        //
        $mewacho = new MewachoSetting;
        $mewacho->name = $request->name;
        $mewacho->purpose = $request->purpose;
        $mewacho->mtype = $request->mtype;
        $mewacho->amount = $request->amount;
        $mewacho->deadline = $this->correctDate($request->deadline);
        $mewacho->save();   
        return[true, 'info', "መዋጮ ብትኽክል ተፈጢሩ ኣሎ", $mewacho->id];
    }
    public function editMewacho(Request $request)
    {   
        $messages = [            
            'required' => ':attribute ብትኽክል ኣይኣተወን።',
            'numeric' => ':attribute ቑፅሪ ክኸውን ኣለዎ።',
            'min' => ':attribute ልዕሊ :min ክኸውን ኣለዎ',
            'max' => ':attribute ትሕቲ :max ክኸውን ኣለዎ',
            'in' => ':attribute ካብቶም ዘለዉ መማረፅታት ክኸውን ኣለዎ',
            'date_format' => 'ዕለት: መዓልቲ/ወርሒ/ዓመተምህረት ክኸውን ኣለዎ',
        ];
        //
        $validator = \Validator::make($request->all(), [
            'id' => 'required|numeric',
            'name' => 'required',
            'purpose' => 'required',            
            'mtype' => 'required|in:ገባር,ሸቃላይ,ምሁር,ደኣንት,መምህር,ተምሃራይ,ነጋዲይ',
            'amount' => 'required',
            'deadline' => 'required|date_format:d/m/Y'
        ],$messages);
        $fieldNames = [
        'name' => 'ሽም መዋጮ',
        'purpose' => 'ዕላማ',
        'mtype' => 'ዓይነት ኣባል',
        'amount' => 'መጠን',
        'deadline' => 'ዝኸፈለሉ ዕለት'
        ];
        $validator->setAttributeNames($fieldNames);
        if($validator->fails()){
            return [false, 'error', $validator->errors()->all()];
        }
        $mewacho = MewachoSetting::find($request->id);
        $mewacho->name = $request->name;
        $mewacho->purpose = $request->purpose;
        $mewacho->mtype = $request->mtype;
        $mewacho->amount = $request->amount;
        $mewacho->deadline = $this->correctDate($request->deadline);
        $mewacho->save();   
        return[true, 'info', "መዋጮ ተስተኻኺሉ ኣሎ", $mewacho->id];
    }
    public function deleteMewacho(Request $request)
    {   
        $mewacho = MewachoSetting::find($request->id);
        $mewacho->delete();
        return [true, "መዋጮ ተሰሪዙ ኣሎ"];
        
    }
}

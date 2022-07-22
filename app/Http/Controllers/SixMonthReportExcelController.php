<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

require_once substr(dirname(__FILE__), 0, -17).'\PHPExcel-1.8\Classes\PHPExcel.php';

use PHPExcel;
use PHPExcel_IOFactory;

use  App\Zobatat;
use  App\Woreda; 
use  App\Tabia;
use  App\Wahio;
use  App\meseretawiWdabe;
use  App\ApprovedHitsuy;
use  App\Hitsuy;
use DB;

class SixMonthReportExcelController extends Controller
{
    private function loadData($zoneCode){
        $zoneName = Zobatat::where('zoneCode', $zoneCode)->select(['zoneName'])->first()->toArray()['zoneName'];
        $woredas = Woreda::where('zoneCode', $zoneCode)->select(['woredaCode', 'isUrban', 'name'])->get()->toArray();
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
        return [$zoneName, $ketemageter, $geterwidabe, $ketemawidabe, $deant];
    }
    public function index(Request $request)
    {
        $zoneCode = "01";
        $zoneName = Zobatat::where('zoneCode', $zoneCode)->select(['zoneName'])->first()->toArray()['zoneName'];
        header('Content-Type: application/vvnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $zoneName . '.xlsx"');
        header('Cache-Control: max-age=0');
        $data = $this->loadData($zoneCode);
        $objPHPExcel = new PHPExcel();

        // ketema geter
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', '2011 ዓ/ም ናይ ዞባታት ገጠርን ከተማን ኣባል ውድብ /ዞባ '.$data[0])
            ->setCellValue('B2', 'ገጠር')
            ->setCellValue('E2', 'ከተማ')
            ->setCellValue('H2', 'ጠ/ድምር')
            ->setCellValue('A3', 'ወረዳ')
            ->setCellValue('B3', 'ተባ')
            ->setCellValue('C3', 'ኣን')
            ->setCellValue('D3', 'ድምር')
            ->setCellValue('E3', 'ተባ')
            ->setCellValue('F3', 'ኣን')
            ->setCellValue('G3', 'ድምር')
            ->setCellValue('H3', 'ተባ')
            ->setCellValue('I3', 'ኣን')
            ->setCellValue('J3', 'ድምር')
            ->mergeCells('A1:J1')
            ->mergeCells('B2:D2')
            ->mergeCells('E2:G2')
            ->mergeCells('H2:J2');
        $i = 4;
        foreach ($data[1] as $a) {
              for ($b=0;$b<count($a);$b++) {
                    $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue(chr(65+$b).(string)$i, $a[$b]);
              }
              $i++;
        }

        //Geter widabe
        $i += 2;
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A'.$i, 'ገጠር ውዳበ ዞባ '.$data[0])
            ->mergeCells('A'.$i.':N'.$i)
            ->setCellValue('B'.($i+1), 'ገባር')
            ->setCellValue('E'.($i+1), 'ሲ/ሰርቫንት')
            ->setCellValue('H'.($i+1), 'መምህራን')
            ->setCellValue('K'.($i+1), 'ተምሃሮ')
            ->setCellValue('N'.($i+1), 'ድምር')
            ->setCellValue('A'.($i+1), 'ወረዳ')
            ->setCellValue('B'.($i+2), 'ተባ')
            ->setCellValue('C'.($i+2), 'ኣን')
            ->setCellValue('D'.($i+2), 'ድምር')
            ->setCellValue('E'.($i+2), 'ተባ')
            ->setCellValue('F'.($i+2), 'ኣን')
            ->setCellValue('G'.($i+2), 'ድምር')
            ->setCellValue('H'.($i+2), 'ተባ')
            ->setCellValue('I'.($i+2), 'ኣን')
            ->setCellValue('J'.($i+2), 'ድምር')
            ->setCellValue('H'.($i+2), 'ተባ')
            ->setCellValue('I'.($i+2), 'ኣን')
            ->setCellValue('J'.($i+2), 'ድምር')
            ->setCellValue('K'.($i+2), 'ተባ')
            ->setCellValue('L'.($i+2), 'ኣን')
            ->setCellValue('M'.($i+2), 'ድምር')
            ->setCellValue('N'.($i+2), 'ተባ')
            ->setCellValue('O'.($i+2), 'ኣን')
            ->setCellValue('P'.($i+2), 'ድምር')
            ->mergeCells('A'.($i+1).':A'.($i+2))
            ->mergeCells('B'.($i+1).':D'.($i+1))
            ->mergeCells('E'.($i+1).':G'.($i+1))
            ->mergeCells('H'.($i+1).':J'.($i+1))
            ->mergeCells('K'.($i+1).':M'.($i+1))
            ->mergeCells('N'.($i+1).':P'.($i+1));

        $i += 3;
        foreach ($data[2] as $a) {
              for ($b=0;$b<count($a);$b++) {
                    $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue(chr(65+$b).(string)$i, $a[$b]);
              }
              $i++;
        }


        //Ketema widabe
        $i += 2;
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A'.$i, 'ከተማ ውድብ ዞባ '.$data[0])
            ->mergeCells('A'.$i.':N'.$i)
            ->setCellValue('B'.($i+1), 'ደኣንት')
            ->setCellValue('E'.($i+1), 'ሸቃሎ')
            ->setCellValue('H'.($i+1), 'ሰብ ሞያ')
            ->setCellValue('K'.($i+1), 'መምህራን')
            ->setCellValue('N'.($i+1), 'ተምሃሮ')
            ->setCellValue('Q'.($i+1), 'ድምር')
            ->setCellValue('A'.($i+1), 'ወረዳ')
            ->setCellValue('B'.($i+2), 'ተባ')
            ->setCellValue('C'.($i+2), 'ኣን')
            ->setCellValue('D'.($i+2), 'ድምር')
            ->setCellValue('E'.($i+2), 'ተባ')
            ->setCellValue('F'.($i+2), 'ኣን')
            ->setCellValue('G'.($i+2), 'ድምር')
            ->setCellValue('H'.($i+2), 'ተባ')
            ->setCellValue('I'.($i+2), 'ኣን')
            ->setCellValue('J'.($i+2), 'ድምር')
            ->setCellValue('H'.($i+2), 'ተባ')
            ->setCellValue('I'.($i+2), 'ኣን')
            ->setCellValue('J'.($i+2), 'ድምር')
            ->setCellValue('K'.($i+2), 'ተባ')
            ->setCellValue('L'.($i+2), 'ኣን')
            ->setCellValue('M'.($i+2), 'ድምር')
            ->setCellValue('N'.($i+2), 'ተባ')
            ->setCellValue('O'.($i+2), 'ኣን')
            ->setCellValue('P'.($i+2), 'ድምር')
            ->setCellValue('Q'.($i+2), 'ተባ')
            ->setCellValue('R'.($i+2), 'ኣን')
            ->setCellValue('S'.($i+2), 'ድምር')
            ->mergeCells('A'.($i+1).':A'.($i+2))
            ->mergeCells('B'.($i+1).':D'.($i+1))
            ->mergeCells('E'.($i+1).':G'.($i+1))
            ->mergeCells('H'.($i+1).':J'.($i+1))
            ->mergeCells('K'.($i+1).':M'.($i+1))
            ->mergeCells('N'.($i+1).':P'.($i+1))
            ->mergeCells('Q'.($i+1).':S'.($i+1));

            $i += 3;
            foreach ($data[3] as $a) {
                  for ($b=0;$b<count($a);$b++) {
                        $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue(chr(65+$b).(string)$i, $a[$b]);
                  }
                  $i++;
            }


            //Deant
            $i += 2;
            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A'.$i, 'ደኣንት ዞባ' . $data[0] .' ብማሕበራዊ ቦታ')
            ->mergeCells('A'.$i.':N'.$i)
            ->setCellValue('B'.($i+1), 'መፍረይቲ')
            ->setCellValue('E'.($i+1), 'ከ/ሕርሻ')
            ->setCellValue('H'.($i+1), 'ኮንስትራክሽን')
            ->setCellValue('K'.($i+1), 'ንግዲ')
            ->setCellValue('N'.($i+1), 'ግልጋሎት')
            ->setCellValue('Q'.($i+1), 'ድምር')
            ->setCellValue('A'.($i+1), 'ወረዳ')
            ->setCellValue('B'.($i+2), 'ተባ')
            ->setCellValue('C'.($i+2), 'ኣን')
            ->setCellValue('D'.($i+2), 'ድምር')
            ->setCellValue('E'.($i+2), 'ተባ')
            ->setCellValue('F'.($i+2), 'ኣን')
            ->setCellValue('G'.($i+2), 'ድምር')
            ->setCellValue('H'.($i+2), 'ተባ')
            ->setCellValue('I'.($i+2), 'ኣን')
            ->setCellValue('J'.($i+2), 'ድምር')
            ->setCellValue('H'.($i+2), 'ተባ')
            ->setCellValue('I'.($i+2), 'ኣን')
            ->setCellValue('J'.($i+2), 'ድምር')
            ->setCellValue('K'.($i+2), 'ተባ')
            ->setCellValue('L'.($i+2), 'ኣን')
            ->setCellValue('M'.($i+2), 'ድምር')
            ->setCellValue('N'.($i+2), 'ተባ')
            ->setCellValue('O'.($i+2), 'ኣን')
            ->setCellValue('P'.($i+2), 'ድምር')
            ->setCellValue('Q'.($i+2), 'ተባ')
            ->setCellValue('R'.($i+2), 'ኣን')
            ->setCellValue('S'.($i+2), 'ድምር')
            ->mergeCells('A'.($i+1).':A'.($i+2))
            ->mergeCells('B'.($i+1).':D'.($i+1))
            ->mergeCells('E'.($i+1).':G'.($i+1))
            ->mergeCells('H'.($i+1).':J'.($i+1))
            ->mergeCells('K'.($i+1).':M'.($i+1))
            ->mergeCells('N'.($i+1).':P'.($i+1))
            ->mergeCells('Q'.($i+1).':S'.($i+1));

            $i += 3;
            foreach ($data[4] as $a) {
                  for ($b=0;$b<count($a);$b++) {
                        $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue(chr(65+$b).(string)$i, $a[$b]);
                  }
                  $i++;
            }



        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        // $objWriter->save(public_path('Nigga.xlsx'));
    }
}

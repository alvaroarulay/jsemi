<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asignaciones;
use App\Models\actaDevolucion;
use Barryvdh\DomPDF\Facade\Pdf;
use Jenssegers\Date\Date;
use App\Models\Responsables;

class DevolucionesController extends Controller
{
    public function actaDevoluciones(Request $request){
        $codresp = $request->codresp;
        $codofic = $request->codofic;
        $id_dev = $request->id_dev;
        $unidad = $request->unidad;
        $idrol = \Auth::user()->idrol;
        $unidad = \Auth::user()->unidad;

        Date::setLocale('es');
        $fechaTitulo = Date::now()->format('l j F Y');
        $fechDerecha = Date::now()->format('d/M/Y');
    
        $datos = Asignaciones::join('actual','actual.id','=','asignacion.codactual')
                               ->join('auxiliar',function ($join) {
                                    $join->on('actual.codaux', '=', 'auxiliar.codaux');
                                    $join->on('actual.codcont', '=', 'auxiliar.codcont');
                                })
            ->join('estado','actual.codestado','=','estado.id')
            ->join('unidadadmin','actual.unidad','=','unidadadmin.unidad')
            ->select('actual.codigo','actual.codaux','auxiliar.nomaux','estado.nomestado', 'actual.descripcion','actual.codcont')
            ->distinct()
            ->where('asignacion.id_asignacion','=',$id_dev)
            ->where('asignacion.codresp','=',$codresp)
            ->where('asignacion.codofic','=',$codofic)
            ->where('asignacion.unidad','=',$unidad)
            ->where('asignacion.descripcion','=',0)
            ->get();

        $total = $datos->count();
        if($idrol == 1){
            if($unidadv == ''){
                $responsable = Responsables::join('oficina','resp.codofic','=','oficina.codofic')
                ->join('unidadadmin','resp.unidad','=','unidadadmin.unidad')
                ->select('resp.nomresp','oficina.nomofic','resp.cargo','oficina.codofic','resp.ci','unidadadmin.descrip as unidad')
                ->where('resp.unidad','=',$unidad)
                ->where('resp.codresp','=',$codresp)
                ->where('resp.codofic','=',$codofic)->first();
            }
            else{
                $responsable = Responsables::join('oficina','resp.codofic','=','oficina.codofic')
                ->join('unidadadmin','resp.unidad','=','unidadadmin.unidad')
                ->select('resp.nomresp','oficina.nomofic','resp.cargo','oficina.codofic','resp.ci','unidadadmin.descrip as unidad')
                ->where('resp.unidad','=',$unidadv)
                ->where('resp.codresp','=',$codresp)
                ->where('resp.codofic','=',$codofic)->first();  
            }
        }
        else{
            $responsable = Responsables::join('oficina','resp.codofic','=','oficina.codofic')
            ->join('unidadadmin','resp.unidad','=','unidadadmin.unidad')
            ->select('resp.nomresp','oficina.nomofic','resp.cargo','oficina.codofic','resp.ci','unidadadmin.descrip as unidad')
            ->where('resp.unidad','=',$unidad)
            ->where('resp.codresp','=',$codresp)
            ->where('resp.codofic','=',$codofic)->first();
        }
        $pdf=Pdf::loadView('plantillapdf.repDevolucion',['datos'=>$datos,'responsable'=>$responsable,'fechaTitulo'=>$fechaTitulo,'fechaDerecha'=>$fechDerecha,'total'=>$total, 'unidad'=>$unidad]);
        $pdf->set_paper(array(0,0,800,617));
        return $pdf->stream();
    }
}

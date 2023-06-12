<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\App;
use Luecano\NumeroALetras\NumeroALetras;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Intervention\Image\ImageManagerStatic as Image;

class ConsultaController extends Controller
{
    public function consulta(Request $request)
    {
        $request->validate([
            'ci' => 'required',
        ]);
        $ci = $request->ci;
        return DB::select("select *
from  tbfactura
where IdCli ='$ci' and cuffac!=''
order by CodAut desc
");
    }

    public function facturaPdf($CodAut){
        $fact=DB::SELECT("SELECT * from tbfactura where CodAut =$CodAut")[0];
        $cliente=DB::SELECT("SELECT * from tbclientes where Id = $fact->IdCli")[0];
        $detalle=DB::SELECT("SELECT v.PVentUnit,v.Monto,v.cant,v.cod_pro,v.Descuatot,p.Producto,(select m.Descripcion from tbunidmed m
        where m.codUnid=v.Unidpeso) as unidad
        FROM tbventas v inner join tbproductos p on v.cod_pro=p.cod_prod
        WHERE v.Comanda=$fact->comanda;");
        $ley=DB::SELECT("SELECT * from tbleyenda where codAut=$fact->Docifi");
        if(sizeof($detalle)<=0)
            return false;
        $subtotal=0;
        $contenido='';

        foreach ($detalle as $value) {
            # code...
            if($value->unidad==null) $value->unidad='KILOGRAMO';
            $contenido.="<tr ><td class='detalle2' style='text-align:right;'>".$value->cod_pro."</td><td class='detalle2' style='text-align:right;'>".number_format($value->cant,2)."</td><td class='detalle2'>".$value->unidad."</td><td class='detalle2'>".$value->Producto."</td><td class='detalle2' style='text-align:right;'>".number_format($value->PVentUnit,2)."</td><td class='detalle2' style='text-align:right;'>".number_format($value->Descuatot,2)."</td><td class='detalle2' style='text-align:right;'>".number_format($value->Monto,2)."</td></tr>";
            $subtotal+=$value->Monto;
        }
        $entero= intval($subtotal);
        $decimal= intval(($subtotal-$entero) * 100);
        $formatter = new NumeroALetras();
        $suma=0;
        $autoriza=$fact->cuffac;
        $urlsiat="https://siat.impuestos.gob.bo/consulta/QR?nit=3779602010&cuf=".$autoriza."&numero=".$fact->nrofac."&t=2";
        $png = QrCode::format('png')->size(250)->generate($urlsiat);
        $png = base64_encode($png);
        $cadena="<style>
        .imagen{
            width:150px;
        }
        *{
            margin:5px;
            font-size:12px;
            font-family: Calibri, sans-serif;
        }
        table {
            width: 100%;
          }
        .area{
            border: 1px solid;
            border-radius: 5px;
        }
        .titulo1{
            text-align: center;
            font-weight: bold;
        }
        .detalle  {
            border: 1px solid;
            border-collapse: collapse;
          }
          .detalle2 {
            padding: 2px;
            border: 1px solid;
            border-collapse: collapse;
            font-size: 11px;
          }
        </style>
        <div style='padding-left: 10px;padding-right: 10px'>
         <table>
        <tr>
        <td style='text-align: center;'>
            <img class='imagen' src='img/sofia.png'>
        </td>
        <td>
        <table class='area'>
        <tr>
        <td><b>NIT:</b></td><td>3779602010</td></tr><tr><td><b>FACTURA No: </b></td><td>$fact->nrofac</td></tr><tr><td style='vertical-align:top'><b>COD. AUTORIZACION:</b> </td><td>".substr($autoriza,0,23)."<br>".substr($autoriza,23,23)."<br> ".substr($autoriza,46)."</td></tr></table></td></tr>
        <tr class='titulo1'><td class='area'>ALMACEN SOFIA<br>SUCURSAL 1<br>PUNTO DE VENTA $fact->PuntVenta<br>Prolongacion Campo Jordan esq Tacna Nro 28 ZONA Norte<br>Telefono : 5230064<br>ORURO</td><td><span style='color:blue;  font-size:16px;font-weight: bold'>COPIA</span><br>$fact->comanda</td></tr></table>
        <div class='titulo1'><span style='color:blue; font-size:16px;font-weight: bold'>FACTURA</span><br><span>(Con derecho a crédito fiscal)</span></div>
        <table class='area'>
        <tr><td><b>FECHA:</b></td><td>$fact->FechaFac</td><td><b>NIT/CI/CEX:</b></td><td>$cliente->Id</td><td><b>Compl:</b></td><td>$cliente->complto</td></tr>
        <tr><td><b>Nombres/Razon Social:</b></td><td>$cliente->Nombres</td><td><b>Cod Cliente:</b></td><td>$cliente->Cod_Aut</td><td></td><td></td></tr>
        </table>
        <table class='detalle'>
        <tr>
            <th style='padding: 5px;border: 1px solid'>Código Producto Servicio</th>
            <th style='padding: 5px;border: 1px solid'>Cantidad</th>
            <th style='padding: 5px;border: 1px solid'>Unidad de Medida</th>
            <th style='padding: 5px;border: 1px solid'>Descripcion</th>
            <th style='padding: 5px;border: 1px solid'>Precio unitario</th>
            <th style='padding: 5px;border: 1px solid'>Descuento</th>
            <th style='padding: 5px;border: 1px solid'>Importe</th>
          </tr>
        ".$contenido."
        </table>
        <table>
            <tr>
                <td style='vertical-align:top'><b>Son:</b> ".$formatter->toString($entero)." $decimal/100 Bolivianos</td>
                <td><table class='detalle2'><tr class='detalle2'><td>SUBTOTAL Bs.</td><td style='color:blue; font-size:16px;font-weight: bold'>".number_format($subtotal,2)."</td></tr><tr class='detalle2'><td>DESCUENTO Bs.</td><td>0</td></tr><tr class='detalle2'><td>TOTAL Bs.</td><td>".number_format($subtotal,2)."</td></tr><tr class='detalle2'><td>MONTO GIFT CARD Bs.</td><td>0</td></tr><tr class='detalle2'><td><b>MONTO A PAGAR Bs.</b></td><td>".number_format($subtotal,2)."</td></tr><tr class='detalle2'><td>IMPORTE BASE CRÉDITO FISCAL Bs.</td><td>".number_format($subtotal,2)."</td></tr></table></td>
        </tr></table>
        <table><tr>
        <td style='text-align:center;'>&quot;ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS, EL USO ILÍCITO SERÁ SANCIONADO PENALMENTE DE ACUERDO A LEY&quot;.</td>
        <td rowspan=3 style='width:30%; text-align:center;'><img src='data:image/png;base64,". $png ."' style='border:2px solid white;width: 120px;height: 120px'></td></tr>
        <tr><td style='text-align:center;'>$ley->leyenda</td></tr>
        <tr><td style='text-align:center;'> Este documento es la Representación Gráfica de un Documento Fiscal Digital emitido en una modalidad de facturación en linea.</td></tr>
        </table>
        </div>

        ";
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($cadena);
        return $pdf->stream();
    }
}

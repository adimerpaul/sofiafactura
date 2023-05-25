<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\App;

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
        $cliente=DB::SELECT("SELECT * from tbclientes where Id = $fact->IdCli");
        $detalle=DB::SELECT("SELECT v.PVentUnit,v.Monto,v.cant,v.cod_pro,v.Descuatot,p.Producto FROM tbventas v inner join tbproductos p on v.cod_pro=p.cod_prod WHERE comanda=$fact->comanda;");
        $detalle='';
        $suma=0;
        $autoriza=$fact->cuffac;
        $cadena="<style>
        .imagen{
            height:100px;
            width:300px;
        }
        *{ margin:5px;
        font-size:12px;}
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
        .detalle, th {
            border: 1px solid;
            border-collapse: collapse;
          }
          .detalle2 {
            border: 1px solid;
            border-collapse: collapse;
          }
        </style>
        <table>
        <tr><td style='width:50%'><img class='imagen' src='img/sofia.png' /></td><td><table class='area'><tr><td>NIT:</td><td>3779602010</td></tr><tr><td>FACTURA No: </td><td>$fact->nrofac</td></tr><tr><td style='vertical-align:top'>COD. AUTORIZACION: </td><td>".substr($autoriza,0,23)."<br>".substr($autoriza,23,23)."<br> ".substr($autoriza,46)."</td></tr></table></td></tr>
        <tr class='titulo1'><td class='area'>ALMACEN SOFIA<br>SUCURSAL 1<br>PUNTO DE VENTA $fact->PuntVenta<br>Prolongacion Campo Jordan esq Tacna Nro 28 ZONA Norte<br>Telefono : 5230064<br>ORURO</td></tr></table>
        <div class='titulo1'>FACTURA<br><span>(Con derecho a crédito fiscal)</span></div>
        <table class='area'>
        <tr><td>FECHA:</td><td></td><td>NIT/CI/CEX:</td><td></td><td>Compl:</td><td></td></tr>
        <tr><td>Nombres/Razon Social:</td><td></td><td>Cod Cliente:</td><td></td><td></td><td></td></tr>
        </table>
        <table class='detalle'>
        <tr><th>Código Producto Servicio</th><th>Cantidad</th><th>Unidad de  medida</th><th>Descripcion</th><th>Precio unitario</th><th>Descuento</th><th>Importe</th></tr>
        ".$detalle."
        </table>
        <table><tr><td style='vertical-align:top'>Son: Seis mil trescientos setenta y cinco 82/100 Bolivianos</td>
        <td><table class='detalle2'><tr class='detalle2'><td>SUBTOTAL Bs.</td><td>0</td></tr><tr class='detalle2'><td>DESCUENTO Bs.</td><td>0</td></tr><tr class='detalle2'><td>TOTAL Bs.</td><td>0</td></tr><tr class='detalle2'><td>MONTO GIFT CARD Bs.</td><td>0</td></tr><tr class='detalle2'><td>MONTO A PAGAR Bs.</td><td>0</td></tr><tr class='detalle2'><td>IMPORTE BASE CRÉDITO FISCAL Bs.</td><td>0</td></tr></table></td>
        </tr></table>
        <table><tr><td>&quot;ESTA FACTURA CONTRIBUYE AL DESARROLLO DEL PAÍS, EL USO ILÍCITO SERÁ SANCIONADO PENALMENTE DE ACUERDO A LEY&quot;.</td><td rowspan=3 style='width:20%'>qr</td></tr>
        <tr><td>No 453: En caso de incumplimiento a lo ofertado o convenido, el proveedor debe reparar o sustituir el producto..</td></tr>
        <tr><td> Este documento es la Representación Gráfica de un Documento Fiscal Digital emitido en una modalidad de facturación en linea.</td></tr>
        </table>
        ";
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($cadena);
        return $pdf->stream();
    }
}

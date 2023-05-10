<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsultaController extends Controller
{
    public function consulta(Request $request)
    {
        $request->validate([
            'ci' => 'required',
        ]);
        $ci = $request->ci;
        return DB::select("select *
from tbctascobbk t
inner join tbfactura t2 on t2.comanda =t.comanda
where CINIT ='$ci' and cuffac!=''
order by t.CodAuto desc
");
    }
}

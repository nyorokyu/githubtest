<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Files\LocalTemporaryFile;
use Illuminate\Support\Facades\DB;
use Session;


class ExcelExportComparisonData implements FromCollection, WithEvents
{
    use Exportable;

    private $template_file = null;

    /**
     * @param string $template_file
     * @return $this
     */
    public function setTemplate(string $template_file)
    {
        if (file_exists($template_file)) {
            $this->template_file = $template_file;
        }
        return $this;
    }


    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {

        // return User::all();
        $id = Session::get('masterId');
        $dates = DB::table('csv_excellent_master AS cem')
                  ->leftjoin('csv_client AS cc', 'cem.bl_code', '=', 'cc.bl_code')
                  ->select(DB::raw('cem.bl_code, cem.item_name, cem.quantity, cc.item_name AS item_name_client, cc.quantity AS quantity_client'))
                  ->where([['cem.is_deleted', 0], ['cem.master_id', $id]])
                  ->orderBy('cem.id', 'ASC')
                  ->limit(30)
                  ->get();
        return $dates;
    }

  
    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            BeforeExport::class => function (BeforeExport $event) {
                if (is_null($this->template_file)) {
                    return;
                }
                $event->writer->reopen(new LocalTemporaryFile($this->template_file), Excel::XLSX);
                $event->writer->getSheetByIndex(0);
                return $event->getWriter()->getSheetByIndex(0);
            },
        ];
    }


}

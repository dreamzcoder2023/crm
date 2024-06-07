<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use DB;
use App\Models\ProjectDetails;
use App\Models\ExpensesUnpaidDate;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClientSummaryExport implements FromCollection, WithHeadings, WithMapping
{


    public function __construct($project_filter, $user_filter, $from, $to_date)
    {

        $this->project_filter = $project_filter;
        $this->user_filter = $user_filter;
        $this->from = $from;
        $this->to_date = $to_date;

    }

    // Headings
    public function headings(): array{

        return[
            'Client Name',
            'Received Date',
            'Project Name',
            'Received Amount',
            'Total Amount',
            'Payment Mode',
            'Stages'


        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */

    public function collection()
    {
        $clients = ProjectDetails::join('wallet','wallet.project_id','=','project_details.id')->join('stage','stage.id','=','wallet.stage_id')->join('clientdetails','wallet.client_id','=','clientdetails.id')->join('payment','payment.id','=','wallet.payment_mode')->select('project_details.*','stage.name as stage_name','wallet.amount','payment.name as payment','wallet.current_date as currentdate','clientdetails.first_name','clientdetails.last_name');
        if($this->from != '' && $this->to_date != ''){
            $clients = $clients->whereBetween('wallet.current_date', [$this->from,$this->to_date]);
        }
        if($this->project_filter != 'undefined' && $this->project_filter != ''){
            $clients = $clients->where('wallet.project_id',$this->project_filter);
            //dd($clients);exit;
          }
          if($this->user_filter != 'undefined' && $this->user_filter != ''){
            $clients = $clients->where('wallet.client_id',$this->user_filter);
          }

        $clients = $clients->get();


        return collect($clients);
    }
    // here you select the row that you want in the file
    public function map($row): array{

        $fields = [
           $row->first_name.' '.$row->last_name,
           $row->currentdate,
           $row->name,
           $row->amount,
           $row->total_amt,
           $row->payment,
           $row->stage_name,



      ];

     return $fields;
    }

}

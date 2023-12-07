<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use DB;
use App\Models\ProjectDetails;
use App\Models\Wallet;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PaymentIncomeExport implements FromCollection, WithHeadings, WithMapping
{
    

    public function __construct($user_filter, $from, $to_date,$id)
    {
      
    
        $this->user_filter = $user_filter;
        $this->from = $from;
        $this->to_date = $to_date;
        $this->id = $id;
        
    }

    // Headings
    public function headings(): array{
  
        return[
            'Client Name',
            'Received Amount',
            'Payment Mode',
            'Description',
            'Stages',
            'Received Date'

        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */

    public function collection()
    {
        $project = Wallet::join('clientdetails','clientdetails.id','=','wallet.client_id')->join('payment','payment.id','=','wallet.payment_mode')->join('stage','stage.id','=','wallet.stage_id')->where('wallet.project_id',$this->id)->select('wallet.*','clientdetails.first_name','clientdetails.last_name','payment.name as payment_name','stage.name as stage_name');
        if($this->from != '' && $this->to_date != ''){
          $project = $project->whereBetween('wallet.current_date', [$this->from,$this->to_date]);
      }
   
        if($this->user_filter != 'undefined' && $this->user_filter != ''){
          $project = $project->where('wallet.client_id',$this->user_filter);
        }
        $project = $project->get();


        return collect($project);
    }
    // here you select the row that you want in the file
    public function map($row): array{
     
        $fields = [
           $row->first_name.' '.$row->last_name,
           $row->amount,
           $row->payment_name,
           $row->description,
           $row->stage_name,
           $row->current_date,
          
           
      ];
  
     return $fields;
    }

}

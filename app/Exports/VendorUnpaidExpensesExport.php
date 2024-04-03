<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use DB;
use App\Models\Expenses;
use App\Models\ExpensesUnpaidDate;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VendorUnpaidExpensesExport implements FromCollection, WithHeadings, WithMapping
{


    public function __construct($category_filter , $project_filter, $user_filter, $from, $to_date,$auth,$role)
    {
        $this->category_filter = $category_filter;
        $this->project_filter = $project_filter;
        $this->user_filter = $user_filter;
        $this->from = $from;
        $this->to_date = $to_date;
        $this->auth = $auth;

        $this->role = $role;

    }

    // Headings
    public function headings(): array{
        return[
            'Category Name',
            'Paid Date',
            'Project Name',
            'Labour Name',
            'Amount',
            'Paid Amount',
            'Unpaid Amount',
            'Advanced Amount',
            'Description',
            'Payment Mode',

            'Added By',
            'Edited By',
            'Advance Edited By'

        ];
    }

    /**
    * @return \Illuminate\Support\Collection
    */

    public function collection()
    {
        $expenses = Expenses::whereNotNull('expenses.vendor_id')->where('expenses.unpaid_amt', '!=', 0)->leftjoin('category', 'category.id', '=', 'expenses.category_id')->leftjoin('vendor_details as l','l.id','=','expenses.vendor_id')
        ->leftJoin('project_details', function ($join) {
          $join->on('project_details.id', 'expenses.project_id')
            ->where('expenses.project_id', '!=', null);
        });


      $expenses = $expenses->leftjoin('payment', 'payment.id', '=', 'expenses.payment_mode')
        ->where(['category.active_status' => 1, 'category.delete_status' => 0]);

        $expenses = $expenses->leftjoin('users', 'users.id', '=', 'expenses.editedBy')->leftjoin('users as users_add', 'users_add.id', '=', 'expenses.user_id')->leftjoin('users as labour_ad','labour_ad.id','=','expenses.is_advance');
        $expenses = $expenses->select('expenses.*', 'category.name as category_name', 'project_details.name as project_name', 'payment.name as payment_name', 'users.first_name', 'users.last_name', 'users_add.first_name as first', 'users_add.last_name as last','l.name as labour_name','labour_ad.first_name as labour_first','labour_ad.last_name as labour_last');
      if ($this->from != '' ) {
        $expenses = $expenses->wheredate('current_date', '>=',$this->from);
        //   ->toSql();
        //  // $bindings = $expenses->getBindings();
        //   print_r($expenses);
        //  exit;

      }
      if($this->to_date != ''){
        $expenses = $expenses->wheredate('current_date', '<=',$this->to_date);
      }
      if ($this->category_filter != 'undefined' && $this->category_filter != '') {
        $expenses = $expenses->where('expenses.category_id', $this->category_filter);
      }
      if ($this->project_filter != 'undefined' && $this->project_filter != '') {
        $expenses = $expenses->where('expenses.project_id', $this->project_filter);
        //dd($expenses);exit;
      }
      if ($this->user_filter != 'undefined' && $this->user_filter != '') {
        $expenses = $expenses->where('expenses.user_id', $this->user_filter);
      }

      // //dd($expenses);
      // if ($this->amount != '' && $this->amount != 'undefined') {
      //   $expenses = $expenses->orderBy('expenses.amount', $this->amount)->get();
      // }

      $expenses = $expenses->orderBy('expenses.id', 'desc')->get();

        return collect($expenses);
    }
    // here you select the row that you want in the file
    public function map($row): array{
        $unpaid_amt = ExpensesUnpaidDate::where('expense_id',$row->id)?->select('*')?->orderBy('id','desc')?->first();
        $unpaid_amt1 = !empty($unpaid_amt) ? $unpaid_amt->updated_at : $row->current_date;
        $fields = [
           $row->category_name,
           $row->current_date,
           $row->project_name,
           $row->labour_name,
           $row->amount,
           $row->paid_amt,
           $row->unpaid_amt,
           $row->extra_amt,
           $row->description,
           $row->payment_name,

           $row->first.' '.$row->last,
           $row->first_name.' '.$row->last_name,
           $row->labour_first.' '.$row->labour_last

      ];

     return $fields;
    }

}

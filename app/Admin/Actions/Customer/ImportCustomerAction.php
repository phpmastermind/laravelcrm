<?php

namespace App\Admin\Actions\Customer;

use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ImportCustomer;
use App\Models\Customer;

class ImportCustomerAction extends Action
{
    public $name = 'Import Data';

    protected $selector = '.import-customer';

    public function handle(Request $request)
    {
        // $request ...
        // The following code gets the uploaded file, then uses the package `maatwebsite/excel` to process and upload your file and save it to the database.
        Excel::import(new ImportCustomer, $request->file('file')->store('files'));
        return $this->response()->success('Success message...')->refresh();
    }

    public function form()
    {
        $this->file('file', 'Please select file');
    }

    public function html()
    {
        return <<<HTML
        <a class="btn btn-sm btn-default import-customer"><i class="fa fa-upload"></i>Import data</a>
HTML;
    }
}
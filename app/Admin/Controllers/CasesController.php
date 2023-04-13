<?php

namespace App\Admin\Controllers;

//require_once("/home/dh_8dcy8x/kentcrm.fugital.com/vendor/twilio/sdk/src/Twilio/autoload.php");


use App\Models\Customer;
//use App\Models\Engineer;
use App\Models\CaseType;
use App\Models\Cases;

use Encore\Admin\Controllers\AdminController;
use Illuminate\Support\Str;
use Illuminate\Support\MessageBag;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Twilio\Rest\Client;
use Log;

class CasesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Cases';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Cases());

        $grid->column('case_number', __('Case Number'));
        $grid->column('customer.name', __('Customer'));
        $grid->column('engineer.name', __('Engineer'));
        $grid->column('casetype.title', __('Case type'));
        $grid->column('remarks', __('Remarks'));
        $grid->column('case_status', __('Case status'));
       // $grid->table('extra', __('Extra'));
        $grid->column('created_at', __('Created at'))->date('Y-m-d');
        //$grid->column('updated_at', __('Updated at'))->date('Y-m-d');

        $grid->model()->orderBy('created_at','desc');
        $grid->filter(function($filter){
           $filter->disableIdFilter();
           $filter->equal('case_number', __('Case Number'));
           $filter->equal('customer.id', __('Customer Name'))->select(function(){
                $engineers = \App\Models\Customer::All();
                $options = [];
                foreach ($engineers as $engineer){
                    $options[$engineer->id] = $engineer->name;
                }
                return $options;
            });

            $filter->equal('engineer.id', __('Engineer Name'))->select(function(){
                $engineers = \App\Models\Engineer::All();
                $options = [];
                foreach ($engineers as $engineer){
                    $options[$engineer->id] = $engineer->name;
                }
                return $options;
            });

            $filter->equal('case_status', __('Case Status'))->select([
                'OPEN' => 'Open',
                'ACTIVE' => 'Active',
                'CLOSE' => 'Close'
            ]);

        });
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $case = Cases::findOrFail($id);
        $show = new Show($case);

        $show->field('case_number', __('Case Number'));
        $show->field('customer.name', __('Customer'));
        $show->field('engineer.name', __('Engineer'));
        $show->field('casetype.title', __('Case type'));
        $show->field('remarks', __('Remarks'));
       // $show->field('extra', __('Extra'));
        $show->field('case_status', __('Case status'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Cases());
        $case = Cases::latest()->first();
        $newid = 1000 + ((int) $case->id + 1);
        $caseid = "KENTRO".$newid;
        $form->text('case_number','Case ID')->readonly()->default($caseid);
        $form->select('customer_id',"Select Customer")->options(function(){
            $engineers = \App\Models\Customer::All();
            $options = [];
            foreach ($engineers as $engineer){
                $options[$engineer->id] = $engineer->name;
            }
            return $options;
        });
        
        $form->select('case_type', __('Case Type'))->options((new CaseType)::selectOptions())->default(2);
        
        $form->divider('Assign Engineer');

        $form->select('engineer_id',"Select Engineer")->options(function(){
            $engineers = \App\Models\Engineer::All();
            $options = [];
            foreach ($engineers as $engineer){
                $options[$engineer->id] = $engineer->name;
            }
            return $options;
        });
        $form->date('visit_date', __('Visit Date'))->format("DD/MM/YYYY");
        $form->time('visit_time', __('Visit Time'))->format('hh:mm A');
        
        $form->divider('Service Details');
        $form->datetime('service_date', __('Service Date'))->format("YYYY-MM-DD")->default(date('Y-m-d'));
        $form->datetime('service_time', __('Service Time'))->format("HH:mm:ss")->default(date('H:i:s'));
        $form->select('service_status', __('Service status'))->options([
            '0' => 'Incomplete',
            '1'  =>  'Complete',
        ])->default('0');
        $form->textarea('remarks', __('Remarks'));
        
        $form->select('case_status', __('Case status'))->options([
            'OPEN'=>'Open',
            'CLOSE' => 'Close',
            'ACTIVE'=>'Active'
        ])->default('OPEN');

        $form->keyValue('extra', __('Extra'));
        
        $form->saving(function (Form $form){
            $customer = \App\Models\Customer::findOrFail($form->customer_id);
            // dump($customer);
            // if service complete
            if($form->service_status == '' && $form->case_status == 'CLOSE'){
                $error = new MessageBag([
                    'title'   => 'Select Service Status',
                    'message' => 'Please mark the service status before closing the case.',
                ]);

                return back()->with(compact('error'));
            }
            if($form->service_status == 1){
                if($form->case_status == 'CLOSE'){
                    // update last service date in 
                    $customer->last_service_date = $form->service_date;
                    $customer->save();
                }else{
                    $error = new MessageBag([
                        'title'   => 'Select Case Status',
                        'message' => 'Please select the case status as close.',
                    ]);

                    return back()->with(compact('error'));
                }
            }

            //Log::Info($form);
            if($form->engineer_id!=$form->model()->engineer_id){
                $engineer = \App\Models\Engineer::findOrFail($form->engineer_id);
                // send whatsapp message to customer
                $this->sendWhatsappMessage("Hi {$customer->name}, Mr. {$engineer->name} (Engineer) is assigned to your case, He will visit your location at {$form->visit_date} {$form->visit_time}. \nThank You!", $customer->mobile);
                //return response("Ok");
            }else{
               Log::Info("Form engineer id" . $form->engineer_id);
               Log::Info("Model engineer id" . $form->model()->engineer_id);
               Log::Info("Message could not send");
            }
        });
        return $form;
    }

    /**
     * Sends a WhatsApp message  to user using
     * @param string $message Body of sms
     * @param string $recipient Number of recipient
     */
    public function sendWhatsappMessage(string $message, string $recipient)
    {
        $twilio_whatsapp_number = getenv('TWILIO_WHATSAPP_NUMBER');
        $account_sid = getenv("TWILIO_SID");
        $auth_token = getenv("TWILIO_AUTH_TOKEN");

        $client = new Client($account_sid, $auth_token);
        return $client->messages->create("whatsapp:+".$recipient, array('from' => "whatsapp:$twilio_whatsapp_number", 'body' => $message));
    }
}

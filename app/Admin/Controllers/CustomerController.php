<?php

namespace App\Admin\Controllers;

use App\Models\Areas;
use App\Models\Customer;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Admin\Actions\Customer\ImportCustomerAction;

class CustomerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Customer';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Customer());

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('mobile', __('Mobile'));
        $grid->column('alternate_mobile', __('Alternate mobile'));
        $grid->column('customer.title', __('Area'));
        $grid->column('address', __('Address'));
        $grid->column('machine_number', __('Machine number'));
        $grid->column('machine_code', __('Machine code'));
        $grid->column('machine_model', __('Machine model'));
        $grid->column('warranty_status', __('Warranty status'));
        $grid->column('date_of_inst', __('Date of inst'))->date('d/m/Y');
        $grid->column('month', __('Month'));
        $grid->column('status', __('Status'))->bool();
        $grid->column('last_service_date', __('Last Service'));
        $grid->column('updated_at', __('Updated at'))->date('DD/MM/YYYY');
        
        $grid->model()->orderBy('id','asc');
        $grid->filter(function($filter){
           $filter->disableIdFilter();
           $filter->like('area', __('Area'));
           $filter->like('mobile', __('Mobile'));
           //$filter->like('article.title', __('Category'));
        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new ImportCustomerAction());
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
        $show = new Show(Customer::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('mobile', __('Mobile'));
        $show->field('alternate_mobile', __('Alternate mobile'));
        $show->field('area', __('Area'));
        $show->field('address', __('Address'));
        $show->field('machine_number', __('Machine Number'));
        $show->field('machine_code', __('Machine Code'));
        $show->field('machine_model', __('Machine Model'));
        $show->field('warranty_status', __('Warranty Status'));
        $show->field('date_of_inst', __('Date of Installation'));
        $show->field('month', __('Month'));
        $show->field('status', __('Status'));
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
        $form = new Form(new Customer());

        $form->text('name', __('Name'));
        $form->text('mobile', __('Mobile'));
        $form->text('alternate_mobile', __('Alternate mobile'));
        $form->select('area', __("Select Area"))->options((new Areas())::selectOptions());
        $form->textarea('address', __('Address'));
        $form->text('machine_number', __('Machine number'));
        $form->text('machine_code', __('Machine code'));
        $form->text('machine_model', __('Machine model'));
        $form->text('warranty_status', __('Warranty status'));
        $form->date('date_of_inst', __('Date of inst'))->default(date('Y-m-d'));
        $form->text('month', __('Month'));
        $form->switch('status', __('Status'));

        return $form;
    }
}

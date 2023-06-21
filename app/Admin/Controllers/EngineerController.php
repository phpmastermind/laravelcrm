<?php

namespace App\Admin\Controllers;

use App\Models\Areas;
use App\Models\Engineer;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Layout\Content;
use Encore\Admin\Tree;

class EngineerController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Engineer';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Engineer());

        $grid->column('id', __('Id'));
        $grid->column('name', __('Name'));
        $grid->column('mobile', __('Mobile'));
        $grid->column('alternate_mobile', __('Alternate mobile'));
        $grid->column('engineer.title', __('Area'));
        $grid->column('address', __('Address'));
        $grid->column('status', __('Status'))->bool();
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(Engineer::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('mobile', __('Mobile'));
        $show->field('alternate_mobile', __('Alternate mobile'));
        $show->field('area', __('Area'));
        $show->field('address', __('Address'));
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
        $form = new Form(new Engineer());

        $form->text('name', __('Name'));
        $form->mobile('mobile', __('Mobile'))->options(['mask' => '9999999999']);
        $form->mobile('alternate_mobile', __('Alternate mobile'))->options(['mask' => '9999999999']);
        $form->select('area', __("Select Area"))->options((new Areas())::selectOptions());
        $form->textarea('address', __('Address'));
        $form->switch('status', __('Status'));

        return $form;
    }
}

<?php

namespace App\Admin\Controllers;

use App\Models\Areas;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Tree;

class AreasController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Area';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Areas());

        $grid->column('id', __('Id'));
        $grid->column('title', __('Area Name'));
        $grid->column('area_code', __('Area Code'));
        $grid->column('created_at', __('Created at'));
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
        $show = new Show(Areas::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Area name'));
        $show->field('area_code', __('Area code'));
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
        $form = new Form(new Areas());
        //$form->select('parent_id', __("Area"))->options((new Areas())::selectOptions());
        $form->text('title', __('Area name'));
        $form->text('area_code', __('Area code'));
        $form->number('order', __("Order"))->default(0);
        return $form;
    }
}

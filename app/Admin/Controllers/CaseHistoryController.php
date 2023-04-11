<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use \App\Models\CaseHistory;

class CaseHistoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'CaseHistory';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CaseHistory());

        $grid->column('id', __('Id'));
        $grid->column('case_id', __('Case id'));
        $grid->column('case_type', __('Case type'));
        $grid->column('case_status', __('Case status'));
        $grid->column('remarks', __('Remarks'));
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
        $show = new Show(CaseHistory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('case_id', __('Case id'));
        $show->field('case_type', __('Case type'));
        $show->field('case_status', __('Case status'));
        $show->field('remarks', __('Remarks'));
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
        $form = new Form(new CaseHistory());

        $form->number('case_id', __('Case id'));
        $form->number('case_type', __('Case type'));
        $form->text('case_status', __('Case status'));
        $form->textarea('remarks', __('Remarks'));

        return $form;
    }
}

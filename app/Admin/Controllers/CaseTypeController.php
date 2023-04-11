<?php

namespace App\Admin\Controllers;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use \App\Models\CaseType;
use Encore\Admin\Layout\Content;
use Encore\Admin\Tree;
class CaseTypeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'CaseType';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    /*protected function grid()
    {
        $grid = new Grid(new CaseType());

        $grid->column('id', __('Id'));
        $grid->column('parent_id', __('Parent id'));
        $grid->column('title', __('Title'));
        $grid->column('order', __('Order'));
        $grid->column('status', __('Status'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

        return $grid;
    }
*/
    public function index(Content $content)
    {
        $tree = new Tree(new CaseType);
        return $content
            ->header('Case Type')
            ->body($tree);
    }
    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(CaseType::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('parent_id', __('Parent id'));
        $show->field('title', __('Title'));
        $show->field('order', __('Order'));
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
        $form = new Form(new CaseType());

        $form->select('parent_id', __('Parent id'))->options((new CaseType)::selectOptions());
        $form->text('title', __('Title'));
        $form->number('order', __('Order'))->default(0);
        $form->switch('status', __('Status'));

        return $form;
    }
}

<?php
use Encore\Admin\Grid\Filter;
use Illuminate\Support\Facades\DB;

class OpenCaseFilter implements Filter
{
    public function apply($builder, $value)
    {
        $builder->where('status', '!=', 'CLOSE');
    }

    protected function build()
    {
        // Build the filter's view
        $this->filter->expand(); // To make the filter expanded by default
        $this->filter->disableIdFilter(); // To disable the default ID filter
    }

    public function options()
    {
        return [];
    }
}

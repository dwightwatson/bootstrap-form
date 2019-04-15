<?php

use Bnb\BootstrapForm\Type;

return [

    /*
    |--------------------------------------------------------------------------
    | Form type
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default form type for the open method. You have
    | the options of Type::HORIZONTAL, Type::VERTICAL and Type::INLINE.
    |
    */

    'type' => Type::VERTICAL,

    /*
    |--------------------------------------------------------------------------
    | Horizontal form default sizing
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default widths of the columns if you're using
    | the horizontal form type. You can use the Bootstrap grid classes as you
    | wish.
    |
    */

    'left_column_class' => 'col-sm-2 col-md-3',
    'right_column_class' => 'col-sm-3 col-md-9',

    'left_column_offset_class' => 'offset-sm-2 offset-md-3',

    /*
    |--------------------------------------------------------------------------
    | Required fields
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default suffix appended to the label and the
    | default form group class added to required fields.
    |
    */

    'label_required_mark' => '*',
    'group_required_class' => 'required',

    /*
    |--------------------------------------------------------------------------
    | Error output
    |--------------------------------------------------------------------------
    |
    | Here you may specify the whether all the errors of an input should be
    | displayed or just the first one.
    |
    */

    'show_all_errors' => false,

    'builder_class' => 'Bnb\BootstrapForm\BootstrapForm'

];

<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Auto ID integer append
|--------------------------------------------------------------------------
|
| Flag to instruct FF to add a progressive integer at the end of every
| element ID.
|
| If FALSE, fields with the same name will have the very same ID.
| Note that when adding multifields using add_checkboxes() or add_radiobuttons()
| methods, the correct naming is carried out automatically to avoid conflicts.
|
*/
$config['fluentform_creator']['auto_id_integer_append'] = FALSE;

/*
|--------------------------------------------------------------------------
| Error delimiter tag
|--------------------------------------------------------------------------
|
| HTML to be used as error message delimiter.
|
*/
$config['fluentform_validator']['error_delimiter_tag'] = 'p';

/*
|--------------------------------------------------------------------------
| Error delimiter classes (string)
|--------------------------------------------------------------------------
|
| Classes to be assigned to error delimiter tag (see above).
|
*/
$config['fluentform_validator']['error_delimiter_classes'] = 'errors global message';

/*
|--------------------------------------------------------------------------
| Default validation rules
|--------------------------------------------------------------------------
|
| For each field type it is possible to set custom validation rules
| so that there is no need to specify them if not when they must be customized.
|
*/
$config['fluentform_validator']['default_rules']['text']         = 'trim|xss_clean';
$config['fluentform_validator']['default_rules']['file']         = 'trim|xss_clean';
$config['fluentform_validator']['default_rules']['textarea']     = 'xss_clean';
$config['fluentform_validator']['default_rules']['password']     = 'required|xss_clean';
$config['fluentform_validator']['default_rules']['dropdown']     = 'xss_clean';
$config['fluentform_validator']['default_rules']['multiselect']  = 'xss_clean';
$config['fluentform_validator']['default_rules']['checkbox']     = 'xss_clean';
$config['fluentform_validator']['default_rules']['checkboxes']   = 'xss_clean';
$config['fluentform_validator']['default_rules']['radiobutton']  = 'xss_clean';
$config['fluentform_validator']['default_rules']['radiobuttons'] = 'xss_clean';

/*
|--------------------------------------------------------------------------
| Field wrapper tag
|--------------------------------------------------------------------------
|
| HTML tag that wraps field, label and error tag, if present.
| See "Error Position" parameter.
|
*/
$config['fluentform_renderer']['field_wrapper_tag'] = 'div';

/*
|--------------------------------------------------------------------------
| Field wrapper add field classes
|--------------------------------------------------------------------------
|
| Wether to add field classes to the field wrapper also.
|
*/
$config['fluentform_renderer']['field_wrapper_add_field_classes'] = FALSE;

/*
|--------------------------------------------------------------------------
| Field wrapper classes (string)
|--------------------------------------------------------------------------
|
| Classes to add the field wrapper.
|
*/
$config['fluentform_renderer']['field_wrapper_classes'] = 'field-wrapper';

/*
|--------------------------------------------------------------------------
| Field wrapper ID suffix
|--------------------------------------------------------------------------
|
| If the suffix is set and if not empty string, an id will be added to the
| wrapper in the form <field_id><id_suffix>. So for a field_id "foo" and a
| suffix "bar" the result will be "foobar".
|
*/
$config['fluentform_renderer']['field_wrapper_id_suffix'] = '_wrapper';

/*
|--------------------------------------------------------------------------
| Checkbox/Radiobutton default label position
|--------------------------------------------------------------------------
|
| Set a default position for labels of checkboxes and radiobuttons.
| This could be overwritten by "adder" method parameter.
|
| Possible values:
|   FF_R_CRB_LABEL_NONE: hide label
|   FF_R_CRB_LABEL_BEFORE: print label before element (es. Agree? [])
|   FF_R_CRB_LABEL_AFTER: print label after element (es. [] Agree)
|
*/
$config['fluentform_renderer']['crb_default_label_position'] = FF_R_CRB_LABEL_AFTER;

/*
|--------------------------------------------------------------------------
| Error show
|--------------------------------------------------------------------------
|
| Set if validation errors will be shown, and how.
|
| Possible values:
|   FF_R_ERROR_HIDE: hide all errors
|   FF_R_ERROR_SHOW_FORM: show errors in a single block at form level
|   FF_R_ERROR_SHOW_FIELD: show errors after each involved field
|
*/
$config['fluentform_renderer']['error_show'] = FF_R_ERROR_SHOW_FIELD;

/*
|--------------------------------------------------------------------------
| Error position
|--------------------------------------------------------------------------
|
| Set the position of error in relation with the holding elements.
|
| Possible values:
|   FF_R_ERRPOS_BEFOREOPEN: Before the opening form or field-wrapper tag
|   FF_R_ERRPOS_AFTEROPEN: After the opening form or field-wrapper tag
|   FF_R_ERRPOS_BEFORECLOSE: Before the closing form or field-wrapper tag
|   FF_R_ERRPOS_AFTERCLOSE: After the closing form or field-wrapper tag
|
*/
$config['fluentform_renderer']['error_position'] = FF_R_ERRPOS_BEFORECLOSE;

/*
|--------------------------------------------------------------------------
| Error classes (string)
|--------------------------------------------------------------------------
|
| Classes to add the error element tag.
|
*/
$config['fluentform_renderer']['error_classes'] = 'error';

/*
|--------------------------------------------------------------------------
| Error classes (string)
|--------------------------------------------------------------------------
|
| Classes to add to fields marked as "Required".
|
*/
$config['fluentform_renderer']['required_classes'] = 'required';

/*
|--------------------------------------------------------------------------
| Fill password fields
|--------------------------------------------------------------------------
|
| Fill password fields with assigned value or submitted value.
|
*/
$config['fluentform_renderer']['fill_password_value'] = FALSE;